<div
    class="flex w-full flex-col gap-11 md:col-span-2"
    id="{{ 'admin-card-' . ($widget?->name?->value ?? 'users-and-platform') }}"
>
    <div class="flex items-center justify-between">
        <h2 class="mb-0 font-bold">@lang('Users and Platform')</h2>
        <x-button
            variant="link"
            href="{{ route('dashboard.user.index') }}"
        >
            <span class="text-nowrap font-bold text-foreground"> {{ __('Visit User Dashboard') }} </span>
            <x-tabler-chevron-right class="ms-auto size-4" />
        </x-button>
    </div>
    <x-card class:body="flex justify-between flex-wrap md:flex-nowrap py-6 px-10 max-sm:gap-8">
        @php
            $users_change = percentageChange(cache('users_previous_week'), cache('users_this_week'));
            $subscribers_change = percentageChange(cache('last_week_subscribers'), cache('this_week_subscribers'));
            $referrals_change = percentageChange(cache('last_week_referrals'), cache('this_week_referrals'));
            $total_users_change = percentageChange(cache('last_week_total_users'), cache('this_week_total_users'));
        @endphp
        <div class="flex gap-4 max-sm:w-full">
            <div class="lqd-statistic-info flex grow flex-col gap-1 max-sm:items-center">
                <div class="lqd-statistic-title mb-1 flex items-center gap-2">
                    <span class="size-2 bg-[#93C5FD]"></span>
                    <span class="font-medium text-heading-foreground">{{ __('New Users') }}</span>
                </div>
                <h3 class="lqd-statistic-change m-0 flex items-center gap-2 text-2xl sm:text-[30px]">
                    {{ cache('users_this_week') }}
                </h3>
                <p class="mb-0 flex items-center gap-1 text-[12px] text-heading-foreground/50">
                    @lang('vs Last week') <x-change-indicator-plus-minus value="{{ floatval($users_change) }}" />
                </p>
            </div>
        </div>

        <span class="w-px bg-border max-sm:hidden"></span>

        <div class="flex gap-4 max-sm:w-full">
            <div class="lqd-statistic-info flex grow flex-col gap-1 max-sm:items-center">
                <div class="lqd-statistic-title mb-1 flex items-center gap-2">
                    <span class="size-2 bg-[#F893FD]"></span>
                    <span class="font-medium text-heading-foreground">{{ __('New Subscribers') }}</span>
                </div>
                <h3 class="lqd-statistic-change m-0 flex items-center gap-2 text-2xl sm:text-[30px]">
                    {{ cache('this_week_subscribers') }}
                </h3>
                <p class="mb-0 flex items-center gap-1 text-[12px] text-heading-foreground/50">
                    @lang('vs Last week') <x-change-indicator-plus-minus value="{{ floatval($subscribers_change) }}" />
                </p>
            </div>
        </div>

        <span class="w-px bg-border max-sm:hidden"></span>

        <div class="flex gap-4 max-sm:w-full">
            <div class="lqd-statistic-info flex grow flex-col gap-1 max-sm:items-center">
                <div class="lqd-statistic-title mb-1 flex items-center gap-2">
                    <span class="size-2 bg-[#89E1C5]"></span>
                    <span class="font-medium text-heading-foreground">{{ __('New Referrals') }}</span>
                </div>
                <h3 class="lqd-statistic-change m-0 flex items-center gap-2 text-2xl sm:text-[30px]">
                    {{ cache('this_week_referrals') }}
                </h3>
                <p class="mb-0 flex items-center gap-1 text-[12px] text-heading-foreground/50">
                    @lang('vs Last week') <x-change-indicator-plus-minus value="{{ floatval($referrals_change) }}" />
                </p>
            </div>
        </div>

        <span class="w-px bg-border max-sm:hidden"></span>

        <div class="flex gap-4 max-sm:w-full">
            <div class="lqd-statistic-info flex grow flex-col gap-1 max-sm:items-center">
                <div class="lqd-statistic-title mb-1 flex items-center gap-2">
                    <span class="size-2 bg-[#93C5FD]"></span>
                    <span class="font-medium text-heading-foreground">{{ __('Total Users') }}</span>
                </div>
                <h3 class="lqd-statistic-change m-0 flex items-center gap-2 text-2xl sm:text-[30px]">
                    {{ cache('this_week_total_users') }}
                </h3>
                <p class="mb-0 flex items-center gap-1 text-[12px] text-heading-foreground/50">
                    @lang('vs Last week') <x-change-indicator-plus-minus value="{{ floatval($total_users_change) }}" />
                </p>
            </div>
        </div>
    </x-card>
</div>
