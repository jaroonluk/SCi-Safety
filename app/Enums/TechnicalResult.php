<?php

namespace App\Enums;

enum TechnicalResult: string
{
    case Found = 'found';
    case Unclear = 'unclear';
    case AngleOff = 'angle_off';
    case CameraFault = 'camera_fault';
    case RecorderFault = 'recorder_fault';
    case BlindSpot = 'blind_spot';
    case RetentionExpired = 'retention_expired';

    public function label(): string
    {
        return match ($this) {
            self::Found => 'พบกล้องและมีภาพ',
            self::Unclear => 'พบกล้องแต่ภาพไม่ชัด',
            self::AngleOff => 'มุมกล้องคลาดเคลื่อน',
            self::CameraFault => 'กล้องขัดข้อง',
            self::RecorderFault => 'ระบบบันทึกขัดข้อง',
            self::BlindSpot => 'จุดอับไม่มีกล้อง',
            self::RetentionExpired => 'พ้นกำหนดระยะเวลาจัดเก็บ',
        };
    }

    public function lacksFootage(): bool
    {
        return match ($this) {
            self::CameraFault, self::RecorderFault, self::BlindSpot, self::RetentionExpired => true,
            default => false,
        };
    }
}
