<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\XuiApiService;
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
            
            if (!empty($inbounds['obj'])) {
                foreach ($inbounds['obj'] as $inbound) {
                    $settings = json_decode($inbound['settings'], true);
                    if (!empty($settings['clients'])) {
                        foreach ($settings['clients'] as $client) {
                            $clientStats = collect($inbound['clientStats'] ?? [])
                                ->firstWhere('email', $client['email']);
                            
                            $clients[] = [
                                'inbound_id' => $inbound['id'],
                                'email' => $client['email'],
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

        return view('admin.test-keys', compact('clients', 'error', 'testPanel'));
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
                $vlessLink = $this->generateVlessLink($inbounds['obj'][0], $uuid, $email);
                
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

    protected function generateVlessLink(array $inbound, string $uuid, string $email): string
    {
        $streamSettings = json_decode($inbound['streamSettings'], true);
        $realitySettings = $streamSettings['realitySettings'] ?? [];
        
        $serverNames = $realitySettings['serverNames'] ?? ['www.cloudflare.com'];
        $publicKey = $realitySettings['settings']['publicKey'] ?? '';
        $shortIds = $realitySettings['shortIds'] ?? [''];
        
        $port = $inbound['port'];
        
        $settings = json_decode($inbound['settings'], true);
        $ip = config('admin.test_panel.server_ip');

        $params = http_build_query([
            'type' => 'tcp',
            'security' => 'reality',
            'encryption' => 'none',
            'fp' => 'chrome',
            'pbk' => $publicKey,
            'sid' => $shortIds[0] ?? '',
            'sni' => $serverNames[0] ?? 'www.cloudflare.com',
        ]);

        return "vless://{$uuid}@{$ip}:{$port}?{$params}#{$email}";
    }
}
