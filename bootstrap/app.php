<?php

use App\Helpers\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        /*
         * Handle validation exceptions for API requests.
         */
        $exceptions->render(function (
            ValidationException $exception,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return ApiResponse::validation(
                    $exception->errors()
                );
            }
        });

        /*
         * Handle 404 exceptions for API requests.
         */
        $exceptions->render(function (
            NotFoundHttpException $exception,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    'Resource Not Found',
                    404
                );
            }
        });

        /*
         * Handle all other API exceptions.
         */
        $exceptions->render(function (
            Throwable $exception,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    config('app.debug')
                        ? $exception->getMessage()
                        : 'Something went wrong',
                    500
                );
            }
        });
    })
    ->create();