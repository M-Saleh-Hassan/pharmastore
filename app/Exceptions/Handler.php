<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if( $exception instanceof ModelNotFoundException)
        {
            return response()->json(['status'=> 0, 'data' => ['message' => 'The object you are trying to access by id not found.']], 404);
        }
        elseif ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException)
        {
            return response()->json(['status'=> 0, 'data' => ['message' => $exception->getMessage()]], 441);
        }
        elseif ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException)
        {
            return response()->json(['status'=> 0, 'data' => ['message' => $exception->getMessage()]], 442);
        }
        elseif ($exception instanceof \Tymon\JWTAuth\Exceptions\JWTException)
        {
            return response()->json(['status'=> 0, 'data' => ['message' => $exception->getMessage()]], 401);
        }
        elseif ($exception instanceof HttpResponseException)
        {
            return parent::render($request, $exception);
        }
        return response()->json([
            'status'=>0,
            'errors' => [
                'message'   => $exception->getMessage(),
                'exception' => get_class($exception),
                'file'      => $exception->getFile(),
                'line'      => $exception->getLine(),
                // 'trace'     => $e->getTrace()
            ]
        ],500);
        // return parent::render($request, $exception);
    }
}
