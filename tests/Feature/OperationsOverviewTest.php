<?php

namespace Tests\Feature;

use App\Filament\Pages\OperationsOverview;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OperationsOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_operations_overview(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->assertTrue($admin->isAdmin());
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->assertTrue(OperationsOverview::canAccess());

        Livewire::test(OperationsOverview::class)
            ->assertSee('Operativni pregled')
            ->assertSee('Aktivacija korisnika');
    }

    public function test_regular_user_cannot_access_operations_overview(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->assertFalse(OperationsOverview::canAccess());
    }
}
