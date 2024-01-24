<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->integer('steam_appid')->index();
            $table->integer('relation_id')->nullable()->index();
            $table->string('name', 100)->index();
            $table->string('type', 20)->default('game')->index();
            $table->text('description')->nullable();
            $table->string('release_date', 30);

            $table->timestamps();
        });

        Schema::create('gameGenres', function (Blueprint $table) {
            $table->integer('game_id')->index();
            $table->integer('genre_id')->index();

            $table->index(['game_id', 'genre_id']);
        });

        // tabela publisher + łącząca
        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index();
            $table->timestamps();
        });

        Schema::create('gamePublishers', function (Blueprint $table) {
            $table->integer('game_id')->index();
            $table->integer('publisher_id')->index();

            $table->index(['game_id', 'publisher_id']);
        });

        // tabela developers + łącząca
        Schema::create('developers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index();
            $table->timestamps();
        });

        Schema::create('gameDevelopers', function (Blueprint $table) {
            $table->integer('game_id')->index();
            $table->integer('developer_id')->index();

            $table->index(['game_id', 'developer_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games');
        Schema::dropIfExists('publishers');
        Schema::dropIfExists('developers');
        Schema::dropIfExists('gameDevelopers');
        Schema::dropIfExists('gamePublishers');
        Schema::dropIfExists('gameGenres');
    }
};
