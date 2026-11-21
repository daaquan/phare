<?php

namespace App\Http\Middleware;

use App\Support\Http\ClientIp;
use Phalcon\Http\RequestInterface;

/**
 * `throttle.two-factor`: 5 attempts a minute per pending login (`login.id`),
 * so a six-digit code cannot be brute-forced by rotating IPs, and one IP
 * cannot lock out other people's challenges.
 */
class ThrottleTwoFactor extends ThrottleRequests
{
    protected const MAX_ATTEMPTS = 5;

    protected function signature(RequestInterface $request): string
    {
        $pending = app('session')->get('login.id');

        return $pending !== null ? 'login:' . (int)$pending : 'ip:' . ClientIp::current();
    }
}
