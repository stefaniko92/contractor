<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Subscription;
use Tests\TestCase;

class SubscriptionLimitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_plan_subscription_uses_the_configured_invoice_limit(): void
    {
        $user = User::factory()->create();

        Subscription::create([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'free_plan_123',
            'stripe_status' => 'active',
            'quantity' => 1,
        ]);

        $this->assertTrue($user->isOnFreePlan());
        $this->assertSame(3, $user->getMonthlyInvoiceLimit());
    }

    public function test_active_stripe_subscription_has_no_invoice_limit(): void
    {
        $user = User::factory()->create();

        Subscription::create([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_123',
            'stripe_status' => 'trialing',
            'quantity' => 1,
        ]);

        $this->assertTrue($user->hasPaidSubscription());
        $this->assertSame(PHP_INT_MAX, $user->getMonthlyInvoiceLimit());
    }
}
