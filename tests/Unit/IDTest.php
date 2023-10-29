<?php

use Framework\Support\Facades\Sqids;

it('id encodes and decodes', function () {
    $original = 1234567890;
    $encoded = Sqids::encode(str_split($original));
    $decoded = Sqids::decode($encoded);

    expect(implode('', $decoded))->toBe((string)$original)
        ->and(count($decoded))->toBe(10);
});
