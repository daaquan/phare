<?php

namespace App\Exceptions;

use Framework\Foundation\Exceptions\Handler as ExceptionHandler;
use Framework\Foundation\Http\ResponseStatusCode;
use Phalcon\Mvc\Router\Exception as RouteException;

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

    public function render($request, \Throwable $e)
    {
        if ($e instanceof RouteException) {
            return response(['error' => 'Resource not found'], ResponseStatusCode::NOT_FOUND);
        }

        return parent::render($request, $e);
    }
}
