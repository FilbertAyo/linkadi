<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileBuilderController extends Controller
{
    /**
     * Display a listing of profiles.
     */
    public function index()
    {
        return view('profile-builder.index');
    }

    /**
     * Show the form for creating a new profile.
     */
    public function create()
    {
        return view('profile-builder.create');
    }

    /**
     * Show the form for editing the specified profile.
     */
    public function edit($id)
    {
        $profile = Auth::user()->profiles()->findOrFail($id);
        return view('profile-builder.edit', compact('profile'));
    }
}

