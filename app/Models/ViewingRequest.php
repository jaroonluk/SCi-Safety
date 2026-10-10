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
        'director_note',
        'dean_note',
        'officer_decision',
        'officer_reviewed_by_id',
        'director_reviewed_by_id',
        'dean_reviewed_by_id',
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

    public function officerReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_reviewed_by_id');
    }

    public function directorReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_reviewed_by_id');
    }

    public function deanReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dean_reviewed_by_id');
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
     * @return array{
     *     steps: list<array{number: int, label: string, hint: string, icon: string, state: string}>,
     *     current: int,
     *     total: int,
     *     next: string,
     *     tone: string,
     *     finished: bool,
     *     stopped: bool
     * }
     */
    public function journey(): array
    {
        $definitions = [
            [
                'label' => 'ยื่นคำขอ',
                'hint' => 'ท่านส่งคำขอเข้าระบบแล้ว',
                'icon' => 'document',
            ],
            [
                'label' => 'เจ้าหน้าที่ตรวจ',
                'hint' => 'ตรวจกล้องและเห็นชอบคำขอ',
                'icon' => 'camera',
            ],
            [
                'label' => 'ผู้บริหารพิจารณา',
                'hint' => 'ผู้อำนวยการกอง หรือรองคณบดี',
                'icon' => 'scale',
            ],
            [
                'label' => 'นัดหมายดูภาพ',
                'hint' => 'กำหนดวันเวลาและห้องควบคุม',
                'icon' => 'calendar',
            ],
            [
                'label' => 'ดูภาพและปิดเรื่อง',
                'hint' => 'ดูภาพต่อหน้าเจ้าหน้าที่ แล้วปิดคำขอ',
                'icon' => 'eye',
            ],
        ];

        $current = match ($this->status) {
            RequestStatus::Submitted, RequestStatus::MoreInfo => 1,
            RequestStatus::NoFootage => 1,
            RequestStatus::PendingDirector, RequestStatus::PendingDean => 2,
            RequestStatus::Rejected => ($this->officer_decision === 'reject') ? 1 : 2,
            RequestStatus::Approved, RequestStatus::Scheduled => 3,
            RequestStatus::Viewed => 4,
            RequestStatus::Closed => 4,
            default => 1,
        };

        $finished = $this->status === RequestStatus::Closed;
        $stopped = in_array($this->status, [RequestStatus::Rejected, RequestStatus::NoFootage], true);
        $nearEnd = $this->status === RequestStatus::Viewed;

        if ($this->status === RequestStatus::PendingDean) {
            $definitions[2]['hint'] = 'อยู่ที่รองคณบดีฝ่ายบริหารพิจารณา';
        } elseif ($this->status === RequestStatus::PendingDirector) {
            $definitions[2]['hint'] = 'อยู่ที่ผู้อำนวยการกองพิจารณา';
        } elseif ($this->status === RequestStatus::MoreInfo) {
            $definitions[1]['hint'] = 'เจ้าหน้าที่ขอข้อมูลเพิ่มจากท่าน';
        } elseif ($this->status === RequestStatus::Approved) {
            $definitions[3]['hint'] = 'อนุมัติแล้ว รอเจ้าหน้าที่เปิดนัด';
        } elseif ($this->status === RequestStatus::Scheduled) {
            $definitions[3]['hint'] = $this->appointment_confirmed_at
                ? 'ยืนยันนัดแล้ว รอวันดูภาพ'
                : 'มีนัดแล้ว กรุณายืนยันด้านล่าง';
        }

        $steps = [];
        foreach ($definitions as $index => $definition) {
            if ($finished || ($nearEnd && $index < 4) || $index < $current) {
                $state = 'done';
            } elseif ($stopped && $index === $current) {
                $state = 'stopped';
            } elseif ($index === $current) {
                $state = 'current';
            } else {
                $state = 'wait';
            }

            if ($finished) {
                $state = 'done';
            }

            $steps[] = [
                'number' => $index + 1,
                'label' => $definition['label'],
                'hint' => $definition['hint'],
                'icon' => $definition['icon'],
                'state' => $state,
            ];
        }

        $next = match ($this->status) {
            RequestStatus::Submitted => 'ขั้นตอนที่ 2 จาก 5 · เจ้าหน้าที่กำลังตรวจสอบคำขอและกล้อง',
            RequestStatus::MoreInfo => 'ขั้นตอนที่ 2 จาก 5 · กรุณาส่งข้อมูลเพิ่มด้านล่างเพื่อให้เจ้าหน้าที่ตรวจต่อ',
            RequestStatus::PendingDirector => 'ขั้นตอนที่ 3 จาก 5 · อยู่ที่ผู้อำนวยการกองพิจารณา',
            RequestStatus::PendingDean => 'ขั้นตอนที่ 3 จาก 5 · อยู่ที่รองคณบดีฝ่ายบริหารพิจารณา',
            RequestStatus::Approved => 'ขั้นตอนที่ 4 จาก 5 · อนุมัติแล้ว รอเจ้าหน้าที่กำหนดวันนัดดูภาพ',
            RequestStatus::Scheduled => $this->appointment_confirmed_at
                ? 'ขั้นตอนที่ 4 จาก 5 · ยืนยันนัดแล้ว โปรดมาตามวันเวลาที่กำหนด'
                : 'ขั้นตอนที่ 4 จาก 5 · มีนัดแล้ว กรุณากดยืนยันด้านล่าง',
            RequestStatus::Viewed => 'ขั้นตอนที่ 5 จาก 5 · ดูภาพเสร็จแล้ว รอเจ้าหน้าที่ปิดคำขอ',
            RequestStatus::Closed => 'ครบทั้ง 5 ขั้นตอนแล้ว · เรื่องนี้ปิดเรียบร้อย',
            RequestStatus::Rejected => 'หยุดที่ขั้นตอนพิจารณา · คำขอไม่ได้รับความเห็นชอบ อ่านเหตุผลด้านล่างได้',
            RequestStatus::NoFootage => 'หยุดที่ขั้นตอนตรวจกล้อง · ช่วงนี้ยังไม่มีภาพที่ใช้ได้',
            default => 'เจ้าหน้าที่และผู้บริหารกำลังดำเนินการตามลำดับขั้นตอน',
        };

        return [
            'steps' => $steps,
            'current' => $finished ? 5 : ($stopped ? $current + 1 : $current + 1),
            'total' => 5,
            'next' => $next,
            'tone' => $this->status->tone(),
            'finished' => $finished,
            'stopped' => $stopped,
        ];
    }
}
