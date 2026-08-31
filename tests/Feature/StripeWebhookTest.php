<?php

namespace Tests\Feature;

use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    public function test_webhook_requests_are_verified_by_cashier_instead_of_csrf(): void
    {
        config()->set('cashier.webhook.secret', 'whsec_test_secret');

        $this->post('/stripe/webhook', [], [
            'Stripe-Signature' => 'invalid',
        ])->assertForbidden();
    }
}
