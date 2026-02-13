<?php

namespace App\Services;

use App\Events\BroadcastEvent;

class BroadcastEventService
{
    public static function signal(string $signal, int | null $id = null)
    {
        broadcast(new BroadcastEvent(["signal" => $signal, "id" => $id]));
    }
}