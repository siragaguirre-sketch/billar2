<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MoveMade implements ShouldBroadcast
{
    public string $gameId;
    public int $position;
    public string $symbol;
    public string $nextTurn;
    public array $board;
    public ?string $winner;
    public string $status;

    public function __construct(string $gameId, int $position, string $symbol, string $nextTurn, array $board, ?string $winner, string $status)
    {
        $this->gameId = $gameId;
        $this->position = $position;
        $this->symbol = $symbol;
        $this->nextTurn = $nextTurn;
        $this->board = $board;
        $this->winner = $winner;
        $this->status = $status;
    }

    public function broadcastOn(): array
    {
        return [new Channel('game.' . $this->gameId)];
    }

    public function broadcastAs(): string
    {
        return 'MoveMade';
    }
}
