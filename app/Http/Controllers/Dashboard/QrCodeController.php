<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    /**
     * Display QR codes for all user profiles.
     */
    public function index()
    {
        $profiles = auth()->user()->profiles()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.qr-codes.index', compact('profiles'));
    }
}

