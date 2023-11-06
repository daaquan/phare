<?php

function abort($message, \Phox\Foundation\Http\ResponseStatusCode $code = \Phox\Foundation\Http\ResponseStatusCode::BAD_REQUEST)
{
    return response(['message' => $message], $code);
}
