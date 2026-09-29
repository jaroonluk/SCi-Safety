<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Enums\TechnicalResult;
use App\Enums\UserRole;
use App\Models\Camera;
use App\Models\CameraInspection;
use App\Models\CoverageGap;
use App\Models\ViewingRequest;
use App\Services\AuditLogger;
use App\Services\RequestWorkflow;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkflowController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly RequestWorkflow $workflow,
    ) {}

    public function inbox(): View
    {
        $status = match (auth()->user()->role) {
            UserRole::Director => RequestStatus::PendingDirector,
            UserRole::AssociateDean => RequestStatus::PendingDean,
            UserRole::PdpaCoordinator => RequestStatus::PendingPdpa,
            default => RequestStatus::Submitted,
        };

        return view('workflow.inbox', [
            'title' => 'คำขอที่รอการพิจารณา',
            'requests' => ViewingRequest::query()->where('status', $status)->latest()->get(),
        ]);
    }

    public function queue(): View
    {
        $requests = ViewingRequest::query()
            ->whereIn('status', [
                RequestStatus::Submitted,
                RequestStatus::MoreInfo,
                RequestStatus::Approved,
                RequestStatus::Scheduled,
                RequestStatus::Viewed,
                RequestStatus::NoFootage,
                RequestStatus::Rejected,
                RequestStatus::Closed,
            ])
            ->latest()
            ->get();

        $period = now()->format('Y-m');

        return view('workflow.queue', [
            'title' => 'คิวเจ้าหน้าที่',
            'requests' => $requests,
            'inspectionTotal' => Camera::query()->count(),
            'inspectionDone' => CameraInspection::query()->where('period', $period)->whereNotNull('result')->count(),
            'inspectionPeriod' => now()->format('m/Y'),
        ]);
    }

    public function technical(Request $request, int $viewingRequest): RedirectResponse
    {
        $record = ViewingRequest::query()->findOrFail($viewingRequest);
        abort_unless($record->status === RequestStatus::Submitted, 403);

        $data = $request->validate([
            'technical_result' => ['required', Rule::enum(TechnicalResult::class)],
            'technical_note' => ['required', 'string', 'max:2000'],
            'high_risk' => ['nullable', 'boolean'],
            'send_to_dean' => ['nullable', 'boolean'],
            'gap_priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
        ]);

        $result = TechnicalResult::from($data['technical_result']);
        if ($result->lacksFootage()) {
            $request->validate(['gap_priority' => ['required', Rule::in(['low', 'medium', 'high'])]]);
        }

        DB::transaction(function () use ($request, $record, $data, $result) {
            $highRisk = $request->boolean('high_risk');
            $sendToDean = $request->boolean('send_to_dean');
            $record->fill(['technical_result' => $result]);
            $next = $this->workflow->destinationAfterReview($record, $highRisk);
            $afterPdpa = null;
            if ($next === RequestStatus::PendingPdpa) {
                $afterPdpa = $sendToDean
                    ? RequestStatus::PendingDean->value
                    : $this->workflow->approvalDestination($record)->value;
            } elseif ($sendToDean && $next !== RequestStatus::NoFootage) {
                $next = RequestStatus::PendingDean;
            }

            $record->fill([
                'technical_result' => $result,
                'technical_note' => $data['technical_note'],
                'high_risk' => $highRisk,
                'workflow_after_pdpa' => $afterPdpa,
                'status' => $next,
            ])->save();

            if ($result->lacksFootage()) {
                CoverageGap::query()->create([
                    'viewing_request_id' => $record->id,
                    'building' => $record->building,
                    'location' => $record->location,
                    'reason' => $result->label(),
                    'priority' => $data['gap_priority'],
                    'created_by' => $request->user()->id,
                ]);
            }

            $this->audit->record($request->user(), 'request.technical_review', [
                'reference' => $record->reference,
                'result' => $result->value,
                'status' => $next->value,
            ], $request, $record->getMorphClass(), $record->id);
        });

        return back()->with('status', 'บันทึกผลการตรวจกล้องแล้ว');
    }

    public function decide(Request $request, int $viewingRequest): RedirectResponse
    {
        $record = ViewingRequest::query()->findOrFail($viewingRequest);
        $user = $request->user();
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject', 'more_info', 'escalate'])],
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        $status = $record->status;
        $allowed = ($status === RequestStatus::PendingDirector && $user->role === UserRole::Director)
            || ($status === RequestStatus::PendingDean && $user->role === UserRole::AssociateDean);
        abort_unless($allowed, 403);
        abort_if($data['decision'] === 'escalate' && $user->role !== UserRole::Director, 403);

        $next = match ($data['decision']) {
            'approve' => RequestStatus::Approved,
            'reject' => RequestStatus::Rejected,
            'more_info' => RequestStatus::MoreInfo,
            'escalate' => RequestStatus::PendingDean,
        };

        $record->update([
            'status' => $next,
            'resume_status' => $next === RequestStatus::MoreInfo ? $status->value : null,
            'technical_note' => trim(($record->technical_note ? $record->technical_note."\n" : '').$data['comment']),
        ]);
        $this->audit->record($user, 'request.decided', [
            'reference' => $record->reference,
            'decision' => $data['decision'],
            'comment' => $data['comment'],
        ], $request, $record->getMorphClass(), $record->id);

        return back()->with('status', 'บันทึกคำวินิจฉัยแล้ว');
    }

    public function pdpa(Request $request, int $viewingRequest): RedirectResponse
    {
        $record = ViewingRequest::query()->findOrFail($viewingRequest);
        abort_unless($record->status === RequestStatus::PendingPdpa, 403);

        $data = $request->validate(['pdpa_opinion' => ['required', 'string', 'max:2000']]);
        $next = RequestStatus::from($record->workflow_after_pdpa ?: RequestStatus::Submitted->value);
        $record->update([
            'pdpa_opinion' => $data['pdpa_opinion'],
            'status' => $next,
        ]);
        $this->audit->record($request->user(), 'request.pdpa_opinion', [
            'reference' => $record->reference,
        ], $request, $record->getMorphClass(), $record->id);

        return back()->with('status', 'บันทึกความเห็นด้านข้อมูลส่วนบุคคลแล้ว');
    }

    public function moreInfo(Request $request, int $viewingRequest): RedirectResponse
    {
        $record = ViewingRequest::query()->findOrFail($viewingRequest);
        abort_unless($record->status === RequestStatus::Submitted, 403);

        $data = $request->validate(['more_info_note' => ['required', 'string', 'max:2000']]);
        $record->update([
            'status' => RequestStatus::MoreInfo,
            'resume_status' => RequestStatus::Submitted->value,
            'technical_note' => trim(($record->technical_note ? $record->technical_note."\n" : '').$data['more_info_note']),
        ]);
        $this->audit->record($request->user(), 'request.more_info', [
            'reference' => $record->reference,
        ], $request, $record->getMorphClass(), $record->id);

        return back()->with('status', 'ขอข้อมูลเพิ่มจากผู้ยื่นคำขอแล้ว');
    }

    public function schedule(Request $request, int $viewingRequest): RedirectResponse
    {
        $record = ViewingRequest::query()->findOrFail($viewingRequest);
        abort_unless($record->status === RequestStatus::Approved, 403);

        $data = $request->validate([
            'appointment_at' => ['required', 'date', 'after:now'],
            'appointment_place' => ['required', 'string', 'max:200'],
            'participants' => ['required', 'string', 'max:500'],
        ]);

        $record->update([
            'appointment_at' => $data['appointment_at'],
            'appointment_place' => $data['appointment_place'],
            'participants' => $data['participants'],
            'supervisor_id' => $request->user()->id,
            'status' => RequestStatus::Scheduled,
        ]);
        $this->audit->record($request->user(), 'appointment.scheduled', [
            'reference' => $record->reference,
            'place' => $data['appointment_place'],
        ], $request, $record->getMorphClass(), $record->id);

        return back()->with('status', 'นัดหมายดูภาพแล้ว การดูภาพต้องทำต่อหน้าเจ้าหน้าที่ และห้ามคัดลอกไฟล์');
    }

    public function viewing(Request $request, int $viewingRequest): RedirectResponse
    {
        $record = ViewingRequest::query()->findOrFail($viewingRequest);
        abort_unless($record->status === RequestStatus::Scheduled && $record->appointment_confirmed_at !== null, 403);

        $data = $request->validate([
            'viewing_started_at' => ['required', 'date'],
            'viewing_ended_at' => ['required', 'date', 'after:viewing_started_at'],
            'viewing_summary' => ['required', 'string', 'max:2000'],
            'controlled' => ['accepted'],
        ]);

        $record->update([
            'viewing_started_at' => $data['viewing_started_at'],
            'viewing_ended_at' => $data['viewing_ended_at'],
            'viewing_summary' => $data['viewing_summary'],
            'viewing_confirmed_at' => now(),
            'status' => RequestStatus::Viewed,
        ]);
        $this->audit->record($request->user(), 'viewing.recorded', [
            'reference' => $record->reference,
        ], $request, $record->getMorphClass(), $record->id);

        return back()->with('status', 'บันทึกการดูภาพภายใต้การควบคุมแล้ว');
    }

    public function close(Request $request, int $viewingRequest): RedirectResponse
    {
        $record = ViewingRequest::query()->findOrFail($viewingRequest);
        abort_unless(in_array($record->status, [RequestStatus::Viewed, RequestStatus::NoFootage, RequestStatus::Rejected], true), 403);
        $record->update(['status' => RequestStatus::Closed]);
        $this->audit->record($request->user(), 'request.closed', ['reference' => $record->reference], $request, $record->getMorphClass(), $record->id);

        return back()->with('status', 'ปิดคำขอแล้ว');
    }
}
