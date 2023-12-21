<?php

function abort($message, Phox\Foundation\Http\ResponseStatusCode $code = \Phox\Foundation\Http\ResponseStatusCode::BAD_REQUEST)
{
    return response(['message' => $message], $code);
}

function view($path, array $params = [])
{
    app()['dispatcher']->setParam('bladeView', $path);

    app()['view']?->setVars($params);

    return app()['blade'];
}
