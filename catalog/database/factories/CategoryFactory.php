<?php

namespace Database\Factories;

use App\Models\Category;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use JetBrains\PhpStorm\ArrayShape;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     * @throws Exception
     */
    #[ArrayShape(['name' => "string", 'description' => "null|string"])]
    public function definition(): array
    {
        return [
            'name' => $this->faker->colorName,
            'description' => random_int(1,10) % 2 === 0 ? $this->faker->sentence() : null,
        ];
    }
}
