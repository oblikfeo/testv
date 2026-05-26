<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrialFeedback;
use App\Models\TrialKey;
use App\Models\User;
use App\Services\TrialKeyService;
use App\Support\SharedVpnAccess;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(
        protected TrialKeyService $trialKeyService,
    ) {}

    public function login()
    {
        if (session('admin_authenticated')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function authenticate(Request $request)
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

        if ($request->login === $envLogin && $request->password === $envPassword) {
            session(['admin_authenticated' => true]);

            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['login' => 'Неверный логин или пароль']);
    }

    public function logout()
    {
        session()->forget('admin_authenticated');

        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function trials()
    {
        $trialKeys = TrialKey::query()
            ->with('user:id,email,name,telegram_username')
            ->orderByDesc('id')
            ->get();

        $trialDefaults = [
            'hours' => (int) config('admin.trial.duration_hours', 3),
            'gb' => (int) config('admin.trial.soft_quota_gb', 5),
        ];

        return view('admin.trials', compact('trialKeys', 'trialDefaults'));
    }

    public function trialFeedback()
    {
        $items = TrialFeedback::query()
            ->with('user:id,email,name,telegram_username')
            ->orderByDesc('id')
            ->paginate(50);

        return view('admin.trial-feedback', compact('items'));
    }

    public function createTrial(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'hours' => 'required|integer|min:1|max:168',
            'gb' => 'required|integer|min:0|max:50',
        ]);

        $user = User::query()->where('email', $request->input('email'))->firstOrFail();

        try {
            $this->trialKeyService->createTrialKeyForAdmin(
                $user,
                $request->integer('hours'),
                $request->integer('gb')
            );

            return back()->with('success', 'Триал выдан: '.$user->email);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function revokeTrial(Request $request)
    {
        $request->validate([
            'trial_key_id' => 'required|integer|exists:trial_keys,id',
        ]);

        $trialKey = TrialKey::query()->findOrFail($request->integer('trial_key_id'));

        try {
            $this->trialKeyService->revokeTrialKey($trialKey);

            return back()->with('success', 'Триал отозван');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
