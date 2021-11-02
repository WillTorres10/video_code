<?php

namespace Database\Factories;

use App\Models\Gender;
use Illuminate\Database\Eloquent\Factories\Factory;
use JetBrains\PhpStorm\ArrayShape;

class GenderFactory extends Factory
{

    protected $model = Gender::class;

    #[ArrayShape(['name' => "string"])]
    public function definition(): array
    {
        return [
            'name' => $this->faker->colorName,
        ];
    }
}
