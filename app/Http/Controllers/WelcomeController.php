<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    /**
     * Display the welcome page with packages.
     */
    public function index()
    {
        $packages = Package::active()
            ->ordered()
            ->with('pricingTiers')
            ->get()
            ->groupBy('type');

        return view('welcome', compact('packages'));
    }
}
