<?php

namespace Database\Seeders;

use App\Models\{Category, Genre, Video};
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Category::factory(20)->create();
        Genre::factory(15)->create();
        Video::factory(100)->create();
    }
}
