<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairTicket extends Model
{
    protected $fillable = ['camera_inspection_id', 'status', 'note'];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(CameraInspection::class, 'camera_inspection_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'in_progress' => 'กำลังซ่อม',
            'done' => 'ซ่อมเสร็จ',
            default => 'เปิดงาน',
        };
    }
}
