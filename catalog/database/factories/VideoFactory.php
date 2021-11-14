<?php

namespace Database\Factories;

use App\Models\Video;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use JetBrains\PhpStorm\ArrayShape;

class VideoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Video::class;

    /**
     * Define the model's default state.
     *
     * @return array
     * @throws Exception
     */
    #[ArrayShape(['title' => "string", 'description' => "string", 'year_launched' => "int", 'opened' => "int", 'rating' => "string", 'duration' => "int"])]
    public function definition(): array
    {
        $rating = Video::RATING_LIST[array_rand(Video::RATING_LIST)];
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(10),
            'year_launched' => random_int(1895, 2021),
            'opened' => random_int(0, 1),
            'rating' => $rating,
            'duration' => random_int(1, 30)
        ];
    }
}
