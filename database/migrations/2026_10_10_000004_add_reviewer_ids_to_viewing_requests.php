<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viewing_requests', function (Blueprint $table) {
            $table->foreignId('officer_reviewed_by_id')->nullable()->after('officer_decision')->constrained('users')->nullOnDelete();
            $table->foreignId('director_reviewed_by_id')->nullable()->after('director_note')->constrained('users')->nullOnDelete();
            $table->foreignId('dean_reviewed_by_id')->nullable()->after('dean_note')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('viewing_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('officer_reviewed_by_id');
            $table->dropConstrainedForeignId('director_reviewed_by_id');
            $table->dropConstrainedForeignId('dean_reviewed_by_id');
        });
    }
};
