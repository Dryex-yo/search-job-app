<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ProfileView;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ProfileViewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all non-admin users
        $users = User::where('role', 'user')->get();

        foreach ($users as $user) {
            // Create profile views for this user (between 5-50 views)
            $viewCount = rand(5, 50);
            
            for ($i = 0; $i < $viewCount; $i++) {
                // Spread the views across the last 30 days
                $daysAgo = rand(0, 30);
                $viewedAt = Carbon::now()->subDays($daysAgo)->addHours(rand(0, 23));
                
                ProfileView::create([
                    'user_id' => $user->id,
                    'viewed_by' => 'admin',
                    'ip_address' => '192.168.' . rand(0, 255) . '.' . rand(0, 255),
                    'created_at' => $viewedAt,
                    'updated_at' => $viewedAt,
                ]);
            }
        }

        // Also update some users with complete profile data
        User::where('role', 'user')->get()->each(function ($user) {
            if (!$user->phone) {
                $user->update([
                    'phone' => '08' . rand(10000000, 99999999),
                    'bio' => 'Passionate professional seeking new opportunities in ' . 
                             ['Technology', 'Finance', 'Health', 'Education', 'Marketing'][rand(0, 4)],
                    'email_verified_at' => $user->email_verified_at ?? Carbon::now(),
                ]);
            }
        });

        $this->command->info('Profile views seeder completed!');
    }
}
