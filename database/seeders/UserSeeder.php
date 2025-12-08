<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

/**
 * UserSeeder
 *
 * Seeder for creating initial User model instances.
 *
 * @package Database\Seeders
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        User::factory()->admin()->create();
        User::factory()->count(1)->create();
    }
}
