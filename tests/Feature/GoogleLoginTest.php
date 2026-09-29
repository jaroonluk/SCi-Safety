<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_the_login_rules(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('@kkumail.com')
            ->assertSee('@kku.ac.th')
            ->assertSee('บัญชี Google')
            ->assertSee('ไม่มีการดาวน์โหลดหรือส่งมอบไฟล์ภาพ');
    }

    public function test_dashboard_requires_login(): void
    {
        $user = User::factory()->create([
            'email' => 'staff@kku.ac.th',
            'account_type' => AccountType::Staff,
        ]);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('staff@kku.ac.th')
            ->assertSee('บุคลากร มข.');
    }

    public function test_callback_rejects_an_account_without_a_valid_email(): void
    {
        $this->fakeGoogleUser('not-an-email');

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login_rejected',
        ]);
    }

    public function test_student_staff_and_external_accounts_can_sign_in_as_requesters(): void
    {
        $this->fakeGoogleUser('student@kkumail.com', '1101', 'Student One');
        $this->get('/auth/google/callback')->assertRedirect(route('home'));

        $student = User::query()->where('email', 'student@kkumail.com')->first();
        $this->assertSame(AccountType::Student, $student->account_type);
        $this->assertSame(UserRole::Requester, $student->role);
        $this->assertAuthenticatedAs($student);

        auth()->logout();

        $this->fakeGoogleUser('officer@kku.ac.th', '2202', 'Officer');
        $this->get('/auth/google/callback')->assertRedirect(route('home'));

        $staff = User::query()->where('email', 'officer@kku.ac.th')->first();
        $this->assertSame(AccountType::Staff, $staff->account_type);

        auth()->logout();

        $this->fakeGoogleUser('visitor@gmail.com', '3304', 'Visitor');
        $this->get('/auth/google/callback')->assertRedirect(route('home'));

        $external = User::query()->where('email', 'visitor@gmail.com')->first();
        $this->assertSame(AccountType::External, $external->account_type);
        $this->assertSame(UserRole::Requester, $external->role);
    }

    public function test_repeat_login_does_not_downgrade_an_assigned_role(): void
    {
        User::factory()->create([
            'email' => 'officer@kku.ac.th',
            'google_id' => '3303',
            'role' => UserRole::CctvAdmin,
            'account_type' => AccountType::Staff,
        ]);

        $this->fakeGoogleUser('officer@kku.ac.th', '3303', 'Officer');
        $this->get('/auth/google/callback')->assertRedirect(route('home'));

        $this->assertSame(
            UserRole::CctvAdmin,
            User::query()->where('email', 'officer@kku.ac.th')->first()->role
        );
    }

    public function test_audit_log_cannot_be_changed(): void
    {
        $log = AuditLog::query()->create([
            'action' => 'auth.login',
            'properties' => ['email' => 'student@kku.ac.th'],
        ]);

        $this->expectException(\RuntimeException::class);
        $log->update(['action' => 'tampered']);
    }

    private function fakeGoogleUser(string $email, string $id = '100', string $name = 'Test User'): void
    {
        $googleUser = new SocialiteUser;
        $googleUser->id = $id;
        $googleUser->name = $name;
        $googleUser->email = $email;
        $googleUser->avatar = null;

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn(new class($googleUser)
            {
                public function __construct(private SocialiteUser $user) {}

                public function user(): SocialiteUser
                {
                    return $this->user;
                }
            });
    }
}
