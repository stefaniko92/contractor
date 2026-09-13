<x-filament-panels::page>
    @php($operations = $this->getOperations())

    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-filament::section compact>
                <p class="text-sm text-gray-500 dark:text-gray-400">Korisnici ukupno</p>
                <p class="mt-1 text-3xl font-semibold">{{ number_format($operations['stats']['total_users'], 0, ',', '.') }}</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $operations['stats']['new_users'] }} novih u poslednjih 30 dana</p>
            </x-filament::section>

            <x-filament::section compact>
                <p class="text-sm text-gray-500 dark:text-gray-400">Plaćeni korisnici</p>
                <p class="mt-1 text-3xl font-semibold text-success-600 dark:text-success-400">{{ number_format($operations['stats']['paid_users'], 0, ',', '.') }}</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $operations['stats']['trials'] }} aktivnih trial naloga</p>
            </x-filament::section>

            <x-filament::section compact>
                <p class="text-sm text-gray-500 dark:text-gray-400">Nacrti stariji od 24h</p>
                <p class="mt-1 text-3xl font-semibold {{ $operations['stats']['drafts_older_than_day'] > 0 ? 'text-warning-600 dark:text-warning-400' : '' }}">{{ number_format($operations['stats']['drafts_older_than_day'], 0, ',', '.') }}</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Moguće nedovršene fakture</p>
            </x-filament::section>

            <x-filament::section compact>
                <p class="text-sm text-gray-500 dark:text-gray-400">SEF greške, 30 dana</p>
                <p class="mt-1 text-3xl font-semibold {{ $operations['stats']['sef_failures'] > 0 ? 'text-danger-600 dark:text-danger-400' : '' }}">{{ number_format($operations['stats']['sef_failures'], 0, ',', '.') }}</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Zahtevaju operativnu proveru</p>
            </x-filament::section>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <x-filament::section heading="Aktivacija korisnika" description="Korisnici registrovani u poslednjih 30 dana.">
                <div class="divide-y divide-gray-200 dark:divide-white/10">
                    @foreach ($operations['funnel'] as $step)
                        <div class="flex items-center justify-between gap-4 py-3">
                            <span class="text-sm font-medium">{{ $step['label'] }}</span>
                            <div class="flex min-w-28 items-baseline justify-end gap-2">
                                <span class="text-lg font-semibold">{{ $step['count'] }}</span>
                                @if ($step['rate'] !== null)
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($step['rate'], 1, ',', '.') }}%</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>

            <x-filament::section heading="Korisnici koji su stali" description="Najnoviji nalozi bez sledećeg koraka u aktivaciji.">
                <div class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse ($operations['blockers'] as $user)
                        <a href="{{ $user['url'] }}" class="block py-3 transition hover:bg-gray-50 dark:hover:bg-white/5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium">{{ $user['name'] }}</p>
                                    <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ $user['email'] }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm text-warning-600 dark:text-warning-400">{{ $user['step'] }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $user['created_at'] }}</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <p class="py-6 text-sm text-gray-500 dark:text-gray-400">Nema korisnika koji su stali u poslednjih 30 dana.</p>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <x-filament::section heading="Nacrti za proveru" description="Fakture u pripremi duže od 24 sata.">
                <div class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse ($operations['drafts'] as $draft)
                        <a href="{{ $draft['url'] }}" class="block py-3 transition hover:bg-gray-50 dark:hover:bg-white/5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium">{{ $draft['invoice_number'] }}</p>
                                    <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ $draft['user'] }} · {{ $draft['client'] }}</p>
                                </div>
                                <span class="shrink-0 text-sm text-warning-600 dark:text-warning-400">{{ $draft['age'] }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="py-6 text-sm text-gray-500 dark:text-gray-400">Nema nacrta starijih od 24 sata.</p>
                    @endforelse
                </div>
            </x-filament::section>

            <x-filament::section heading="Poslednje SEF greške" description="Greške evidentirane u poslednjih 30 dana.">
                <div class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse ($operations['sef_failures'] as $failure)
                        <a href="{{ $failure['url'] }}" class="block py-3 transition hover:bg-gray-50 dark:hover:bg-white/5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium">{{ $failure['invoice_number'] }} · {{ $failure['user'] }}</p>
                                    <p class="mt-1 line-clamp-2 text-sm text-gray-500 dark:text-gray-400">{{ $failure['error'] }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm text-danger-600 dark:text-danger-400">{{ $failure['status'] }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $failure['occurred_at'] }}</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <p class="py-6 text-sm text-gray-500 dark:text-gray-400">Nema evidentiranih SEF grešaka u poslednjih 30 dana.</p>
                    @endforelse
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
