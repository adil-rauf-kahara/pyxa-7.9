@php use App\Domains\Entity\Enums\EntityEnum; @endphp
@extends('panel.layout.settings', ['layout' => 'wide'])
@section('title', __('Edit Banner'))
@section('titlebar_actions', '')
@section('additional_css')
    <link
        href="{{ custom_theme_url('/assets/libs/select2/select2.min.css') }}"
        rel="stylesheet"
    />
@endsection

@section('settings')
    <form
        action="{{ route('dashboard.admin.onboarding-pro.banner.update', ['id' => $banner->id]) }}"
        method="POST"
    >
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-12">
                <div
                    class="form-control mb-3 border-none p-0 [&_.select2-selection--multiple]:!rounded-[--tblr-border-radius] [&_.select2-selection--multiple]:!border-[--tblr-border-color] [&_.select2-selection--multiple]:!p-[1em_1.23em]">

                    <div class="mb-4">
                        <x-forms.input
                            class:label="text-heading-foreground"
                            id="description"
                            label="{{ __('Description') }}"
                            name="description"
                            type="textarea"
                            size="lg"
                            tooltip="{{ __('Enter the banner content here.') }}"
                            required
                        >
                            {{ $banner->description }}
                        </x-forms.input>
                    </div>
                    <div class="mb-4">
                        <x-forms.input
                            class:label="text-heading-foreground"
                            id="bg_color"
                            label="{{ __('Background Color') }}"
                            tooltip="{{ __('HEX color code (e.g. #F2F7FF) for the background.') }}"
                            type="color"
                            name="background_color"
                            value="{{ $banner->background_color }}"
                            size="lg"
                            required
                        />
                    </div>
                    <div class="mb-4">
                        <x-forms.input
                            class:label="text-heading-foreground"
                            id="txt_color"
                            label="{{ __('Text Color') }}"
                            tooltip="{{ __('HEX color code (e.g. #F2F7FF) for the background.') }}"
                            type="color"
                            name="text_color"
                            value="{{ $banner->text_color }}"
                            size="lg"
                            required
                        />
                    </div>
                    <div class="mb-4">
                        <x-forms.input
                            id="status"
                            label="{{ __('Status') }}"
                            name="status"
                            type="select"
                            size="lg"
                            tooltip="{{ __('Only one banner can be active.') }}"
                            required
                        >
                            <option
                                value="1"
                                {{ $banner->status == 1 ? 'selected' : '' }}
                            >
                                {{ __('Publish') }}
                            </option>
                            <option
                                value="0"
                                {{ $banner->status == 0 ? 'selected' : '' }}
                            >
                                {{ __('Draft') }}
                            </option>
                        </x-forms.input>
                    </div>

                    <div class="mb-4">
                        <x-forms.input
                            id="permanent"
                            label="{{ __('Visibility') }}"
                            name="permanent"
                            type="select"
                            size="lg"
                            tooltip="{{ __('Select how often it will be shown to the user.') }}"
                            required
                        >
                            <option
                                value="0"
                                {{ $banner->permanent == 0 ? 'selected' : '' }}
                            >
                                {{ __('One Time') }}
                            </option>
                            <option
                                value="1"
                                {{ $banner->permanent == 1 ? 'selected' : '' }}
                            >
                                {{ __('Permanent') }}
                            </option>
                        </x-forms.input>
                    </div>
                    <div class="mb-4">
                        <x-button
                            class="w-full"
                            type="submit"
                        >
                            @lang('Save')
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/js/panel/settings.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/select2/select2.min.js') }}"></script>
@endpush
