<?php

function abort($message, \Phox\Foundation\Http\ResponseStatusCode $code = \Phox\Foundation\Http\ResponseStatusCode::BAD_REQUEST)
{
    return response(['message' => $message], $code);
}

function view($path, array $params = []): \Phox\View\BladeView
{
    app()['dispatcher']->setParam('bladeView', $path);

    return app()['view']?->setVars($params);
}
