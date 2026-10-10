<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('viewing_requests')
            ->where('status', 'pending_pdpa')
            ->update([
                'status' => 'pending_director',
                'workflow_after_pdpa' => null,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Irreversible data move; previous pending_pdpa rows cannot be restored safely.
    }
};
