<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
        $this->renderable(function (Throwable $e) {
            if ($this->isHttpException($e)) {
                if ($e->getStatusCode() == 405) {
                    http_response_code(405);
                    $error[] = [
                        'code' => 405,
                        'message' => 'Wrong HTTP method for this endpoint!'
                    ];
                    return response()->json($error, 405);
                }
            }
        });


        $this->reportable(function (Throwable $e) {
        });
    }
}
