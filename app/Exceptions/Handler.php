<?php

namespace App\Exceptions;

use Phalcon\Mvc\Router\Exception as RouteException;
use Phare\Foundation\Exceptions\Handler as ExceptionHandler;
use Phare\Foundation\Http\ResponseStatusCode;

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
            $error = $request->isAjax() ? ['error' => 'Resource not found'] : '404 page not found.';
            return response($error, ResponseStatusCode::NOT_FOUND);
        }

        return parent::render($request, $e);
    }
}
