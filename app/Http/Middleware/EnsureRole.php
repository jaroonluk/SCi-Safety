<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        abort_if($user === null, 403);

        foreach ($roles as $role) {
            if ($user->role->value === $role) {
                return $next($request);
            }

            if ($role === UserRole::CctvAdmin->value && $user->isActiveBackup()) {
                return $next($request);
            }
        }

        abort(403);
    }
}
