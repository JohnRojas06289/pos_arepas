<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Recurso no encontrado'], 404);
            }
            return response()->view('errors.404', [], 404);
        });

        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($e->getStatusCode() === 401) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'No autorizado'], 401);
                }
                return response()->view('errors.401', [], 401);
            }
            if ($e->getStatusCode() === 403) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Acceso denegado'], 403);
                }
                return response()->view('errors.401', [], 403);
            }
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                $mensaje = config('app.debug') ? $e->getMessage() : 'Error interno del servidor';
                return response()->json(['message' => $mensaje], 500);
            }
        });
    }
}
