<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;

class Handler extends ExceptionHandler
{

    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    public function render($request, Exception $exception)
    {

        if($exception instanceof NotFoundHttpException) {
            return response()->json(["message" => "PAGE_NOT_FOUND"], 404);
        }

        if ($exception instanceof UnauthorizedHttpException) {
            // detect previous instance
            if ($exception->getPrevious() instanceof TokenExpiredException) {
                return response()->json(['message' => 'TOKEN_EXPIRED'], $exception->getStatusCode());
            } else if ($exception->getPrevious() instanceof TokenInvalidException) {
                return response()->json(['message' => 'TOKEN_INVALID'], $exception->getStatusCode());
            } else if ($exception->getPrevious() instanceof TokenBlacklistedException) {
                return response()->json(['message' => 'TOKEN_BLACKLISTED'], $exception->getStatusCode());
            } else {
                return response()->json(['message' => "UNAUTHORIZED_REQUEST"], 401);
            }
        } elseif ($exception instanceof JWTException) {
            return response()->json(['message' => "TOKEN_REQUIRED"], 401);
        }

        return parent::render($request, $exception);
    }
}
