<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Figure;
use App\Models\Timeline;
use Illuminate\Database\Seeder;

/**
 * TimelineSeeder
 *
 * Seeds a small set of factory-generated timelines so the application
 * works standalone. Real content is loaded manually with:
 * php artisan timelines:import {path}
 */
class TimelineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Timeline::factory()
            ->count(5)
            ->has(Figure::factory()->count(2), 'figures')
            ->create();
    }
}
