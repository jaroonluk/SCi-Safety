<?php

namespace App\Models;

use App\Enums\IncidentType;
use Illuminate\Database\Eloquent\Model;

class WorkflowRule extends Model
{
    protected $fillable = ['incident_type', 'approver'];

    public function incident(): ?IncidentType
    {
        return IncidentType::tryFrom($this->incident_type);
    }

    public function approverLabel(): string
    {
        return match ($this->approver) {
            'director' => 'ผู้อำนวยการกองบริหารงานคณะ',
            'dean' => 'รองคณบดีฝ่ายบริหาร',
            default => 'เจ้าหน้าที่นัดหมายได้เอง',
        };
    }
}
