@php
    use Illuminate\Support\Facades\Cache;

    $store = config('exchange.cache_store', 'file');
    $key = config('exchange.cache_key', 'exchange_rates');
    $rates = Cache::store($store)->get($key, []);

    $eurRate = null;
    $usdRate = null;

    if (!isset($rates['error'])) {
        foreach ($rates as $rate) {
            if (isset($rate['currency_code']) && $rate['currency_code'] === 'EUR') {
                $eurRate = $rate;
            }
            if (isset($rate['currency_code']) && $rate['currency_code'] === 'USD') {
                $usdRate = $rate;
            }
        }
    }

    $hasRates = $eurRate || $usdRate;
@endphp

@if($hasRates)
<div
    x-data="{
        open: false,
        toggle() {
            this.open = !this.open;
        }
    }"
    style="position: fixed; bottom: 24px; right: 24px; z-index: 9999;"
>
    {{-- Floating chat-style button --}}
    <button
        @click="toggle()"
        type="button"
        style="
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        "
        onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 6px 16px rgba(37, 99, 235, 0.5)';"
        onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 12px rgba(37, 99, 235, 0.4)';"
        x-bind:aria-expanded="open"
        aria-label="Kursna lista"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 28px; height: 28px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
    </button>

    {{-- Modal popup --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        @click.away="open = false"
        style="
            position: absolute;
            bottom: 75px;
            right: 0;
            width: 360px;
            max-width: calc(100vw - 48px);
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        "
        x-cloak
    >
        {{-- Header --}}
        <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 20px; color: white;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <h3 style="font-size: 18px; font-weight: 600; margin: 0;">Kursna lista</h3>
                </div>
                <button
                    @click="open = false"
                    type="button"
                    style="
                        background: rgba(255, 255, 255, 0.2);
                        border: none;
                        border-radius: 6px;
                        padding: 6px;
                        color: white;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: background 0.2s;
                    "
                    onmouseover="this.style.background='rgba(255, 255, 255, 0.3)';"
                    onmouseout="this.style.background='rgba(255, 255, 255, 0.2)';"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 20px; height: 20px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div style="padding: 20px;">
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 16px 0; text-align: center;">
                Srednji kurs Narodne banke Srbije
            </p>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                @if($eurRate)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px; background: #f9fafb; border-radius: 10px; border: 1px solid #e5e7eb;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; font-weight: 700;">
                            €
                        </div>
                        <div>
                            <p style="font-size: 16px; font-weight: 600; color: #111827; margin: 0;">EUR</p>
                            <p style="font-size: 13px; color: #6b7280; margin: 0;">{{ $eurRate['currency_name'] }}</p>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <p style="font-size: 20px; font-weight: 700; color: #111827; margin: 0;">{{ number_format($eurRate['middle_rate'], 4) }}</p>
                        <p style="font-size: 12px; color: #6b7280; margin: 0;">RSD</p>
                    </div>
                </div>
                @endif

                @if($usdRate)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px; background: #f9fafb; border-radius: 10px; border: 1px solid #e5e7eb;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; font-weight: 700;">
                            $
                        </div>
                        <div>
                            <p style="font-size: 16px; font-weight: 600; color: #111827; margin: 0;">USD</p>
                            <p style="font-size: 13px; color: #6b7280; margin: 0;">{{ $usdRate['currency_name'] }}</p>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <p style="font-size: 20px; font-weight: 700; color: #111827; margin: 0;">{{ number_format($usdRate['middle_rate'], 4) }}</p>
                        <p style="font-size: 12px; color: #6b7280; margin: 0;">RSD</p>
                    </div>
                </div>
                @endif
            </div>

            {{-- Footer --}}
            @if($eurRate && isset($eurRate['date']))
            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                <p style="font-size: 12px; color: #9ca3af; text-align: center; margin: 0;">
                    Ažurirano: {{ $eurRate['date'] }}
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endif
