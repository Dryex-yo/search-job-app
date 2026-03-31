<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create only the admin user
        User::updateOrCreate(
            ['email' => 'admin@dryex.com'],
            [
                'name' => 'Administrator Dryex',
                'password' => bcrypt('admin909'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
