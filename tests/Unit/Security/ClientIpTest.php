<?php

use App\Support\Http\ClientIp;

const PROXIES = ['127.0.0.1', '::1'];

test('the client IP comes from trusted proxy headers only', function (array $server, string $expected) {
    expect(ClientIp::resolve($server, PROXIES))->toBe($expected);
})->with([
    'cloudflared via loopback with CF-Connecting-IP' => [['REMOTE_ADDR' => '127.0.0.1', 'HTTP_CF_CONNECTING_IP' => '203.0.113.7'], '203.0.113.7'],
    'IPv6 client via loopback' => [['REMOTE_ADDR' => '::1', 'HTTP_CF_CONNECTING_IP' => '2001:db8::5'], '2001:db8::5'],
    'loopback without forwarding headers' => [['REMOTE_ADDR' => '127.0.0.1'], '127.0.0.1'],
    'untrusted peer cannot spoof CF-Connecting-IP' => [['REMOTE_ADDR' => '198.51.100.9', 'HTTP_CF_CONNECTING_IP' => '203.0.113.7'], '198.51.100.9'],
    'untrusted peer cannot spoof X-Forwarded-For' => [['REMOTE_ADDR' => '198.51.100.9', 'HTTP_X_FORWARDED_FOR' => '10.0.0.1'], '198.51.100.9'],
    'nginx real_ip already applied' => [['REMOTE_ADDR' => '203.0.113.7', 'HTTP_CF_CONNECTING_IP' => '203.0.113.7'], '203.0.113.7'],
    'X-Forwarded-For uses the hop the proxy saw, not a client-supplied prefix' => [['REMOTE_ADDR' => '127.0.0.1', 'HTTP_X_FORWARDED_FOR' => '6.6.6.6, 203.0.113.7'], '203.0.113.7'],
    'X-Forwarded-For skips trusted hops' => [['REMOTE_ADDR' => '127.0.0.1', 'HTTP_X_FORWARDED_FOR' => '203.0.113.7, 127.0.0.1'], '203.0.113.7'],
    'garbage CF header falls back to X-Forwarded-For' => [['REMOTE_ADDR' => '127.0.0.1', 'HTTP_CF_CONNECTING_IP' => 'not-an-ip', 'HTTP_X_FORWARDED_FOR' => '203.0.113.8'], '203.0.113.8'],
    'garbage everywhere keeps REMOTE_ADDR' => [['REMOTE_ADDR' => '127.0.0.1', 'HTTP_CF_CONNECTING_IP' => '1.2.3.4; DROP', 'HTTP_X_FORWARDED_FOR' => 'nope'], '127.0.0.1'],
]);

test('the trusted proxy list comes from config and defaults to loopback', function () {
    $proxies = config('app.trusted_proxies');
    $proxies = is_object($proxies) ? $proxies->toArray() : (array)$proxies;

    expect($proxies)->toBe(['127.0.0.1', '::1']);
});
