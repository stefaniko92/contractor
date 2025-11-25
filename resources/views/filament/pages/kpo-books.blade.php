<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('kpo.section_heading') }}
        </x-slot>

        <x-slot name="description">
            {{ __('kpo.section_description') }}
        </x-slot>

        <div class="fi-ta-table-ctn overflow-x-auto">
            <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 dark:divide-white/5">
                <thead class="divide-y divide-gray-200 dark:divide-white/5">
                    <tr class="bg-gray-50 dark:bg-white/5">
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                <span class="fi-ta-header-cell-label text-sm font-semibold !text-gray-950 dark:!text-white">
                                    {{ __('kpo.fields.year') }}
                                </span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-center">
                                <span class="fi-ta-header-cell-label text-sm font-semibold !text-gray-950 dark:!text-white">
                                    {{ __('kpo.fields.invoice_count') }}
                                </span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-end">
                                <span class="fi-ta-header-cell-label text-sm font-semibold !text-gray-950 dark:!text-white">
                                    {{ __('kpo.fields.total_amount') }}
                                </span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-end">
                                <span class="fi-ta-header-cell-label text-sm font-semibold !text-gray-950 dark:!text-white">
                                    {{ __('kpo.fields.actions') }}
                                </span>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5 whitespace-nowrap">
                    @forelse($this->getYearsWithInvoices() as $yearData)
                        <tr class="fi-ta-row transition duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                <div class="fi-ta-col-wrp">
                                    <div class="flex w-full justify-start">
                                        <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                            <div class="flex">
                                                <div class="flex max-w-max">
                                                    <div class="fi-ta-text-item inline-flex items-center gap-1.5">
                                                        <span class="fi-ta-text-item-label text-sm leading-6 font-medium !text-gray-950 dark:!text-white">
                                                            {{ $yearData['year'] }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                <div class="fi-ta-col-wrp">
                                    <div class="flex w-full justify-center">
                                        <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                            <div class="flex">
                                                <div class="flex max-w-max">
                                                    <x-filament::badge color="info">
                                                        {{ $yearData['invoice_count'] }}
                                                    </x-filament::badge>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                <div class="fi-ta-col-wrp">
                                    <div class="flex w-full justify-end">
                                        <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                            <div class="flex">
                                                <div class="flex max-w-max">
                                                    <div class="fi-ta-text-item inline-flex items-center gap-1.5">
                                                        <span class="fi-ta-text-item-label text-sm leading-6 font-semibold !text-gray-950 dark:!text-white">
                                                            {{ $yearData['total_amount'] }} {{ $yearData['currency'] }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                <div class="fi-ta-col-wrp">
                                    <div class="flex w-full justify-end">
                                        <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                            <div class="flex">
                                                <div class="flex max-w-max">
                                                    <x-filament::button
                                                        color="primary"
                                                        icon="heroicon-o-arrow-down-tray"
                                                        tag="a"
                                                        :href="route('kpo.download', ['year' => $yearData['year']])"
                                                        target="_blank"
                                                        size="sm"
                                                    >
                                                        {{ __('kpo.actions.download') }}
                                                    </x-filament::button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="fi-ta-empty-state-ctn px-6 py-12">
                                <div class="fi-ta-empty-state-content mx-auto grid max-w-lg justify-items-center text-center">
                                    <div class="fi-ta-empty-state-icon-ctn mb-4 rounded-full bg-gray-100 dark:bg-gray-500/20 p-3">
                                        <x-filament::icon
                                            icon="heroicon-o-document-text"
                                            class="h-6 w-6 text-gray-500 dark:text-gray-400"
                                        />
                                    </div>
                                    <h4 class="fi-ta-empty-state-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                        {{ __('kpo.no_invoices') }}
                                    </h4>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
