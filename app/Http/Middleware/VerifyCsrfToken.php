<?php

namespace App\Http\Middleware;

use Phare\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * CSRF 検証から除外する URI。
     *
     * @var array<int, string>
     */
    protected array $except = [
        //
    ];
}
