<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viewing_requests', function (Blueprint $table) {
            $table->text('director_note')->nullable()->after('technical_note');
            $table->text('dean_note')->nullable()->after('director_note');
            $table->string('officer_decision')->nullable()->after('dean_note');
        });
    }

    public function down(): void
    {
        Schema::table('viewing_requests', function (Blueprint $table) {
            $table->dropColumn(['director_note', 'dean_note', 'officer_decision']);
        });
    }
};
