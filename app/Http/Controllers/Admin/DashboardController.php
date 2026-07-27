<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\KeyOrder;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\TrialKey;
use App\Models\User;
use App\Support\SharedVpnAccess;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $now = Carbon::now();

        $fulfilled = KeyOrder::query()->where('status', OrderStatus::Fulfilled);

        $stats = [
            'activeSubscriptions' => Subscription::query()->active()->count(),
            'revenueToday' => (int) (clone $fulfilled)->where('paid_at', '>=', $now->copy()->startOfDay())->sum('amount'),
            'revenueMonth' => (int) (clone $fulfilled)->where('paid_at', '>=', $now->copy()->startOfMonth())->sum('amount'),
            'newUsersToday' => User::query()->where('created_at', '>=', $now->copy()->startOfDay())->count(),
            'newUsersWeek' => User::query()->where('created_at', '>=', $now->copy()->subDays(7))->count(),
            'pendingOrders' => KeyOrder::query()->where('status', OrderStatus::Pending)->count(),
            'activeTrials' => TrialKey::query()->where('expires_at', '>', $now)->count(),
            'openTickets' => SupportTicket::query()->where('status', '!=', SupportTicket::STATUS_CLOSED)->count(),
            'totalUsers' => User::query()->count(),
        ];

        $recentOrders = KeyOrder::query()
            ->with(['user:id,name,email,telegram_username', 'plan:id,name'])
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (KeyOrder $o) => [
                'id' => $o->id,
                'user' => $this->userLabel($o->user),
                'plan' => $o->plan?->name ?? '—',
                'amount' => (int) $o->amount,
                'status' => $o->status?->value,
                'paymentStatus' => $o->payment_status,
                'source' => $o->purchase_source,
                'createdAt' => $o->created_at?->format('d.m.Y H:i'),
            ]);

        $recentUsers = User::query()
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'contact' => $u->isBotOnly() ? ('@'.($u->telegram_username ?? $u->telegram_id)) : $u->email,
                'isTelegram' => $u->telegram_id !== null,
                'createdAt' => $u->created_at?->format('d.m.Y H:i'),
            ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'recentUsers' => $recentUsers,
            'nodes' => $this->nodes(),
        ]);
    }

    private function userLabel(?User $user): string
    {
        if (! $user) {
            return '—';
        }

        if ($user->isBotOnly()) {
            return $user->telegram_username ? '@'.$user->telegram_username : ('TG '.$user->telegram_id);
        }

        return $user->name ?: $user->email;
    }

    /**
     * @return list<array{host: string, scheme: string}>
     */
    private function nodes(): array
    {
        $nodes = [];

        foreach (SharedVpnAccess::nodeUris() as $uri) {
            $nodes[] = [
                'host' => (string) (parse_url($uri, PHP_URL_HOST) ?: '—'),
                'scheme' => (string) (parse_url($uri, PHP_URL_SCHEME) ?: 'vless'),
            ];
        }

        return $nodes;
    }
}
