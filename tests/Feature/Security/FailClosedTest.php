<?php

use App\Http\Middleware\ThrottlePasswordChecks;
use App\Http\Middleware\ThrottleRequests;
use App\Support\Concurrency\FileLock;
use App\Support\TwoFactor\UsedTotpSteps;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\Response;

/*
 * Authentication-sensitive limiters must fail closed: if the lock or cache
 * cannot be used, the attempt is refused (and logged), never waved through.
 */

beforeEach(function () {
    $this->lockDir = FileLock::$directory;
    $this->tmp = tempnam(sys_get_temp_dir(), 'phare-lock');
    $this->logFile = tempnam(sys_get_temp_dir(), 'phare-log');
    $this->oldLog = ini_get('error_log');
    // A directory path that is really a regular file: mkdir/fopen under it fail.
    FileLock::$directory = $this->tmp . '/nope';
});

afterEach(function () {
    FileLock::$directory = $this->lockDir;
    ini_set('error_log', (string)$this->oldLog);
    @unlink($this->tmp);
    @unlink($this->logFile);
});

function failClosedRequest(): RequestInterface
{
    $request = test()->createMock(RequestInterface::class);
    $request->method('getURI')->willReturn('/user/login');

    return $request;
}

test('the throttle refuses with 503 and does not call $next when its store fails', function (string $class) {
    ini_set('error_log', $this->logFile);
    $passed = false;
    $response = (new $class(app()))->handle(failClosedRequest(), function () use (&$passed) {
        $passed = true;

        return new Response();
    });

    expect($passed)->toBeFalse()
        ->and($response->getStatusCode())->toBe(503)
        ->and(file_get_contents($this->logFile))->toContain('rate limiter unavailable');
})->with([ThrottleRequests::class, ThrottlePasswordChecks::class]);

test('the throttle refuses when the cache itself throws', function () {
    FileLock::$directory = $this->lockDir;
    $middleware = new class(app()) extends ThrottleRequests
    {
        protected function hit(string $signature, ?int &$retryAfter = null): int
        {
            throw new RuntimeException('cache down');
        }
    };
    $passed = false;
    $response = $middleware->handle(failClosedRequest(), function () use (&$passed) {
        $passed = true;

        return new Response();
    });

    expect($passed)->toBeFalse()->and($response->getStatusCode())->toBe(503);
});

test('a TOTP step is refused, not accepted, when the claim store fails', function () {
    ini_set('error_log', $this->logFile);
    expect(UsedTotpSteps::claim(7, 60000000))->toBeFalse()
        ->and(file_get_contents($this->logFile))->toContain('refusing code');
});

test('a TOTP step cannot be claimed twice, even a replay of an older one', function () {
    FileLock::$directory = $this->lockDir;
    app('cache')->flush();

    expect(UsedTotpSteps::claim(9, 60000001))->toBeTrue()
        ->and(UsedTotpSteps::claim(9, 60000001))->toBeFalse()
        ->and(UsedTotpSteps::claim(9, 60000000))->toBeFalse();
});

test('FileLock serialises concurrent processes (no lost updates)', function () {
    FileLock::$directory = $this->lockDir;
    $counter = tempnam(sys_get_temp_dir(), 'phare-counter');
    file_put_contents($counter, '0');
    $workers = 4;
    $rounds = 25;

    $pids = [];
    for ($i = 0; $i < $workers; $i++) {
        $pid = pcntl_fork();
        if ($pid === 0) {
            for ($n = 0; $n < $rounds; $n++) {
                FileLock::run('counter-test', function () use ($counter) {
                    $value = (int)file_get_contents($counter);
                    usleep(200); // widen the read-modify-write window
                    file_put_contents($counter, (string)($value + 1));
                });
            }
            exit(0);
        }
        $pids[] = $pid;
    }
    foreach ($pids as $pid) {
        pcntl_waitpid($pid, $status);
    }

    $total = (int)file_get_contents($counter);
    @unlink($counter);
    expect($total)->toBe($workers * $rounds);
})->skip(!function_exists('pcntl_fork'), 'pcntl not available');
