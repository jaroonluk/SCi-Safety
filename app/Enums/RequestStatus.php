<?php

namespace App\Enums;

enum RequestStatus: string
{
    case Submitted = 'submitted';
    case MoreInfo = 'more_info';
    case PendingDirector = 'pending_director';
    case PendingDean = 'pending_dean';
    case PendingPdpa = 'pending_pdpa';
    case Approved = 'approved';
    case Scheduled = 'scheduled';
    case Viewed = 'viewed';
    case Closed = 'closed';
    case Rejected = 'rejected';
    case NoFootage = 'no_footage';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'รอเจ้าหน้าที่ตรวจสอบ',
            self::MoreInfo => 'ขอข้อมูลเพิ่ม',
            self::PendingDirector => 'รอผู้อำนวยการกองพิจารณา',
            self::PendingDean => 'รอรองคณบดีฝ่ายบริหารพิจารณา',
            self::PendingPdpa => 'รอความเห็นด้านข้อมูลส่วนบุคคล',
            self::Approved => 'อนุมัติให้นัดดูภาพแล้ว',
            self::Scheduled => 'นัดดูภาพแล้ว',
            self::Viewed => 'ดูภาพแล้ว',
            self::Closed => 'ปิดเรื่อง',
            self::Rejected => 'ไม่เห็นชอบ',
            self::NoFootage => 'ไม่พบภาพหรือไม่มีกล้อง',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Approved, self::Scheduled, self::Viewed, self::Closed => 'success',
            self::MoreInfo, self::NoFootage => 'warning',
            self::Rejected => 'danger',
            default => 'info',
        };
    }

    /**
     * Distinct accent for status cards and filters.
     */
    public function accent(): string
    {
        return match ($this) {
            self::Submitted => 'amber',
            self::MoreInfo => 'orange',
            self::PendingDirector => 'indigo',
            self::PendingDean => 'violet',
            self::PendingPdpa => 'teal',
            self::Approved => 'emerald',
            self::Scheduled => 'sky',
            self::Viewed => 'cyan',
            self::Closed => 'slate',
            self::Rejected => 'rose',
            self::NoFootage => 'stone',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Submitted => 'clipboard',
            self::MoreInfo => 'message',
            self::PendingDirector, self::PendingDean => 'scale',
            self::PendingPdpa => 'shield',
            self::Approved => 'check',
            self::Scheduled => 'calendar',
            self::Viewed => 'eye',
            self::Closed => 'flag',
            self::Rejected => 'alert',
            self::NoFootage => 'camera',
        };
    }

    public function shortHint(): string
    {
        return match ($this) {
            self::Submitted => 'เจ้าหน้าที่กำลังตรวจคำขอ',
            self::MoreInfo => 'กรุณาตอบข้อมูลเพิ่ม',
            self::PendingDirector => 'รอผู้อำนวยการกองพิจารณา',
            self::PendingDean => 'รอรองคณบดีฝ่ายบริหาร',
            self::PendingPdpa => 'รอความเห็นด้านข้อมูลส่วนบุคคล',
            self::Approved => 'รอเจ้าหน้าที่เปิดนัดดูภาพ',
            self::Scheduled => 'มีนัดดูภาพแล้ว',
            self::Viewed => 'ดูภาพเสร็จแล้ว รอปิดคำขอ',
            self::Closed => 'ปิดเรื่องเรียบร้อย',
            self::Rejected => 'ไม่ได้รับความเห็นชอบ',
            self::NoFootage => 'ไม่พบภาพในช่วงที่แจ้ง',
        };
    }
}
