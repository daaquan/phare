<?php

use App\Http\Middleware\HandleInertiaRequests;

function inertiaValidationErrors(): object
{
    $middleware = new class() extends HandleInertiaRequests
    {
        public function errors(): object
        {
            return $this->resolveErrors();
        }
    };

    return $middleware->errors();
}

test('Inertia shares empty validation errors as an object', function () {
    app('session')->remove('errors');

    expect(json_encode(inertiaValidationErrors()))->toBe('{}');
});

test('Inertia consumes flashed validation errors exactly once', function () {
    $session = app('session');
    $session->set('errors', ['email' => 'The email address is invalid.']);

    try {
        expect((array)inertiaValidationErrors())->toBe(['email' => 'The email address is invalid.'])
            ->and($session->has('errors'))->toBeFalse()
            ->and(json_encode(inertiaValidationErrors()))->toBe('{}');
    } finally {
        $session->remove('errors');
    }
});
