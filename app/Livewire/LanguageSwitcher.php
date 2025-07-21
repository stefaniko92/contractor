<?php

namespace App\Livewire;

use Livewire\Component;

class LanguageSwitcher extends Component
{
    public $currentLocale;

    public $availableLocales;

    public function mount()
    {
        $this->currentLocale = app()->getLocale();
        $this->availableLocales = [
            'sr' => ['name' => 'Srpski', 'flag' => '🇷🇸'],
            'en' => ['name' => 'English', 'flag' => '🇬🇧'],
            'ru' => ['name' => 'Русский', 'flag' => '🇷🇺'],
        ];
    }

    public function changeLanguage($locale)
    {
        session(['locale' => $locale]);

        return redirect()->to(request()->fullUrlWithQuery(['lang' => $locale]));
    }

    public function render()
    {
        return view('livewire.language-switcher');
    }
}
