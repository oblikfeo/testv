<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaleKey;
use App\Models\Setting;
use App\Models\TrialKey;
use App\Models\User;
use App\Services\SaleKeyService;
use App\Services\XuiApiService;
use App\Support\HappSubscriptionFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    protected XuiApiService $xuiApi;

    public function __construct(XuiApiService $xuiApi)
    {
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

        $envLogin = config('admin.login');
        $envPassword = config('admin.password');

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
        $testPanel = config('admin.test_panel');

        $clients = [];
        $error = null;

        try {
            $inbounds = $this->xuiApi->getInbounds($testPanel['url'], $testPanel['username'], $testPanel['password']);

            if (! empty($inbounds['obj'])) {
                foreach ($inbounds['obj'] as $inbound) {
                    $settings = json_decode($inbound['settings'], true);
                    if (! empty($settings['clients'])) {
                        foreach ($settings['clients'] as $client) {
                            $clientStats = collect($inbound['clientStats'] ?? [])
                                ->firstWhere('email', $client['email']);

                            $clients[] = [
                                'inbound_id' => $inbound['id'],
                                'email' => $client['email'],
                                'name' => $client['name'] ?? null,
                                'uuid' => $client['id'],
                                'enable' => $client['enable'],
                                'expiry_time' => $client['expiryTime'] ?? 0,
                                'total_gb' => $client['totalGB'] ?? 0,
                                'up' => $clientStats['up'] ?? 0,
                                'down' => $clientStats['down'] ?? 0,
                                'last_online' => $clientStats['lastOnline'] ?? null,
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $emails = collect($clients)->pluck('email')->filter()->unique()->values()->all();
        $trialByEmail = TrialKey::query()
            ->whereIn('email', $emails)
            ->with('user:id,email,name')
            ->get()
            ->keyBy('email');

        return view('admin.test-keys', compact('clients', 'error', 'testPanel', 'trialByEmail'));
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
            'hours' => 'required|integer|min:1|max:24',
            'gb' => 'required|integer|min:1|max:50',
        ]);

        $testPanel = config('admin.test_panel');
        $email = 'test-' . time();
        $uuid = Str::uuid()->toString();
        
        $expiryTime = now()->addHours($request->hours)->timestamp * 1000;
        $totalBytes = $request->gb * 1024 * 1024 * 1024;

        try {
            $inbounds = $this->xuiApi->getInbounds($testPanel['url'], $testPanel['username'], $testPanel['password']);
            
            if (empty($inbounds['obj'])) {
                return back()->withErrors(['error' => 'Не найдены inbound на панели']);
            }

            $inboundId = $inbounds['obj'][0]['id'];
            
            $result = $this->xuiApi->addClient(
                $testPanel['url'],
                $testPanel['username'],
                $testPanel['password'],
                $inboundId,
                [
                    'id' => $uuid,
                    'email' => $email,
                    'enable' => true,
                    'expiryTime' => $expiryTime,
                    'totalGB' => $totalBytes,
                    'limitIp' => 0,
                    'flow' => '',
                    'subId' => Str::random(16),
                    'tgId' => '',
                    'reset' => 0,
                ]
            );

            if ($result['success']) {
                $vlessLink = $this->generateVlessLink($inbounds['obj'][0], $uuid);
                
                return back()->with('success', "Тестовый ключ создан: $email")
                    ->with('vless_link', $vlessLink);
            }

            return back()->withErrors(['error' => $result['msg'] ?? 'Ошибка создания ключа']);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function deleteTestKey(Request $request)
    {
        $request->validate([
            'inbound_id' => 'required|integer',
            'uuid' => 'required|string',
        ]);

        $testPanel = config('admin.test_panel');

        try {
            $result = $this->xuiApi->deleteClient(
                $testPanel['url'],
                $testPanel['username'],
                $testPanel['password'],
                $request->inbound_id,
                $request->uuid
            );

            if ($result['success']) {
                return back()->with('success', 'Ключ удалён');
            }

            return back()->withErrors(['error' => $result['msg'] ?? 'Ошибка удаления ключа']);

        } catch (\Exception $e) {
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
