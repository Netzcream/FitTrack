<?php

namespace Database\Factories;

use App\Models\Tenant\CommercialPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CommercialPlanFactory extends Factory
{
    protected $model = CommercialPlan::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement(['Free','Starter','Pro','Enterprise']);
        $code = strtoupper(Str::slug($name,'_'));

        $monthly = match ($name) {
            'Free'       => 0,
            'Starter'    => 19.00,
            'Pro'        => 49.00,
            'Enterprise' => 0, // pricing a medida
            default      => 0,
        };
        $yearly = $monthly ? $monthly * 10 : 0;

        return [
            'name'        => $name,
            'code'        => $code,
            'slug'        => Str::slug($name) . '-' . $this->faker->unique()->randomNumber(5),
            'description' => $this->faker->sentence(10),
            'monthly_price' => $monthly ?: null,
            'yearly_price'  => $yearly ?: null,
            'currency'      => 'USD',
            'billing_interval' => $monthly && $yearly ? 'both' : ($monthly ? 'monthly' : 'yearly'),
            'trial_days'    => $this->faker->randomElement([0,7,14]),
            'max_users'     => $name === 'Enterprise' ? null : $this->faker->numberBetween(3, 100),
            'max_teams'     => $name === 'Enterprise' ? null : $this->faker->numberBetween(1, 20),
            'max_projects'  => $name === 'Enterprise' ? null : $this->faker->numberBetween(5, 200),
            'storage_gb'    => $name === 'Enterprise' ? null : $this->faker->numberBetween(5, 1000),
            'is_active'     => true,
            'visibility'    => 'public',
            'plan_type'     => strtolower($name) === 'free' ? 'free' : (strtolower($name) === 'enterprise' ? 'enterprise' : 'standard'),
            'features'      => ['support' => 'email', 'analytics' => true],
            'limits'        => ['exports_per_month' => 50],
            'sort_order'    => $this->faker->numberBetween(0, 10),
        ];
    }
}
