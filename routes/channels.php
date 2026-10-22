<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('game.{id}', function ($user, $id) {
    return true;
});
