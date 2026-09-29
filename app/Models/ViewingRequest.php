<?php

namespace App\Models;

use App\Enums\IncidentType;
use App\Enums\RequestStatus;
use App\Enums\TechnicalResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ViewingRequest extends Model
{
    public const PRIVACY_NOTICE_VERSION = '2026-09-29';

    protected $fillable = [
        'user_id',
        'reference',
        'incident_type',
        'purpose',
        'started_at',
        'ended_at',
        'building',
        'floor',
        'location',
        'urgency',
        'details',
        'status',
        'privacy_notice_version',
        'privacy_accepted_at',
        'requester_kind',
        'identifier',
        'affiliation',
        'phone',
        'external_organization',
        'official_letter_no',
        'external_contact_name',
        'subject_name',
        'subject_code',
        'exam_room',
        'landmark',
        'on_behalf',
        'intake_channel',
        'received_at',
        'informant_name',
        'on_behalf_reason',
        'recorded_by_id',
        'technical_result',
        'technical_note',
        'high_risk',
        'pdpa_opinion',
        'workflow_after_pdpa',
        'supplement_note',
        'resume_status',
        'appointment_at',
        'appointment_place',
        'participants',
        'supervisor_id',
        'appointment_confirmed_at',
        'viewing_started_at',
        'viewing_ended_at',
        'viewing_summary',
        'viewing_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'incident_type' => IncidentType::class,
            'status' => RequestStatus::class,
            'technical_result' => TechnicalResult::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
            'received_at' => 'datetime',
            'appointment_at' => 'datetime',
            'appointment_confirmed_at' => 'datetime',
            'viewing_started_at' => 'datetime',
            'viewing_ended_at' => 'datetime',
            'viewing_confirmed_at' => 'datetime',
            'on_behalf' => 'boolean',
            'high_risk' => 'boolean',
        ];
    }

    public function coverageGap(): HasOne
    {
        return $this->hasOne(CoverageGap::class);
    }

    public function requesterKindLabel(): string
    {
        return match ($this->requester_kind) {
            'student_sci' => 'นักศึกษาคณะวิทยาศาสตร์',
            'student_other' => 'นักศึกษาต่างคณะ',
            'staff_sci' => 'บุคลากรคณะวิทยาศาสตร์',
            'staff_kku' => 'บุคลากรหน่วยงานอื่นใน มข.',
            'external_org' => 'ผู้แทนหน่วยงานภายนอก',
            default => 'บุคคลภายนอก',
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return $this->status->label();
    }

    public function urgencyLabel(): string
    {
        return $this->urgency === 'urgent' ? 'เร่งด่วน' : 'ปกติ';
    }

    /**
     * @return array{steps: list<array{label: string, state: string}>, next: string, tone: string}
     */
    public function journey(): array
    {
        $labels = ['ยื่นคำขอ', 'รับเรื่อง', 'ตรวจสอบกล้อง', 'ผลการพิจารณา', 'นัดหมายดูภาพ'];
        $current = match ($this->status) {
            RequestStatus::Submitted, RequestStatus::MoreInfo => 1,
            RequestStatus::NoFootage => 2,
            RequestStatus::PendingDirector, RequestStatus::PendingDean, RequestStatus::PendingPdpa, RequestStatus::Rejected => 3,
            RequestStatus::Approved, RequestStatus::Scheduled, RequestStatus::Viewed, RequestStatus::Closed => 4,
        };
        $finished = in_array($this->status, [RequestStatus::Viewed, RequestStatus::Closed], true);
        $stopped = in_array($this->status, [RequestStatus::Rejected, RequestStatus::NoFootage], true);
        $steps = [];

        foreach ($labels as $index => $label) {
            $state = 'wait';
            if ($finished || $index < $current) {
                $state = 'done';
            } elseif ($index === $current && ! $stopped) {
                $state = 'current';
            } elseif ($index === $current) {
                $state = 'current';
            }
            $steps[] = ['label' => $label, 'state' => $state];
        }

        $next = match ($this->status) {
            RequestStatus::Submitted => 'เจ้าหน้าที่จะเริ่มตรวจสอบข้อมูลภายใน 24 ชั่วโมงทำการ จากนั้นหน้านี้จะบอกขั้นถัดไปให้ท่าน',
            RequestStatus::MoreInfo => 'เจ้าหน้าที่ต้องการข้อมูลเพิ่มเล็กน้อย กรอกรายละเอียดด้านล่างแล้วส่งกลับได้เลย',
            RequestStatus::PendingDirector => 'เรื่องนี้อยู่ที่ผู้อำนวยการกองบริหารงานคณะเพื่อพิจารณาการนัดดูภาพ',
            RequestStatus::PendingDean => 'เรื่องนี้อยู่ที่รองคณบดีฝ่ายบริหารเพื่อพิจารณา',
            RequestStatus::PendingPdpa => 'เจ้าหน้าที่คุ้มครองข้อมูลส่วนบุคคลกำลังตรวจว่าการดูภาพกระทบสิทธิใครหรือไม่',
            RequestStatus::Approved => 'ท่านได้รับอนุญาตให้นัดดูภาพแล้ว เจ้าหน้าที่จะกำหนดวัน เวลา และห้องควบคุมบนหน้านี้',
            RequestStatus::Scheduled => $this->appointment_confirmed_at
                ? 'ยืนยันนัดแล้ว โปรดมาตามวันเวลาด้านล่าง การดูภาพทำต่อหน้าเจ้าหน้าที่'
                : 'มีนัดหมายแล้ว โปรดกดยืนยันด้านล่างเพื่อให้เจ้าหน้าที่เตรียมห้องควบคุม',
            RequestStatus::Viewed => 'การดูภาพเสร็จแล้ว เจ้าหน้าที่จะปิดเรื่องหลังจากบันทึกผล',
            RequestStatus::Closed => 'เรื่องนี้ปิดเรียบร้อย หากยังต้องการความช่วยเหลือ สามารถยื่นคำขอใหม่ได้',
            RequestStatus::Rejected => 'ครั้งนี้ยังไม่สามารถนัดดูภาพได้ อ่านเหตุผลในบันทึกด้านล่างได้',
            RequestStatus::NoFootage => 'ช่วงเวลาและจุดนี้ยังไม่มีภาพที่ใช้ได้ เจ้าหน้าที่บันทึกไว้เพื่อปรับปรุงพื้นที่',
        };

        return [
            'steps' => $steps,
            'next' => $next,
            'tone' => $this->status->tone(),
        ];
    }
}
