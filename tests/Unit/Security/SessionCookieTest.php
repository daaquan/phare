<?php

use App\Support\Http\SessionCookie;

test('session cookie settings default to HttpOnly, Secure and SameSite=Lax', function () {
    $ini = SessionCookie::iniSettings([]);

    expect($ini['session.cookie_httponly'])->toBe('1')
        ->and($ini['session.cookie_secure'])->toBe('1')
        ->and($ini['session.cookie_samesite'])->toBe('Lax')
        ->and($ini['session.use_strict_mode'])->toBe('1')
        ->and($ini['session.use_only_cookies'])->toBe('1');
});

test('session cookie settings follow the config values', function (array $cookie, string $secure, string $httpOnly, string $sameSite) {
    $ini = SessionCookie::iniSettings($cookie);

    expect($ini['session.cookie_secure'])->toBe($secure)
        ->and($ini['session.cookie_httponly'])->toBe($httpOnly)
        ->and($ini['session.cookie_samesite'])->toBe($sameSite);
})->with([
    'local http' => [['secure' => false, 'http_only' => true, 'same_site' => 'lax'], '0', '1', 'Lax'],
    'strict' => [['secure' => true, 'same_site' => 'STRICT'], '1', '1', 'Strict'],
    'unknown same-site falls back to Lax' => [['same_site' => 'bogus'], '1', '1', 'Lax'],
    'None without Secure is downgraded' => [['secure' => false, 'same_site' => 'none'], '0', '1', 'Lax'],
    'None with Secure' => [['secure' => true, 'same_site' => 'none'], '1', '1', 'None'],
]);

test('the shipped session config enables HttpOnly and SameSite=Lax', function () {
    $cookie = config('session.cookie');
    $cookie = is_object($cookie) ? $cookie->toArray() : (array)$cookie;

    expect($cookie['http_only'])->toBeTrue()
        ->and(strtolower((string)$cookie['same_site']))->toBe('lax');
});

test('the booted application starts its session with the hardened cookie', function () {
    $script = dirname(__DIR__, 2) . '/Fixtures/session-cookie-probe.php';
    $env = array_merge(getenv(), ['APP_ENV' => 'testing', 'SESSION_SECURE_COOKIE' => 'true']);
    $process = proc_open([PHP_BINARY, $script], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, $env);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    $status = proc_close($process);

    expect($status)->toBe(0, $stderr);
    $result = json_decode((string)$stdout, true);

    expect($result)->toBeArray($stdout . $stderr)
        ->and($result['params']['httponly'])->toBeTrue()
        ->and($result['params']['secure'])->toBeTrue()
        ->and($result['params']['samesite'])->toBe('Lax')
        ->and($result['strict'])->toBe('1');
});

test('debug mode is forced off in production whatever APP_DEBUG says', function (string $env, bool $debugFlag, bool $expected) {
    $saved = [getenv('APP_ENV'), getenv('APP_DEBUG'), $_ENV['APP_ENV'] ?? null, $_ENV['APP_DEBUG'] ?? null, $_SERVER['APP_ENV'] ?? null, $_SERVER['APP_DEBUG'] ?? null];
    $set = function (string $key, ?string $value) {
        if ($value === null) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        } else {
            putenv("{$key}={$value}");
            $_ENV[$key] = $_SERVER[$key] = $value;
        }
    };

    try {
        $set('APP_ENV', $env);
        $set('APP_DEBUG', $debugFlag ? 'true' : 'false');
        $config = require dirname(__DIR__, 3) . '/config/app.php';

        expect($config['debug'])->toBe($expected);
    } finally {
        putenv($saved[0] === false ? 'APP_ENV' : 'APP_ENV=' . $saved[0]);
        putenv($saved[1] === false ? 'APP_DEBUG' : 'APP_DEBUG=' . $saved[1]);
        foreach (['APP_ENV' => [2, 4], 'APP_DEBUG' => [3, 5]] as $key => [$envIdx, $serverIdx]) {
            if ($saved[$envIdx] === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $saved[$envIdx];
            }
            if ($saved[$serverIdx] === null) {
                unset($_SERVER[$key]);
            } else {
                $_SERVER[$key] = $saved[$serverIdx];
            }
        }
    }
})->with([
    'production with APP_DEBUG=true' => ['production', true, false],
    'production with APP_DEBUG=false' => ['production', false, false],
    'local with APP_DEBUG=true' => ['local', true, true],
]);
