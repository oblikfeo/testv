<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\KeyOrder;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\YooKassaService;
use App\Support\SharedVpnAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundRevokesAccessTest extends TestCase
{
    use RefreshDatabase;

    private function plan(array $overrides = []): Plan
    {
        return Plan::create(array_merge([
            'name' => 'Месяц',
            'slug' => 'month',
            'devices' => 3,
            'days' => 30,
            'price' => 299,
        ], $overrides));
    }

    private function paidOrder(User $user, Plan $plan, array $overrides = []): KeyOrder
    {
        return KeyOrder::create(array_merge([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => OrderStatus::Pending,
            'purchase_source' => 'web',
            'purchase_action' => 'new_purchase',
            'payment_id' => 'pay-'.uniqid(),
            'payment_status' => 'pending',
            'amount' => $plan->price,
        ], $overrides));
    }

    private function succeededWebhook(KeyOrder $order, Plan $plan, User $user): array
    {
        return [
            'event' => 'payment.succeeded',
            'object' => [
                'id' => $order->payment_id,
                'status' => 'succeeded',
                'payment_method' => ['type' => 'bank_card'],
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'user_id' => (string) $user->id,
                    'plan_id' => (string) $plan->id,
                ],
            ],
        ];
    }

    private function refundWebhook(KeyOrder $order): array
    {
        return [
            'event' => 'refund.succeeded',
            'object' => [
                'id' => 'refund-1',
                'payment_id' => $order->payment_id,
                'status' => 'succeeded',
            ],
        ];
    }

    public function test_refund_revokes_access_for_a_new_purchase(): void
    {
        $service = app(YooKassaService::class);
        $user = User::factory()->create();
        $plan = $this->plan();
        $order = $this->paidOrder($user, $plan);

        $service->processWebhook($this->succeededWebhook($order, $plan, $user));

        $this->assertTrue(SharedVpnAccess::userHasAccess($user->fresh()));
        // Заказ должен помнить выданную подписку — иначе возврату нечего отзывать.
        $this->assertNotNull($order->fresh()->target_subscription_id);

        $service->processWebhook($this->refundWebhook($order));

        $this->assertFalse(SharedVpnAccess::userHasAccess($user->fresh()));
        $this->assertSame('refunded', $order->fresh()->payment_status);
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
    }

    public function test_repeated_payment_webhook_after_refund_does_not_restore_access(): void
    {
        $service = app(YooKassaService::class);
        $user = User::factory()->create();
        $plan = $this->plan();
        $order = $this->paidOrder($user, $plan);

        $service->processWebhook($this->succeededWebhook($order, $plan, $user));
        $service->processWebhook($this->refundWebhook($order));

        // ЮKassa повторно доставляет payment.succeeded — доступ восстанавливаться не должен.
        $service->processWebhook($this->succeededWebhook($order, $plan, $user));

        $this->assertFalse(SharedVpnAccess::userHasAccess($user->fresh()));
        $this->assertSame(1, Subscription::query()->where('user_id', $user->id)->count());
    }

    public function test_refund_revokes_access_when_renewal_changed_the_plan(): void
    {
        $service = app(YooKassaService::class);
        $user = User::factory()->create();
        $monthly = $this->plan();
        $yearly = $this->plan(['name' => 'Год', 'slug' => 'year', 'days' => 365, 'price' => 2990]);

        $firstOrder = $this->paidOrder($user, $monthly);
        $service->processWebhook($this->succeededWebhook($firstOrder, $monthly, $user));

        $subscription = Subscription::query()->where('user_id', $user->id)->firstOrFail();

        // Продление другим тарифом переписывает plan_id подписки — старый заказ
        // переставал её находить, и возврат по нему не отзывал доступ.
        $renewOrder = $this->paidOrder($user, $yearly, [
            'purchase_action' => 'renew_subscription',
            'target_subscription_id' => $subscription->id,
        ]);
        $service->processWebhook($this->succeededWebhook($renewOrder, $yearly, $user));

        $this->assertSame($yearly->id, $subscription->fresh()->plan_id);

        $service->processWebhook($this->refundWebhook($firstOrder));

        $this->assertFalse(SharedVpnAccess::userHasAccess($user->fresh()));
    }

    public function test_reconcile_command_revokes_access_left_active_by_an_earlier_refund(): void
    {
        $user = User::factory()->create();
        $plan = $this->plan();

        // Заказ, обработанный старым кодом: возврат отмечен, а подписка осталась активной.
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'max_devices' => $plan->devices,
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $order = $this->paidOrder($user, $plan, [
            'status' => OrderStatus::Cancelled,
            'payment_status' => 'refunded',
            'paid_at' => now(),
        ]);

        $this->assertTrue(SharedVpnAccess::userHasAccess($user->fresh()));

        $this->artisan('payments:reconcile-refunds')->assertSuccessful();

        $this->assertFalse(SharedVpnAccess::userHasAccess($user->fresh()));
        $this->assertSame('expired', $subscription->fresh()->status);
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
    }
}
