<?php

namespace Database\Factories;

use App\Models\Tenant\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::orderedUuid(),
            'user_id' => User::factory(),
            'status' => 'active',
            'email' => $this->faker->unique()->safeEmail(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'goal' => $this->faker->optional()->sentence(4),
            'is_user_enabled' => true,
            'last_login_at' => null,
            'commercial_plan_id' => null,
            'billing_frequency' => 'monthly',
            'account_status' => 'on_time',
            'data' => [
                'notifications' => [
                    'new_plan' => false,
                    'session_reminder' => false,
                ],
            ],
        ];
    }
}
