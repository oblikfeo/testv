<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaleKey;
use App\Models\Setting;
use App\Models\TrialFeedback;
use App\Models\TrialKey;
use App\Models\User;
use App\Services\SaleKeyService;
use App\Services\TrialKeyService;
use App\Services\XuiApiService;
use App\Support\HappSubscriptionFormatter;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected XuiApiService $xuiApi;

    public function __construct(
        XuiApiService $xuiApi,
        protected TrialKeyService $trialKeyService,
    ) {
        $this->xuiApi = $xuiApi;
    }

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

    public function testKeys()
    {
        $activeTab = request()->query('tab', 'test');
        $paidFilters = [
            'source' => (string) request()->query('source', ''),
            'status' => (string) request()->query('status', ''),
            'traffic' => (string) request()->query('traffic', ''),
            'expiring' => (string) request()->query('expiring', ''),
            'sort_by' => (string) request()->query('sort_by', 'expires_at'),
            'sort_dir' => (string) request()->query('sort_dir', 'desc'),
        ];

        $trialKeys = TrialKey::query()
            ->with('user:id,email,name,telegram_username')
            ->orderByDesc('id')
            ->get();

        $paidKeys = SaleKey::query()
            ->where('is_sponsor', false)
            ->where('is_admin_bundle', false)
            ->with([
                'user:id,email,name,telegram_username',
                'subscription:id,user_id,plan_id,status,expires_at,purchase_source',
                'subscription.plan:id,name,slug',
                'keyOrder:id,purchase_source',
            ])
            ->get();

        if ($activeTab === 'paid') {
            $now = now();
            $paidKeys = $paidKeys->filter(function (SaleKey $saleKey) use ($paidFilters, $now): bool {
                $source = (string) ($saleKey->subscription?->purchase_source ?: ($saleKey->keyOrder?->purchase_source ?: 'unknown'));
                $isExpired = $saleKey->expires_at?->isPast() ?? false;
                $isLimitExceeded = (int) ($saleKey->total_bytes ?? 0) > 0 && (int) ($saleKey->used_bytes ?? 0) >= (int) $saleKey->total_bytes;
                $isSubscriptionInactive = $saleKey->subscription && $saleKey->subscription->status !== 'active';
                $status = match (true) {
                    $saleKey->status !== 'active' => 'revoked',
                    $isSubscriptionInactive => 'sub_inactive',
                    $isExpired => 'expired',
                    $isLimitExceeded => 'limit_exceeded',
                    default => 'active',
                };

                if ($paidFilters['source'] !== '' && $paidFilters['source'] !== $source) {
                    return false;
                }
                if ($paidFilters['status'] !== '' && $paidFilters['status'] !== $status) {
                    return false;
                }

                if ($paidFilters['traffic'] === 'exhausted' && ! $isLimitExceeded) {
                    return false;
                }
                if ($paidFilters['traffic'] === 'remaining' && $isLimitExceeded) {
                    return false;
                }
                if ($paidFilters['traffic'] === 'unlimited' && (int) ($saleKey->total_bytes ?? 0) > 0) {
                    return false;
                }

                if ($paidFilters['expiring'] === 'expired' && ! $isExpired) {
                    return false;
                }
                if ($paidFilters['expiring'] === 'today' && ! ($saleKey->expires_at?->isToday() ?? false)) {
                    return false;
                }
                if ($paidFilters['expiring'] === '3days' && ! ($saleKey->expires_at && $saleKey->expires_at->isFuture() && $saleKey->expires_at->lte($now->copy()->addDays(3)))) {
                    return false;
                }
                if ($paidFilters['expiring'] === '7days' && ! ($saleKey->expires_at && $saleKey->expires_at->isFuture() && $saleKey->expires_at->lte($now->copy()->addDays(7)))) {
                    return false;
                }

                return true;
            });

            $sortBy = in_array($paidFilters['sort_by'], ['source', 'status', 'traffic', 'expires_at'], true)
                ? $paidFilters['sort_by']
                : 'expires_at';
            $sortDir = $paidFilters['sort_dir'] === 'asc' ? 'asc' : 'desc';

            $paidKeys = $paidKeys->sortBy(function (SaleKey $saleKey) use ($sortBy): mixed {
                $source = (string) ($saleKey->subscription?->purchase_source ?: ($saleKey->keyOrder?->purchase_source ?: 'unknown'));
                $isExpired = $saleKey->expires_at?->isPast() ?? false;
                $isLimitExceeded = (int) ($saleKey->total_bytes ?? 0) > 0 && (int) ($saleKey->used_bytes ?? 0) >= (int) $saleKey->total_bytes;
                $isSubscriptionInactive = $saleKey->subscription && $saleKey->subscription->status !== 'active';
                $status = match (true) {
                    $saleKey->status !== 'active' => 'revoked',
                    $isSubscriptionInactive => 'sub_inactive',
                    $isExpired => 'expired',
                    $isLimitExceeded => 'limit_exceeded',
                    default => 'active',
                };

                return match ($sortBy) {
                    'source' => $source,
                    'status' => $status,
                    'traffic' => (int) ($saleKey->used_bytes ?? 0),
                    default => (int) ($saleKey->expires_at?->timestamp ?? 0),
                };
            }, options: SORT_REGULAR, descending: $sortDir === 'desc')->values();
        }

        $trialDefaults = [
            'hours' => (int) config('admin.trial.duration_hours', 3),
            'gb' => (int) config('admin.trial.soft_quota_gb', 5),
        ];

        return view('admin.test-keys', compact('trialKeys', 'paidKeys', 'activeTab', 'paidFilters', 'trialDefaults'));
    }

    public function settings()
    {
        $panels = config('admin.sale_panels', []);
        $active = (int) Setting::get('active_sale_panel', '0');
        $health = [];

        foreach (array_keys($panels) as $idx) {
            $p = $panels[$idx];
            try {
                $this->xuiApi->getInbounds($p['url'], $p['username'], $p['password']);
                $health[$idx] = 'ok';
            } catch (\Throwable $e) {
                $health[$idx] = $e->getMessage();
            }
        }

        return view('admin.settings', compact('panels', 'active', 'health'));
    }

    public function updateSettings(Request $request)
    {
        $panels = config('admin.sale_panels', []);
        $max = max(0, count($panels) - 1);

        $request->validate([
            'active_sale_panel' => 'required|integer|min:0|max:'.$max,
        ]);

        Setting::set('active_sale_panel', (string) $request->integer('active_sale_panel'));

        return back()->with('success', 'Активная панель сохранена');
    }

    public function sponsorKeys()
    {
        $remaining = max(0, SaleKeyService::MAX_SPONSOR_KEYS - SaleKey::query()->where('is_sponsor', true)->count());

        return view('admin.sponsor', compact('remaining'));
    }

    public function createSponsor(Request $request, SaleKeyService $saleKeyService)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'days' => 'required|integer|min:1|max:3650',
            'traffic_gb' => 'required|integer|min:0|max:100000',
            'max_devices' => 'required|integer|min:1|max:50',
        ]);

        $user = User::query()->where('email', $request->input('email'))->firstOrFail();

        try {
            $saleKey = $saleKeyService->createSponsorBundle(
                $user,
                $request->integer('days'),
                $request->integer('traffic_gb'),
                $request->integer('max_devices')
            );

            return back()->with('success', 'Подписка (2 сервера) создана. Happ: '.url('/sub/'.$saleKey->sub_id));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function adminFriends()
    {
        $adminKey = SaleKey::query()
            ->where('is_admin_bundle', true)
            ->with('user')
            ->first();

        return view('admin.admin-friends', compact('adminKey'));
    }

    public function trialFeedback()
    {
        $items = TrialFeedback::query()
            ->with('user:id,email,name,telegram_username')
            ->orderByDesc('id')
            ->paginate(50);

        return view('admin.trial-feedback', compact('items'));
    }

    public function createAdminFriends(Request $request, SaleKeyService $saleKeyService)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'days' => 'required|integer|min:1|max:3650',
            'traffic_gb' => 'required|integer|min:0|max:100000',
            'max_devices' => 'required|integer|min:1|max:50',
        ]);

        $user = User::query()->where('email', $request->input('email'))->firstOrFail();

        try {
            $saleKey = $saleKeyService->createAdminFriendsBundle(
                $user,
                $request->integer('days'),
                $request->integer('traffic_gb'),
                $request->integer('max_devices')
            );

            return back()->with('success', 'Подписка «полный доступ» создана. Happ: '.url('/sub/'.$saleKey->sub_id));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function revokeAdminFriends(SaleKeyService $saleKeyService)
    {
        $key = SaleKey::query()->where('is_admin_bundle', true)->first();
        if (! $key) {
            return back()->withErrors(['error' => 'Активной админской подписки нет']);
        }

        try {
            $saleKeyService->revokeAdminFriendsBundle($key);

            return back()->with('success', 'Подписка отозвана, клиенты удалены с панелей');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function createTestKey(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'hours' => 'required|integer|min:1|max:24',
            'gb' => 'required|integer|min:0|max:50',
        ]);

        $user = User::query()->where('email', $request->input('email'))->firstOrFail();

        try {
            $trialKey = $this->trialKeyService->createTrialKeyForAdmin(
                $user,
                $request->integer('hours'),
                $request->integer('gb')
            );

            return back()->with('success', 'Тестовый доступ выдан пользователю '.$user->email)
                ->with('vless_link', url('/sub/'.$trialKey->sub_id));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function deleteTestKey(Request $request)
    {
        $request->validate([
            'trial_key_id' => 'required|integer|exists:trial_keys,id',
        ]);

        $trialKey = TrialKey::query()->findOrFail($request->integer('trial_key_id'));

        try {
            $this->trialKeyService->revokeTrialKey($trialKey);

            return back()->with('success', 'Тестовый доступ отозван');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function deletePaidKey(Request $request)
    {
        $request->validate([
            'sale_key_id' => 'required|integer|exists:sale_keys,id',
        ]);

        $saleKey = SaleKey::query()
            ->with('subscription')
            ->findOrFail($request->integer('sale_key_id'));

        $panels = config('admin.sale_panels', []);
        $panel = $panels[$saleKey->panel_index] ?? null;

        if (! is_array($panel)) {
            return back()->withErrors(['error' => 'Не найдена конфигурация панели для выбранного ключа']);
        }

        try {
            $result = $this->xuiApi->deleteClient(
                $panel['url'],
                $panel['username'],
                $panel['password'],
                (int) $saleKey->inbound_id,
                $saleKey->uuid
            );

            if (! ($result['success'] ?? false)) {
                return back()->withErrors(['error' => $result['msg'] ?? 'Ошибка удаления оплаченного ключа на панели']);
            }

            $saleKey->status = 'revoked';
            $saleKey->save();

            if ($saleKey->subscription) {
                $saleKey->subscription->status = 'expired';
                $saleKey->subscription->expires_at = now();
                $saleKey->subscription->save();
            }

            return back()->with('success', 'Оплаченный ключ удалён');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    protected function generateVlessLink(array $inbound, string $uuid): string
    {
        $ip = config('admin.test_panel.server_ip');
        $label = HappSubscriptionFormatter::happNodeLabel(
            (string) (config('admin.test_panel.happ_label') ?? '🇷🇺 Тест')
        );

        [$line, $err] = HappSubscriptionFormatter::vlessLineFromInboundOrError(
            $inbound,
            $uuid,
            (string) $ip,
            $label
        );

        if ($err !== null) {
            return 'Ошибка: '.$err;
        }

        return $line;
    }
}
