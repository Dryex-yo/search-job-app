<?php

namespace App\Actions\Applications;

use App\Models\Application;
use Illuminate\Support\Facades\Auth;

class UpdateApplicationStatusAction
{
    public function execute(int $id, string $status): Application
    {
        $application = Application::findOrFail($id);
        
        // Get authenticated admin user
        $adminUser = Auth::user();
        
        $updateData = [
            'status' => $status,
            'reviewed_at' => now()
        ];
        
        // Set admin_id if not already set
        if (!$application->admin_id && $adminUser) {
            $updateData['admin_id'] = $adminUser->id;
        }
        
        $application->update($updateData);

        return $application;
    }
}