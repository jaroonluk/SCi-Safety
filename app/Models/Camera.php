<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Camera extends Model
{
    protected $fillable = ['code', 'name', 'building', 'floor', 'coverage', 'status', 'retention_days'];

    public function inspections(): HasMany
    {
        return $this->hasMany(CameraInspection::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'broken' => 'ชำรุด',
            'waiting_repair' => 'รอซ่อม',
            'closed' => 'ปิดปรับปรุง',
            default => 'ปกติ',
        };
    }
}
