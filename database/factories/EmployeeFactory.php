<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Enums\ActiveStatus;
use App\Actions\RandomGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $faker = \Faker\Factory::create('id_ID');
        
        return [
            'join_date' => $faker->date($format = 'Y-m-d', $max = 'now'),
            'status' => $this->faker->randomElement(ActiveStatus::toArrayValue())
        ];
    }
}
