<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privado del panel: solo el personal (carnicero) recibe pedidos en vivo.
Broadcast::channel('admin-orders', function ($user) {
    return (bool) $user->is_staff;
});
