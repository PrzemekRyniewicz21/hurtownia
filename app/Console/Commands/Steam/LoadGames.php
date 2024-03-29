<?php

namespace App\Console\Commands\Steam;

use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Log;

class LoadGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steam:load-games {steep=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load steam games';

    private Factory $httpClient;

    private string $currentGamesListPath = './storage/app/steam';

    private array $genres = [];
    private array $publishers = [];
    private array $developers = [];

    public function __construct(Factory $httpClient)
    {
        parent::__construct();
        $this->httpClient = $httpClient;
    }

    private function loadGameList(): void
    {
        $steamAllGamesUrl = config('steam.api.games.all');
        $response = $this->httpClient->get($steamAllGamesUrl);

        if ($response->failed()) {
            $this->error('Request failed. Status code: ' . $response->status());
            exit;
        }

        $steamResponse = $response->json();
        $apps = $steamResponse['applist']['apps'];
        $jsonApps = json_encode($apps);
        $res = file_put_contents($this->currentGamesListPath, $jsonApps);
        $this->info('Loaded.');
    }

    public function updateGames()
    {
        $steamGamesResponse = $this->httpClient->get(config('steam.api.games.all'))->json();

        $steamGamesResponse = $steamGamesResponse['applist']['apps'];
        $steamGamesIds = array();
        foreach ($steamGamesResponse as $r) {
            $steamGamesIds[] = $r['appid'];
        }

        $currentAppIds = DB::table('games')
            ->select('steam_appid')
            ->pluck('steam_appid')
            ->toArray();
        Log::info($currentAppIds);

        $newAppIds = array_diff($steamGamesIds, $currentAppIds);

        Log::info(count($newAppIds));
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // $this->loadGameList();
        // exit;
        // $this->updateGames();
        // $this->info("test");
        // exit;


        // $steep = $this->argument('steep');
        // if ($steep == 1) {
        //     $this->loadGameList();
        //     return 0;
        // }

        $gameList = file_get_contents($this->currentGamesListPath);
        $gameList = json_decode($gameList, true);

        $savedGames = DB::table('games')
            ->select('steam_appid')
            ->pluck('steam_appid')
            ->toArray();
        $savedGames = array_flip($savedGames);

        $genres = DB::table('genres')
            ->select()
            ->get()
            ->toArray();
        foreach ($genres ?? [] as $row) {
            $this->genres[$row->id] = (array) $row;
        }

        $progressDb = $this->output->createProgressBar(count($gameList));

        foreach ($gameList as $row) {
            $appId = $row['appid'];

            if (array_key_exists($appId, $savedGames)) {
                $progressDb->advance();
                $this->info(' - ' . $appId);
                continue;
            }

            sleep(1);

            $steamGameDetailsUrl = config('steam.api.games.details');
            $response = $this->httpClient->get(
                $steamGameDetailsUrl,
                [
                    'appids' => $appId,
                    'l' => 'en'
                ]
            );

            if ($response->failed()) {
                $this->error("ERROR: Game: $appId HTTP Code {$response->status()}");
                continue;
            }

            $data = $response->json();

            if ($data[$row['appid']]['success'] === false) {
                $this->error("ERROR: Game: $appId Data is empty");
                continue;
            }

            try {
                $this->create($data);
            } catch (\Throwable $e) {
                dump($data);
                dump($e);
                continue;
            }

            $progressDb->advance();
            $this->info(" - " . $appId . ": " . $data[$appId]['data']['name']);
        }

        $progressDb->finish();

        $this->info('End from DB');

        return 0;
    }

    private function create($data)
    {
        $result = DB::transaction(function () use ($data) {

            $data = array_shift($data);
            if ($data['success'] !== true) {
                return;
            }

            $data = $data['data'];
            $game = [
                'steam_appid' => $data['steam_appid'],
                'relation_id' => !empty($data['fullgame']) ? (int) $data['fullgame']['appid'] : null,
                'name' => $data['name'],
                'type' => $data['type'],
                'description' => $data['detailed_description'],
                'short_description' => $data['short_description'],
                'price' => $data['price_overview']['initial'] ?? null,
                'release_date' => $data['release_date']['date'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'amount' => range(1, 12),
            ];

            $gameId = DB::table('games')->insertGetId($game);

            foreach ($data['genres'] ?? [] as $genre) {
                if (empty($this->genres[$genre['id']])) {
                    $genreData = [
                        'id' => $genre['id'],
                        'name' => $genre['description'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                    $result = DB::table('genres')->insert($genreData);
                    $this->genres[$genreData['id']] = $genreData;
                }

                DB::table('gameGenres')->insert([
                    'game_id' => $gameId,
                    'genre_id' => $genre['id']
                ]);
            }

            foreach ($data['publishers'] ?? [] as $publisher) {
                if (empty($this->publishers[$publisher])) {
                    $publisherData = [
                        'name' => $publisher,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                    $publisherId = DB::table('publishers')->insertGetId($publisherData);
                    $publisherData['id'] = $publisherId;
                    $this->publishers[$publisher] = $publisherData;
                }

                $publisherId = $this->publishers[$publisher]['id'];

                DB::table('gamePublishers')->insert([
                    'game_id' => $gameId,
                    'publisher_id' => $publisherId
                ]);
            }

            foreach ($data['developers'] ?? [] as $developer) {
                if (empty($this->developers[$developer])) {
                    $developerData = [
                        'name' => $developer,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                    $developerId = DB::table('developers')->insertGetId($developerData);
                    $developerData['id'] = $developerId;
                    $this->developers[$developer] = $developerData;
                }

                $developerId = $this->developers[$developer]['id'];

                DB::table('gameDevelopers')->insert([
                    'game_id' => $gameId,
                    'developer_id' => $developerId
                ]);
            }
        });
    }
}
