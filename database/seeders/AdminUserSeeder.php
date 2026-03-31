<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create only admin user
        User::updateOrCreate(
            ['email' => 'admin@dryex.com'],
            [
                'name' => 'Administrator Dryex',
                'password' => bcrypt('admin909'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin account created successfully!');
    }
}
