<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\Impersonation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ImpersonationController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function start(Request $request, User $user): RedirectResponse
    {
        abort_unless(Impersonation::enabled(), 404);
        abort_if(Impersonation::active(), 403, 'กำลังทดสอบสิทธิ์หรือเข้าใช้งานแทนอยู่แล้ว');

        $actor = $request->user();
        abort_unless($actor !== null && $actor->realRole() === UserRole::SuperAdmin, 403);
        abort_if($actor->is($user), 422, 'ไม่สามารถเข้าใช้งานแทนตนเองได้');

        $request->session()->put(Impersonation::SESSION_KEY, $actor->id);
        $request->session()->forget(Impersonation::ROLE_SESSION_KEY);
        Auth::login($user);
        $request->session()->regenerate();

        $this->audit->record($actor, 'auth.impersonation_started', [
            'mode' => 'user',
            'actor_email' => $actor->email,
            'target_email' => $user->email,
            'target_role' => $user->realRole()->value,
        ], $request, $user->getMorphClass(), $user->id);

        return redirect()
            ->route('home')
            ->with('status', 'เข้าใช้งานแทน '.$user->name.' ('.$user->role->label().') แล้ว — ใช้ระหว่างทดสอบระบบเท่านั้น');
    }

    public function startRole(Request $request): RedirectResponse
    {
        abort_unless(Impersonation::enabled(), 404);

        $actor = $request->user();
        abort_unless($actor !== null && $actor->realRole() === UserRole::SuperAdmin, 403);
        abort_if(Impersonation::isUserImpersonation(), 403, 'กำลังเข้าใช้งานแทนผู้ใช้อยู่ กรุณากลับสู่บัญชีผู้ดูแลก่อน');

        $data = $request->validate([
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        $role = UserRole::from($data['role']);

        $request->session()->put(Impersonation::SESSION_KEY, $actor->id);
        $request->session()->put(Impersonation::ROLE_SESSION_KEY, $role->value);

        $this->audit->record($actor, 'auth.impersonation_started', [
            'mode' => 'role',
            'actor_email' => $actor->email,
            'target_role' => $role->value,
        ], $request, $actor->getMorphClass(), $actor->id);

        return redirect()
            ->route('home')
            ->with('status', 'กำลังทดสอบสิทธิ์ '.$role->label().' — เมนูและการอนุญาตจะตรงกับสิทธินี้');
    }

    public function leave(Request $request): RedirectResponse
    {
        abort_unless(Impersonation::active(), 403);

        $adminId = $request->session()->get(Impersonation::SESSION_KEY);
        $wasRolePreview = Impersonation::isRolePreview();
        $previewRole = Impersonation::actingAsRole();
        $target = $request->user();

        Impersonation::clear($request->session());

        $admin = User::query()->find($adminId);
        abort_unless($admin !== null && $admin->realRole() === UserRole::SuperAdmin, 403);

        if (! $wasRolePreview) {
            Auth::login($admin);
            $request->session()->regenerate();
        }

        $this->audit->record($admin, 'auth.impersonation_stopped', [
            'mode' => $wasRolePreview ? 'role' : 'user',
            'actor_email' => $admin->email,
            'target_email' => $wasRolePreview ? $admin->email : $target?->email,
            'target_role' => $wasRolePreview ? $previewRole?->value : $target?->realRole()->value,
        ], $request, ($wasRolePreview ? $admin : $target)?->getMorphClass(), ($wasRolePreview ? $admin : $target)?->id);

        return redirect()
            ->route('admin.users')
            ->with('status', 'กลับสู่บัญชีผู้ดูแลระบบสูงสุดแล้ว');
    }
}
