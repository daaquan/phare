<?php

namespace App\Http\Middleware;

use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phare\Contracts\Http\MiddlewareContract;
use Phare\Foundation\Http\Concerns\BeforeMiddleware;
use Phare\Security\Csrf;
use Phare\Support\Facades\Auth;
use Phare\Support\Facades\Inertia;

/**
 * Shares the data every Inertia response needs (authenticated user, flash
 * messages, validation errors) and sets the asset version. Runs before the
 * controller so the shared props are merged into whatever it renders.
 */
class HandleInertiaRequests extends MiddlewareContract implements BeforeMiddleware
{
    public function handle(RequestInterface $request, \Closure $next): ResponseInterface
    {
        // Resolve through the facade so we share into the same factory instance
        // the controllers render through (app('inertia') array-access and the
        // facade's make() can otherwise yield different singleton instances).
        if (!Inertia::getVersion()) {
            Inertia::version($this->assetVersion());
        }

        Inertia::share('auth', ['user' => $this->resolveUser()]);
        Inertia::share('flash', $this->resolveFlash());
        Inertia::share('errors', $this->resolveErrors());
        // fetch ベースのエンドポイント（パスキー等）が X-CSRF-TOKEN ヘッダーで
        // 送り返せるよう、セッションの CSRF トークンを共有する。
        Inertia::share('csrf_token', app(Csrf::class)->getToken());

        return $next();
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function resolveUser(): ?array
    {
        if (!Auth::check()) {
            return null;
        }

        $user = Auth::user();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified' => method_exists($user, 'hasVerifiedEmail') && $user->hasVerifiedEmail(),
            'is_admin' => method_exists($user, 'isAdmin') && $user->isAdmin(),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    protected function resolveFlash(): array
    {
        $flash = ['success' => null, 'error' => null];

        $session = app('flashSession');
        if ($session === null || !method_exists($session, 'getMessages')) {
            return $flash;
        }

        foreach ($session->getMessages() as $type => $messages) {
            $key = $type === 'success' ? 'success' : ($type === 'error' ? 'error' : null);
            if ($key !== null && $messages !== []) {
                $flash[$key] = is_array($messages) ? reset($messages) : (string)$messages;
            }
        }

        return $flash;
    }

    /**
     * Validation errors flashed to the session by controllers, consumed once.
     */
    protected function resolveErrors(): object
    {
        $session = app('session');

        if ($session !== null && $session->has('errors')) {
            $errors = (array)$session->get('errors');
            $session->remove('errors');

            return (object)$errors;
        }

        return (object)[];
    }

    protected function assetVersion(): ?string
    {
        $manifest = public_path('build/manifest.json');

        return is_file($manifest) ? md5_file($manifest) : null;
    }
}
