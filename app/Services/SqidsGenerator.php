<?php

namespace App\Services;

class SqidsGenerator
{
    private string $alphabet;

    private int $base;

    public function __construct(string $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
    {
        if (strlen($alphabet) < 2) {
            throw new \InvalidArgumentException('Alphabet must contain at least two characters.');
        }

        $this->alphabet = $alphabet;
        $this->base = strlen($alphabet);
    }

    public function encode(array $numbers): string
    {
        $normalized = implode('', array_map(static fn ($value) => (string)$value, $numbers));
        if ($normalized === '') {
            throw new \InvalidArgumentException('The numbers array cannot be empty.');
        }
        if (!ctype_digit($normalized)) {
            throw new \InvalidArgumentException('The numbers array must only contain digits.');
        }

        $value = gmp_init($normalized, 10);
        if (gmp_cmp($value, 0) === 0) {
            return $this->alphabet[0];
        }

        $encoded = '';
        while (gmp_cmp($value, 0) > 0) {
            [$value, $remainder] = $this->divmod($value);
            $encoded = $this->alphabet[$remainder] . $encoded;
        }

        return $encoded;
    }

    public function decode(string $id): array
    {
        if ($id === '') {
            return [];
        }

        $value = gmp_init(0, 10);
        foreach (str_split($id) as $char) {
            $position = strpos($this->alphabet, $char);
            if ($position === false) {
                throw new \InvalidArgumentException('Encoded string contains invalid characters.');
            }

            $value = gmp_add(gmp_mul($value, $this->base), $position);
        }

        $decimal = gmp_strval($value, 10);

        return str_split($decimal);
    }

    private function divmod(\GMP $number): array
    {
        [$quotient, $remainder] = gmp_div_qr($number, $this->base);

        return [$quotient, gmp_intval($remainder)];
    }
}
