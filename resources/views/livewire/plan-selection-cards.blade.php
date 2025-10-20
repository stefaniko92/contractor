<div x-data="{ selectedPlan: $wire.entangle('selectedPlan') }" class="grid grid-cols-1 md:grid-cols-3 gap-6">
    {{-- Free Plan Card --}}
    <div
        @click="selectedPlan = 'free'"
        :class="selectedPlan === 'free' ? 'border-success-500 bg-success-50 dark:bg-success-950 ring-2 ring-success-500' : 'border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600'"
        class="relative cursor-pointer rounded-xl p-6 transition-all duration-200 border-2"
        style="min-height: 400px; display: flex; flex-direction: column;"
    >
        <div x-show="selectedPlan === 'free'" class="absolute top-3 right-3">
            <span class="inline-flex items-center gap-x-1.5 rounded-md bg-success-50 px-2 py-1 text-xs font-medium text-success-700 ring-1 ring-inset ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/20">
                <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                Izabrano
            </span>
        </div>

        <div class="flex justify-center mb-4">
            <div class="rounded-full bg-success-100 dark:bg-success-900 p-3" style="width: 56px; height: 56px;">
                <svg style="width: 32px; height: 32px;" class="text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                </svg>
            </div>
        </div>

        <h3 class="text-2xl font-bold text-center mb-2 text-gray-900 dark:text-gray-100">Free Plan</h3>
        <p class="text-center text-sm text-gray-600 dark:text-gray-400 mb-4">Za početak</p>

        <div class="text-center mb-6">
            <p class="text-4xl font-bold text-gray-900 dark:text-gray-100">0 RSD</p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Zauvek besplatno</p>
        </div>

        <ul class="space-y-3 mb-6 flex-grow">
            <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                <svg style="width: 20px; height: 20px; min-width: 20px;" class="text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>3 fakture mesečno</span>
            </li>
            <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                <svg style="width: 20px; height: 20px; min-width: 20px;" class="text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>Osnovna fakturisanja</span>
            </li>
            <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                <svg style="width: 20px; height: 20px; min-width: 20px;" class="text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>PDF izvoz</span>
            </li>
        </ul>
    </div>

    {{-- Basic Monthly Card --}}
    <div
        @click="selectedPlan = 'basic_monthly'"
        :class="selectedPlan === 'basic_monthly' ? 'border-primary-500 bg-primary-50 dark:bg-primary-950 ring-2 ring-primary-500' : 'border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600'"
        class="relative cursor-pointer rounded-xl p-6 transition-all duration-200 border-2"
        style="min-height: 400px; display: flex; flex-direction: column;"
    >
        <div x-show="selectedPlan === 'basic_monthly'" class="absolute top-3 right-3">
            <span class="inline-flex items-center gap-x-1.5 rounded-md bg-primary-50 px-2 py-1 text-xs font-medium text-primary-700 ring-1 ring-inset ring-primary-600/20 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/20">
                <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                Izabrano
            </span>
        </div>

        <div class="flex justify-center mb-4">
            <div class="rounded-full bg-primary-100 dark:bg-primary-900 p-3" style="width: 56px; height: 56px;">
                <svg style="width: 32px; height: 32px;" class="text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
            </div>
        </div>

        <h3 class="text-2xl font-bold text-center mb-2 text-gray-900 dark:text-gray-100">Basic - Mesečno</h3>
        <p class="text-center text-sm text-gray-600 dark:text-gray-400 mb-4">Neograničeno fakturisanje</p>

        <div class="text-center mb-4">
            <p class="text-4xl font-bold text-gray-900 dark:text-gray-100">600 RSD</p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Po mesecu</p>
        </div>

        <div class="flex justify-center mb-4">
            <span class="inline-flex items-center rounded-md bg-warning-50 px-2 py-1 text-xs font-medium text-warning-700 ring-1 ring-inset ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/20">7 dana besplatno</span>
        </div>

        <ul class="space-y-3 mb-6 flex-grow">
            <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                <svg style="width: 20px; height: 20px; min-width: 20px;" class="text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span><strong>Neograničeno</strong> faktura</span>
            </li>
            <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                <svg style="width: 20px; height: 20px; min-width: 20px;" class="text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>Sva fakturisanja</span>
            </li>
            <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                <svg style="width: 20px; height: 20px; min-width: 20px;" class="text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>Email podrška</span>
            </li>
        </ul>
    </div>

    {{-- Basic Yearly Card (Recommended) --}}
    <div
        @click="selectedPlan = 'basic_yearly'"
        :class="selectedPlan === 'basic_yearly' ? 'border-warning-500 bg-warning-50 dark:bg-warning-950 ring-2 ring-warning-500' : 'border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600'"
        class="relative cursor-pointer rounded-xl p-6 transition-all duration-200 border-2"
        style="min-height: 400px; display: flex; flex-direction: column;"
    >
        <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
            <span class="inline-flex items-center rounded-md bg-warning-50 px-2.5 py-1 text-xs font-medium text-warning-700 ring-1 ring-inset ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/20">⭐ Preporučeno</span>
        </div>

        <div x-show="selectedPlan === 'basic_yearly'" class="absolute top-3 right-3">
            <span class="inline-flex items-center gap-x-1.5 rounded-md bg-warning-50 px-2 py-1 text-xs font-medium text-warning-700 ring-1 ring-inset ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/20">
                <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                Izabrano
            </span>
        </div>

        <div class="flex justify-center mb-4 mt-2">
            <div class="rounded-full bg-warning-100 dark:bg-warning-900 p-3" style="width: 56px; height: 56px;">
                <svg style="width: 32px; height: 32px;" class="text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
            </div>
        </div>

        <h3 class="text-2xl font-bold text-center mb-2 text-gray-900 dark:text-gray-100">Basic - Godišnje</h3>
        <p class="text-center text-sm text-gray-600 dark:text-gray-400 mb-4">Najbolja ušteda</p>

        <div class="text-center mb-4">
            <p class="text-4xl font-bold text-gray-900 dark:text-gray-100">6,000 RSD</p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Po godini</p>
        </div>

        <div class="flex justify-center mb-4">
            <span class="inline-flex items-center rounded-md bg-success-50 px-2 py-1 text-xs font-medium text-success-700 ring-1 ring-inset ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/20">Ušteda 1,200 RSD</span>
        </div>

        <ul class="space-y-3 mb-6 flex-grow">
            <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                <svg style="width: 20px; height: 20px; min-width: 20px;" class="text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span><strong>Neograničeno</strong> faktura</span>
            </li>
            <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                <svg style="width: 20px; height: 20px; min-width: 20px;" class="text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>Sva fakturisanja</span>
            </li>
            <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                <svg style="width: 20px; height: 20px; min-width: 20px;" class="text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>Email podrška</span>
            </li>
            <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                <svg style="width: 20px; height: 20px; min-width: 20px;" class="text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span><strong>7 dana besplatno</strong></span>
            </li>
            <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                <svg style="width: 20px; height: 20px; min-width: 20px;" class="text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span><strong>2 meseca gratis</strong></span>
            </li>
        </ul>
    </div>
</div>

<div class="text-center text-sm text-gray-600 dark:text-gray-400 mt-6">
    <p>🔒 Sigurna naplata preko Stripe • Otkaži bilo kada</p>
</div>
