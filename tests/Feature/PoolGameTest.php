<?php

namespace Tests\Feature;

use App\GameManager;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PoolGameTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::flush();
    }

    public function test_a_player_can_create_a_solo_game_at_any_of_eight_levels(): void
    {
        $response = $this->post(route('juegos.store'), [
            'playerX' => 'Ana',
            'mode' => 'solo',
            'level' => 8,
        ]);

        $response->assertRedirect();
        $id = basename(parse_url($response->headers->get('Location'), PHP_URL_PATH));
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $redirectQuery);
        $game = GameManager::instance()->getGame($id);

        $this->assertSame('solo', $game['mode']);
        $this->assertSame(8, $game['level']);
        $this->assertSame('active', $game['status']);
        $this->assertCount(16, $game['balls']);
        $this->assertSame('X', GameManager::instance()->roleForToken($id, $redirectQuery['seat'] ?? null));
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('Nivel 8 de 8');
    }

    public function test_a_rival_can_join_an_invitation_and_take_their_turn(): void
    {
        $game = GameManager::instance()->createGame('Ana', 'duel');

        $this->get(route('juegos.show', ['id' => $game['id'], 'invite' => 1]))
            ->assertOk()
            ->assertSee('Te invitaron a una partida');

        $response = $this->post(route('juegos.join', $game['id']), ['playerName' => 'Luis']);
        $response->assertRedirect();

        $joinedGame = GameManager::instance()->getGame($game['id']);
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $redirectQuery);
        $this->assertSame('active', $joinedGame['status']);
        $this->assertSame('X', GameManager::instance()->roleForToken($game['id'], $game['seatToken']));
        $this->assertSame('O', GameManager::instance()->roleForToken($game['id'], $redirectQuery['seat'] ?? null));
    }

    public function test_only_the_current_player_can_start_one_shot_at_a_time(): void
    {
        $game = GameManager::instance()->createGame('Ana', 'duel');
        $hostToken = $game['seatToken'];
        $joinedGame = GameManager::instance()->joinGame($game['id'], 'Luis');
        $manager = GameManager::instance();
        $this->assertSame('X', $manager->roleForToken($game['id'], $hostToken));
        $this->assertSame('O', $manager->roleForToken($game['id'], $joinedGame['seatToken']));

        $this->withHeader('X-Player-Token', $joinedGame['seatToken'])
            ->postJson(route('juegos.shot', $game['id']), ['angle' => 0, 'power' => .6])
            ->assertStatus(409);

        $this->withHeader('X-Player-Token', $game['seatToken'])
            ->postJson(route('juegos.shot', $game['id']), ['angle' => 0, 'power' => .6])
            ->assertOk()
            ->assertJsonPath('shot.shooter', 'X');

        $this->postJson(route('juegos.shot', $game['id']), ['angle' => 0, 'power' => .6])
            ->assertStatus(409);
    }

    public function test_a_shot_can_be_settled_before_the_turn_changes(): void
    {
        $manager = GameManager::instance();
        $game = $manager->createGame('Ana', 'solo');
        $shot = $manager->startShot($game['id'], 'X', 0, .6);
        $state = [
            'balls' => $game['balls'],
            'groups' => ['X' => null, 'O' => null],
            'turn' => 'O',
            'status' => 'active',
            'winner' => null,
        ];

        $settled = $manager->settleShot($game['id'], 'X', $shot['id'], $state);

        $this->assertSame('O', $settled['turn']);
        $this->assertNull($settled['pendingShot']);
    }
}
