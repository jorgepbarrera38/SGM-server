<?php

namespace Database\Factories;

use App\Models\Responsible;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResponsibleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Responsible::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'identification' => $this->faker->numberBetween('1110573514', '1109145870'),
            'names' => $this->faker->name,
            'lastnames' => $this->faker->lastName,
            'fullnames' => $this->faker->name . ' ' . $this->faker->lastName
        ];
    }
}
