@php
    $banner = \App\Extensions\OnboardingPro\System\Models\Banner::query()->where('status', true)->first();
    $style_string = '';

    if ($banner) {
        if (!empty($banner->background_color)) {
            $style_string .= '.top-banner { background-color: ' . $banner->background_color . '; }';
        }
        if (!empty($banner->text_color)) {
            $style_string .= '.top-banner { color: ' . $banner->text_color . '; }';
        }
    }
@endphp

@if (filled($style_string))
    <style>
        {{ $style_string }}
    </style>
@endif

@auth
    @if (auth()->user()->type === \App\Enums\Roles::USER && $banner)
        @php
            $display = \App\Extensions\OnboardingPro\System\Models\BannerUser::query()
                ->where('user_id', auth()->user()->id)
                ->where('banner_id', $banner->id)
                ->first();
        @endphp

        @if ($banner->permanent == 0 && !$display)
            <x-alert
                class="top-banner relative z-[9999] items-center rounded-md py-3 shadow-md"
                id="banner-extension"
                size="xs"
            >
                <div class="flex w-full grow items-center justify-between gap-3">
                    <p class="m-0 text-lg font-semibold">
                        {{ $banner->description }}
                    </p>
                    <x-button
                        class="rounded-md bg-white px-4 py-2 text-sm transition"
                        onclick="sendRequestBanner({{ $banner->id }})"
                    >
                        {{ __('Close') }}
                    </x-button>
                </div>
            </x-alert>
        @elseif($banner->permanent == 1)
            <x-alert
                class="top-banner relative z-[9999] items-center rounded-md py-3 shadow-md"
                id="banner-extension"
                size="xs"
            >
                <div class="flex w-full grow items-center justify-between gap-3">
                    <p class="m-0 text-lg font-semibold">
                        {{ $banner->description }}
                    </p>
                </div>
            </x-alert>
        @endif
    @endif
@endauth
<script>
    function sendRequestBanner(bannerId) {
        const url = `{{ route('dashboard.admin.onboarding-pro.banner.display', ['id' => ':id']) }}`.replace(':id', bannerId);

        fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else {}
            })
            .then(data => {
                const alertElement = document.getElementById('banner-extension');
                if (alertElement) {
                    alertElement.style.display = 'none';
                }
            })
            .catch(error => {});
    }
</script>
