<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GamesSeeder extends Seeder
{
    protected array $seeds = [
        [
            'id'          => 'quake-iii-arena-retail',
            'name'        => 'Quake III Arena',
            'description' => 'Original id release of Quake 3.',
            'identifiers' => [
                [
                    'key'   => 'version',
                    'match' => 'contains',
                    'value' => 'Q3'
                ],
                [
                    'key'   => 'gamename',
                    'match' => 'contains',
                    'value' => 'baseq3'
                ],
            ],
            'parser' => 'Q3AParser',
        ],
        [
            'id'          => 'quake-iii-arena-io',
            'name'        => 'Quake III Arena',
            'description' => 'Open-source ioquake3 version',
            'identifiers' => [
                [
                    'key'   => 'version',
                    'match' => 'contains',
                    'value' => 'ioq3'
                ],
                [
                    'key'   => 'gamename',
                    'match' => 'contains',
                    'value' => 'baseq3'
                ],
            ],
            'parser' => 'Q3AParser',
        ]
    ];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->seeds as $seed) {
            $game = Game::query()->firstOrNew(['id' => $seed['id']]);
            $game->fill($seed);
            $game->save();
        }
    }
}
