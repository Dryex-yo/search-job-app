<?php

namespace App\Actions\Jobs;

use App\Models\Job;
use Illuminate\Database\Eloquent\Collection;

class ListAvailableJobsAction
{
    /**
     * Mengambil daftar lowongan dengan filter search.
     */
    public function execute(?string $search = null): Collection
    {
        $query = Job::query();

        // Logika pencarian sederhana berdasarkan judul atau perusahaan
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        return $query->latest()->get();
    }
}