<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'slug' => fake()->unique()->slug(1),
            'description' => fake()->sentence(),
            'price_cents' => 1900,
            'billing_period' => 'monthly',
            'monthly_credits' => 100,
            'features' => ['Feature A', 'Feature B'],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
