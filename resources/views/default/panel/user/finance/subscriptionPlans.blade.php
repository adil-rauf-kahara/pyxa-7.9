@php
    $team = Auth::user()->getAttribute('team');
    $teamManager = Auth::user()->getAttribute('teamManager');
@endphp
@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Plans and Pricing'))
@section('titlebar_actions', '')
 

@section('content')
<div class="py-10">
    <div class="flex flex-col gap-14">
        <div class="w-full">
            <x-card
                class="lqd-plan-overview scroll-mt-11 bg-gradient-to-b from-secondary to-[#F1EEFF] to-100% pb-4 pt-2 dark:from-pink-300/10 dark:to-transparent max-md:text-center"
                id="overview"
                size="lg"
            >
                <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
                    <h3 class="mb-0">
                        @lang('Here is your plan summary:')
                    </h3>
                    <div class="flex items-center gap-2">
                        @if ($getCurrentActiveSubscription = \App\Helpers\Classes\Helper::getCurrentActiveSubscription())
                            <x-button
                                class="hover:text-red-500"
                                variant="link"
                                
                                href="{{ route('dashboard.support.new') }}"
                            >
                                {{ __('Cancel My Plan') }}
                            </x-button>
                        @endif
                    </div>
                </div>
               

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <x-card class="flex items-center text-start text-2xs" variant="shadow" size="sm">
                        <div class="flex items-center justify-between gap-1.5">
                            <p class="m-0">
                                
                                <span class="block font-semibold">@lang('Active Plan')</span>
                                {{ Auth::user()->activePlan()->name  }}
                            </p>
                        </div>
                    </x-card>

                    <x-card class="flex items-center text-start text-2xs" variant="shadow" size="sm">
                        <div class="flex items-center justify-between gap-1.5">
                            <p class="m-0">
                                <span class="block font-semibold">@lang('Renewal Date')</span>
                                {{ Auth::user()->activePlan() != null ? getSubscriptionDaysLeft() . ' ' . __('Days') : __('None') }}
                            </p>
                        </div>
                    </x-card>

                    <x-card class="flex items-center text-start text-2xs" variant="shadow" size="sm">
                        <div class="flex items-center justify-between gap-1.5">
                            <p class="m-0">
                                <span class="block font-semibold">@lang('Team Plan')</span>
                                {{ $team ? __('Active') : __('Not Active') }}
                            </p>
                        </div>
                    </x-card>

                    <x-card class="flex items-center text-start text-2xs" variant="shadow" size="sm">
                        <x-credit-list />
                    </x-card>
                </div>
            </x-card>
        </div>

        <div class="w-full">
            <h2 class="mb-5">{{ __('Select a Plan') }}:</h2>
            <p class="mb-5 lg:w-1/3">
                @lang('Choose the best plan that suits your creative journey.')
            </p>

            <div class="grid scroll-mt-28 grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                @php
                    $plans = [
                        ['name' => 'ULTIMATE CREATOR', 'price' => '99.99', 'url' => 'https://pyxa.ai'],
                        ['name' => 'PREMIUM CREATOR', 'price' => '149.99', 'url' => 'https://pyxa.ai'],
                        ['name' => 'UNLIMITED WORD/IMAGES FOR LIFE', 'price' => '249.99', 'url' => 'https://pyxa.ai/standalone-words-images-chatbot'],
                        ['name' => 'UNLIMITED WORDS FOR LIFE', 'price' => '149.00', 'url' => 'https://pyxa.ai/standalone-words-ds'],
                        ['name' => 'AI WHISPERER (E-BOOK)', 'price' => '49.00', 'url' => 'https://pyxa.ai/standalone-ebook'],
                    ];
                @endphp

                @foreach ($plans as $plan)
                    <div class="lqd-price-table w-full rounded-3xl border bg-background shadow-[0_7px_20px_rgba(0,0,0,0.04)] p-7 flex flex-col">
                       
                        <p class="text-base font-bold mb-4">{{ $plan['name'] }}</p>
                         <div class="mb-4 text-[40px] font-bold text-heading-foreground">
                            <small class="text-sm font-normal">{{ $currency->symbol ?? '$' }}</small>{{ $plan['price'] }}
                        </div>
                        <div class="mt-auto text-center">
                           <a
                                href="{{ $plan['url'] }}?ref={{ urlencode(Auth::user()->email) }}"
                                target="_blank"
                                class="inline-block w-full rounded-xl bg-primary px-6 py-3 text-white text-sm font-semibold shadow hover:bg-primary/90 transition-all"
                            >
    {{ __('Click For More Info') }}
</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
