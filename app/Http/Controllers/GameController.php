<?php

namespace App\Http\Controllers;

use App\Events\ChatMessage;
use App\Events\PoolShot;
use App\Events\PoolStateUpdated;
use App\GameManager;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        $playerName = session('pool-player-name', '');
        $maxLevel = $playerName ? GameManager::instance()->getPlayerMaxLevel($playerName) : 1;

        return view('juegos.index', [
            'games' => GameManager::instance()->getWaitingGames(),
            'maxLevel' => $maxLevel,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'playerX' => 'required|string|max:50',
            'mode' => 'required|in:solo,duel',
            'level' => 'required_if:mode,solo|integer|min:1|max:8',
        ]);

        $manager = GameManager::instance();
        $level = (int) ($data['level'] ?? 1);

        if ($data['mode'] === 'solo' && !$manager->isLevelUnlocked($data['playerX'], $level)) {
            return back()->withErrors(['level' => 'Ese nivel todavía está bloqueado. Gana el nivel anterior para desbloquearlo.'])
                ->withInput();
        }

        session(['pool-player-name' => $data['playerX']]);

        $game = $manager->createGame(
            $data['playerX'],
            $data['mode'],
            $level,
        );

        return redirect()->route('juegos.show', ['id' => $game['id'], 'seat' => $game['seatToken']]);
    }

    public function show(Request $request, string $id)
    {
        $game = GameManager::instance()->getGame($id);

        if (!$game) {
            abort(404, 'Partida no encontrada o expirada');
        }

        $manager = GameManager::instance();
        $seatToken = $request->query('invite') ? null : $request->query('seat');
        $role = $manager->roleForToken($id, $seatToken);

        return view('juegos.show', [
            'game' => $game,
            'id' => $id,
            'role' => $role,
            'seatToken' => $role ? $seatToken : null,
            'isInviteLink' => $request->boolean('invite'),
        ]);
    }

    public function join(Request $request, string $id)
    {
        $data = $request->validate(['playerName' => 'required|string|max:50']);
        $game = GameManager::instance()->joinGame($id, $data['playerName']);

        if (!$game) {
            return back()->withErrors(['playerName' => 'Esta invitación ya no está disponible.']);
        }

        $seatToken = $game['seatToken'];
        unset($game['seatToken']);
        broadcast(new PoolStateUpdated($id, $game));

        return redirect()->route('juegos.show', ['id' => $id, 'seat' => $seatToken]);
    }

    public function shot(Request $request, string $id)
    {
        $data = $request->validate([
            'angle' => 'required|numeric|between:-6.284,6.284',
            'power' => 'required|numeric|between:0.05,1',
        ]);
        $manager = GameManager::instance();
        $playerRole = $manager->roleForToken($id, $request->header('X-Player-Token'));

        if (!$playerRole) {
            return response()->json(['error' => 'Únete a la partida para jugar.'], 403);
        }

        $shot = $manager->startShot($id, $playerRole, (float) $data['angle'], (float) $data['power']);

        if (!$shot) {
            return response()->json(['error' => 'No es tu turno o la mesa está ocupada.'], 409);
        }

        broadcast(new PoolShot($id, $shot));

        return response()->json(['ok' => true, 'shot' => $shot]);
    }

    public function settle(Request $request, string $id)
    {
        $data = $request->validate([
            'shotId' => 'required|uuid',
            'state.balls' => 'required|array|size:16',
            'state.groups' => 'required|array',
            'state.groups.X' => 'nullable|in:solids,stripes',
            'state.groups.O' => 'nullable|in:solids,stripes',
            'state.turn' => 'required|in:X,O',
            'state.status' => 'required|in:active,finished',
            'state.winner' => 'nullable|in:X,O',
        ]);
        $manager = GameManager::instance();
        $playerRole = $manager->roleForToken($id, $request->header('X-Player-Token'));

        if (!$playerRole) {
            return response()->json(['error' => 'No tienes acceso a esta partida.'], 403);
        }

        $game = $manager->settleShot($id, $playerRole, $data['shotId'], $data['state']);

        if (!$game) {
            return response()->json(['error' => 'No se pudo guardar el resultado del tiro.'], 409);
        }

        if ($game['status'] === 'finished') {
            $scoreX = 0; $scoreO = 0;
            foreach ($game['balls'] as $ball) {
                if ($ball['pocketed'] && $ball['id'] !== 0 && $ball['id'] !== 8) {
                    if ($ball['id'] >= 1 && $ball['id'] <= 7) $scoreX++;
                    if ($ball['id'] >= 9 && $ball['id'] <= 15) $scoreO++;
                }
            }
            $winner = $game['winner'] ?? ($game['groups']['X'] !== null ? 'X' : 'O');
            $manager->recordMatch($id, $winner, $game['playerX'], $game['playerO'] ?? '', $scoreX, $scoreO, $game['mode'] ?? 'solo');

            if (($game['mode'] ?? 'solo') === 'solo' && $winner === 'X') {
                $manager->unlockNextLevel($game['playerX'], (int) ($game['level'] ?? 1));
            }
        }

        broadcast(new PoolStateUpdated($id, $game));

        return response()->json(['ok' => true, 'game' => $game]);
    }

    public function chat(Request $request, string $id)
    {
        $data = $request->validate([
            'message' => 'required|string|max:255',
        ]);
        $manager = GameManager::instance();
        $role = $manager->roleForToken($id, $request->header('X-Player-Token'));
        $game = GameManager::instance()->getGame($id);

        if (!$game || !$role) {
            return response()->json(['error' => 'Únete a la partida para usar el chat.'], 403);
        }

        $playerName = $role === 'X' ? $game['playerX'] : $game['playerO'];
        $message = $manager->addChat($id, $playerName, $data['message']);

        if (!$message) {
            return response()->json(['error' => 'La partida no está disponible para el chat.'], 409);
        }

        broadcast(new ChatMessage($id, $message['playerName'], $message['message'], $message['time']));

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, string $id)
    {
        $manager = GameManager::instance();
        $game = $manager->getGame($id);

        if (!$game || $game['status'] !== 'waiting') {
            return back()->withErrors(['partida' => 'Esta partida ya no se puede eliminar.']);
        }

        $manager->deleteGame($id);

        return back()->with('ok', 'Invitación eliminada.');
    }

    public function history()
    {
        $manager = GameManager::instance();
        return view('juegos.history', ['history' => $manager->getHistory()]);
    }

    public function leaderboard()
    {
        $manager = GameManager::instance();
        return view('juegos.leaderboard', ['leaderboard' => $manager->getLeaderboard()]);
    }
}
