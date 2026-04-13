<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class CabinetController extends Controller
{
    public function subscription(Request $request): View
    {
        return view('cabinet.subscription', [
            'activeRoute' => 'subscription',
            'user' => $request->user(),
        ]);
    }

    public function trial(Request $request): View
    {
        return view('cabinet.trial', [
            'activeRoute' => 'trial',
            'user' => $request->user(),
        ]);
    }

    public function profile(Request $request): View
    {
        return view('cabinet.profile', [
            'activeRoute' => 'profile',
            'user' => $request->user(),
        ]);
    }

    public function security(Request $request): View
    {
        return view('cabinet.security', [
            'activeRoute' => 'security',
            'user' => $request->user(),
        ]);
    }

    public function history(): View
    {
        return view('cabinet.history', [
            'activeRoute' => 'history',
        ]);
    }
}
