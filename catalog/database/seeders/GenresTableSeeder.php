<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Category, Genre};

class GenresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = Category::all();
        Genre::factory(100)
            ->create()
            ->each(function(Genre $genre) use($categories){
            $categoriesId = $categories->random(5)->pluck('id')->toArray();
            $genre->categories()->attach($categoriesId);
        });
    }
}
