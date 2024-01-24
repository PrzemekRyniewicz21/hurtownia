<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Products;
use App\Models\Game;
use App\Models\Genres;
use App\Repository\GameRepositoryInterface;
use Illuminate\Support\Facades\Log;

class ProductsController extends Controller
{
    private GameRepositoryInterface $gameRepository;
    private $gameModel;

    public function __construct(GameRepositoryInterface $gameRepository, Game $gameModel)
    {
        $this->gameRepository = $gameRepository;
        $this->gameModel = $gameModel;
    }

    public function index()
    {
        // $products = Game::all();
        $products = $this->gameRepository->all();

        foreach ($products as $p) {
            $p['genres'] = $p->genres()->pluck('name')->toArray();
        }
        // $products = Products::all();
        // dd($products[1]->type);


        if ($products->count() > 0) {
            return response()->json([
                "products" => $products,
                'status' => 200,
            ]);
        } else {
            return response()->json([
                'products' => [],
                'status' => 404,
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $game = $this->gameModel->find($id);

        Log::info("Update id:" . $id);
        Log::info("Amount " . $game->amount);

        if (!$game) {
            return response()->json([
                'error' => 'Game not found',
                'status' => 404,
            ]);
        }

        // Log::info($game->amount);
        $game->amount = strval(intval($game->amount) - 1);
        Log::info("Amount " . $game->amount);
        $game->save();

        if ($game->amount == 0) {
            $game->delete();
        }

        return response()->json([
            'status' => 200,
        ]);
    }

    public function show(Request $request)
    {
        Log::info($request->id ?? "BRAK");
        dump("HELLO");

        $game = $this->gameModel->find($request->id);

        if (!$game) {
            return response()->json([
                'error' => 'Game not found',
                'status' => '404',
            ]);
        }

        // dd($desc);
        $desc = $game->description;
        return response()->json([
            'description' => $desc,
            'status' => 404,
        ]);
    }
}
