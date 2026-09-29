<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CameraInspection extends Model
{
    protected $fillable = ['camera_id', 'period', 'result', 'note', 'inspected_by'];

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(RepairTicket::class);
    }

    public function resultLabel(): string
    {
        return match ($this->result) {
            'shifted' => 'มุมเบี่ยง',
            'blur' => 'เลนส์มัว',
            'no_signal' => 'สัญญาณขาดหาย',
            'normal' => 'ปกติ',
            default => 'ยังไม่ตรวจ',
        };
    }
}
