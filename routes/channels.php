<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('event.{eventId}', function (User $user, int $eventId) {
    return $user->email == 'dpmfmipaunud2025@gmail.com';
});