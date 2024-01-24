<?php

namespace App\Providers;

use App\Repository\GameRepository;
use App\Repository\GameRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class GameServiceProvider extends ServiceProvider
{
    /**
     * Register any application services related to the Game module.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(GameRepositoryInterface::class, GameRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
