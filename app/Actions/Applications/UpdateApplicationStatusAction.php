<?php

namespace App\Actions\Applications;

use App\Models\Application;

class UpdateApplicationStatusAction
{
    public function execute(int $id, string $status): Application
    {
        $application = Application::findOrFail($id);
        $application->update(['status' => $status]);

        return $application;
    }
}