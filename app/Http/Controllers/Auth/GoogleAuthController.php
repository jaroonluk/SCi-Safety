<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\AllowedEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function __construct(
        private readonly AllowedEmail $allowedEmail,
        private readonly AuditLogger $audit,
    ) {}

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'email', 'profile'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()
                ->route('login')
                ->with('error', 'ไม่สามารถยืนยันตัวตนกับ Google ได้ กรุณาลองอีกครั้ง');
        }

        $email = strtolower((string) $googleUser->getEmail());
        $accountType = $this->allowedEmail->accountType($email);

        if ($accountType === null) {
            $this->audit->record(null, 'auth.login_rejected', [
                'email' => $email,
                'reason' => 'email_missing_or_invalid',
            ], $request);

            return redirect()
                ->route('login')
                ->with('error', 'ไม่พบอีเมลจากบัญชี Google นี้ กรุณาเลือกบัญชีที่มีอีเมล และเข้าสู่ระบบอีกครั้ง');
        }

        $user = User::query()
            ->where(function ($query) use ($googleUser, $email): void {
                $query->where('google_id', $googleUser->getId())
                    ->orWhere('email', $email);
            })
            ->first();

        if ($user === null) {
            $user = new User([
                'role' => UserRole::Requester,
                'account_type' => $accountType,
            ]);
        }

        $user->fill([
            'google_id' => $googleUser->getId(),
            'name' => $googleUser->getName() ?: $email,
            'email' => $email,
            'avatar' => $googleUser->getAvatar(),
            'account_type' => $accountType,
            'email_verified_at' => $user->email_verified_at ?? now(),
            'last_login_at' => now(),
        ]);

        if ($this->isSuperAdmin($email)) {
            $user->role = UserRole::SuperAdmin;
        }

        $user->save();

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        $this->audit->record($user, 'auth.login', [
            'email' => $user->email,
            'account_type' => $user->account_type->value,
            'role' => $user->role->value,
        ], $request, $user->getMorphClass(), $user->id);

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user !== null) {
            $this->audit->record($user, 'auth.logout', [
                'email' => $user->email,
            ], $request, $user->getMorphClass(), $user->id);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function isSuperAdmin(string $email): bool
    {
        $allowed = config('sci.super_admin_emails', []);

        return in_array($email, $allowed, true);
    }
}
