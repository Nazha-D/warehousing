<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    public function render($request, Throwable $exception)
    {
        // Debugging line: Check if Laravel thinks this is an API request
        \Log::info('Is API request? ' . ($request->is('api/*') ? 'Yes' : 'No'));
        \Log::info('Expects JSON? ' . ($request->expectsJson() ? 'Yes' : 'No'));

        if ($exception instanceof AuthorizationException) {
            // Check if the request path starts with 'api/'
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'You are not authorized to perform this action.'
                ], Response::HTTP_FORBIDDEN); // 403
            }
        }

        // This line handles all other exceptions/requests using default behavior
        return parent::render($request, $exception);
    }

}
