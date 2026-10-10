<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class ThaiDate
{
    private const MONTHS = [
        1 => 'มกราคม',
        2 => 'กุมภาพันธ์',
        3 => 'มีนาคม',
        4 => 'เมษายน',
        5 => 'พฤษภาคม',
        6 => 'มิถุนายน',
        7 => 'กรกฎาคม',
        8 => 'สิงหาคม',
        9 => 'กันยายน',
        10 => 'ตุลาคม',
        11 => 'พฤศจิกายน',
        12 => 'ธันวาคม',
    ];

    private const MONTHS_SHORT = [
        1 => 'ม.ค.',
        2 => 'ก.พ.',
        3 => 'มี.ค.',
        4 => 'เม.ย.',
        5 => 'พ.ค.',
        6 => 'มิ.ย.',
        7 => 'ก.ค.',
        8 => 'ส.ค.',
        9 => 'ก.ย.',
        10 => 'ต.ค.',
        11 => 'พ.ย.',
        12 => 'ธ.ค.',
    ];

    private const WEEKDAYS = [
        0 => 'อาทิตย์',
        1 => 'จันทร์',
        2 => 'อังคาร',
        3 => 'พุธ',
        4 => 'พฤหัสบดี',
        5 => 'ศุกร์',
        6 => 'เสาร์',
    ];

    public static function at(?CarbonInterface $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        return Carbon::instance($value)->timezone(config('app.display_timezone', 'Asia/Bangkok'));
    }

    public static function datetime(?CarbonInterface $value, bool $withWeekday = false): string
    {
        $date = self::at($value);
        if ($date === null) {
            return '—';
        }

        $prefix = $withWeekday
            ? 'วัน'.self::WEEKDAYS[(int) $date->dayOfWeek].'ที่ '
            : '';

        return sprintf(
            '%s%d %s %d เวลา %s น.',
            $prefix,
            $date->day,
            self::MONTHS[$date->month],
            $date->year + 543,
            $date->format('H:i'),
        );
    }

    public static function short(?CarbonInterface $value): string
    {
        $date = self::at($value);
        if ($date === null) {
            return '—';
        }

        return sprintf(
            '%d %s %d · %s น.',
            $date->day,
            self::MONTHS_SHORT[$date->month],
            $date->year + 543,
            $date->format('H:i'),
        );
    }

    public static function range(?CarbonInterface $start, ?CarbonInterface $end): string
    {
        $from = self::at($start);
        $to = self::at($end);

        if ($from === null && $to === null) {
            return '—';
        }

        if ($from === null) {
            return 'ถึง '.self::datetime($to);
        }

        if ($to === null) {
            return 'ตั้งแต่ '.self::datetime($from);
        }

        if ($from->isSameDay($to)) {
            return sprintf(
                '%d %s %d เวลา %s – %s น.',
                $from->day,
                self::MONTHS[$from->month],
                $from->year + 543,
                $from->format('H:i'),
                $to->format('H:i'),
            );
        }

        return self::short($from).' ถึง '.self::short($to);
    }
}
