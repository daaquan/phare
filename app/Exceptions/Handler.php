<?php

namespace App\Exceptions;

use Phalcon\Mvc\Router\Exception as RouteException;
use Phare\Foundation\Exceptions\Handler as ExceptionHandler;
use Phare\Foundation\Http\ResponseStatusCode;
use Phare\Support\Facades\Inertia;
use Psr\Log\LogLevel;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, LogLevel::*>
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
            if ($request->isAjax() || str_contains((string)$request->getHeader('Accept'), 'application/json')) {
                return response(['error' => 'Resource not found'], ResponseStatusCode::NOT_FOUND);
            }

            // Render the shared Inertia Error page (HTML on a normal visit,
            // JSON on an Inertia visit), with the 404 status code.
            return Inertia::render('Error', ['status' => 404])
                ->toResponse($request)
                ->setStatusCode(ResponseStatusCode::NOT_FOUND->value);
        }

        return parent::render($request, $e);
    }
}
