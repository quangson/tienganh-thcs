<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        if (!$user || $user->role !== $role) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Bạn không có quyền truy cập chức năng này.'], 403);
            }
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập đúng tài khoản để tiếp tục.');
        }

        return $next($request);
    }
}
