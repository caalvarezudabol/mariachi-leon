<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        $this->renderable(function (NotFoundHttpException $e, $request) {
            return redirect()->route('web.home');
        });

        $this->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->is('logout') || !$request->expectsJson()) {
                return redirect()->route('web.home');
            }
        });

        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->is('logout') || !$request->expectsJson()) {
                    return redirect()->route('web.home');
                }
            }
        });
    }
}
