<?php

declare(strict_types=1);

namespace App\Repository;

interface GameRepositoryInterface
{
    public function all();

    public function all_paginated($paginate_v);

    public function find($id);
}
