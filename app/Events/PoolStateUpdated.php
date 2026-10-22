<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PoolStateUpdated implements ShouldBroadcastNow
{
    public function __construct(
        public string $gameId,
        public array $game,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('game.'.$this->gameId)];
    }

    public function broadcastAs(): string
    {
        return 'PoolStateUpdated';
    }
}
