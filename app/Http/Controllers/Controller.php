<?php

namespace App\Http\Controllers;

use Phalcon\Mvc\Controller as BaseController;
use Phare\Http\Request;

abstract class Controller extends BaseController
{
    /**
     * Flatten a validated request's messages into an Inertia-friendly
     * `{field: message}` bag.
     *
     * @return array<string, string>
     */
    protected function fieldErrors(Request $request): array
    {
        return array_map(
            fn ($message) => is_array($message) ? (string)($message['message'] ?? '') : (string)$message,
            $request->getMessages(),
        );
    }

    /**
     * Flash validation errors to the session (picked up by HandleInertiaRequests
     * as the shared `errors` prop) and redirect back.
     */
    protected function backWithErrors(Request $request, ?string $fallback = '/')
    {
        $this->session->set('errors', $this->fieldErrors($request));

        $referer = $_SERVER['HTTP_REFERER'] ?? $fallback;

        return $this->response->redirect($referer);
    }
}
