<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ViewingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewingRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_signed_in_user_can_submit_and_track_their_own_request(): void
    {
        $user = User::factory()->create(['email' => 'student@kkumail.com']);

        $this->actingAs($user)
            ->get(route('requests.create'))
            ->assertOk()
            ->assertDontSee('type="file"', false);

        $this->actingAs($user)
            ->post(route('requests.store'), $this->payload())
            ->assertRedirect();

        $request = ViewingRequest::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($request);
        $this->assertSame('lost_property', $request->incident_type->value);

        $this->actingAs($user)
            ->get(route('requests.index'))
            ->assertOk()
            ->assertSee($request->reference);

        $this->actingAs($user)
            ->get(route('requests.show', $request))
            ->assertOk()
            ->assertSee('รอเจ้าหน้าที่ตรวจสอบ')
            ->assertSee('ไม่มีการดาวน์โหลดหรือส่งมอบไฟล์ภาพ');
    }

    public function test_another_user_cannot_open_the_request(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $request = ViewingRequest::query()->create([
            'user_id' => $owner->id,
            'reference' => 'SCI-20260929-0001',
            'incident_type' => 'accident',
            'purpose' => 'ตรวจสอบอุบัติเหตุ',
            'started_at' => now()->subHour(),
            'ended_at' => now(),
            'building' => 'อาคาร 1',
            'location' => 'โถงชั้น 1',
            'urgency' => 'normal',
            'details' => 'ลื่นล้มบริเวณบันได',
            'status' => 'submitted',
            'privacy_notice_version' => ViewingRequest::PRIVACY_NOTICE_VERSION,
            'privacy_accepted_at' => now(),
        ]);

        $this->actingAs($other)
            ->get(route('requests.show', $request))
            ->assertNotFound();
    }

    public function test_privacy_acknowledgement_is_required(): void
    {
        $user = User::factory()->create();
        $payload = $this->payload();
        unset($payload['privacy_accepted']);

        $this->actingAs($user)
            ->from(route('requests.create'))
            ->post(route('requests.store'), $payload)
            ->assertRedirect(route('requests.create'))
            ->assertSessionHasErrors('privacy_accepted');

        $this->assertDatabaseCount('viewing_requests', 0);
    }

    private function payload(): array
    {
        return [
            'incident_type' => 'lost_property',
            'purpose' => 'ตามหาโน้ตบุ๊กที่หายในอาคารเรียน',
            'started_at' => now()->subHours(2)->format('Y-m-d\TH:i'),
            'ended_at' => now()->subHour()->format('Y-m-d\TH:i'),
            'building' => 'อาคารวิทยาศาสตร์ 1',
            'floor' => '2',
            'location' => 'หน้าห้อง 220',
            'urgency' => 'normal',
            'details' => 'โน้ตบุ๊กสีดำหายระหว่างพักเที่ยง',
            'requester_kind' => 'student_sci',
            'phone' => '0812345678',
            'privacy_notice_version' => \App\Models\PrivacyNotice::current()->version,
            'privacy_accepted' => '1',
        ];
    }
}
