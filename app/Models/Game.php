<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;
    protected $attributes = [
        'metacritic_score' => null
    ];

    // kastowanie - jesli pobierzemy z modelu ta wlasniwosc, to otrzymamy int'a
    protected $casts = [
        'steam_appid' => 'integer',
    ];

    // tablica z atrybutami które możemy uzupełniać przez create lub konstruktor
    //protected $fillable = ['name', 'description', 'metacritic_score', 'publisher', 'genre_id'];

    // Atrybuty

    public function getSteamIdAttribute(): int
    {
        return $this->steam_appid;
    }

    // Relacje

    public function genres()
    {
        return $this->belongsToMany('App\Models\Genre', 'gameGenres');
    }

    public function publishers()
    {
        return $this->belongsToMany('App\Model\Publisher', 'gamePublishers');
    }
}
