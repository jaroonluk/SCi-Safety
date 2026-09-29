<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viewing_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('incident_type');
            $table->string('purpose');
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->string('building');
            $table->string('floor')->nullable();
            $table->string('location');
            $table->string('urgency');
            $table->text('details');
            $table->string('status')->default('submitted');
            $table->string('privacy_notice_version');
            $table->timestamp('privacy_accepted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viewing_requests');
    }
};
