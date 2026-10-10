<?php

namespace Tests\Unit;

use App\Support\ThaiDate;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ThaiDateTest extends TestCase
{
    public function test_it_formats_thailand_datetime_with_buddhist_year(): void
    {
        config(['app.display_timezone' => 'Asia/Bangkok']);

        $value = Carbon::parse('2026-10-10 14:30:00', 'Asia/Bangkok');

        $this->assertSame(
            '10 ตุลาคม 2569 เวลา 14:30 น.',
            ThaiDate::datetime($value)
        );
        $this->assertSame(
            'วันเสาร์ที่ 10 ตุลาคม 2569 เวลา 14:30 น.',
            ThaiDate::datetime($value, true)
        );
    }

    public function test_it_formats_same_day_ranges(): void
    {
        config(['app.display_timezone' => 'Asia/Bangkok']);

        $start = Carbon::parse('2026-10-10 13:00:00', 'Asia/Bangkok');
        $end = Carbon::parse('2026-10-10 15:30:00', 'Asia/Bangkok');

        $this->assertSame(
            '10 ตุลาคม 2569 เวลา 13:00 – 15:30 น.',
            ThaiDate::range($start, $end)
        );
    }
}
