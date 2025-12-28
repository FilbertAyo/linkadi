<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * Display the specified package.
     */
    public function show(Package $package)
    {
        $package->load('pricingTiers');
        
        if (!$package->is_active) {
            abort(404);
        }
        
        return view('packages.show', compact('package'));
    }
}
