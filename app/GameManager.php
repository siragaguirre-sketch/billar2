<?php

namespace App;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GameManager
{
    private const GAME_TTL_MINUTES = 480;

    public static function instance(): self
    {
        return new self();
    }

    private function key(string $id): string
    {
        return 'pool-game:'.$id;
    }

    private function seatsKey(string $id): string
    {
        return 'pool-game-seats:'.$id;
    }

    public function createGame(string $playerX, string $mode, int $level = 1): array
    {
        $id = Str::lower(Str::random(12));
        $seatToken = Str::random(64);
        $game = [
            'id' => $id,
            'mode' => $mode,
            'level' => $mode === 'solo' ? $level : null,
            'playerX' => $playerX,
            'playerO' => $mode === 'solo' ? 'Rival de la casa' : null,
            'status' => $mode === 'solo' ? 'active' : 'waiting',
            'turn' => 'X',
            'balls' => $this->initialBalls(),
            'groups' => ['X' => null, 'O' => null],
            'winner' => null,
            'pendingShot' => null,
            'chat' => [],
            'createdAt' => now()->toIso8601String(),
        ];

        Cache::put($this->key($id), $game, now()->addMinutes(self::GAME_TTL_MINUTES));
        Cache::put($this->seatsKey($id), ['X' => $seatToken, 'O' => null], now()->addMinutes(self::GAME_TTL_MINUTES));
        Cache::put('pool-game:lobby', array_slice(array_merge(Cache::get('pool-game:lobby', []), [$id]), -100), now()->addDays(2));

        return $game + ['seatToken' => $seatToken];
    }

    public function getWaitingGames(): array
    {
        return array_values(array_filter(array_map(
            fn (string $id) => $this->getGame($id),
            Cache::get('pool-game:lobby', []),
        ), fn (?array $game) => $game && $game['mode'] === 'duel' && $game['status'] === 'waiting'));
    }

    public function deleteGame(string $id): bool
    {
        Cache::forget($this->key($id));
        Cache::forget($this->seatsKey($id));

        $lobby = Cache::get('pool-game:lobby', []);
        $lobby = array_values(array_filter($lobby, fn ($gid) => $gid !== $id));
        Cache::put('pool-game:lobby', $lobby, now()->addDays(2));

        return true;
    }

    public function getGame(string $id): ?array
    {
        return Cache::get($this->key($id));
    }

    public function roleForToken(string $id, ?string $token): ?string
    {
        if (!$token) {
            return null;
        }

        $seats = Cache::get($this->seatsKey($id), []);

        foreach (['X', 'O'] as $role) {
            if (isset($seats[$role]) && hash_equals($seats[$role], $token)) {
                return $role;
            }
        }

        return null;
    }

    public function joinGame(string $id, string $playerName): ?array
    {
        return Cache::lock('pool-game-lock:'.$id, 10)->block(3, function () use ($id, $playerName): ?array {
            $game = $this->getGame($id);

            if (!$game || $game['mode'] !== 'duel' || $game['status'] !== 'waiting') {
                return null;
            }

            $seatToken = Str::random(64);
            $seats = Cache::get($this->seatsKey($id), ['X' => null, 'O' => null]);
            $seats['O'] = $seatToken;
            Cache::put($this->seatsKey($id), $seats, now()->addMinutes(self::GAME_TTL_MINUTES));

            $game['playerO'] = $playerName;
            $game['status'] = 'active';
            $this->save($game);

            return $game + ['seatToken' => $seatToken];
        });
    }

    public function startShot(string $id, string $playerRole, float $angle, float $power): ?array
    {
        return Cache::lock('pool-game-lock:'.$id, 10)->block(3, function () use ($id, $playerRole, $angle, $power): ?array {
            $game = $this->getGame($id);

            if (
                !$game
                || $game['status'] !== 'active'
                || $game['pendingShot'] !== null
                || !in_array($playerRole, ['X', 'O'], true)
                || ($game['mode'] === 'solo' ? $playerRole !== 'X' : $game['turn'] !== $playerRole)
            ) {
                return null;
            }

            $shot = [
                'id' => (string) Str::uuid(),
                'shooter' => $game['turn'],
                'angle' => $angle,
                'power' => $power,
            ];
            $game['pendingShot'] = $shot;
            $this->save($game);

            return $shot;
        });
    }

    public function settleShot(string $id, string $playerRole, string $shotId, array $state): ?array
    {
        return Cache::lock('pool-game-lock:'.$id, 10)->block(3, function () use ($id, $playerRole, $shotId, $state): ?array {
            $game = $this->getGame($id);

            if (
                !$game
                || !$game['pendingShot']
                || $game['pendingShot']['id'] !== $shotId
                || ($game['mode'] === 'solo' ? $playerRole !== 'X' : $game['pendingShot']['shooter'] !== $playerRole)
            ) {
                return null;
            }

            if (!$this->validState($state)) {
                return null;
            }

            $game['balls'] = $state['balls'];
            $game['groups'] = $state['groups'];
            $game['turn'] = $state['turn'];
            $game['status'] = $state['status'];
            $game['winner'] = $state['winner'];
            $game['pendingShot'] = null;
            $this->save($game);

            return $game;
        });
    }

    public function addChat(string $id, string $playerName, string $message): ?array
    {
        $game = $this->getGame($id);

        if (!$game || $game['status'] === 'waiting') {
            return null;
        }

        $msg = [
            'playerName' => $playerName,
            'message' => $message,
            'time' => now()->format('H:i'),
        ];
        $game['chat'][] = $msg;
        $game['chat'] = array_slice($game['chat'], -50);
        $this->save($game);

        return $msg;
    }

    private function save(array $game): void
    {
        Cache::put($this->key($game['id']), $game, now()->addMinutes(self::GAME_TTL_MINUTES));
    }

    private function validState(array $state): bool
    {
        if (
            !isset($state['balls'], $state['groups'], $state['turn'], $state['status'])
            || !is_array($state['balls'])
            || count($state['balls']) !== 16
            || !in_array($state['turn'], ['X', 'O'], true)
            || !in_array($state['status'], ['active', 'finished'], true)
            || !array_key_exists('X', $state['groups'])
            || !array_key_exists('O', $state['groups'])
        ) {
            return false;
        }

        foreach ($state['balls'] as $index => $ball) {
            if (
                !is_array($ball)
                || ($ball['id'] ?? null) !== $index
                || !is_numeric($ball['x'] ?? null)
                || !is_numeric($ball['y'] ?? null)
                || !is_bool($ball['pocketed'] ?? null)
            ) {
                return false;
            }
        }

        return $state['status'] !== 'finished' || in_array($state['winner'] ?? null, ['X', 'O'], true);
    }

    private function initialBalls(): array
    {
        $positions = [
            [250, 250],
            [640, 250],
            [664, 238], [664, 262],
            [688, 226], [688, 250], [688, 274],
            [712, 214], [712, 238], [712, 262], [712, 286],
            [736, 202], [736, 226], [736, 250], [736, 274], [736, 298],
        ];

        return array_map(
            fn (array $position, int $id) => [
                'id' => $id,
                'x' => $position[0],
                'y' => $position[1],
                'pocketed' => false,
            ],
            $positions,
            array_keys($positions),
        );
    }

    /* ---------- Historial y XP ---------- */
    public function recordMatch(string $id, string $winner, string $playerX, string $playerO, int $scoreX, int $scoreO, string $mode): void
    {
        $history = Cache::get('pool-history', []);
        $history[] = [
            'gameId' => $id,
            'winner' => $winner,
            'playerX' => $playerX,
            'playerO' => $playerO,
            'scoreX' => $scoreX,
            'scoreO' => $scoreO,
            'mode' => $mode,
            'playedAt' => now()->toIso8601String(),
        ];
        $history = array_slice($history, -100);
        Cache::put('pool-history', $history, now()->addDays(30));

        $this->addXP($playerX, $winner === 'X' ? 1 : 0, $scoreX);
        $this->addXP($playerO, $winner === 'O' ? 1 : 0, $scoreO);
    }

    public function getHistory(): array
    {
        return array_values(array_reverse(Cache::get('pool-history', [])));
    }

    public function getPlayerXP(string $playerName): array
    {
        $players = Cache::get('pool-players-xp', []);
        return $players[$playerName] ?? ['xp' => 0, 'level' => 1, 'wins' => 0, 'games' => 0];
    }

    public function getLeaderboard(): array
    {
        $players = Cache::get('pool-players-xp', []);
        $list = [];
        foreach ($players as $name => $data) {
            $list[] = ['name' => $name] + $data;
        }
        usort($list, fn ($a, $b) => $b['xp'] <=> $a['xp']);
        return array_slice($list, 0, 10);
    }

    private function addXP(string $playerName, bool $won, int $score): void
    {
        $players = Cache::get('pool-players-xp', []);
        if (!isset($players[$playerName])) {
            $players[$playerName] = ['xp' => 0, 'level' => 1, 'wins' => 0, 'games' => 0];
        }
        $p = &$players[$playerName];
        $p['games']++;
        if ($won) {
            $p['wins']++;
            $p['xp'] += 30 + $score * 2;
        } else {
            $p['xp'] += 10;
        }
        $p['level'] = 1 + (int) floor($p['xp'] / 100);
        unset($p);
        Cache::put('pool-players-xp', $players, now()->addDays(30));
    }

    /* ---------- Niveles Individual ---------- */
    public function getPlayerMaxLevel(string $playerName): int
    {
        $players = Cache::get('pool-players-xp', []);
        return (int) ($players[$playerName]['maxLevel'] ?? 1);
    }

    public function unlockNextLevel(string $playerName, int $completedLevel): void
    {
        $players = Cache::get('pool-players-xp', []);
        if (!isset($players[$playerName])) {
            $players[$playerName] = ['xp' => 0, 'level' => 1, 'wins' => 0, 'games' => 0, 'maxLevel' => 1];
        }
        $currentMax = (int) ($players[$playerName]['maxLevel'] ?? 1);
        if ($completedLevel >= $currentMax && $completedLevel < 8) {
            $players[$playerName]['maxLevel'] = $completedLevel + 1;
        }
        Cache::put('pool-players-xp', $players, now()->addDays(30));
    }

    public function isLevelUnlocked(string $playerName, int $level): bool
    {
        return $level <= $this->getPlayerMaxLevel($playerName);
    }
}
