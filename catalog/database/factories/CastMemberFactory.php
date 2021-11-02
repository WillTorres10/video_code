<?php

namespace Database\Factories;

use App\Models\CastMember;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use JetBrains\PhpStorm\ArrayShape;

class CastMemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CastMember::class;

    /**
     * Define the model's default state.
     *
     * @return array
     * @throws Exception
     */
    #[ArrayShape(['name' => "string", 'type' => "int"])]
    public function definition():array
    {
        return [
            'name' => $this->faker->name,
            'type' => random_int(1,10) % 2 === 0 ? CastMember::TYPE_DIRECTOR : CastMember::TYPE_ACTOR
        ];
    }
}
