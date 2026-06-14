<?php

namespace App\Http\Middleware;

use Phalcon\Http\RequestInterface;
use Phalcon\Http\ResponseInterface;
use Phare\Contracts\Http\MiddlewareContract;
use Phare\Foundation\Http\Concerns\BeforeMiddleware;
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
        // Always present so the client `errors` prop is a stable map;
        // field-level errors arrive with form requests.
        Inertia::share('errors', (object)[]);

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

    protected function assetVersion(): ?string
    {
        $manifest = public_path('build/manifest.json');

        return is_file($manifest) ? md5_file($manifest) : null;
    }
}
