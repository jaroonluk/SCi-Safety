<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoverageGap extends Model
{
    protected $fillable = [
        'viewing_request_id', 'building', 'location', 'reason', 'priority', 'created_by',
    ];

    public function viewingRequest(): BelongsTo
    {
        return $this->belongsTo(ViewingRequest::class);
    }
}
