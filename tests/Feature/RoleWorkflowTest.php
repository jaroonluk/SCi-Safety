<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\CoverageGap;
use App\Models\OfficerCover;
use App\Models\User;
use App\Models\ViewingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class RoleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_reaches_only_its_own_work(): void
    {
        $requester = User::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::CctvAdmin]);
        $director = User::factory()->create(['role' => UserRole::Director]);
        $dean = User::factory()->create(['role' => UserRole::AssociateDean]);
        $pdpa = User::factory()->create(['role' => UserRole::PdpaCoordinator]);
        $super = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($requester)->get(route('queue'))->assertForbidden();
        $this->actingAs($requester)->get(route('reviews'))->assertForbidden();
        $this->actingAs($requester)->get(route('admin.users'))->assertForbidden();
        $this->actingAs($requester)->get(route('cameras.index'))->assertForbidden();

        $this->actingAs($admin)->get('/')->assertOk()->assertSee('คิวเจ้าหน้าที่');
        $this->actingAs($admin)->get(route('queue'))->assertOk()->assertSee('คิวเจ้าหน้าที่');
        $this->actingAs($admin)->get(route('cameras.index'))->assertOk();
        $this->actingAs($admin)->get(route('reviews'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.users'))->assertForbidden();

        $this->actingAs($director)->get(route('reviews'))->assertOk();
        $this->actingAs($director)->get(route('reports.index'))->assertOk();
        $this->actingAs($director)->get(route('queue'))->assertForbidden();

        $this->actingAs($dean)->get(route('reviews'))->assertOk();
        $this->actingAs($dean)->get(route('reports.export'))->assertOk();

        $this->actingAs($pdpa)->get(route('pdpa.inbox'))->assertOk();
        $this->actingAs($pdpa)->get(route('privacy.edit'))->assertOk();
        $this->actingAs($pdpa)->get(route('reviews'))->assertForbidden();

        $this->actingAs($super)->get(route('admin.users'))->assertOk()->assertSee('กำหนดสิทธิผู้ใช้งาน');
        $this->actingAs($super)->get(route('admin.rules'))
            ->assertOk()
            ->assertSee('อุบัติเหตุ')
            ->assertSee('เหตุการณ์สอบ')
            ->assertSee('ทรัพย์สินสูญหาย')
            ->assertSee('คำขอจากหน่วยงานภายนอก')
            ->assertDontSee('accident');
        $this->actingAs($super)->get(route('admin.audit'))->assertOk();
        $this->actingAs($super)->get(route('cameras.index'))->assertOk();
        $this->actingAs($super)->get(route('queue'))->assertForbidden();
        $this->actingAs($super)->post(route('requests.decide', $this->request($requester)), [
            'decision' => 'approve',
            'comment' => 'ไม่ควรทำได้',
        ])->assertForbidden();
    }

    public function test_general_exam_urgent_and_pdpa_paths(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::CctvAdmin]);
        $director = User::factory()->create(['role' => UserRole::Director]);
        $dean = User::factory()->create(['role' => UserRole::AssociateDean]);
        $pdpa = User::factory()->create(['role' => UserRole::PdpaCoordinator]);

        $general = $this->request($owner);
        $this->actingAs($admin)->post(route('requests.technical', $general), [
            'technical_result' => 'found',
            'technical_note' => 'พบภาพในช่วงเวลาที่แจ้ง',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::Approved, $general->fresh()->status);

        $exam = $this->request($owner, ['incident_type' => 'exam', 'reference' => 'SCI-EXAM-0001']);
        $this->actingAs($admin)->post(route('requests.technical', $exam), [
            'technical_result' => 'found',
            'technical_note' => 'พบภาพในห้องสอบ',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::PendingDirector, $exam->fresh()->status);
        $this->actingAs($director)->post(route('requests.decide', $exam), [
            'decision' => 'approve',
            'comment' => 'เห็นชอบให้นัดดูเฉพาะห้องสอบ',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::Approved, $exam->fresh()->status);

        $urgent = $this->request($owner, ['reference' => 'SCI-URGENT-0001']);
        $this->actingAs($admin)->post(route('requests.technical', $urgent), [
            'technical_result' => 'found',
            'technical_note' => 'เหตุอันตราย ต้องให้ผู้บริหารสั่งทันที',
            'send_to_dean' => '1',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::PendingDean, $urgent->fresh()->status);
        $this->actingAs($dean)->post(route('requests.decide', $urgent), [
            'decision' => 'approve',
            'comment' => 'ให้นัดดูภาพภายใต้การควบคุม',
        ])->assertRedirect();

        $risk = $this->request($owner, ['reference' => 'SCI-RISK-0001']);
        $this->actingAs($admin)->post(route('requests.technical', $risk), [
            'technical_result' => 'found',
            'technical_note' => 'อาจเห็นบุคคลภายนอกจำนวนมาก',
            'high_risk' => '1',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::PendingPdpa, $risk->fresh()->status);
        $this->actingAs($pdpa)->post(route('requests.pdpa', $risk), [
            'pdpa_opinion' => 'ดูได้เฉพาะจุดและเวลาที่เกี่ยวข้อง',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::Approved, $risk->fresh()->status);
    }

    public function test_missing_footage_becomes_a_coverage_gap_and_supervised_viewing_is_logged(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::CctvAdmin]);
        $gap = $this->request($owner, ['reference' => 'SCI-GAP-0001']);

        $this->actingAs($admin)->post(route('requests.technical', $gap), [
            'technical_result' => 'blind_spot',
            'technical_note' => 'บริเวณนี้ไม่มีกล้อง',
            'gap_priority' => 'high',
        ])->assertRedirect();

        $this->assertSame(RequestStatus::NoFootage, $gap->fresh()->status);
        $this->assertTrue(CoverageGap::query()->where('viewing_request_id', $gap->id)->exists());

        $viewing = $this->request($owner, [
            'reference' => 'SCI-VIEW-0001',
            'status' => RequestStatus::Approved,
        ]);
        $this->actingAs($admin)->post(route('requests.schedule', $viewing), [
            'appointment_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'appointment_place' => 'ห้องควบคุมความปลอดภัย',
            'participants' => 'ผู้ยื่นและเจ้าหน้าที่',
        ])->assertRedirect();

        $this->actingAs($owner)->post(route('requests.confirm', $viewing))->assertRedirect();
        $this->actingAs($admin)->post(route('requests.viewing', $viewing), [
            'viewing_started_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'viewing_ended_at' => now()->format('Y-m-d H:i:s'),
            'viewing_summary' => 'เห็นเหตุการณ์ตามที่แจ้ง',
            'controlled' => '1',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::Viewed, $viewing->fresh()->status);
    }

    public function test_super_admin_assigns_any_role_and_a_dated_backup(): void
    {
        $super = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $person = User::factory()->create();
        $officer = User::factory()->create(['role' => UserRole::CctvAdmin]);

        $this->actingAs($super)->post(route('admin.users.role', $person), [
            'role' => UserRole::Director->value,
        ])->assertRedirect();
        $this->assertSame(UserRole::Director, $person->fresh()->role);

        $this->actingAs($super)->post(route('admin.covers.store'), [
            'officer_user_id' => $officer->id,
            'backup_user_id' => $person->id,
            'starts_on' => today()->toDateString(),
            'ends_on' => today()->addDay()->toDateString(),
        ])->assertRedirect();

        $this->assertTrue(OfficerCover::query()->where('backup_user_id', $person->id)->exists());
        $this->actingAs($person->fresh())->get(route('queue'))->assertOk();

        $this->actingAs($super)->from(route('admin.users'))->post(route('admin.users.role', $super), [
            'role' => UserRole::Requester->value,
        ])->assertRedirect(route('admin.users'))->assertSessionHas('error');
        $this->assertSame(UserRole::SuperAdmin, $super->fresh()->role);
    }

    public function test_additional_information_returns_to_the_same_reviewer(): void
    {
        $owner = User::factory()->create();
        $director = User::factory()->create(['role' => UserRole::Director]);
        $request = $this->request($owner, [
            'incident_type' => 'external',
            'status' => RequestStatus::PendingDirector,
            'reference' => 'SCI-INFO-0001',
        ]);

        $this->actingAs($director)->post(route('requests.decide', $request), [
            'decision' => 'more_info',
            'comment' => 'ขอเลขที่หนังสือ',
        ])->assertRedirect();

        $this->actingAs($owner)->post(route('requests.supplement', $request), [
            'supplement_note' => 'หนังสือที่ 123',
        ])->assertRedirect();

        $this->assertSame(RequestStatus::PendingDirector, $request->fresh()->status);
    }

    public function test_masked_export_and_immutable_audit_log(): void
    {
        $director = User::factory()->create(['role' => UserRole::Director, 'name' => 'สมชาย ใจดี']);
        $owner = User::factory()->create(['name' => 'มานี ศรีสุข', 'email' => 'student@kkumail.com']);
        $this->request($owner);

        $export = $this->actingAs($director)->get(route('reports.export'));
        $export->assertOk();
        $body = $export->streamedContent();
        $this->assertStringContainsString('ม***', $body);
        $this->assertStringContainsString('s***@kkumail.com', $body);
        $this->assertStringNotContainsString('student@kkumail.com', $body);

        $log = AuditLog::query()->create([
            'user_id' => $director->id,
            'action' => 'test.event',
            'properties' => ['ok' => true],
        ]);

        $this->expectException(RuntimeException::class);
        $log->update(['action' => 'changed']);
    }

    public function test_privacy_notice_is_public(): void
    {
        $this->get(route('privacy.show'))
            ->assertOk()
            ->assertSee('ประกาศความเป็นส่วนตัว')
            ->assertSee('2026-09-29');
    }

    private function request(User $owner, array $extra = []): ViewingRequest
    {
        return ViewingRequest::query()->create(array_merge([
            'user_id' => $owner->id,
            'reference' => 'SCI-TEST-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'incident_type' => 'lost_property',
            'purpose' => 'ตามหาของ',
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHour(),
            'building' => 'SC01',
            'location' => 'โถงชั้น 1',
            'urgency' => 'normal',
            'details' => 'รายละเอียดเหตุการณ์',
            'requester_kind' => 'student_sci',
            'phone' => '0811111111',
            'status' => RequestStatus::Submitted,
            'privacy_notice_version' => '2026-09-29',
            'privacy_accepted_at' => now(),
        ], $extra));
    }
}
