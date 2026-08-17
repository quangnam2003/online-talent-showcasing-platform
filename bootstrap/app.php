<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias phan quyen theo vai tro: ->middleware('role:admin')
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);

        // Chan tai khoan bi khoa o moi request web (khong chi luc dang nhap)
        $middleware->web(append: [
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Request vuot post_max_size cua PHP (tep upload qua lon) → bao loi than thien
        // thay vi trang 413 trang; form upload gui bang XHR nhan JSON de hien ngay tai cho.
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $limit = ini_get('upload_max_filesize') ?: '100M';
            $message = 'Tệp quá lớn — máy chủ chỉ nhận tối đa '.$limit.'. Hãy nén hoặc cắt ngắn tiết mục rồi thử lại.';

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $message, 'errors' => ['video' => [$message]]], 413);
            }

            return back()->withInput($request->except(['video', 'thumbnail']))->with('error', $message);
        });
    })->create();
