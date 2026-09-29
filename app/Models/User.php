<?php

namespace App\Models;

use App\Enums\AccountType;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'role',
        'account_type',
        'email_verified_at',
        'last_login_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'account_type' => AccountType::class,
        ];
    }

    public function viewingRequests(): HasMany
    {
        return $this->hasMany(ViewingRequest::class);
    }

    public function isActiveBackup(): bool
    {
        return OfficerCover::query()
            ->where('backup_user_id', $this->id)
            ->whereDate('starts_on', '<=', today())
            ->whereDate('ends_on', '>=', today())
            ->exists();
    }

    public function hasRole(UserRole ...$roles): bool
    {
        if (in_array($this->role, $roles, true)) {
            return true;
        }

        return in_array(UserRole::CctvAdmin, $roles, true) && $this->isActiveBackup();
    }
}
