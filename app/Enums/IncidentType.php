<?php

namespace App\Enums;

enum IncidentType: string
{
    case LostProperty = 'lost_property';
    case Accident = 'accident';
    case Exam = 'exam';
    case External = 'external';
    case Urgent = 'urgent';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::LostProperty => 'ทรัพย์สินสูญหาย',
            self::Accident => 'อุบัติเหตุ',
            self::Exam => 'เหตุการณ์สอบ',
            self::External => 'คำขอจากหน่วยงานภายนอก',
            self::Urgent => 'เหตุเร่งด่วนด้านความปลอดภัย',
            self::Other => 'เหตุอื่น',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::LostProperty => 'ผู้ยื่นทำของหายในพื้นที่คณะ และต้องการให้เจ้าหน้าที่ตรวจว่ามีภาพในช่วงเวลานั้นหรือไม่',
            self::Accident => 'มีคนลื่นล้ม ชนกัน หรือได้รับบาดเจ็บ และต้องการดูภาพเพื่อประกอบการดูแลเหตุการณ์',
            self::Exam => 'เกี่ยวกับการสอบ หรือข้อสงสัยว่ามีการทุจริตในการสอบ ต้องระบุวิชา ห้องสอบ และวันเวลา',
            self::External => 'คำขอจากบุคคลหรือหน่วยงานภายนอก เช่น เจ้าหน้าที่ตำรวจหรือบริษัทประกัน ต้องมีหนังสือและชื่อผู้ประสานงาน',
            self::Urgent => 'เหตุที่อาจเป็นอันตราย หรือต้องให้ผู้บริหารตัดสินใจทันที',
            self::Other => 'เรื่องที่ไม่อยู่ในประเภทด้านบน ใช้เมื่ออธิบายในรายละเอียดได้ชัดกว่าการเลือกประเภทเฉพาะ',
        };
    }

    public function example(): string
    {
        return match ($this) {
            self::LostProperty => 'เช่น กระเป๋า โทรศัพท์ หรือจักรยานหายที่อาคารเรียน',
            self::Accident => 'เช่น ลื่นล้มที่บันได หรือรถจักรยานชนกันที่ลานจอดรถ',
            self::Exam => 'เช่น ข้อสงสัยระหว่างสอบในห้องที่ระบุไว้',
            self::External => 'เช่น หนังสือจากสถานีตำรวจหรือบริษัทประกันภัย',
            self::Urgent => 'เช่น เหตุอันตรายที่ต้องนัดดูภาพโดยเร็ว',
            self::Other => 'เช่น เหตุที่อธิบายเพิ่มในรายละเอียดได้เอง',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::LostProperty => 'clipboard',
            self::Accident => 'help',
            self::Exam => 'document',
            self::External => 'users',
            self::Urgent => 'shield',
            self::Other => 'route',
        };
    }
}
