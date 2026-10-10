<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BuildingSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('building')) {
            return;
        }

        $path = database_path('building.sql');
        if (! is_file($path)) {
            return;
        }

        $sql = file_get_contents($path);
        if ($sql === false) {
            return;
        }

        preg_match_all("/INSERT INTO `building` VALUES \\('([^']+)', '([^']+)'\\);/u", $sql, $matches, PREG_SET_ORDER);

        $rows = [];
        foreach ($matches as $match) {
            $rows[$match[1].'|'.$match[2]] = [
                'rep_build' => $match[1],
                'rep_room' => $match[2],
            ];
        }

        if ($rows === []) {
            return;
        }

        DB::table('building')->delete();

        foreach (array_chunk(array_values($rows), 200) as $chunk) {
            DB::table('building')->insert($chunk);
        }
    }
}
