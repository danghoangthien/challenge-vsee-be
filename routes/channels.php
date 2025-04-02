<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Test channel
Broadcast::channel('test-channel', function () {
    return true;
});

// Visitor private channel
Broadcast::channel('visitor.{id}', function (User $user, $id) {
    return $user->visitor && $user->visitor->id == $id;
});

// Provider private channel
Broadcast::channel('provider.{id}', function (User $user, $id) {
    return $user->provider && $user->provider->id == $id;
});

// Public providers only channel
Broadcast::channel('providers', function () {
    return true;
}); 

// Public lounge queue channel
Broadcast::channel('lounge.queue', function () {
    return true;
}); 