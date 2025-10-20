<?php

namespace App\Livewire;

use Livewire\Component;

class PlanSelectionCards extends Component
{
    public string $selectedPlan = 'free';

    public function selectPlan(string $plan): void
    {
        $this->selectedPlan = $plan;

        // Dispatch event to parent component (Register page)
        $this->dispatch('plan-selected', plan: $plan);
    }

    public function render()
    {
        return view('livewire.plan-selection-cards');
    }
}
