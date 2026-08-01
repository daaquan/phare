<?php

use App\Models\User;
use Phare\Support\Facades\Broadcast;

/*
 * Broadcast channel authorisation.
 * Evaluated from POST /broadcasting/auth when a private / presence channel is
 * subscribed to. A callback receives (authenticated user, ...channel parameters)
 * - false / null  -> denied
 * - true          -> allowed (private)
 * - an array      -> allowed, plus the presence member payload
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
