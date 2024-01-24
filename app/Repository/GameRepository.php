<?php

declare(strict_types=1);

namespace App\Repository;

use App\Models\Game;
use App\Repository\GameRepositoryInterface;

class GameRepository implements GameRepositoryInterface
{
    private Game $gameModel;

    public function __construct(Game $gameModel)
    {
        $this->gameModel = $gameModel;
    }

    public function all()
    {
        return $this->gameModel->all();
    }

    public function all_paginated($paginate_v)
    {
        return $this->gameModel->paginate($paginate_v);
    }

    public function find($id)
    {
        return $this->gameModel->find($id);
    }
}
