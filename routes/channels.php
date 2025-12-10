<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('requisitions.{user_id}', function ($user, $user_id) {
    return (int) $user->id === (int) $user_id;
});
