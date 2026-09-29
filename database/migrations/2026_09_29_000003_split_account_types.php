<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('account_type', 'internal')
            ->update(['account_type' => 'staff']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('account_type', 'staff')
            ->update(['account_type' => 'internal']);
    }
};
