<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ChatMessage implements ShouldBroadcastNow
{
    public string $gameId;
    public string $playerName;
    public string $message;
    public string $time;

    public function __construct(string $gameId, string $playerName, string $message, string $time)
    {
        $this->gameId = $gameId;
        $this->playerName = $playerName;
        $this->message = $message;
        $this->time = $time;
    }

    public function broadcastOn(): array
    {
        return [new Channel('game.' . $this->gameId)];
    }

    public function broadcastAs(): string
    {
        return 'ChatMessage';
    }
}
