<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OnboardingGuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_sees_guide_once_and_can_reopen_it(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(Dashboard::class)
            ->assertActionMounted('onboarding')
            ->assertSee('Proveri, pa izdaj prvu fakturu')
            ->call('unmountAction')
            ->assertSet('mountedActions', []);

        $this->assertNotNull($user->fresh()->onboarding_seen_at);
        $this->assertNull($otherUser->fresh()->onboarding_seen_at);
        $this->assertSame(0, $user->invoices()->count());

        Livewire::test(Dashboard::class)
            ->assertSet('mountedActions', [])
            ->mountAction('onboarding')
            ->assertActionMounted('onboarding');
    }

    public function test_existing_user_is_not_interrupted(): void
    {
        $user = User::factory()->create(['onboarding_seen_at' => now()]);
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(Dashboard::class)
            ->assertSet('mountedActions', [])
            ->assertSee('Vodič za početak');
    }
}
