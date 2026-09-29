<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viewing_requests', function (Blueprint $table) {
            $table->string('requester_kind')->default('external_person')->after('user_id');
            $table->string('identifier')->nullable()->after('requester_kind');
            $table->string('affiliation')->nullable()->after('identifier');
            $table->string('phone')->nullable()->after('affiliation');
            $table->string('external_organization')->nullable();
            $table->string('official_letter_no')->nullable();
            $table->string('external_contact_name')->nullable();
            $table->string('subject_name')->nullable();
            $table->string('subject_code')->nullable();
            $table->string('exam_room')->nullable();
            $table->string('landmark')->nullable();
            $table->boolean('on_behalf')->default(false);
            $table->string('intake_channel')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->string('informant_name')->nullable();
            $table->text('on_behalf_reason')->nullable();
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('technical_result')->nullable();
            $table->text('technical_note')->nullable();
            $table->boolean('high_risk')->default(false);
            $table->text('pdpa_opinion')->nullable();
            $table->string('workflow_after_pdpa')->nullable();
            $table->text('supplement_note')->nullable();
            $table->string('resume_status')->nullable();
            $table->timestamp('appointment_at')->nullable();
            $table->string('appointment_place')->nullable();
            $table->string('participants')->nullable();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('appointment_confirmed_at')->nullable();
            $table->timestamp('viewing_started_at')->nullable();
            $table->timestamp('viewing_ended_at')->nullable();
            $table->text('viewing_summary')->nullable();
            $table->timestamp('viewing_confirmed_at')->nullable();
        });

        Schema::create('workflow_rules', function (Blueprint $table) {
            $table->id();
            $table->string('incident_type')->unique();
            $table->string('approver');
            $table->timestamps();
        });

        Schema::create('privacy_notices', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->text('body');
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('coverage_gaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viewing_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('building');
            $table->string('location');
            $table->string('reason');
            $table->string('priority');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cameras', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('building');
            $table->string('floor')->nullable();
            $table->string('coverage');
            $table->string('status')->default('normal');
            $table->unsignedSmallInteger('retention_days')->default(30);
            $table->timestamps();
        });

        Schema::create('camera_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camera_id')->constrained()->cascadeOnDelete();
            $table->string('period');
            $table->string('result')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['camera_id', 'period']);
        });

        Schema::create('repair_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camera_inspection_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('open');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('officer_covers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('backup_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('area')->nullable();
            $table->timestamps();
        });

        $now = now();
        foreach ([
            'lost_property' => 'admin',
            'accident' => 'admin',
            'other' => 'admin',
            'exam' => 'director',
            'external' => 'director',
            'urgent' => 'dean',
        ] as $type => $approver) {
            DB::table('workflow_rules')->insert([
                'incident_type' => $type,
                'approver' => $approver,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('privacy_notices')->insert([
            'version' => '2026-09-29',
            'body' => <<<'TEXT'
ประกาศความเป็นส่วนตัวของระบบ SCi-Safety (ร่างเพื่อให้กองกฎหมาย มหาวิทยาลัยขอนแก่นตรวจรับรองก่อนใช้งานจริง)

คณะวิทยาศาสตร์ มหาวิทยาลัยขอนแก่น จัดเก็บข้อมูลในระบบนี้เพื่อรับคำขอตรวจสอบและนัดหมายดูภาพจากกล้องวงจรปิดภายใต้การควบคุมของเจ้าหน้าที่เท่านั้น ระบบไม่ได้เก็บไฟล์ภาพหรือวิดีโอ และไม่อนุญาตให้ดาวน์โหลดหรือส่งมอบไฟล์ภาพ

ฐานทางกฎหมายที่ใช้เป็นการปฏิบัติภารกิจเพื่อประโยชน์สาธารณะหรือประโยชน์โดยชอบด้วยกฎหมายตามพระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562 ข้อมูลที่ใช้ประกอบด้วยชื่อ ช่องทางติดต่อ รายละเอียดเหตุการณ์ และบันทึกการนัดหมาย

การดูภาพทำได้เฉพาะจุดและช่วงเวลาที่เกี่ยวข้อง ต่อหน้าเจ้าหน้าที่ผู้ควบคุม ห้ามถ่ายภาพหน้าจอ ห้ามอัดวิดีโอ และห้ามนำผู้ที่ไม่เกี่ยวข้องเข้าชม ท่านสามารถขอเข้าถึงหรือแก้ไขข้อมูลของท่านได้โดยติดต่อเจ้าหน้าที่ผู้รับผิดชอบคำขอของคณะวิทยาศาสตร์
TEXT,
            'created_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('officer_covers');
        Schema::dropIfExists('repair_tickets');
        Schema::dropIfExists('camera_inspections');
        Schema::dropIfExists('cameras');
        Schema::dropIfExists('coverage_gaps');
        Schema::dropIfExists('privacy_notices');
        Schema::dropIfExists('workflow_rules');
        Schema::table('viewing_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recorded_by_id');
            $table->dropConstrainedForeignId('supervisor_id');
            $table->dropColumn([
                'requester_kind', 'identifier', 'affiliation', 'phone',
                'external_organization', 'official_letter_no', 'external_contact_name',
                'subject_name', 'subject_code', 'exam_room', 'landmark',
                'on_behalf', 'intake_channel', 'received_at', 'informant_name', 'on_behalf_reason',
                'technical_result', 'technical_note', 'high_risk', 'pdpa_opinion', 'workflow_after_pdpa',
                'supplement_note', 'resume_status', 'appointment_at', 'appointment_place', 'participants',
                'appointment_confirmed_at', 'viewing_started_at', 'viewing_ended_at',
                'viewing_summary', 'viewing_confirmed_at',
            ]);
        });
    }
};
