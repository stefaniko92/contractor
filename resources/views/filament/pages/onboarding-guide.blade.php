@php
    $steps = [
        ['title' => 'Od podataka firme do prve fakture', 'icon' => 'heroicon-o-document-check',
            'description' => 'Pripremi podatke svoje registrovane firme, poslovni račun i podatke klijenta. Proći ćemo kroz ono što ti je potrebno za početak.',
            'items' => ['Tvoja firma', 'Poslovni račun', 'Prvi klijent'],
            'note' => 'Nema potrebe da sve završiš odmah. Vodič možeš ponovo otvoriti na početnoj strani.'],
        ['title' => 'Predstavi svoju firmu', 'icon' => 'heroicon-o-building-office-2',
            'description' => 'Unesi poslovno ime, PIB, matični broj i adresu prema podacima svoje firme. Ovi podaci se prikazuju na fakturama koje šalješ klijentima.',
            'items' => ['Poslovno ime i adresa', 'PIB i matični broj', 'Račun za uplatu'],
            'note' => 'Proveri podatke pre izdavanja prve fakture, naročito naziv firme i broj poslovnog računa.'],
        ['title' => 'Dodaj klijenta kome naplaćuješ uslugu', 'icon' => 'heroicon-o-globe-europe-africa',
            'description' => 'Klijent je osoba ili firma kojoj izdaješ fakturu. Unesi njegove podatke i izaberi da li je domaći ili strani klijent.',
            'items' => ['Domaći klijent: podaci za fakturisanje', 'Strani klijent: država i valuta', 'Za deviznu uplatu: IBAN i SWIFT'],
            'note' => 'IBAN i SWIFT svog deviznog računa preuzmi iz instrukcija banke. To su podaci tvog računa, a ne računa klijenta.'],
        ['title' => 'Proveri, pa izdaj prvu fakturu', 'icon' => 'heroicon-o-document-text',
            'description' => 'Izaberi klijenta, dodaj opis usluge, iznos, valutu i datume. Proveri podatke pre izdavanja.',
            'items' => ['Nacrt: sačuvano za kasniju doradu', 'Izdata faktura: ulazi u prikazani promet', 'Slanje klijentu je poseban korak'],
            'note' => 'Nacrti ne ulaze u prikazani promet. Pregled zavisi od podataka koje uneseš: uključi i fakture izdate van aplikacije. Za poreske nedoumice proveri sa knjigovođom.'],
    ];
@endphp

<div x-data="{ step: 0 }" class="onboarding-guide space-y-6">
    <div class="onboarding-guide-muted flex items-center justify-between gap-3 text-sm" aria-live="polite">
        <span x-text="`Korak ${step + 1} od 4`"></span>
        <span>Vodič za početak</span>
    </div>

    <div class="guide-progress" aria-hidden="true">
        @foreach (['Priprema', 'Tvoja firma', 'Klijent', 'Faktura'] as $label)
            <div :class="{ 'is-current': step === {{ $loop->index }}, 'is-done': step > {{ $loop->index }} }">
                <span class="guide-progress-line"></span>
                <span>{{ $label }}</span>
            </div>
        @endforeach
    </div>

    @foreach ($steps as $index => $item)
        <section x-show="step === {{ $index }}" @if ($index !== 0) x-cloak @endif class="space-y-5">
            <div class="guide-scene">
                <div class="guide-example">
                    <div class="guide-example-header">
                        <x-filament::icon :icon="$item['icon']" class="onboarding-guide-icon h-6 w-6" />
                        <strong>{{ ['Sve na jednom mestu', 'Podaci firme', 'Podaci klijenta', 'Pregled fakture'][$index] }}</strong>
                        <span class="guide-example-label">Primer</span>
                    </div>
                    @if ($index === 0)
                        <div class="guide-journey">
                            @foreach (['Firma', 'Klijent', 'Faktura'] as $label)
                                <div><span class="guide-number">{{ $loop->iteration }}</span><strong>{{ $label }}</strong><small>{{ ['Unesi svoje podatke', 'Dodaj kome naplaćuješ', 'Proveri i izdaj'][$loop->index] }}</small></div>
                            @endforeach
                        </div>
                    @elseif ($index === 1)
                        <dl class="guide-fields">
                            <div><dt>Poslovno ime</dt><dd>Studio Primer PR</dd></div>
                            <div><dt>Adresa</dt><dd>Ulica primera 12, Beograd</dd></div>
                            <div><dt>PIB</dt><dd>Podatak iz registracije</dd></div>
                            <div><dt>Poslovni račun</dt><dd>Podatak iz tvoje banke</dd></div>
                        </dl>
                    @elseif ($index === 2)
                        <dl class="guide-fields">
                            <div><dt>Domaći klijent</dt><dd>Primer d.o.o. · Srbija</dd></div>
                            <div><dt>Strani klijent</dt><dd>Example Studio · Nemačka</dd></div>
                        </dl>
                        <div class="guide-bank"><x-filament::icon icon="heroicon-o-building-library" class="h-5 w-5" /><span>Devizna uplata na tvoj račun<strong>IBAN + SWIFT iz instrukcija banke</strong></span></div>
                    @else
                        <div class="guide-invoice">
                            <div><span>FAKTURA · PRIMER</span><span class="guide-draft">Nacrt</span></div>
                            <div><span>Dizajn vizitkarte</span><strong>10.000 RSD</strong></div>
                            <div class="guide-total"><span>Za uplatu</span><strong>10.000 RSD</strong></div>
                            <small>Primer prikaza. Vodič ne kreira fakturu.</small>
                        </div>
                    @endif
                </div>
            </div>
            <h2 class="onboarding-guide-title text-xl font-semibold">{{ $item['title'] }}</h2>
            <p class="text-sm leading-relaxed">{{ $item['description'] }}</p>
            <div class="guide-note"><x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5 shrink-0" /><p>{{ $item['note'] }}</p></div>
        </section>
    @endforeach

    <p class="onboarding-guide-muted text-sm">Imaš pitanje? Chat je dostupan u aplikaciji i nakon zatvaranja vodiča.</p>

    <div class="guide-footer">
        <x-filament::button color="gray" wire:click="unmountAction" type="button">Kasnije</x-filament::button>
        <div class="guide-navigation">
            <x-filament::icon-button class="guide-back" icon="heroicon-o-arrow-left" label="Prethodni korak" color="gray" x-show="step > 0" x-cloak x-on:click="step--" />
            <x-filament::button type="button" x-show="step < 3" x-on:click="step++" icon="heroicon-o-arrow-right" icon-position="after">Dalje</x-filament::button>
            <x-filament::button tag="a" :href="$companyUrl" x-show="step === 3" x-cloak>Podesi podatke firme</x-filament::button>
        </div>
    </div>
</div>
