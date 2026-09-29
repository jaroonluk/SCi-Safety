<?php

namespace App\Http\Controllers;

use App\Models\Camera;
use App\Models\CameraInspection;
use App\Models\CoverageGap;
use App\Models\RepairTicket;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CameraController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(): View
    {
        $this->openMonthlyRound();

        return view('cameras.index', [
            'cameras' => Camera::query()->orderBy('code')->get(),
            'inspections' => CameraInspection::query()->with(['camera', 'ticket'])->where('period', now()->format('Y-m'))->get(),
            'tickets' => RepairTicket::query()->with('inspection.camera')->latest()->get(),
            'gaps' => CoverageGap::query()->latest()->limit(30)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:cameras,code'],
            'name' => ['required', 'string', 'max:160'],
            'building' => ['required', 'string', 'max:120'],
            'floor' => ['nullable', 'string', 'max:40'],
            'coverage' => ['required', 'string', 'max:200'],
            'status' => ['required', Rule::in(['normal', 'broken', 'waiting_repair', 'closed'])],
            'retention_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $camera = Camera::query()->create($data);
        $this->audit->record($request->user(), 'camera.created', ['code' => $camera->code], $request, $camera->getMorphClass(), $camera->id);

        return back()->with('status', 'เพิ่มกล้อง '.$camera->code.' แล้ว');
    }

    public function gap(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'building' => ['required', 'string', 'max:120'],
            'location' => ['required', 'string', 'max:200'],
            'reason' => ['required', 'string', 'max:200'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
        ]);

        $gap = CoverageGap::query()->create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);
        $this->audit->record($request->user(), 'coverage_gap.registered', $data, $request, $gap->getMorphClass(), $gap->id);

        return back()->with('status', 'บันทึกจุดเสี่ยงที่ไม่มีกล้องแล้ว');
    }

    public function inspect(Request $request, CameraInspection $inspection): RedirectResponse
    {
        $data = $request->validate([
            'result' => ['required', Rule::in(['normal', 'shifted', 'blur', 'no_signal'])],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $inspection->update([
            'result' => $data['result'],
            'note' => $data['note'] ?? null,
            'inspected_by' => $request->user()->id,
        ]);

        if ($data['result'] !== 'normal' && $inspection->ticket === null) {
            RepairTicket::query()->create([
                'camera_inspection_id' => $inspection->id,
                'status' => 'open',
                'note' => $data['note'] ?? $inspection->resultLabel(),
            ]);
            $inspection->camera->update(['status' => 'waiting_repair']);
        }

        $this->audit->record($request->user(), 'camera.inspected', [
            'code' => $inspection->camera->code,
            'result' => $data['result'],
        ], $request, $inspection->getMorphClass(), $inspection->id);

        return back()->with('status', 'บันทึกผลตรวจประจำเดือนแล้ว');
    }

    public function ticket(Request $request, RepairTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'done'])],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        $ticket->update($data);
        if ($data['status'] === 'done') {
            $ticket->inspection->camera->update(['status' => 'normal']);
        }
        $this->audit->record($request->user(), 'repair.updated', ['status' => $data['status']], $request, $ticket->getMorphClass(), $ticket->id);

        return back()->with('status', 'อัปเดตงานซ่อมแล้ว');
    }

    private function openMonthlyRound(): void
    {
        $period = now()->format('Y-m');
        foreach (Camera::query()->pluck('id') as $cameraId) {
            CameraInspection::query()->firstOrCreate([
                'camera_id' => $cameraId,
                'period' => $period,
            ]);
        }
    }
}
