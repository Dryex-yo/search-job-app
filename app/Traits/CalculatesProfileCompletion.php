<?php

namespace App\Traits;

trait CalculatesProfileCompletion
{
    /**
     * Calculate profile completion percentage based on 14 required fields
     * 
     * @param mixed $user The user model with profile data
     * @return int Profile completion percentage (0-100)
     */
    protected function calculateProfileCompletion($user): int
    {
        $totalFields = 14; // Total profile fields for 100% completion
        $completedFields = 0;

        // 1. Name (required)
        if ($user->name && strlen($user->name) > 0) {
            $completedFields++;
        }

        // 2. Email verification
        if ($user->email_verified_at) {
            $completedFields++;
        }

        // 3. Phone number
        if ($user->phone && strlen($user->phone) > 0) {
            $completedFields++;
        }

        // 4. Bio/Profile summary
        if ($user->bio && strlen($user->bio) > 0) {
            $completedFields++;
        }

        // 5. Resume uploaded
        if ($user->resume_path && strlen($user->resume_path) > 0) {
            $completedFields++;
        }

        // 6. Address
        if ($user->address && strlen($user->address) > 0) {
            $completedFields++;
        }

        // 7. City
        if ($user->city && strlen($user->city) > 0) {
            $completedFields++;
        }

        // 8. Date of birth
        if ($user->date_of_birth) {
            $completedFields++;
        }

        // 9. Gender
        if ($user->gender && strlen($user->gender) > 0) {
            $completedFields++;
        }

        // 10. ID number
        if ($user->id_number && strlen($user->id_number) > 0) {
            $completedFields++;
        }

        // 11. Education level
        if ($user->education_level && strlen($user->education_level) > 0) {
            $completedFields++;
        }

        // 12. Education institution
        if ($user->education_institution && strlen($user->education_institution) > 0) {
            $completedFields++;
        }

        // 13. Skills
        if ($user->skills && strlen($user->skills) > 0) {
            $completedFields++;
        }

        // 14. Emergency contact name
        if ($user->emergency_contact_name && strlen($user->emergency_contact_name) > 0) {
            $completedFields++;
        }

        // Calculate percentage
        $completion = ($completedFields / $totalFields) * 100;

        return (int) min(round($completion), 100);
    }
}
