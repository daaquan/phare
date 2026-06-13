<?php

namespace App\Http\Middleware;

use Phare\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * URIs excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected array $except = [
        //
    ];
}
