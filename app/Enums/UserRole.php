<?php

namespace App\Enums;

enum UserRole: string
{
    case Requester = 'requester';
    case CctvAdmin = 'cctv_admin';
    case Director = 'director';
    case AssociateDean = 'associate_dean';
    case PdpaCoordinator = 'pdpa_coordinator';
    case SuperAdmin = 'super_admin';

    public function label(): string
    {
        return match ($this) {
            self::Requester => 'ผู้ยื่นคำขอ',
            self::CctvAdmin => 'เจ้าหน้าที่ผู้รับผิดชอบคำขอ',
            self::Director => 'ผู้อำนวยการกองบริหารงานคณะ',
            self::AssociateDean => 'รองคณบดีฝ่ายบริหาร',
            self::PdpaCoordinator => 'เจ้าหน้าที่ประสานงานคุ้มครองข้อมูลส่วนบุคคล',
            self::SuperAdmin => 'ผู้ดูแลระบบสูงสุด',
        };
    }
}
