<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            // Стандартный - 2 устройства
            [
                'name' => 'Стандартный',
                'slug' => 'standard-30',
                'devices' => 2,
                'days' => 30,
                'price' => 250,
                'discount' => 0,
                'is_popular' => false,
                'sort_order' => 1,
                'traffic_gb' => 100,
            ],
            [
                'name' => 'Стандартный',
                'slug' => 'standard-90',
                'devices' => 2,
                'days' => 90,
                'price' => 600,
                'discount' => 20,
                'is_popular' => false,
                'sort_order' => 2,
                'traffic_gb' => 300,
            ],
            [
                'name' => 'Стандартный',
                'slug' => 'standard-180',
                'devices' => 2,
                'days' => 180,
                'price' => 990,
                'discount' => 34,
                'is_popular' => false,
                'sort_order' => 3,
                'traffic_gb' => 600,
            ],

            // Расширенный - 5 устройств
            [
                'name' => 'Расширенный',
                'slug' => 'extended-30',
                'devices' => 5,
                'days' => 30,
                'price' => 550,
                'discount' => 0,
                'is_popular' => false,
                'sort_order' => 4,
                'traffic_gb' => 100,
            ],
            [
                'name' => 'Расширенный',
                'slug' => 'extended-90',
                'devices' => 5,
                'days' => 90,
                'price' => 1350,
                'discount' => 18,
                'is_popular' => true,
                'sort_order' => 5,
                'traffic_gb' => 300,
            ],
            [
                'name' => 'Расширенный',
                'slug' => 'extended-180',
                'devices' => 5,
                'days' => 180,
                'price' => 2400,
                'discount' => 27,
                'is_popular' => false,
                'sort_order' => 6,
                'traffic_gb' => 600,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        Plan::updateOrCreate(
            ['slug' => 'sponsor-bundle'],
            [
                'name' => 'Двойная связка (2 сервера)',
                'devices' => 5,
                'days' => 30,
                'price' => 0,
                'discount' => 0,
                'is_popular' => false,
                'is_active' => false,
                'sort_order' => 999,
                'traffic_gb' => 0,
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'admin-friends-bundle'],
            [
                'name' => 'Полный доступ (все серверы продаж)',
                'devices' => 10,
                'days' => 365,
                'price' => 0,
                'discount' => 0,
                'is_popular' => false,
                'is_active' => false,
                'sort_order' => 998,
                'traffic_gb' => 0,
            ]
        );
    }
}
