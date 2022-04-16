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
        $this->call(CategoriesTableSeeder::class);
        $this->call(GenresTableSeeder::class);
        $this->call(CastMembersTableSeeder::class);
        $this->call(VideosTableSeeder::class);
    }
}
