<?php

namespace Tests\Unit;

use App\Support\BuildingCatalog;
use PHPUnit\Framework\TestCase;

class BuildingCatalogTest extends TestCase
{
    public function test_it_infers_floor_from_room_codes(): void
    {
        $this->assertSame('1', BuildingCatalog::inferFloorKey('1101'));
        $this->assertSame('2', BuildingCatalog::inferFloorKey('1201'));
        $this->assertSame('2', BuildingCatalog::inferFloorKey('1_2ทางสัญจร'));
        $this->assertSame('0', BuildingCatalog::inferFloorKey('8001'));
        $this->assertSame('other', BuildingCatalog::inferFloorKey('Unit A'));
    }

    public function test_it_formats_labels(): void
    {
        $this->assertSame('ทางสัญจร', BuildingCatalog::roomLabel('1_2ทางสัญจร'));
        $this->assertSame('1101', BuildingCatalog::roomLabel('1101'));
        $this->assertSame('ชั้น 2', BuildingCatalog::floorLabel('2'));
        $this->assertSame('ชั้นล่าง / ชั้น 0', BuildingCatalog::floorLabel('0'));
        $this->assertSame('อื่นๆ / ไม่ระบุชั้น', BuildingCatalog::floorLabel('other'));
    }
}
