<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // CRM Module — يُسجَّل بعد web.php لضمان الأولوية
            Route::middleware('web')->group(base_path('routes/crm.php'));
            Route::middleware('web')->group(base_path('routes/portal.php'));
            // Referral Module
            Route::middleware('web')->group(base_path('routes/referral.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'subscription'   => \App\Http\Middleware\CheckSubscriptionLimits::class,
            'active.account' => \App\Http\Middleware\EnsureUserIsActive::class,
            'portal.auth'    => \App\Http\Middleware\EnsurePortalAuthenticated::class,
        ]);

        // تطبيق التحقق من حالة الحساب على جميع الـ web routes المحمية
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // استجابة JSON تلقائية لطلبات الـ API
        $exceptions->shouldRenderJsonWhen(function (Request $request) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // 404
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'المورد غير موجود.'], 404);
            }
        });

        // 403
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'غير مصرح.'], 403);
            }
        });

        // 422 Validation
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'بيانات غير صالحة.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // 401 Unauthenticated
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'يجب تسجيل الدخول أولاً.'], 401);
            }
        });

    })->create();
