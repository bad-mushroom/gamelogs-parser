<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GamesSeeder extends Seeder
{
    protected array $seeds = [
        [
            'id'          => 'jedi-knight-2-jedi-outcast',
            'name'        => 'Jedi Knight 2: Jedi Outcast',
            'description' => '',
            'identifiers' => [
                [ 'key' => 'gamename', 'value' => 'basejk' ],
            ],
            'parser' => 'JK2Parser',
        ],
        [
            'id'          => 'quake-iii-arena-excessive-plus',
            'name'        => 'Quake III Arena - Excessive Plus',
            'description' => 'Excessive Plus mod for Quake III',
            'identifiers' => [
                [ 'key' => 'gamename', 'value' => 'excessiveplus' ],
            ],
            'parser' => 'Q3AParser',
        ],
        [
            'id'          => 'quake-iii-arena-retail',
            'name'        => 'Quake III Arena',
            'description' => 'Original id release of Quake 3.',
            'identifiers' => [
                [ 'key' => 'version', 'value' => 'Q3' ],
                [ 'key' => 'gamename', 'value' => 'baseq3' ],
            ],
            'parser' => 'Q3AParser',
        ],
        [
            'id'          => 'quake-iii-arena-io',
            'name'        => 'Quake III Arena',
            'description' => 'Open-source ioquake3 version',
            'identifiers' => [
                ['key' => 'version', 'value' => 'ioq3'],
                ['key' => 'gamename', 'value' => 'baseq3'],
            ],
            'parser' => 'Q3AParser',
        ],
        [
            'id'          => 'ioq3-ut',
            'name'        => 'Urban Terror',
            'description' => 'Urban Terror standalone game using ioq3',
            'identifiers' => [
                ['key' => 'gamename', 'value' => 'q3urt42'],
            ],
            'parser' => 'IOQ3UrtParser',
        ],
        [
            'id'          => 'ioq3-smokin-guns',
            'name'        => 'Smokin Guns',
            'description' => 'Smokin Guns standalone game using ioq3',
            'identifiers' => [
                ['key' => 'gamename', 'value' => 'smokinguns'],
            ],
            'parser' => 'IOQ3SGParser',
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
