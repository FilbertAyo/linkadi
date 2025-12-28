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
        $packages = Package::active()
            ->ordered()
            ->with('pricingTiers')
            ->get();

        return view('dashboard', compact('packages'));
    }
}
