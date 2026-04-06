<?php

namespace App\Actions\Jobs;

use App\Models\Job;
use Illuminate\Database\Eloquent\Collection;

class ListAvailableJobsAction
{
    /**
     * Mengambil daftar lowongan dengan filter search dan filter lainnya.
     * 
     * @param array $filters Filter yang berisi keys: search, type, location, status, salary_min, salary_max
     * @return Collection
     */
    public function execute(?array $filters = null): Collection
    {
        $query = Job::query();

        // Jika filters adalah string (backward compatibility), ubah ke array
        if (is_string($filters)) {
            $filters = ['search' => $filters];
        }

        $filters = $filters ?? [];

        // Filter: Pencarian berdasarkan judul, perusahaan, atau lokasi
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Filter: Tipe pekerjaan (Full-time, Contract, dll)
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter: Lokasi
        if (!empty($filters['location'])) {
            $query->where('location', 'like', "%{$filters['location']}%");
        }

        // Filter: Status (active, closed)
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter: Salary range (mengasumsikan salary disimpan sebagai numerik atau string yang bisa diparse)
        if (!empty($filters['salary_min']) || !empty($filters['salary_max'])) {
            // Note: ini adalah contoh sederhana. Dalam produksi, Anda mungkin perlu menyimpan salary sebagai integer
            // Untuk sekarang, kita akan melakukan filter di PHP setelah pengambilan data
        }

        return $query->latest()->get();
    }
}