<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Models\ProfileView;
use App\Services\DashboardCacheService;
use App\Traits\CalculatesProfileCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use CalculatesProfileCompletion;

    private DashboardCacheService $cacheService;

    public function __construct(DashboardCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Show dashboard based on user role
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Redirect admin to admin dashboard
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // Redirect recruiter to recruiter dashboard
        if ($user->isRecruiter()) {
            return redirect()->route('recruiter.dashboard');
        }

        // Show user dashboard for regular users
        return Inertia::render('Dashboard', [
            'dashboardData' => $this->cacheService->getDashboardData($user),
        ]);
    }
}
