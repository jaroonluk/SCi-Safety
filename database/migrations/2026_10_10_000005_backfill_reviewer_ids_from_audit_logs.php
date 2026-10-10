<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->backfill(
            column: 'officer_reviewed_by_id',
            action: 'request.technical_review',
            requires: fn ($query) => $query->where(function ($q) {
                $q->whereNotNull('officer_decision')
                    ->orWhereNotNull('technical_result')
                    ->orWhereNotNull('technical_note');
            }),
        );

        $this->backfill(
            column: 'director_reviewed_by_id',
            action: 'request.decided',
            requires: fn ($query) => $query->whereNotNull('director_note'),
            role: 'director',
        );

        $this->backfill(
            column: 'dean_reviewed_by_id',
            action: 'request.decided',
            requires: fn ($query) => $query->whereNotNull('dean_note'),
            role: 'associate_dean',
        );
    }

    public function down(): void
    {
        // Data backfill only — nothing to reverse safely.
    }

    private function backfill(string $column, string $action, callable $requires, ?string $role = null): void
    {
        $rows = DB::table('viewing_requests')
            ->whereNull($column)
            ->tap($requires)
            ->pluck('id');

        foreach ($rows as $requestId) {
            $logQuery = DB::table('audit_logs')
                ->where('action', $action)
                ->where('subject_type', 'App\\Models\\ViewingRequest')
                ->where('subject_id', $requestId)
                ->whereNotNull('user_id')
                ->orderByDesc('id');

            if ($role !== null) {
                $logQuery->where('properties->role', $role);
            }

            $userId = $logQuery->value('user_id');
            if ($userId) {
                DB::table('viewing_requests')
                    ->where('id', $requestId)
                    ->whereNull($column)
                    ->update([$column => $userId]);
            }
        }
    }
};
