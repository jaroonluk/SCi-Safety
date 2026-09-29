<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\OfficerCover;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserRoleController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(): View
    {
        return view('admin.users', [
            'users' => User::query()->orderBy('name')->get(),
            'roles' => UserRole::cases(),
            'covers' => OfficerCover::query()->latest()->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        $next = UserRole::from($data['role']);
        if ($user->role === UserRole::SuperAdmin && $next !== UserRole::SuperAdmin) {
            $remaining = User::query()->where('role', UserRole::SuperAdmin)->whereKeyNot($user->id)->count();
            if ($remaining === 0) {
                return back()->with('error', 'ต้องมีผู้ดูแลระบบสูงสุดอย่างน้อยหนึ่งคน');
            }
        }

        $before = $user->role->value;
        $user->update(['role' => $next]);
        $this->audit->record($request->user(), 'user.role_changed', [
            'email' => $user->email,
            'before' => $before,
            'after' => $next->value,
        ], $request, $user->getMorphClass(), $user->id);

        return back()->with('status', 'กำหนดสิทธิ์ของ '.$user->name.' เป็น '.$next->label().' แล้ว');
    }

    public function cover(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'officer_user_id' => ['required', 'exists:users,id', 'different:backup_user_id'],
            'backup_user_id' => ['required', 'exists:users,id'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'area' => ['nullable', 'string', 'max:120'],
        ]);

        $cover = OfficerCover::query()->create($data);
        $this->audit->record($request->user(), 'officer.backup_assigned', $data, $request, $cover->getMorphClass(), $cover->id);

        return back()->with('status', 'กำหนดเจ้าหน้าที่สำรองแล้ว ระบบจะมอบหมายงานในช่วงวันที่ระบุ');
    }
}
