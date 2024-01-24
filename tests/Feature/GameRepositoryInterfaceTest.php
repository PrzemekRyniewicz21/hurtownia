<?php

namespace Tests\Feature;

use App\Repository\GameRepositoryInterface;
use App\Models\Game;
use Tests\TestCase;

class GameRepositoryInterfaceTest extends TestCase
{
    /** @test */
    public function it_can_get_all_games()
    {
        $gameModelMock = $this->createMock(Game::class);
        $gameRepository = new GameRepositoryInterface($gameModelMock);

        // ... testowanie metody all() ...
    }

    /** @test */
    public function it_can_get_paginated_games()
    {
        $gameModelMock = $this->createMock(Game::class);
        $gameRepository = new GameRepositoryInterface($gameModelMock);

        // ... testowanie metody all_paginated() ...
    }

    /** @test */
    public function it_can_find_a_game_by_id()
    {
        $gameModelMock = $this->createMock(Game::class);
        $gameRepository = new GameRepositoryInterface($gameModelMock);

        // ... testowanie metody find() ...
    }
}
