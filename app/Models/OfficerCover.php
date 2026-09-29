<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficerCover extends Model
{
    protected $fillable = ['officer_user_id', 'backup_user_id', 'starts_on', 'ends_on', 'area'];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_user_id');
    }

    public function backup(): BelongsTo
    {
        return $this->belongsTo(User::class, 'backup_user_id');
    }
}
