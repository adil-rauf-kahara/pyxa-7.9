@extends('panel.layout.app', ['disable_tblr' => true])
@section('title', __('Onboarding Pro'))
@section('titlebar_actions')
@endsection

@section('content')
    <div class="py-10">
        <div class="lqd-extension-grid grid grid-cols-1 gap-7 md:grid-cols-3 lg:grid-cols-4">
            <x-card
                class="text-center"
                size="lg"
            >
                <x-tabler-badge-ad
                    class="size-28 mx-auto mb-3 text-primary"
                    stroke-width="1.5"
                />

                <h3 class="mb-5">
                    {{ __('Banners') }}
                </h3>

                <x-button
                    class="w-full"
                    href="{{ $app_is_demo ? '#' : route('dashboard.admin.onboarding-pro.banners') }}"
                    onclick="{{ $app_is_demo ? 'return toastr.info(\'This feature is disabled in Demo version.\')' : '' }}"
                >
                    @lang('View')
                </x-button>
            </x-card>

            <x-card
                class="text-center"
                size="lg"
            >
                <x-tabler-list-details
                    class="size-28 mx-auto mb-3 text-primary"
                    stroke-width="1.5"
                />

                <h3 class="mb-5">
                    {{ __('Surveys') }}
                </h3>

                <x-button
                    class="w-full"
                    href="{{ $app_is_demo ? '#' : route('dashboard.admin.onboarding-pro.surveys') }}"
                    onclick="{{ $app_is_demo ? 'return toastr.info(\'This feature is disabled in Demo version.\')' : '' }}"
                >
                    @lang('View')
                </x-button>
            </x-card>

            <x-card
                class="text-center"
                size="lg"
            >
                <x-tabler-presentation
                    class="size-28 mx-auto mb-3 text-primary"
                    stroke-width="1.5"
                />

                <h3 class="mb-5">
                    {{ __('Onboarding') }}
                </h3>

                <x-button
                    class="w-full"
                    href="{{ $app_is_demo ? '#' : route('dashboard.admin.onboarding-pro.introduction') }}"
                    onclick="{{ $app_is_demo ? 'return toastr.info(\'This feature is disabled in Demo version.\')' : '' }}"
                >
                    @lang('View')
                </x-button>
            </x-card>
        </div>
    </div>
@endsection

@push('script')
@endpush
