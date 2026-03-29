<?php

namespace App\Actions\Jobs;

use App\Models\Job;

class GetJobDetailsAction
{
    public function execute(int $id): Job
    {
        // Mencari job berdasarkan ID atau lempar 404 jika tidak ada
        return Job::findOrFail($id);
    }
}