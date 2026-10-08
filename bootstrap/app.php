<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Mỗi agent phase 2 sở hữu một file route riêng, không sửa file của nhau.
            require base_path('routes/web_identity.php');  // agent Identity
            require base_path('routes/web_library.php');   // agent Library
            require base_path('routes/web_gameplay.php');  // agent Gameplay
            require base_path('routes/web_progress.php');  // agent Progress
            require base_path('routes/web_admin.php');     // agent Admin
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
