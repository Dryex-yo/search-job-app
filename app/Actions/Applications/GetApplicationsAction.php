<?php

namespace App\Actions\Applications;

use App\Models\Application;
use Illuminate\Database\Eloquent\Collection;

class GetApplicationsAction
{
    public function execute(): Collection
    {
        // Mengambil data lamaran terbaru beserta info User dan Lowongan-nya
        return Application::with(['user', 'job'])->latest()->get();
    }
}