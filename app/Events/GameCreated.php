<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GameCreated implements ShouldBroadcast
{
    public string $gameId;
    public string $playerX;
    public string $status;

    public function __construct(string $gameId, string $playerX, string $status)
    {
        $this->gameId = $gameId;
        $this->playerX = $playerX;
        $this->status = $status;
    }

    public function broadcastOn(): array
    {
        return [new Channel('game.' . $this->gameId)];
    }

    public function broadcastAs(): string
    {
        return 'GameCreated';
    }
}
