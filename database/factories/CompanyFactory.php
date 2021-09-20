<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'logo' => 'company/nkM7BXQEbWfiU3C6RcMqah3RLs9rCpTZ0OfFg5Tj.jpg',
            'reason_preventive_maintenance_report_digital' => 'Mantenimiento preventivo programado'
        ];
    }
}
