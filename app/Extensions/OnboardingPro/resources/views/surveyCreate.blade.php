@php use App\Domains\Entity\Enums\EntityEnum; @endphp
@extends('panel.layout.settings', ['layout' => 'wide'])
@section('title', __('Create Survey'))
@section('titlebar_actions', '')
@section('additional_css')
    <link
        href="{{ custom_theme_url('/assets/libs/select2/select2.min.css') }}"
        rel="stylesheet"
    />
@endsection

@section('settings')
    <form action="{{route("dashboard.admin.onboarding-pro.survey.post")}}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-12">
                <div
                    class="form-control mb-3 border-none p-0 [&_.select2-selection--multiple]:!rounded-[--tblr-border-radius] [&_.select2-selection--multiple]:!border-[--tblr-border-color] [&_.select2-selection--multiple]:!p-[1em_1.23em]">

                    <div class="mb-4">
                        <x-forms.input
                            id="description"
                            class:label="text-heading-foreground"
                            label="{{ __('Description') }}"
                            name="description"
                            type="textarea"
                            size="lg"
                            tooltip="{{ __('Enter the banner content here.') }}"
                            required
                        >
                        </x-forms.input>
                    </div>
                    <div class="mb-4">
                        <x-forms.input
                            id="bg_color"
                            label="{{ __('Background Color') }}"
                            tooltip="{{ __('HEX color code (e.g. #F2F7FF) for the background.') }}"
                            type="color"
                            class:label="text-heading-foreground"
                            name="background_color"
                            value="#008000"
                            size="lg"
                            required
                        />
                    </div>
                    <div class="mb-4">
                        <x-forms.input
                            id="txt_color"
                            label="{{ __('Text Color') }}"
                            tooltip="{{ __('HEX color code (e.g. #F2F7FF) for the background.') }}"
                            type="color"
                            class:label="text-heading-foreground"
                            name="text_color"
                            value="#ffffff"
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
                            <option value="1">
                                {{ __('Publish') }}
                            </option>
                            <option value="0">
                                {{ __('Draft') }}
                            </option>
                        </x-forms.input>
                    </div>
                    <div class="mb-4">
                        <x-button type="submit" class="w-full">
                            @lang('Save')
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection

@push('script')

    <script>
        $(document).ready(function () {
            "use strict";

            const bgColorInput = document.querySelector('#bg_color');
            const bgColorValue = document.querySelector('#bg_color_value');

            const txtColorInput = document.querySelector('#txt_color');
            const txtColorValue = document.querySelector('#txt_color_value');

            bgColorInput?.addEventListener('input', ev => {
                const input = ev.currentTarget;

                if (bgColorValue) {
                    bgColorValue.value = input.value
                }
                ;
            });

            bgColorValue?.addEventListener('input', ev => {
                const input = ev.currentTarget;

                if (bgColorInput) {
                    bgColorInput.value = input.value
                }
                ;
            });

            txtColorInput?.addEventListener('input', ev => {
                const input = ev.currentTarget;

                if (txtColorValue) {
                    txtColorValue.value = input.value
                }
                ;
            });

            txtColorValue?.addEventListener('input', ev => {
                const input = ev.currentTarget;

                if (txtColorInput) {
                    txtColorInput.value = input.value
                }
                ;
            });
        });
    </script>

    <script src="{{ custom_theme_url('/assets/js/panel/settings.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/select2/select2.min.js') }}"></script>
@endpush
