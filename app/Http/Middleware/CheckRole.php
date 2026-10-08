<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kiểm tra role của user đang đăng nhập.
 *
 * Dùng: 'role:admin' hoặc 'role:teacher,parent'
 * Không đúng role → abort 403.
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        // Admin có toàn quyền: qua mọi cổng kiểm tra role.
        if ($user->role === 'admin') {
            return $next($request);
        }

        $allowed = array_map('trim', $roles);

        if (! in_array($user->role, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
