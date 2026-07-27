<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function show(): Response|RedirectResponse
    {
        if (session('admin_authenticated')) {
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('Admin/Login', [
            'configured' => (string) config('admin.login') !== '' && (string) config('admin.password') !== '',
        ]);
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $envLogin = (string) config('admin.login');
        $envPassword = (string) config('admin.password');

        if ($envLogin === '' || $envPassword === '') {
            return back()->withErrors(['login' => 'Задайте ADMIN_LOGIN и ADMIN_PASSWORD в .env']);
        }

        if (hash_equals($envLogin, (string) $request->input('login'))
            && hash_equals($envPassword, (string) $request->input('password'))) {
            $request->session()->regenerate();
            session(['admin_authenticated' => true]);

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['login' => 'Неверный логин или пароль']);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_authenticated');

        return redirect()->route('admin.login');
    }
}
