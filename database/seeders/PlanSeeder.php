<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::upsert([
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Get started with a handful of generations each month.',
                'price_cents' => 0,
                'billing_period' => 'monthly',
                'monthly_credits' => 10,
                'features' => json_encode(['10 AI generations / month', 'Live preview', 'Visual & code editor']),
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'For builders shipping sites regularly.',
                'price_cents' => 1900,
                'billing_period' => 'monthly',
                'monthly_credits' => 100,
                'features' => json_encode(['100 AI generations / month', 'Priority generation queue', 'Unlimited projects']),
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Agency',
                'slug' => 'agency',
                'description' => 'High volume for teams and agencies.',
                'price_cents' => 4900,
                'billing_period' => 'monthly',
                'monthly_credits' => 500,
                'features' => json_encode(['500 AI generations / month', 'Highest priority queue', 'Unlimited projects']),
                'is_active' => true,
                'sort_order' => 3,
            ],
        ], ['slug'], ['name', 'description', 'price_cents', 'billing_period', 'monthly_credits', 'features', 'is_active', 'sort_order']);
    }
}
