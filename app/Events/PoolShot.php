<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PoolShot implements ShouldBroadcastNow
{
    public function __construct(
        public string $gameId,
        public array $shot,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('game.'.$this->gameId)];
    }

    public function broadcastAs(): string
    {
        return 'PoolShot';
    }
}
