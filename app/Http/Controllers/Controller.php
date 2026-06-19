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

    /**
     * Return a JSON response (used by fetch-based endpoints like passkeys).
     *
     * @param array<string, mixed> $data
     */
    protected function json(array $data, int $status = 200)
    {
        $this->response->setStatusCode($status);
        $this->response->setJsonContent($data);

        return $this->response;
    }

    /**
     * Return an already-encoded JSON string verbatim (avoids double-encoding,
     * e.g. WebAuthn option objects serialized by the library).
     */
    protected function rawJson(string $json, int $status = 200)
    {
        $this->response->setStatusCode($status);
        $this->response->setContentType('application/json', 'UTF-8');
        $this->response->setContent($json);

        return $this->response;
    }
}
