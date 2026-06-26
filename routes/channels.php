<?php

use App\Models\User;
use Phare\Support\Facades\Broadcast;

/*
 * Broadcast channel authorization.
 * Evaluated by POST /broadcasting/auth when subscribing to private / presence channels.
 * Callbacks receive (authenticated user, ...channel parameters)
 * - false / null  → deny access
 * - true          → allow access (private)
 * - array         → allow access + presence member information
 * as their return values.
 */

// Private channel only its owner may subscribe to: private-App.User.{id}
Broadcast::channel('App.User.{id}', function (?User $user, $id) {
    return $user !== null && (int)$user->id === (int)$id;
});

// Presence channel for the monitor demo: presence-monitor
// Any authenticated user may join; the member payload is returned.
Broadcast::channel('monitor', function (?User $user) {
    if ($user === null) {
        return false;
    }

    return ['id' => $user->id, 'name' => $user->name];
});
