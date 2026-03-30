<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
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

        // Show user dashboard for regular users
        return Inertia::render('Dashboard');
    }
}
