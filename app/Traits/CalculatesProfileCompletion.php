<?php

namespace App\Traits;

trait CalculatesProfileCompletion
{
    /**
     * Calculate profile completion percentage using User model method
     * Delegating to User::getProfileCompletionPercentage() for consistency
     * 
     * @param mixed $user The user model with profile data
     * @return int Profile completion percentage (0-100)
     */
    protected function calculateProfileCompletion($user): int
    {
        // Use the User model method for consistent calculation everywhere
        return $user->getProfileCompletionPercentage();
    }
}
