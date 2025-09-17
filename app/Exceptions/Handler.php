<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Inertia\Inertia;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * The list of inputs that are never flashed to the session on validation exceptions.
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
            // Optional: log exceptions here
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // 1️⃣ API requests → return JSON
        if ($request->expectsJson()) {
            return parent::render($request, $e);
        }

        // 1b️⃣ Inertia SPA requests
        $isInertia = $request->header('X-Inertia');

        // 2️⃣ Throttling errors
        if ($e instanceof ThrottleRequestsException) {
            return back()->withErrors([
                'comment' => 'You have reached the maximum allowed actions. Please try again later.',
            ]);
        }

        // 3️⃣ Validation errors
        if ($e instanceof ValidationException) {
            return back()->withErrors($e->errors())->withInput();
        }

        // 4️⃣ Authorization errors → 403
        if ($e instanceof AuthorizationException) {
            if ($isInertia) {
                return Inertia::render('errors/Error', [
                    'status' => 403,
                    'message' => $e->getMessage() ?: 'You do not have permission to access this resource.',
                ])->toResponse($request)->setStatusCode(403);
            }
            return parent::render($request, $e);
        }

        // 5️⃣ Not Found errors → 404
        if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
            Log::info('Rendering Inertia 404 page', [
                'url' => $request->fullUrl(),
                'is_inertia' => $isInertia,
                'exception_class' => get_class($e),
            ]);
            dd([
                'caught_by_handler' => true,
                'url' => $request->fullUrl(),
                'is_inertia' => $request->header('X-Inertia'),
                'exception_class' => get_class($e),
            ]);

            return Inertia::render('errors/NotFound', [
                'status' => 404,
                'message' => 'The resource you are looking for could not be found.',
            ])->toResponse($request)->setStatusCode(404);
        }

        // 6️⃣ All other HTTP exceptions
        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $messages = [
                400 => 'Bad request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not found',
                419 => 'Page expired',
                429 => 'Too many requests',
                500 => 'Something went wrong on our servers. We\'re working to fix it.',
                503 => 'The service is temporarily unavailable. Please try again later.',
            ];

            Log::debug('Rendering Inertia error page', [
                'status' => $status,
                'original_message' => $e->getMessage(),
                'final_message' => $messages[$status] ?? 'An error occurred',
            ]);

            if ($isInertia) {
                return Inertia::render('errors/Error', [
                    'status' => $status,
                    'message' => $messages[$status] ?? $e->getMessage(),
                ])->toResponse($request)->setStatusCode($status);
            }
        }

        // 7️⃣ Fallback → unexpected server errors
        Log::error('Unexpected server exception', [
            'exception' => $e,
        ]);

        if ($isInertia) {
            return Inertia::render('errors/Error', [
                'status' => 500,
                'message' => 'Something went wrong on our servers. We\'re working to fix it.',
            ])->toResponse($request)->setStatusCode(500);
        }

        return parent::render($request, $e);
    }

    /**
     * Ensure all HTTP exceptions go through the Inertia render logic
     */
    protected function renderHttpException(HttpExceptionInterface $e)
    {
        return $this->render(request(), $e);
    }
}
