<?php

use Illuminate\Support\Facades\Broadcast;

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

Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
    // Ensure the user is either the sender or the receiver of the message
    return (int) $user->id === (int) $receiverId;
});

