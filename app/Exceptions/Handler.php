<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Inertia\Inertia;
use Throwable;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);

        // Handle custom rate limit message
        if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
            return back()->withErrors([
                'comment' => 'You have reached the maximum of 10 comments today. Please try again tomorrow.',
            ]);
        }

        // Get the status code
        $statusCode = $response->getStatusCode();

        // Only handle these specific error codes
        if (in_array($statusCode, [403, 404, 500, 503])) {
            // Make sure we're not handling API requests
            if (!$request->expectsJson()) {
                // Debug information
                Log::debug('Rendering error page', [
                    'status' => $statusCode,
                    'message' => $e->getMessage(),
                    'using_inertia' => true
                ]);

                // Render the Inertia error component
                return Inertia::render('errors/Error', [
                    'status' => $statusCode,
                    'message' => $e->getMessage(),
                ])
                ->toResponse($request)
                ->setStatusCode($statusCode);
            }
        }

        return $response;
    }
}
