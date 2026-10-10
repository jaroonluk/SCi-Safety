<?php

namespace App\Support;

use App\Models\Building;
use Illuminate\Support\Facades\Schema;

class BuildingCatalog
{
    /**
     * Distinct buildings from the building table for the request form.
     *
     * @return list<array{code: string, label: string, rooms: int}>
     */
    public static function forForm(): array
    {
        if (! Schema::hasTable('building')) {
            return [];
        }

        $labels = config('sci.building_labels', []);

        $rows = Building::query()
            ->selectRaw('rep_build, COUNT(*) as rooms')
            ->groupBy('rep_build')
            ->orderBy('rep_build')
            ->get();

        $catalog = $rows
            ->map(static function ($row) use ($labels): array {
                $code = (string) $row->rep_build;

                return [
                    'code' => $code,
                    'label' => $labels[$code] ?? ('อาคาร '.$code),
                    'rooms' => (int) $row->rooms,
                ];
            })
            ->filter(static fn (array $item): bool => $item['code'] !== '')
            ->values()
            ->all();

        usort($catalog, static function (array $a, array $b) use ($labels): int {
            $aRank = array_key_exists($a['code'], $labels) ? 0 : 1;
            $bRank = array_key_exists($b['code'], $labels) ? 0 : 1;

            return $aRank <=> $bRank ?: strnatcasecmp($a['label'], $b['label']);
        });

        return $catalog;
    }

    public static function inferFloorKey(string $room): string
    {
        if (preg_match('/^\d_(\d+)/u', $room, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('/^\d(\d)\d+/u', $room, $matches) === 1) {
            return $matches[1];
        }

        return 'other';
    }

    public static function floorLabel(string $floorKey): string
    {
        if ($floorKey === 'other') {
            return 'อื่นๆ / ไม่ระบุชั้น';
        }

        if ($floorKey === '0') {
            return 'ชั้นล่าง / ชั้น 0';
        }

        return 'ชั้น '.$floorKey;
    }

    public static function roomLabel(string $room): string
    {
        if (preg_match('/^\d_\d+(.+)$/u', $room, $matches) === 1) {
            return trim($matches[1]) !== '' ? trim($matches[1]) : $room;
        }

        return $room;
    }
}
