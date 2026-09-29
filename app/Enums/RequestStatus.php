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
            self::PendingDirector => 'รอผู้อำนวยการพิจารณา',
            self::PendingDean => 'รอรองคณบดีฝ่ายบริหาร',
            self::PendingPdpa => 'รอความเห็นด้านข้อมูลส่วนบุคคล',
            self::Approved => 'อนุญาตให้นัดดูภาพ',
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
}
