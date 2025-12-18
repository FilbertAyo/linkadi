<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_profiles' => Profile::count(),
            'public_profiles' => Profile::where('is_public', true)->count(),
            'recent_users' => User::latest()->take(5)->get(),
            'recent_profiles' => Profile::with('user')->latest()->take(5)->get(),
            'users_this_month' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'profiles_this_month' => Profile::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
