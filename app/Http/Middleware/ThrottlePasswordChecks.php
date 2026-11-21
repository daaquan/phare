<?php

namespace App\Http\Middleware;

/**
 * `throttle.password`: endpoints that check a password or credentials
 * (login, password change / confirm, account deletion, reset). Signed-in
 * callers are counted per user, guests per client IP.
 */
class ThrottlePasswordChecks extends ThrottleRequests
{
    protected const MAX_ATTEMPTS = 5;
}
