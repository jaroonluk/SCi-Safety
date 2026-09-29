<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivacyNotice extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['version', 'body', 'published_by'];

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public static function current(): self
    {
        return static::query()->latest('id')->firstOrFail();
    }
}
