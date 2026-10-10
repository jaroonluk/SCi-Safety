<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\Impersonation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['sci.impersonation_enabled' => true]);
    }

    public function test_super_admin_can_impersonate_another_role_and_leave(): void
    {
        $super = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $officer = User::factory()->create(['role' => UserRole::CctvAdmin, 'name' => 'เจ้าหน้าที่ทดสอบ']);

        $this->actingAs($super)
            ->post(route('admin.impersonate', $officer))
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($officer);
        $this->assertTrue(Impersonation::active());
        $this->assertSame($super->id, Impersonation::impersonatorId());

        $this->get(route('queue'))->assertOk()->assertSee('ตรวจสอบคำขอ');
        $this->get('/')->assertSee('โหมดทดสอบ')->assertSee('เจ้าหน้าที่ทดสอบ');

        $this->assertTrue(
            AuditLog::query()->where('action', 'auth.impersonation_started')->where('user_id', $super->id)->exists()
        );

        $this->post(route('admin.impersonate.leave'))
            ->assertRedirect(route('admin.users'));

        $this->assertAuthenticatedAs($super);
        $this->assertFalse(Impersonation::active());
        $this->assertTrue(
            AuditLog::query()->where('action', 'auth.impersonation_stopped')->where('user_id', $super->id)->exists()
        );
    }

    public function test_super_admin_can_preview_every_role_without_other_users(): void
    {
        $super = User::factory()->create(['role' => UserRole::SuperAdmin, 'name' => 'ผู้ดูแลทดสอบ']);

        $cases = [
            UserRole::Requester->value => fn () => $this->get(route('requests.index'))->assertOk(),
            UserRole::CctvAdmin->value => fn () => $this->get(route('queue'))->assertOk()->assertSee('ตรวจสอบคำขอ'),
            UserRole::Director->value => fn () => $this->get(route('reviews'))->assertOk(),
            UserRole::AssociateDean->value => fn () => $this->get(route('reviews'))->assertOk(),
            UserRole::PdpaCoordinator->value => fn () => $this->get(route('privacy.edit'))->assertOk(),
            UserRole::SuperAdmin->value => fn () => $this->get(route('admin.users'))->assertOk(),
        ];

        foreach ($cases as $role => $assertAccess) {
            $this->actingAs($super)
                ->post(route('admin.impersonate.role'), ['role' => $role])
                ->assertRedirect(route('home'));

            $this->assertAuthenticatedAs($super);
            $this->assertTrue(Impersonation::isRolePreview());
            $this->assertSame($role, Impersonation::actingAsRole()?->value);
            $this->assertSame(UserRole::from($role), $super->fresh()->role);
            $this->assertSame(UserRole::SuperAdmin, $super->fresh()->realRole());

            $assertAccess();
            $this->get('/')->assertSee('โหมดทดสอบสิทธิ์');

            $this->post(route('admin.impersonate.leave'))->assertRedirect(route('admin.users'));
            $this->assertFalse(Impersonation::active());
            $this->assertSame(UserRole::SuperAdmin, $super->fresh()->role);
        }
    }

    public function test_impersonation_is_disabled_when_flag_is_off(): void
    {
        config(['sci.impersonation_enabled' => false]);

        $super = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $officer = User::factory()->create(['role' => UserRole::CctvAdmin]);

        $this->actingAs($super)
            ->post(route('admin.impersonate', $officer))
            ->assertNotFound();

        $this->actingAs($super)
            ->post(route('admin.impersonate.role'), ['role' => UserRole::CctvAdmin->value])
            ->assertNotFound();

        $this->actingAs($super)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertDontSee('เข้าใช้งานด้วยสิทธิ์นี้')
            ->assertDontSee('เข้าใช้งานแทน');
    }

    public function test_non_super_admin_cannot_impersonate(): void
    {
        $director = User::factory()->create(['role' => UserRole::Director]);
        $officer = User::factory()->create(['role' => UserRole::CctvAdmin]);

        $this->actingAs($director)
            ->post(route('admin.impersonate', $officer))
            ->assertForbidden();

        $this->actingAs($director)
            ->post(route('admin.impersonate.role'), ['role' => UserRole::CctvAdmin->value])
            ->assertForbidden();
    }

    public function test_cannot_impersonate_self(): void
    {
        $super = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($super)
            ->post(route('admin.impersonate', $super))
            ->assertStatus(422);
    }
}
