<?php

namespace Database\Seeders;

use App\Models\Board;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $board = Board::query()->firstOrCreate(
            ['slug' => 'forge-2-board'],
            ['title' => 'Forge 2 Kanban']
        );

        foreach (['Todo', 'Doing', 'Done'] as $position => $title) {
            $board->lists()->firstOrCreate(
                ['title' => $title],
                ['position' => $position]
            );
        }
    }
}
