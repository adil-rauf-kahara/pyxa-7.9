@php
    use App\Domains\Entity\Enums\EntityEnum;
@endphp

@extends('panel.layout.settings', ['layout' => 'wide'])
@section('title', __('Custom Onboarding'))
@section('titlebar_actions', '')
@section('additional_css')
    <link
        href="{{ custom_theme_url('/assets/libs/select2/select2.min.css') }}"
        rel="stylesheet"
    />
@endsection

@section('settings')

    <h2 class="mb-4">
        {{ __('Customize Introduction') }}
    </h2>
    <p class="mb-10 lg:w-10/12">
        @lang('Customize the introduction screen to match your brand. You can change the colors in light and dark mode to align with your brand identity.')
    </p>

    <form
        action="{{ route('dashboard.admin.onboarding-pro.introduction.customization.save') }}"
        method="POST"
        enctype="multipart/form-data"
        x-data="{ activeTab: 0 }"
    >
        @csrf

        <div class="mb-4 space-y-5">
            @php
                $title_size = $introduction['title_size'] ?? 20;
                $description_size = $introduction['description_size'] ?? 16;
            @endphp
            <div
                class="w-full space-y-3"
                x-data="{ currentVal: {{ $title_size }} }"
            >
                <label
                    class="lqd-input-label flex w-fit cursor-pointer items-center gap-2 text-2xs font-medium leading-none text-label"
                    for="title-size"
                >
                    @lang('Title Size')
                </label>
                <div class="flex items-center gap-3">
                    <input
                        class="h-1.5 w-full cursor-ew-resize appearance-none rounded-full bg-heading-foreground/5 focus:outline-heading-foreground [&::-moz-range-thumb]:size-3 [&::-moz-range-thumb]:appearance-none [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border-background [&::-moz-range-thumb]:bg-heading-foreground active:[&::-moz-range-thumb]:scale-110 [&::-webkit-slider-thumb]:size-3.5 [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:border-none [&::-webkit-slider-thumb]:bg-heading-foreground active:[&::-webkit-slider-thumb]:scale-110"
                        id="title-size"
                        name="title_size"
                        x-model="currentVal"
                        type="range"
                        min="0"
                        max="100"
                        step="1"
                        value="{{ $title_size }}"
                    />
                    <span
                        class="inline-block w-10 text-end font-medium text-heading-foreground"
                        x-text="currentVal + 'px'"
                    >
                        {{ $title_size }}px
                    </span>
                </div>
            </div>

            <div
                class="w-full space-y-3"
                x-data="{ currentVal: {{ $description_size }} }"
            >
                <label
                    class="lqd-input-label flex w-fit cursor-pointer items-center gap-2 text-2xs font-medium leading-none text-label"
                    for="description-size"
                >
                    @lang('Description Size')
                </label>
                <div class="flex items-center gap-3">
                    <input
                        class="h-1.5 w-full cursor-ew-resize appearance-none rounded-full bg-heading-foreground/5 focus:outline-heading-foreground [&::-moz-range-thumb]:size-3 [&::-moz-range-thumb]:appearance-none [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border-background [&::-moz-range-thumb]:bg-heading-foreground active:[&::-moz-range-thumb]:scale-110 [&::-webkit-slider-thumb]:size-3.5 [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:border-none [&::-webkit-slider-thumb]:bg-heading-foreground active:[&::-webkit-slider-thumb]:scale-110"
                        id="description-size"
                        name="description_size"
                        x-model="currentVal"
                        type="range"
                        min="0"
                        max="100"
                        step="1"
                        value="{{ $description_size }}"
                    />
                    <span
                        class="inline-block w-10 text-end font-medium text-heading-foreground"
                        x-text="currentVal + 'px'"
                    >
                        {{ $description_size }}px
                    </span>
                </div>
            </div>
        </div>

        <hr class="my-7">

        <ul class="mb-6 flex w-full gap-3 rounded-full bg-foreground/10 p-1.5 text-xs font-medium">
            @foreach (['Light', 'Dark'] as $scheme)
                <li class="grow">
                    <button
                        @class([
                            'px-6 py-2.5 w-full leading-tight rounded-full transition-all hover:bg-background/80 [&.lqd-is-active]:bg-background [&.lqd-is-active]:shadow-xl [&.lqd-is-active]:shadow-black/10',
                            'lqd-is-active' => $loop->first,
                        ])
                        @click="activeTab = {{ $loop->index }}"
                        :class="{ 'lqd-is-active': activeTab == {{ $loop->index }} }"
                        type="button"
                    >
                        {{ $scheme }}
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="grid">
            <div
                class="col-start-1 col-end-1 row-start-1 row-end-1 space-y-5"
                x-show="activeTab === 0"
                x-transition
            >
                <x-forms.input
                    label="{{ __('Background Color') }}"
                    type="color"
                    name="background_color"
                    value="{{ $introduction->background_color ?? '' }}"
                    size="lg"
                />

                <x-forms.input
                    label="{{ __('Title Color') }}"
                    type="color"
                    name="title_color"
                    value="{{ $introduction->title_color ?? '' }}"
                    size="lg"
                />

                <x-forms.input
                    label="{{ __('Description Color') }}"
                    type="color"
                    name="description_color"
                    value="{{ $introduction->description_color ?? '' }}"
                    size="lg"
                />
            </div>

            <div
                class="col-start-1 col-end-1 row-start-1 row-end-1 space-y-5"
                x-show="activeTab === 1"
                x-transition
            >
                <x-forms.input
                    label="{{ __('Background Color') }}"
                    type="color"
                    name="dark_background_color"
                    value="{{ $introduction->dark_background_color ?? '' }}"
                    size="lg"
                />

                <x-forms.input
                    label="{{ __('Title Color') }}"
                    type="color"
                    name="dark_title_color"
                    value="{{ $introduction->dark_title_color ?? '' }}"
                    size="lg"
                />

                <x-forms.input
                    label="{{ __('Description Color') }}"
                    type="color"
                    name="dark_description_color"
                    value="{{ $introduction->dark_description_color ?? '' }}"
                    size="lg"
                />
            </div>
        </div>

        <x-button
            class="mt-7 w-full"
            type="submit"
            size="lg"
        >
            @lang('Save')
        </x-button>
    </form>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/js/panel/settings.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/select2/select2.min.js') }}"></script>
@endpush
