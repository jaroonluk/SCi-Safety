<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Session\Session;

class Impersonation
{
    public const SESSION_KEY = 'impersonator_id';

    public const ROLE_SESSION_KEY = 'acting_as_role';

    public static function enabled(): bool
    {
        return (bool) config('sci.impersonation_enabled', false);
    }

    public static function active(): bool
    {
        return session()->has(self::SESSION_KEY);
    }

    public static function isRolePreview(): bool
    {
        return self::active() && session()->has(self::ROLE_SESSION_KEY);
    }

    public static function isUserImpersonation(): bool
    {
        return self::active() && ! self::isRolePreview();
    }

    public static function impersonatorId(): ?int
    {
        $id = session(self::SESSION_KEY);

        return $id !== null ? (int) $id : null;
    }

    public static function impersonator(): ?User
    {
        $id = self::impersonatorId();

        return $id !== null ? User::query()->find($id) : null;
    }

    public static function actingAsRole(): ?UserRole
    {
        $value = session(self::ROLE_SESSION_KEY);

        if ($value === null || $value === '') {
            return null;
        }

        return UserRole::tryFrom((string) $value);
    }

    public static function actingAsRoleFor(User $user): ?UserRole
    {
        if (! self::isRolePreview()) {
            return null;
        }

        if (self::impersonatorId() !== (int) $user->getKey()) {
            return null;
        }

        return self::actingAsRole();
    }

    public static function clear(?Session $session = null): void
    {
        $bag = $session ?? session();
        $bag->forget([self::SESSION_KEY, self::ROLE_SESSION_KEY]);
    }
}
