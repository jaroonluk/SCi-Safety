<?php

namespace App\Http\Controllers;

use App\Enums\IncidentType;
use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\PrivacyNotice;
use App\Models\ViewingRequest;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ViewingRequestController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function create(): View
    {
        return view('requests.create', [
            'incidentTypes' => IncidentType::cases(),
            'notice' => PrivacyNotice::current(),
            'onBehalf' => $this->requestUserCanRecordForOthers(),
        ]);
    }

    private function requestUserCanRecordForOthers(): bool
    {
        return auth()->user()?->hasRole(UserRole::CctvAdmin) ?? false;
    }

    public function store(Request $request): RedirectResponse
    {
        $notice = PrivacyNotice::current();
        $onBehalf = $request->boolean('on_behalf') && $request->user()->hasRole(UserRole::CctvAdmin);

        $data = $request->validate([
            'requester_kind' => ['required', Rule::in(['student_sci', 'student_other', 'staff_sci', 'staff_kku', 'external_person', 'external_org'])],
            'identifier' => ['nullable', 'string', 'max:40'],
            'affiliation' => ['nullable', 'string', 'max:160'],
            'phone' => ['required', 'string', 'max:30'],
            'incident_type' => ['required', Rule::enum(IncidentType::class)],
            'purpose' => ['required', 'string', 'max:500'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['required', 'date', 'after_or_equal:started_at'],
            'building' => ['required', 'string', 'max:120'],
            'floor' => ['nullable', 'string', 'max:40'],
            'location' => ['required', 'string', 'max:200'],
            'landmark' => ['nullable', 'string', 'max:200'],
            'urgency' => ['required', Rule::in(['normal', 'urgent'])],
            'details' => ['required', 'string', 'max:2000'],
            'external_organization' => ['required_if:incident_type,external', 'nullable', 'string', 'max:160'],
            'official_letter_no' => ['required_if:incident_type,external', 'nullable', 'string', 'max:80'],
            'external_contact_name' => ['required_if:incident_type,external', 'nullable', 'string', 'max:160'],
            'subject_name' => ['required_if:incident_type,exam', 'nullable', 'string', 'max:160'],
            'subject_code' => ['required_if:incident_type,exam', 'nullable', 'string', 'max:40'],
            'exam_room' => ['required_if:incident_type,exam', 'nullable', 'string', 'max:80'],
            'intake_channel' => [Rule::requiredIf($onBehalf), 'nullable', 'string', 'max:80'],
            'informant_name' => [Rule::requiredIf($onBehalf), 'nullable', 'string', 'max:160'],
            'on_behalf_reason' => [Rule::requiredIf($onBehalf), 'nullable', 'string', 'max:1000'],
            'privacy_notice_version' => ['required', 'in:'.$notice->version],
            'privacy_accepted' => ['accepted'],
        ]);

        $viewingRequest = DB::transaction(function () use ($request, $data, $onBehalf, $notice) {
            $record = ViewingRequest::query()->create([
                'user_id' => $request->user()->id,
                'reference' => $this->nextReference(),
                'incident_type' => $data['incident_type'],
                'purpose' => $data['purpose'],
                'started_at' => $data['started_at'],
                'ended_at' => $data['ended_at'],
                'building' => $data['building'],
                'floor' => $data['floor'] ?? null,
                'location' => $data['location'],
                'urgency' => $data['urgency'],
                'details' => $data['details'],
                'requester_kind' => $data['requester_kind'],
                'identifier' => $data['identifier'] ?? null,
                'affiliation' => $data['affiliation'] ?? null,
                'phone' => $data['phone'],
                'landmark' => $data['landmark'] ?? null,
                'external_organization' => $data['external_organization'] ?? null,
                'official_letter_no' => $data['official_letter_no'] ?? null,
                'external_contact_name' => $data['external_contact_name'] ?? null,
                'subject_name' => $data['subject_name'] ?? null,
                'subject_code' => $data['subject_code'] ?? null,
                'exam_room' => $data['exam_room'] ?? null,
                'on_behalf' => $onBehalf,
                'intake_channel' => $onBehalf ? $data['intake_channel'] : null,
                'received_at' => $onBehalf ? now() : null,
                'informant_name' => $onBehalf ? $data['informant_name'] : null,
                'on_behalf_reason' => $onBehalf ? $data['on_behalf_reason'] : null,
                'recorded_by_id' => $onBehalf ? $request->user()->id : null,
                'status' => RequestStatus::Submitted,
                'privacy_notice_version' => $notice->version,
                'privacy_accepted_at' => now(),
            ]);

            $this->audit->record(
                $request->user(),
                'request.submitted',
                ['reference' => $record->reference],
                $request,
                $record->getMorphClass(),
                $record->id,
            );

            return $record;
        });

        return redirect()
            ->route('requests.show', $viewingRequest)
            ->with('status', 'ยื่นคำขอแล้ว เลขที่ '.$viewingRequest->reference);
    }

    public function index(Request $request): View
    {
        return view('requests.index', [
            'requests' => $this->ownRequests($request),
        ]);
    }

    public function appointments(Request $request): View
    {
        return view('requests.appointments', [
            'requests' => $this->appointmentRequests($request),
        ]);
    }

    public function show(Request $request, int $viewingRequest): View
    {
        $record = $this->visibleRequest($request, $viewingRequest);

        return view('requests.show', [
            'viewingRequest' => $record,
            'technicalResults' => \App\Enums\TechnicalResult::cases(),
        ]);
    }

    public function supplement(Request $request, int $viewingRequest): RedirectResponse
    {
        $record = ViewingRequest::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($viewingRequest);
        abort_unless($record->status === RequestStatus::MoreInfo, 403);

        $data = $request->validate(['supplement_note' => ['required', 'string', 'max:2000']]);
        $next = $record->resume_status
            ? RequestStatus::from($record->resume_status)
            : RequestStatus::Submitted;
        $record->update([
            'supplement_note' => $data['supplement_note'],
            'status' => $next,
            'resume_status' => null,
        ]);
        $this->audit->record($request->user(), 'request.supplemented', ['reference' => $record->reference], $request, $record->getMorphClass(), $record->id);

        return back()->with('status', 'ส่งข้อมูลเพิ่มแล้ว');
    }

    public function confirmAppointment(Request $request, int $viewingRequest): RedirectResponse
    {
        $record = ViewingRequest::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($viewingRequest);
        abort_unless($record->status === RequestStatus::Scheduled, 403);

        $record->update(['appointment_confirmed_at' => now()]);
        $this->audit->record($request->user(), 'appointment.confirmed', ['reference' => $record->reference], $request, $record->getMorphClass(), $record->id);

        return back()->with('status', 'ยืนยันการนัดหมายแล้ว');
    }

    private function visibleRequest(Request $request, int $id): ViewingRequest
    {
        $record = ViewingRequest::query()->findOrFail($id);
        $user = $request->user();
        $staff = $user->hasRole(
            UserRole::CctvAdmin,
            UserRole::Director,
            UserRole::AssociateDean,
            UserRole::PdpaCoordinator,
            UserRole::SuperAdmin,
        );

        abort_unless($staff || $record->user_id === $user->id, 404);

        return $record;
    }

    private function ownRequests(Request $request)
    {
        return ViewingRequest::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();
    }

    private function appointmentRequests(Request $request)
    {
        return ViewingRequest::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', [
                RequestStatus::Approved,
                RequestStatus::Scheduled,
                RequestStatus::Viewed,
                RequestStatus::Closed,
            ])
            ->latest()
            ->get();
    }

    private function nextReference(): string
    {
        $prefix = 'SCI-'.now()->format('Ymd').'-';
        $latest = ViewingRequest::query()
            ->where('reference', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('reference');

        $sequence = $latest ? ((int) substr($latest, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
