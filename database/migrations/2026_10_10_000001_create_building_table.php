<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('building')) {
            return;
        }

        Schema::create('building', function (Blueprint $table) {
            $table->string('rep_build', 20);
            $table->string('rep_room', 20);
            $table->primary(['rep_build', 'rep_room']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building');
    }
};
