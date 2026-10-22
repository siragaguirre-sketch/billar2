<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GameEnded implements ShouldBroadcast
{
    public string $gameId;
    public string $winner;
    public string $status;

    public function __construct(string $gameId, string $winner, string $status)
    {
        $this->gameId = $gameId;
        $this->winner = $winner;
        $this->status = $status;
    }

    public function broadcastOn(): array
    {
        return [new Channel('game.' . $this->gameId)];
    }

    public function broadcastAs(): string
    {
        return 'GameEnded';
    }
}
