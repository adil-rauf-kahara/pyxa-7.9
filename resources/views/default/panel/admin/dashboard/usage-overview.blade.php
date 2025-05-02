<x-card
    class="md:col-span-2"
    class:body="flex justify-between flex-wrap md:flex-nowrap py-6 px-10 max-sm:gap-8"
    id="{{ 'admin-card-' . ($widget?->name?->value ?? 'usage-overview') }}"
>
    @php
        $sales_change = percentageChange($sales_prev_week, $sales_this_week);
        $users_change = percentageChange(cache('users_previous_week'), cache('users_this_week'));
        $generated_change = percentageChange(cache('usage_previous_week'), cache('usage_this_week'));
        $dialy_activity_change = percentageChange(cache('daily_activity_last_week'), cache('daily_activity_this_week'));
    @endphp
    <div class="flex gap-4 max-sm:w-full">
        <div class="lqd-statistic-info flex grow flex-col gap-1 max-sm:items-center">
            <div class="lqd-statistic-title mb-1 flex items-center gap-2">
                <span class="size-2 bg-[#EBCCFE]"></span>
                <span class="font-medium text-heading-foreground">{{ __('Earnings') }}</span>
            </div>
            <h3 class="lqd-statistic-change m-0 flex items-center gap-2 text-2xl sm:text-[30px]">
                @if (currencyShouldDisplayOnRight($currencySymbol))
                    {{ number_format(cache('total_sales')) }} {{ $currencySymbol }}
                @else
                    {{ $currencySymbol }}{{ number_format(cache('total_sales')) }}
                @endif
            </h3>
            <p class="mb-0 flex items-center gap-1 text-[12px] text-heading-foreground/50">
                @lang('vs Last Week') <x-change-indicator-plus-minus value="{{ floatval($sales_change) }}" />
            </p>
        </div>
    </div>

    <span class="w-px bg-border max-sm:hidden"></span>

    <div class="flex gap-4 max-sm:w-full">
        <div class="lqd-statistic-info flex grow flex-col gap-1 max-sm:items-center">
            <div class="lqd-statistic-title mb-1 flex items-center gap-2">
                <span class="size-2 bg-[#EBCCFE]"></span>
                <span class="font-medium text-heading-foreground">{{ __('New Users') }}</span>
            </div>
            <h3 class="lqd-statistic-change m-0 flex items-center gap-2 text-2xl sm:text-[30px]">
                {{ cache('total_users') }}
            </h3>
            <p class="mb-0 flex items-center gap-1 text-[12px] text-heading-foreground/50">
                @lang('vs Last Week') <x-change-indicator-plus-minus value="{{ floatval($users_change) }}" />
            </p>
        </div>
    </div>

    <span class="w-px bg-border max-sm:hidden"></span>

    <div class="flex gap-4 max-sm:w-full">
        <div class="lqd-statistic-info flex grow flex-col gap-1 max-sm:items-center">
            <div class="lqd-statistic-title mb-1 flex items-center gap-2">
                <span class="size-2 bg-[#EBCCFE]"></span>
                <span class="font-medium text-heading-foreground">{{ __('AI Usage') }}</span>
            </div>
            <h3 class="lqd-statistic-change m-0 flex items-center gap-2 text-2xl sm:text-[30px]">
                {{ cache('total_usage') }}
            </h3>
            <p class="mb-0 flex items-center gap-1 text-[12px] text-heading-foreground/50">
                @lang('vs Last Week') <x-change-indicator-plus-minus value="{{ floatval($generated_change) }}" />
            </p>
        </div>
    </div>

    <span class="w-px bg-border max-sm:hidden"></span>

    <div class="flex gap-4 max-sm:w-full">
        <div class="lqd-statistic-info flex grow flex-col gap-1 max-sm:items-center">
            <div class="lqd-statistic-title mb-1 flex items-center gap-2">
                <span class="size-2 bg-[#EBCCFE]"></span>
                <span class="font-medium text-heading-foreground">{{ __('Daily Visit') }}</span>
            </div>
            <h3 class="lqd-statistic-change m-0 flex items-center gap-2 text-2xl sm:text-[30px]">
                {{ cache('total_daily_activity') }}
            </h3>
            <p class="mb-0 flex items-center gap-1 text-[12px] text-heading-foreground/50">
                @lang('vs Last Week') <x-change-indicator-plus-minus value="{{ floatval($dialy_activity_change) }}" />
            </p>
        </div>
    </div>
</x-card>
