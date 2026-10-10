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
            default => RequestStatus::Submitted,
        };

        return view('workflow.inbox', [
            'title' => 'คำขอที่รอการพิจารณา',
            'requests' => ViewingRequest::query()->where('status', $status)->latest()->get(),
        ]);
    }

    public function queue(Request $request): View
    {
        $filter = $request->string('filter')->toString() ?: 'all';
        $search = trim($request->string('q')->toString());
        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $base = ViewingRequest::query()->latest();

        $counts = [
            'all' => (clone $base)->count(),
            'new' => (clone $base)->where('status', RequestStatus::Submitted)->count(),
            'mine' => (clone $base)->whereIn('status', [
                RequestStatus::Submitted,
                RequestStatus::MoreInfo,
                RequestStatus::Approved,
                RequestStatus::Scheduled,
                RequestStatus::Viewed,
                RequestStatus::Rejected,
                RequestStatus::NoFootage,
            ])->count(),
            'director' => (clone $base)->where('status', RequestStatus::PendingDirector)->count(),
            'dean' => (clone $base)->where('status', RequestStatus::PendingDean)->count(),
            'urgent' => (clone $base)->where('urgency', 'urgent')
                ->whereNotIn('status', [RequestStatus::Closed])->count(),
            'close' => (clone $base)->whereIn('status', [
                RequestStatus::Viewed,
                RequestStatus::Rejected,
                RequestStatus::NoFootage,
            ])->count(),
            'closed' => (clone $base)->where('status', RequestStatus::Closed)->count(),
        ];

        $query = ViewingRequest::query()->latest();

        $query = match ($filter) {
            'new' => $query->where('status', RequestStatus::Submitted),
            'mine' => $query->whereIn('status', [
                RequestStatus::Submitted,
                RequestStatus::MoreInfo,
                RequestStatus::Approved,
                RequestStatus::Scheduled,
                RequestStatus::Viewed,
                RequestStatus::Rejected,
                RequestStatus::NoFootage,
            ]),
            'director' => $query->where('status', RequestStatus::PendingDirector),
            'dean' => $query->where('status', RequestStatus::PendingDean),
            'urgent' => $query->where('urgency', 'urgent')->whereNotIn('status', [RequestStatus::Closed]),
            'close' => $query->whereIn('status', [
                RequestStatus::Viewed,
                RequestStatus::Rejected,
                RequestStatus::NoFootage,
            ]),
            'closed' => $query->where('status', RequestStatus::Closed),
            default => $query,
        };

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('building', 'like', '%'.$search.'%')
                    ->orWhere('location', 'like', '%'.$search.'%')
                    ->orWhere('purpose', 'like', '%'.$search.'%');
            });
        }

        $period = now()->format('Y-m');

        return view('workflow.queue', [
            'title' => 'ตรวจสอบคำขอ',
            'requests' => $query->paginate($perPage)->withQueryString(),
            'filter' => $filter,
            'search' => $search,
            'perPage' => $perPage,
            'counts' => $counts,
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
            'decision' => ['required', Rule::in(['approve', 'reject'])],
            'reject_reason' => ['nullable', 'string', 'max:2000'],
            'gap_priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
        ]);

        if ($data['decision'] === 'reject') {
            $request->validate(['reject_reason' => ['required', 'string', 'max:2000']]);
        }

        $result = TechnicalResult::from($data['technical_result']);
        if ($result->lacksFootage()) {
            $request->validate(['gap_priority' => ['required', Rule::in(['low', 'medium', 'high'])]]);
        }

        DB::transaction(function () use ($request, $record, $data, $result) {
            $decision = $data['decision'];

            if ($decision === 'reject') {
                $note = trim($data['technical_note']."\nเหตุผลที่ไม่เห็นชอบ: ".$data['reject_reason']);
                $record->fill([
                    'technical_result' => $result,
                    'technical_note' => $note,
                    'officer_decision' => 'reject',
                    'officer_reviewed_by_id' => $request->user()->id,
                    'high_risk' => false,
                    'status' => RequestStatus::Rejected,
                ])->save();
            } else {
                $record->fill(['technical_result' => $result]);
                $next = $this->workflow->destinationAfterReview($record);

                $record->fill([
                    'technical_result' => $result,
                    'technical_note' => $data['technical_note'],
                    'officer_decision' => 'approve',
                    'officer_reviewed_by_id' => $request->user()->id,
                    'high_risk' => false,
                    'workflow_after_pdpa' => null,
                    'status' => $next,
                ])->save();
            }

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
                'decision' => $decision,
                'status' => $record->status->value,
            ], $request, $record->getMorphClass(), $record->id);
        });

        $message = $data['decision'] === 'reject'
            ? 'บันทึกว่าไม่เห็นชอบคำขอแล้ว'
            : 'บันทึกผลตรวจและส่งเรื่องให้ผู้อำนวยการกองพิจารณาแล้ว';

        return back()->with('status', $message);
    }

    public function decide(Request $request, int $viewingRequest): RedirectResponse
    {
        $record = ViewingRequest::query()->findOrFail($viewingRequest);
        $user = $request->user();
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject', 'escalate'])],
            'comment' => [
                Rule::requiredIf(fn () => $request->input('decision') === 'reject'),
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $status = $record->status;
        $isDirector = $status === RequestStatus::PendingDirector && $user->role === UserRole::Director;
        $isDean = $status === RequestStatus::PendingDean && $user->role === UserRole::AssociateDean;
        abort_unless($isDirector || $isDean, 403);
        abort_if($data['decision'] === 'escalate' && ! $isDirector, 403);

        $next = match ($data['decision']) {
            'approve' => RequestStatus::Approved,
            'reject' => RequestStatus::Rejected,
            'escalate' => RequestStatus::PendingDean,
        };

        $comment = filled($data['comment'] ?? null) ? trim($data['comment']) : null;

        $updates = [
            'status' => $next,
            'resume_status' => null,
        ];

        if ($isDirector) {
            $updates['director_note'] = $comment;
            $updates['director_reviewed_by_id'] = $user->id;
        } else {
            $updates['dean_note'] = $comment;
            $updates['dean_reviewed_by_id'] = $user->id;
        }

        $record->update($updates);
        $this->audit->record($user, 'request.decided', [
            'reference' => $record->reference,
            'decision' => $data['decision'],
            'comment' => $comment,
            'role' => $user->role->value,
        ], $request, $record->getMorphClass(), $record->id);

        $flash = match ($data['decision']) {
            'approve' => 'อนุมัติคำขอแล้ว เจ้าหน้าที่สามารถนัดดูภาพได้',
            'reject' => 'บันทึกว่าไม่อนุมัติคำขอแล้ว',
            'escalate' => 'ส่งเรื่องให้รองคณบดีฝ่ายบริหารพิจารณาแล้ว',
        };

        return back()->with('status', $flash);
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

        $previous = $record->status->value;
        $record->update(['status' => RequestStatus::Closed]);
        $this->audit->record($request->user(), 'request.closed', [
            'reference' => $record->reference,
            'previous_status' => $previous,
        ], $request, $record->getMorphClass(), $record->id);

        return redirect()
            ->route('queue')
            ->with('status', 'ปิดคำขอ '.$record->reference.' แล้ว');
    }
}
