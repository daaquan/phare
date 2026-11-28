<?php

namespace App\Http\Middleware;

use App\Support\Concurrency\FileLock;
use App\Support\Http\ClientIp;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phare\Contracts\Foundation\Application;
use Phare\Contracts\Http\MiddlewareContract;
use Phare\Foundation\Http\Concerns\BeforeMiddleware;
use Phare\Support\Facades\Auth;

/**
 * Fixed-window rate limiting per route path and caller (`throttle` alias).
 *
 * Replaces Phare\Middleware\ThrottleRequests, which implements only the bare
 * Middleware interface: the Phalcon events bridge never called it, so every
 * `throttle` route was unlimited. This one hooks in like the other route
 * middleware (BeforeMiddleware) and keys guests by the real client IP
 * (ClientIp), not the proxy's 127.0.0.1.
 */
class ThrottleRequests extends MiddlewareContract implements BeforeMiddleware
{
    /** Requests allowed per window. */
    protected const MAX_ATTEMPTS = 60;

    /** Window length in seconds. */
    protected const DECAY_SECONDS = 60;

    public function __construct(protected Application $app) {}

    public function handle(RequestInterface $request, \Closure $next): ResponseInterface
    {
        try {
            $hits = $this->hit(static::class . '|' . $this->signature($request), $retryAfter);
        } catch (\Throwable $e) {
            // Fail closed: these routes guard credentials, so an unreadable
            // counter must not mean unlimited guesses.
            error_log('ThrottleRequests: rate limiter unavailable, refusing request: ' . $e->getMessage());

            return $this->unavailable();
        }

        if ($hits > static::MAX_ATTEMPTS) {
            return $this->tooManyAttempts($retryAfter);
        }

        $this->app['response']->setHeader('X-RateLimit-Limit', (string)static::MAX_ATTEMPTS);
        $this->app['response']->setHeader('X-RateLimit-Remaining', (string)(static::MAX_ATTEMPTS - $hits));

        return $next($request);
    }

    /**
     * Count one attempt in the current fixed window and return the total.
     *
     * Not Phare\RateLimit\RateLimiter: its "<sha1>:timer" keys contain ':',
     * which Phalcon's cache adapters reject, so it throws on every call.
     * The read-modify-write runs under a host-wide FileLock so concurrent
     * workers cannot both read the same count (see FileLock for the ceiling).
     */
    protected function hit(string $signature, ?int &$retryAfter = null): int
    {
        $key = 'throttle_' . sha1($signature);

        $result = FileLock::run($key, fn () => $this->count($key));
        $retryAfter = $result[1];

        return $result[0];
    }

    /**
     * @return array{0: int, 1: int} hits in the window, seconds until reset
     */
    private function count(string $key): array
    {
        $cache = $this->app->make('cache');
        $now = time();

        $window = $cache->get($key);
        if (!is_array($window) || (int)($window['reset'] ?? 0) <= $now) {
            $window = ['hits' => 0, 'reset' => $now + static::DECAY_SECONDS];
        }

        $retryAfter = max(1, (int)$window['reset'] - $now);
        if ($window['hits'] >= static::MAX_ATTEMPTS) {
            return [(int)$window['hits'] + 1, $retryAfter];
        }

        $window['hits']++;
        if (!$cache->put($key, $window, $retryAfter)) {
            throw new \RuntimeException('Rate limit counter could not be stored.');
        }

        return [(int)$window['hits'], $retryAfter];
    }

    /**
     * Who is being counted: the signed-in user, otherwise the client IP;
     * scoped to the route path so one form does not use up another's budget.
     */
    protected function signature(RequestInterface $request): string
    {
        $path = (string)parse_url((string)$request->getURI(), PHP_URL_PATH);
        $id = Auth::id();

        return $path . '|' . ($id !== null ? 'user:' . $id : 'ip:' . ClientIp::current());
    }

    protected function unavailable(): ResponseInterface
    {
        if (method_exists($this->app, 'stop')) {
            $this->app->stop();
        }

        $response = $this->app['response'];
        $response->setStatusCode(503, 'Service Unavailable');
        $response->setHeader('Retry-After', '30');
        $response->setJsonContent(['message' => 'Temporarily unavailable. Please try again shortly.']);

        return $response;
    }

    protected function tooManyAttempts(int $retryAfter): ResponseInterface
    {
        // Halts a Micro (API) dispatch; web dispatch stops via MiddlewareContract.
        if (method_exists($this->app, 'stop')) {
            $this->app->stop();
        }

        $response = $this->app['response'];
        $response->setStatusCode(429, 'Too Many Requests');
        $response->setHeader('Retry-After', (string)$retryAfter);
        $response->setJsonContent(['message' => 'Too many attempts. Please try again in ' . $retryAfter . ' seconds.']);

        return $response;
    }
}
