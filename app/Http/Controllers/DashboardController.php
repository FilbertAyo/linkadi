<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function index()
    {
        // Redirect admins to admin dashboard
        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        $packages = Package::active()
            ->ordered()
            ->with('pricingTiers')
            ->get();
        
        // Get profiles expiring soon (within 30 days)
        $expiringProfiles = auth()->user()->profiles()
            ->where('status', 'published')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays(30))
            ->orderBy('expires_at')
            ->get();
        
        // Get pending payment orders
        $pendingOrders = auth()->user()->orders()
            ->where('payment_status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('packages', 'expiringProfiles', 'pendingOrders'));
    }
}
