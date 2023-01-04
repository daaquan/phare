<?php

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     */
    function env($key, $default = null): mixed
    {
        return \App\Env::get($key, $default);
    }
}

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @param  mixed  $args
     */
    function value($value, ...$args): mixed
    {
        return $value instanceof \Closure ? $value(...$args) : $value;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     */
    function dd(): void
    {
        call_user_func_array('dump', func_get_args());

        die(1);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump the passed variables without end the script.
     *
     * @param  mixed
     */
    function dump(): void
    {
        array_map(static function ($x): void {
            $string = (new \Phalcon\Support\Debug\Dump([], true))->variable($x);

            echo PHP_SAPI === 'cli' ? strip_tags($string).PHP_EOL : $string;
        }, func_get_args());
    }
}
