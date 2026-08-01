<?php

namespace App\Http\Controllers\Broadcasting;

use App\Http\Controllers\Controller;
use Phare\Attributes\Route;
use Phare\Broadcasting\AccessDeniedException;
use Phare\Http\Request;
use Phare\Support\Facades\Broadcast;

/**
 * Subscription authorisation endpoint for private / presence channels.
 * laravel-echo (pusher-js) posts socket_id and channel_name to
 * POST /broadcasting/auth. Equivalent to the Laravel endpoint of the same name.
 */
class AuthController extends Controller
{
    #[Route('auth', methods: ['POST'], middlewares: ['auth'], name: 'broadcasting.auth')]
    public function authenticate(Request $request)
    {
        // Register the channel authorisation callbacks per request. Broadcaster
        // (driver) instances are cached, so registering here means the auth()
        // call right below sees the same instance. Lazy registration keeps this
        // independent of boot order.
        require base_path('routes/channels.php');

        try {
            $result = Broadcast::auth($request);
        } catch (AccessDeniedException) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        // The pusher driver returns a signed JSON string; bools/arrays are wrapped.
        return $this->rawJson(is_string($result) ? $result : (string)json_encode($result));
    }
}
