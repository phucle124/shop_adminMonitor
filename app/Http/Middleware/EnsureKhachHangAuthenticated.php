<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKhachHangAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('ma_khach_hang')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'authenticated' => false,
                    'message'       => 'Vui lòng đăng nhập tài khoản khách hàng',
                ], 401);
            }

            return redirect()
                ->route('login') // đổi theo tên route trang đăng nhập của bạn
                ->with('auth_message', 'Vui lòng đăng nhập');
        }

        return $next($request);
    }

}
