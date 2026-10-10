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

        $this->actingAs($admin)->get('/')->assertOk()->assertSee('ตรวจสอบคำขอ');
        $this->actingAs($admin)->get(route('queue'))->assertOk()->assertSee('ตรวจสอบคำขอ');
        $this->actingAs($admin)->get(route('cameras.index'))->assertOk();
        $this->actingAs($admin)->get(route('reviews'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.users'))->assertForbidden();

        $this->actingAs($director)->get(route('reviews'))->assertOk();
        $this->actingAs($director)->get(route('reports.index'))->assertOk();
        $this->actingAs($director)->get(route('queue'))->assertForbidden();

        $this->actingAs($dean)->get(route('reviews'))->assertOk();
        $this->actingAs($dean)->get(route('reports.export'))->assertOk();

        $this->actingAs($pdpa)->get(route('privacy.edit'))->assertOk();
        $this->actingAs($pdpa)->get(route('reviews'))->assertForbidden();
        $this->actingAs($pdpa)->get(route('queue'))->assertForbidden();

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

    public function test_officer_endorsement_goes_to_director_then_optional_dean(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::CctvAdmin]);
        $director = User::factory()->create(['role' => UserRole::Director]);
        $dean = User::factory()->create(['role' => UserRole::AssociateDean]);

        $general = $this->request($owner);
        $this->actingAs($admin)->post(route('requests.technical', $general), [
            'technical_result' => 'found',
            'technical_note' => 'พบภาพในช่วงเวลาที่แจ้ง',
            'decision' => 'approve',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::PendingDirector, $general->fresh()->status);
        $this->assertSame('approve', $general->fresh()->officer_decision);
        $this->assertSame($admin->id, $general->fresh()->officer_reviewed_by_id);

        $this->actingAs($owner)
            ->get(route('requests.show', $general))
            ->assertOk()
            ->assertSee('รอผู้อำนวยการกองพิจารณา')
            ->assertSee('พบภาพในช่วงเวลาที่แจ้ง')
            ->assertSee('ผลการพิจารณา · เจ้าหน้าที่ผู้รับผิดชอบคำขอ')
            ->assertSee($admin->email);

        $this->actingAs($director)
            ->get(route('requests.show', $general))
            ->assertOk()
            ->assertSee('ผลการพิจารณา · เจ้าหน้าที่ผู้รับผิดชอบคำขอ')
            ->assertSee($admin->email)
            ->assertSee('พิจารณาคำขอ')
            ->assertSee('สรุปสำหรับผู้พิจารณา')
            ->assertDontSee('ข้อมูลที่ท่านกรอก')
            ->assertDontSee('ขอข้อมูลเพิ่ม');

        $this->actingAs($director)->post(route('requests.decide', $general), [
            'decision' => 'approve',
            'comment' => 'อนุมัติให้นัดดูภาพ',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::Approved, $general->fresh()->status);
        $this->assertSame('อนุมัติให้นัดดูภาพ', $general->fresh()->director_note);
        $this->assertSame($director->id, $general->fresh()->director_reviewed_by_id);

        $exam = $this->request($owner, ['incident_type' => 'exam', 'reference' => 'SCI-EXAM-0001']);
        $this->actingAs($admin)->post(route('requests.technical', $exam), [
            'technical_result' => 'found',
            'technical_note' => 'พบภาพในห้องสอบ',
            'decision' => 'approve',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::PendingDirector, $exam->fresh()->status);

        $this->actingAs($director)->post(route('requests.decide', $exam), [
            'decision' => 'escalate',
            'comment' => 'เป็นเรื่องสอบ ส่งรองคณบดีพิจารณา',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::PendingDean, $exam->fresh()->status);
        $this->assertSame('เป็นเรื่องสอบ ส่งรองคณบดีพิจารณา', $exam->fresh()->director_note);

        $this->actingAs($dean)
            ->get(route('requests.show', $exam))
            ->assertOk()
            ->assertSee('พบภาพในห้องสอบ')
            ->assertSee('เป็นเรื่องสอบ ส่งรองคณบดีพิจารณา')
            ->assertSee('ผลการพิจารณา · ผู้อำนวยการกอง');

        $this->actingAs($dean)->post(route('requests.decide', $exam), [
            'decision' => 'approve',
            'comment' => 'อนุมัติให้นัดดูภายใต้การควบคุม',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::Approved, $exam->fresh()->status);
        $this->assertSame('อนุมัติให้นัดดูภายใต้การควบคุม', $exam->fresh()->dean_note);

        $rejected = $this->request($owner, ['reference' => 'SCI-REJ-0001']);
        $this->actingAs($admin)->post(route('requests.technical', $rejected), [
            'technical_result' => 'unclear',
            'technical_note' => 'ภาพไม่ชัดพอ',
            'decision' => 'reject',
            'reject_reason' => 'ข้อมูลช่วงเวลายังไม่พอและภาพไม่ชัด',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::Rejected, $rejected->fresh()->status);
        $this->actingAs($owner)
            ->get(route('requests.show', $rejected))
            ->assertOk()
            ->assertSee('ไม่เห็นชอบ')
            ->assertSee('ข้อมูลช่วงเวลายังไม่พอและภาพไม่ชัด');

        $risk = $this->request($owner, ['reference' => 'SCI-RISK-0001']);
        $this->actingAs($admin)->post(route('requests.technical', $risk), [
            'technical_result' => 'found',
            'technical_note' => 'อาจเห็นบุคคลภายนอกจำนวนมาก',
            'decision' => 'approve',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::PendingDirector, $risk->fresh()->status);
    }

    public function test_director_rejection_reason_is_visible_to_requester(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::CctvAdmin]);
        $director = User::factory()->create(['role' => UserRole::Director]);
        $record = $this->request($owner, ['reference' => 'SCI-DIR-REJ-0001']);

        $this->actingAs($admin)->post(route('requests.technical', $record), [
            'technical_result' => 'found',
            'technical_note' => 'พบภาพ',
            'decision' => 'approve',
        ])->assertRedirect();

        $this->actingAs($director)->post(route('requests.decide', $record), [
            'decision' => 'reject',
            'comment' => 'ยังไม่มีหนังสือจากหน่วยงานภายนอก',
        ])->assertRedirect();

        $this->assertSame(RequestStatus::Rejected, $record->fresh()->status);
        $this->actingAs($owner)
            ->get(route('requests.show', $record))
            ->assertOk()
            ->assertSee('ยังไม่มีหนังสือจากหน่วยงานภายนอก')
            ->assertSee('ผลการพิจารณา · ผู้อำนวยการกอง');
    }

    public function test_missing_footage_becomes_a_coverage_gap_and_supervised_viewing_is_logged(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::CctvAdmin]);
        $gap = $this->request($owner, ['reference' => 'SCI-GAP-0001']);

        $this->actingAs($admin)->post(route('requests.technical', $gap), [
            'technical_result' => 'blind_spot',
            'technical_note' => 'บริเวณนี้ไม่มีกล้อง',
            'decision' => 'approve',
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

        $ownerShow = $this->actingAs($owner)->get(route('requests.show', $viewing));
        $ownerShow->assertOk()
            ->assertSee('นัดดูภาพของคุณ')
            ->assertSee('มีนัดดูภาพแล้ว — ดูวันเวลาและสถานที่ด้านล่างนี้ก่อน')
            ->assertSee('ยืนยันว่ามาตามนัดได้')
            ->assertSee('ห้องควบคุมความปลอดภัย');
        $html = $ownerShow->getContent();
        $this->assertLessThan(
            strpos($html, 'ข้อมูลที่ท่านกรอก'),
            strpos($html, 'นัดดูภาพของคุณ'),
            'Appointment spotlight should appear before the submitted-details section for the requester.'
        );

        $this->actingAs($owner)->post(route('requests.confirm', $viewing))->assertRedirect();
        $this->actingAs($admin)->post(route('requests.viewing', $viewing), [
            'viewing_started_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'viewing_ended_at' => now()->format('Y-m-d H:i:s'),
            'viewing_summary' => 'เห็นเหตุการณ์ตามที่แจ้ง',
            'controlled' => '1',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::Viewed, $viewing->fresh()->status);

        $this->actingAs($admin)
            ->post(route('requests.close', $viewing))
            ->assertRedirect(route('queue'));
        $this->assertSame(RequestStatus::Closed, $viewing->fresh()->status);
        $this->assertTrue(
            AuditLog::query()->where('action', 'request.closed')->where('user_id', $admin->id)->exists()
        );

        $this->actingAs($admin)->post(route('requests.close', $gap))->assertRedirect(route('queue'));
        $this->assertSame(RequestStatus::Closed, $gap->fresh()->status);
    }

    public function test_only_cctv_admin_can_close_terminal_requests(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::CctvAdmin]);
        $director = User::factory()->create(['role' => UserRole::Director]);
        $record = $this->request($owner, [
            'reference' => 'SCI-CLOSE-0001',
            'status' => RequestStatus::Rejected,
        ]);

        $this->actingAs($owner)->post(route('requests.close', $record))->assertForbidden();
        $this->actingAs($director)->post(route('requests.close', $record))->assertForbidden();

        $this->actingAs($admin)
            ->get(route('requests.show', $record))
            ->assertOk()
            ->assertSee('ปิดคำขอฉบับนี้');

        $this->actingAs($admin)
            ->get(route('queue'))
            ->assertOk()
            ->assertSee('รอปิดคำขอ')
            ->assertSee('SCI-CLOSE-0001')
            ->assertSee('ค้างที่เจ้าหน้าที่ · รอปิดคำขอ');

        $waitingDirector = $this->request($owner, [
            'reference' => 'SCI-WAIT-DIR-0001',
            'status' => RequestStatus::PendingDirector,
        ]);
        $this->actingAs($admin)
            ->get(route('queue', ['filter' => 'director']))
            ->assertOk()
            ->assertSee('SCI-WAIT-DIR-0001')
            ->assertSee('ค้างที่ผู้อำนวยการกอง');

        $this->actingAs($admin)
            ->get(route('queue', ['filter' => 'new']))
            ->assertOk()
            ->assertDontSee('SCI-WAIT-DIR-0001');
        $this->assertSame(RequestStatus::PendingDirector, $waitingDirector->fresh()->status);

        $open = $this->request($owner, [
            'reference' => 'SCI-CLOSE-0002',
            'status' => RequestStatus::Approved,
        ]);
        $this->actingAs($admin)->post(route('requests.close', $open))->assertForbidden();
        $this->assertSame(RequestStatus::Approved, $open->fresh()->status);
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
        $admin = User::factory()->create(['role' => UserRole::CctvAdmin]);
        $request = $this->request($owner, [
            'incident_type' => 'external',
            'status' => RequestStatus::Submitted,
            'reference' => 'SCI-INFO-0001',
        ]);

        $this->actingAs($admin)->post(route('requests.more', $request), [
            'more_info_note' => 'ขอเลขที่หนังสือ',
        ])->assertRedirect();

        $this->actingAs($owner)->post(route('requests.supplement', $request), [
            'supplement_note' => 'หนังสือที่ 123',
        ])->assertRedirect();

        $this->assertSame(RequestStatus::Submitted, $request->fresh()->status);
        $this->assertStringContainsString('ขอเลขที่หนังสือ', (string) $request->fresh()->technical_note);
    }

    public function test_director_may_approve_without_comment_but_must_explain_rejection(): void
    {
        $owner = User::factory()->create();
        $director = User::factory()->create(['role' => UserRole::Director]);
        $approved = $this->request($owner, [
            'status' => RequestStatus::PendingDirector,
            'reference' => 'SCI-DIR-OK',
            'technical_note' => 'พบภาพ',
            'officer_decision' => 'approve',
        ]);
        $rejected = $this->request($owner, [
            'status' => RequestStatus::PendingDirector,
            'reference' => 'SCI-DIR-NO',
            'technical_note' => 'พบภาพ',
            'officer_decision' => 'approve',
        ]);

        $this->actingAs($director)->post(route('requests.decide', $approved), [
            'decision' => 'approve',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::Approved, $approved->fresh()->status);
        $this->assertNull($approved->fresh()->director_note);

        $this->actingAs($director)->from(route('requests.show', $rejected))->post(route('requests.decide', $rejected), [
            'decision' => 'reject',
        ])->assertRedirect(route('requests.show', $rejected))->assertSessionHasErrors('comment');

        $this->actingAs($director)->post(route('requests.decide', $rejected), [
            'decision' => 'reject',
            'comment' => 'เอกสารไม่ครบ',
        ])->assertRedirect();
        $this->assertSame(RequestStatus::Rejected, $rejected->fresh()->status);
        $this->assertSame('เอกสารไม่ครบ', $rejected->fresh()->director_note);
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
