<?php

namespace App\Http\Controllers;

use App\Enums\IncidentType;
use App\Enums\RequestStatus;
use App\Models\AuditLog;
use App\Models\Camera;
use App\Models\CoverageGap;
use App\Models\RepairTicket;
use App\Models\ViewingRequest;
use App\Models\WorkflowRule;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        $requests = ViewingRequest::query()->get();
        $closed = $requests->whereIn('status', [RequestStatus::Closed, RequestStatus::Viewed, RequestStatus::Rejected, RequestStatus::NoFootage]);
        $approved = $requests->whereIn('status', [RequestStatus::Approved, RequestStatus::Scheduled, RequestStatus::Viewed, RequestStatus::Closed]);
        $decided = $approved->count() + $requests->where('status', RequestStatus::Rejected)->count();

        return view('reports.index', [
            'total' => $requests->count(),
            'pending' => $requests->whereIn('status', [RequestStatus::Submitted, RequestStatus::PendingDirector, RequestStatus::PendingDean, RequestStatus::PendingPdpa, RequestStatus::MoreInfo])->count(),
            'approvalRate' => $decided === 0 ? 0 : (int) round(($approved->count() / $decided) * 100),
            'averageDays' => $this->averageDays($closed),
            'gaps' => CoverageGap::query()->latest()->limit(20)->get(),
            'cameras' => Camera::query()->count(),
            'openTickets' => RepairTicket::query()->where('status', '!=', 'done')->count(),
        ]);
    }

    public function export(): StreamedResponse
    {
        $rows = ViewingRequest::query()->with('user')->latest()->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['เลขที่', 'ประเภท', 'สถานะ', 'อาคาร', 'ผู้ยื่น', 'อีเมล']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->reference,
                    $row->incident_type->label(),
                    $row->status->label(),
                    $row->building,
                    $this->maskName($row->user->name),
                    $this->maskEmail($row->user->email),
                ]);
            }
            fclose($out);
        }, 'sci-safety-masked.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function audit(): View
    {
        return view('admin.audit', [
            'logs' => AuditLog::query()->with('user')->latest('id')->limit(100)->get(),
        ]);
    }

    public function rules(): View
    {
        $order = array_flip(array_map(fn (IncidentType $type) => $type->value, IncidentType::cases()));

        return view('admin.rules', [
            'rules' => WorkflowRule::query()->get()->sortBy(fn (WorkflowRule $rule) => $order[$rule->incident_type] ?? 99)->values(),
        ]);
    }

    public function updateRule(Request $request, WorkflowRule $rule): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate(['approver' => ['required', 'in:admin,director,dean']]);
        $before = $rule->approver;
        $rule->update($data);
        app(\App\Services\AuditLogger::class)->record($request->user(), 'workflow.updated', [
            'incident_type' => $rule->incident_type,
            'before' => $before,
            'after' => $rule->approver,
        ], $request, $rule->getMorphClass(), $rule->id);

        return back()->with('status', 'ปรับเส้นทางคำขอแล้ว');
    }

    private function averageDays($closed): int
    {
        if ($closed->isEmpty()) {
            return 0;
        }

        $days = $closed->avg(fn (ViewingRequest $request) => $request->created_at->diffInDays($request->updated_at));

        return (int) round($days);
    }

    private function maskName(string $name): string
    {
        $first = mb_substr($name, 0, 1);

        return $first.str_repeat('*', max(2, mb_strlen($name) - 1));
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return mb_substr($local, 0, 1).'***@'.$domain;
    }
}
