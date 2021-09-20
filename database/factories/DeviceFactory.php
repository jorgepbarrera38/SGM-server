<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Device;
use App\Models\Element;
use App\Models\Location;
use App\Models\Dependence;
use App\Models\Responsible;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Device::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'group_id' => Group::inRandomOrder()->first()->id,
            'element_id' => Element::inRandomOrder()->first()->id,
            'location_id' => Location::inRandomOrder()->first()->id,
            'dependence_id' => Dependence::inRandomOrder()->first()->id,
            'responsible_id' => Responsible::inRandomOrder()->first()->id,
            'internal_code' => $this->faker->numberBetween('1', '9999'),
            'serial_number' => $this->faker->numberBetween(1000000, 50000000),
            'brand_id' => Brand::inRandomOrder()->first()->id,
            'model' => $this->faker->sentence,
            'description' => $this->faker->text(400),
            'observations' => '',
            'start_operation' => now(),
            'date_end_guarantee' => now(),
            'useful_life' => 5,
            'description_support_preventive_maintenance' => 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Impedit ipsam doloribus et sint modi quis voluptas est eligendi numquam maxime',
            'user_id' => User::inRandomOrder()->first()->id
        ];
    }
}
