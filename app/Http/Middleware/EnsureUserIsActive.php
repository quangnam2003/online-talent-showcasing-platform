<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Tai khoan bi admin khoa (is_active = false) phai bi chan ngay o request ke tiep,
// khong chi luc dang nhap — neu khong, phien cu van binh luan / dang video / vote duoc.
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'Tài khoản của bạn đã bị khóa.';

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $message], 403);
            }

            return redirect()->route('login')->with('error', $message);
        }

        return $next($request);
    }
}
