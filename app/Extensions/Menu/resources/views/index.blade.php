@php
    $filters = ['User', 'Admin'];
@endphp

@extends('panel.layout.settings')
@section('title', __('Menu Management'))
@section('titlebar_actions')
    <x-modal
            title="{{ __('New menu item') }}"
            disable-modal="{{ $app_is_demo }}"
            disable-modal-message="{{ __('This feature is disabled in Demo version.') }}"
    >
        <x-slot:trigger>
            <x-tabler-plus class="size-4" />
            {{ __('Add Menu Item') }}
        </x-slot:trigger>
        <x-slot:modal>
            <form
                    class="menu-item-add-form flex flex-col gap-6"
                    action="#"
                    x-data="{ selectedType: 'label' }"
            >
                <x-forms.input
                        id="type"
                        type="select"
                        size="lg"
                        name="type"
                        label="{{ __('Type') }}"
                        x-model="selectedType"
                >
                    {{--                    <option value="link">@lang('Link')</option> --}}
                    <option value="label">@lang('Title')</option>
                    <option value="divider">@lang('Divider')</option>
                    <option value="item">@lang('Link')</option>
                </x-forms.input>

                <div :class="{ hidden: selectedType === 'divider' }">
                    <x-forms.input
                            id="label"
                            name="label"
                            label="{{ __('Label') }}"
                            placeholder="{{ __('Enter text') }}"
                            ::type="selectedType === 'divider' ? 'hidden' : 'text'"
                            size="lg"
                    />
                </div>
                <div :class="{ hidden: (selectedType === 'divider' || selectedType === 'label') }">
                    <x-forms.input
                            id="link"
                            name="link"
                            label="{{ __('Link') }}"
                            value="https://"
                            placeholder="{{ __('https://www.example.com') }}"
                            ::type="(selectedType === 'divider' || selectedType === 'label') ? 'hidden' : 'text'"
                            size="lg"
                    />
                </div>

                <div class="flex justify-end gap-2">
                    <x-button
                            @click.prevent="modalOpen = false"
                            variant="ghost-shadow"
                            type="button"
                    >
                        {{ __('Cancel') }}
                    </x-button>
                    <x-button type="submit">
                        {{ __('Add Item') }}
                    </x-button>
                </div>
            </form>
        </x-slot:modal>
    </x-modal>
@endsection

@push('css')
    <style>
        .lqd-menu-management {
            --switcher-ball: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23ffffff'/%3e%3c/svg%3e");
        }

        .lqd-menu-list {
            ol {
                display: flex;
                flex-direction: column;
                margin-top: 1rem;
                gap: 1rem;
                padding-inline-start: 4rem;
            }
        }

        .lqd-menu-item-placeholder {
            border: 2px dashed hsl(var(--heading-foreground) / 25%);
            border-radius: 0.75rem;
        }
    </style>
@endpush

@section('settings')
    <div class="lqd-menu-management">
        <h2 class="mb-5">
            <span class="opacity-60">
                @lang('Editing:')
            </span>
            @lang('Menu')
        </h2>
        <p class="mb-8">
            @lang('With the MagicAI menu creator, you can edit menu labels, change icons, switch between user and admin menus, and hide specific menu items.')
        </p>
        <div class="lqd-user-menu-list">
            <ol class="lqd-menu-list flex flex-col gap-4">
                @foreach ($items as $item)
                    @if($item['show_condition'])
                        <li
                                class="group/item text-xs font-medium"
                                id="{{ 'menu-' . $item['id'] }}"
                                @if ($item['type']) data-type="{{ $item['type'] }}" @endif
                        >
                            <div class=" items-center gap-5 rounded-xl border bg-background px-4 py-3 transition-all hover:shadow-lg hover:shadow-black/5">
                                <div class="flex items-center  gap-5  w-full">
                                    <div class="lqd-menu-item-handle size-6 flex cursor-grab items-center justify-center">
                                        <svg
                                                width="10"
                                                height="16"
                                                viewBox="0 0 10 16"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                    d="M2 16C1.45 16 0.979167 15.8042 0.5875 15.4125C0.195833 15.0208 0 14.55 0 14C0 13.45 0.195833 12.9792 0.5875 12.5875C0.979167 12.1958 1.45 12 2 12C2.55 12 3.02083 12.1958 3.4125 12.5875C3.80417 12.9792 4 13.45 4 14C4 14.55 3.80417 15.0208 3.4125 15.4125C3.02083 15.8042 2.55 16 2 16ZM8 16C7.45 16 6.97917 15.8042 6.5875 15.4125C6.19583 15.0208 6 14.55 6 14C6 13.45 6.19583 12.9792 6.5875 12.5875C6.97917 12.1958 7.45 12 8 12C8.55 12 9.02083 12.1958 9.4125 12.5875C9.80417 12.9792 10 13.45 10 14C10 14.55 9.80417 15.0208 9.4125 15.4125C9.02083 15.8042 8.55 16 8 16ZM2 10C1.45 10 0.979167 9.80417 0.5875 9.4125C0.195833 9.02083 0 8.55 0 8C0 7.45 0.195833 6.97917 0.5875 6.5875C0.979167 6.19583 1.45 6 2 6C2.55 6 3.02083 6.19583 3.4125 6.5875C3.80417 6.97917 4 7.45 4 8C4 8.55 3.80417 9.02083 3.4125 9.4125C3.02083 9.80417 2.55 10 2 10ZM8 10C7.45 10 6.97917 9.80417 6.5875 9.4125C6.19583 9.02083 6 8.55 6 8C6 7.45 6.19583 6.97917 6.5875 6.5875C6.97917 6.19583 7.45 6 8 6C8.55 6 9.02083 6.19583 9.4125 6.5875C9.80417 6.97917 10 7.45 10 8C10 8.55 9.80417 9.02083 9.4125 9.4125C9.02083 9.80417 8.55 10 8 10ZM2 4C1.45 4 0.979167 3.80417 0.5875 3.4125C0.195833 3.02083 0 2.55 0 2C0 1.45 0.195833 0.979167 0.5875 0.5875C0.979167 0.195833 1.45 0 2 0C2.55 0 3.02083 0.195833 3.4125 0.5875C3.80417 0.979167 4 1.45 4 2C4 2.55 3.80417 3.02083 3.4125 3.4125C3.02083 3.80417 2.55 4 2 4ZM8 4C7.45 4 6.97917 3.80417 6.5875 3.4125C6.19583 3.02083 6 2.55 6 2C6 1.45 6.19583 0.979167 6.5875 0.5875C6.97917 0.195833 7.45 0 8 0C8.55 0 9.02083 0.195833 9.4125 0.5875C9.80417 0.979167 10 1.45 10 2C10 2.55 9.80417 3.02083 9.4125 3.4125C9.02083 3.80417 8.55 4 8 4Z"
                                                    fill="#A6A5AB"
                                            />
                                        </svg>
                                    </div>

                                    <div class="flex grow items-center gap-3">
                                        @if ($item['type'] === 'divider')
                                            <span class="cursor-default">
                                        @lang('Divider')
                                    </span>
                                            <hr class="grow border-t-2 border-heading-foreground/15">
                                        @else
                                            @if ($item['type'] === 'label')
                                                <span class="size-6 inline-flex shrink-0 items-center justify-center rounded-md bg-primary text-4xs text-primary-foreground">
                                            {{ strtoupper(substr($item['label'], 0, 1)) }}
                                        </span>
                                            @endif
                                            @if ($item['type'] === 'item')
                                                <button
                                                        class="icon-wrap [&_i]:size-6 cursor-pointer transition-transform hover:scale-110 [&_i]:text-[24px]"
                                                        id="icon-wrap-{{ $item['id'] }}"
                                                >
                                                    @if($item['icon'])
                                                        <x-dynamic-component
                                                                stroke-width="1.5"
                                                                :component="$item['icon']"
                                                        />
                                                    @else
                                                        <div class="flex flex-col justify-content-center items-center">
                                                            <svg stroke-width="1.5" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M12.802 2.165l5.575 2.389c.48 .206 .863 .589 1.07 1.07l2.388 5.574c.22 .512 .22 1.092 0 1.604l-2.389 5.575c-.206 .48 -.589 .863 -1.07 1.07l-5.574 2.388c-.512 .22 -1.092 .22 -1.604 0l-5.575 -2.389a2.036 2.036 0 0 1 -1.07 -1.07l-2.388 -5.574a2.036 2.036 0 0 1 0 -1.604l2.389 -5.575c.206 -.48 .589 -.863 1.07 -1.07l5.574 -2.388a2.036 2.036 0 0 1 1.604 0z"></path>
                                                                <path d="M12 8v4"></path>
                                                                <path d="M12 16h.01"></path>
                                                            </svg>
                                                            <small class="">none</small>
                                                        </div>                                                  @endif
                                                </button>
                                            @endif
                                            <input
                                                    class="border-none bg-transparent"
                                                    data-link="{{ route('dashboard.admin.menu.update', [$item['id'], 'label']) }}"
                                                    data-item="input"
                                                    type="text"
                                                    name="label"
                                                    value="{{ $item['label'] }}"
                                                    x-data
                                                    x-init="$el.style.width = `${$el.value.length + 3}ch`"
                                                    x-on:keydown="$el.style.width = `${$el.value.length + 3}ch`"
                                                    x-on:change="$el.style.width = `${$el.value.length + 3}ch`"
                                            >
                                            <input
                                                    class="icon-name-input"
                                                    id="icon-name-input-{{ $item['id'] }}"
                                                    data-link="{{ route('dashboard.admin.menu.update', [$item['id'], 'icon']) }}"
                                                    data-item="input"
                                                    type="hidden"
                                                    name="icon"
                                                    value="{{ $item['icon'] }}"
                                            >
                                        @endif

                                        @if ($item['custom_menu'])
                                            <x-button
                                                    class="size-6 "
                                                    data-item="delete"
                                                    variant="ghost"
                                                    hover-variant="danger"
                                                    size="none"
                                                    href="{{ route('dashboard.admin.menu.delete', $item['id']) }}"
                                                    onclick="return confirm('{{ __('Are you sure? This is permanent.') }}')"
                                            >
                                                <x-tabler-trash class="size-5" />
                                            </x-button>
                                        @endif
                                    </div>
                                    @if($item['type'] == 'item' && $dash_theme == 'bolt')
                                        <div class="ms-auto flex items-center gap-2">
                                            <x-forms.input
                                                    label="Bolt Menu"
                                                    class="h-4 w-8 bg-input-border [background-size:10px]"
                                                    data-href="{{ route('dashboard.admin.menu.bolt-menu', $item['id']) }}"
                                                    data-status="menu"
                                                    data-color-div="{{ $item['id'].'-bolt-color' }}"
                                                    type="checkbox"
                                                    switcher
                                                    type="checkbox"
                                                    :checked="$item['bolt_menu'] == '1'"
                                            />
                                        </div>
                                    @endif
                                    @if($item['key'] != 'menu_setting')
                                        <div class="ms-auto flex items-center gap-2">
                                            <x-forms.input
                                                    class="h-4 w-8 bg-input-border [background-size:10px]"
                                                    id="login_without_confirmation"
                                                    data-href="{{ route('dashboard.admin.menu.status', $item['id']) }}"
                                                    data-status="menu"
                                                    type="checkbox"
                                                    switcher
                                                    type="checkbox"
                                                    :checked="$item['is_active'] == '1'"
                                            />
                                        </div>
                                    @endif
                                </div>
                                @if($item['type'] == 'item' && $dash_theme == 'bolt')
                                    <div id="{{ $item['id'].'-bolt-color' }}" class="flex border mt-2 p-2 gap-3 rounded  {{ $item['bolt_menu'] ? '' : 'hidden' }}">
                                        <x-forms.input
                                                data-link="{{ route('dashboard.admin.menu.update', [$item['id'], 'bolt_background']) }}"
                                                data-item="input"
                                                id="bolt_background"
                                                name="bolt_background"
                                                label="{{ __('Bolt menu background') }}"
                                                type="color"
                                                size="lg"
                                                value="{{ $item['bolt_background'] }}"
                                                tooltip="{{ __('Pick a color for for the icon background. Color is in HEX format.') }}"
                                        />
                                        <x-forms.input
                                                data-link="{{ route('dashboard.admin.menu.update', [$item['id'], 'bolt_foreground']) }}"
                                                data-item="input"
                                                id="bolt_foreground"
                                                name="bolt_foreground"
                                                label="{{ __('Bolt menu foreground') }}"
                                                type="color"
                                                size="lg"
                                                value="{{ $item['bolt_foreground'] }}"
                                                tooltip="{{ __('Pick a color for for the icon foreground. Color is in HEX format.') }}"
                                        />
                                    </div>
                                @endif
                            </div>
                            @php
                                $children = $item['children'];
                            @endphp

                            @if ($children)
                                <ol class="lqd-menu-list-children mt-4 flex flex-col gap-4 ps-16">
                                    @foreach ($children as $child)

                                        @if(! $child['extension'])
                                            @php
                                                $child['show_condition'] = true;
                                            @endphp
                                        @endif
                                        @if(isset($child['show_condition']))
                                            <li
                                                    class="group/child"
                                                    id="{{ 'menu-' . $child['id'] }}"
                                                    @if (isset($child['type']) && $child['type']) data-type="{{ $child['type'] }}" @endif
                                            >
                                                <div class="flex items-center gap-5 rounded-xl border bg-background px-4 py-3 transition-all hover:shadow-lg hover:shadow-black/5">
                                                    <div class="lqd-menu-item-handle size-6 flex cursor-grab items-center justify-center">
                                                        <svg
                                                                width="10"
                                                                height="16"
                                                                viewBox="0 0 10 16"
                                                                fill="none"
                                                                xmlns="http://www.w3.org/2000/svg"
                                                        >
                                                            <path
                                                                    d="M2 16C1.45 16 0.979167 15.8042 0.5875 15.4125C0.195833 15.0208 0 14.55 0 14C0 13.45 0.195833 12.9792 0.5875 12.5875C0.979167 12.1958 1.45 12 2 12C2.55 12 3.02083 12.1958 3.4125 12.5875C3.80417 12.9792 4 13.45 4 14C4 14.55 3.80417 15.0208 3.4125 15.4125C3.02083 15.8042 2.55 16 2 16ZM8 16C7.45 16 6.97917 15.8042 6.5875 15.4125C6.19583 15.0208 6 14.55 6 14C6 13.45 6.19583 12.9792 6.5875 12.5875C6.97917 12.1958 7.45 12 8 12C8.55 12 9.02083 12.1958 9.4125 12.5875C9.80417 12.9792 10 13.45 10 14C10 14.55 9.80417 15.0208 9.4125 15.4125C9.02083 15.8042 8.55 16 8 16ZM2 10C1.45 10 0.979167 9.80417 0.5875 9.4125C0.195833 9.02083 0 8.55 0 8C0 7.45 0.195833 6.97917 0.5875 6.5875C0.979167 6.19583 1.45 6 2 6C2.55 6 3.02083 6.19583 3.4125 6.5875C3.80417 6.97917 4 7.45 4 8C4 8.55 3.80417 9.02083 3.4125 9.4125C3.02083 9.80417 2.55 10 2 10ZM8 10C7.45 10 6.97917 9.80417 6.5875 9.4125C6.19583 9.02083 6 8.55 6 8C6 7.45 6.19583 6.97917 6.5875 6.5875C6.97917 6.19583 7.45 6 8 6C8.55 6 9.02083 6.19583 9.4125 6.5875C9.80417 6.97917 10 7.45 10 8C10 8.55 9.80417 9.02083 9.4125 9.4125C9.02083 9.80417 8.55 10 8 10ZM2 4C1.45 4 0.979167 3.80417 0.5875 3.4125C0.195833 3.02083 0 2.55 0 2C0 1.45 0.195833 0.979167 0.5875 0.5875C0.979167 0.195833 1.45 0 2 0C2.55 0 3.02083 0.195833 3.4125 0.5875C3.80417 0.979167 4 1.45 4 2C4 2.55 3.80417 3.02083 3.4125 3.4125C3.02083 3.80417 2.55 4 2 4ZM8 4C7.45 4 6.97917 3.80417 6.5875 3.4125C6.19583 3.02083 6 2.55 6 2C6 1.45 6.19583 0.979167 6.5875 0.5875C6.97917 0.195833 7.45 0 8 0C8.55 0 9.02083 0.195833 9.4125 0.5875C9.80417 0.979167 10 1.45 10 2C10 2.55 9.80417 3.02083 9.4125 3.4125C9.02083 3.80417 8.55 4 8 4Z"
                                                                    fill="#A6A5AB"
                                                            />
                                                        </svg>
                                                    </div>

                                                    <div class="flex grow items-center gap-3">
                                                        @if ($child['type'] === 'divider')
                                                            <span class="cursor-default">
                                                        @lang('Divider')
                                                    </span>
                                                            <hr class="grow border-t-2 border-heading-foreground/15">
                                                        @else
                                                            @if ($child['type'] === 'label')
                                                                <span class="size-6 inline-flex shrink-0 items-center justify-center rounded-md bg-primary text-4xs text-primary-foreground">
                                                            {{ strtoupper(substr($child['label'], 0, 1)) }}
                                                        </span>
                                                            @endif

                                                            @if ($child['type'] === 'item')
                                                                <button
                                                                        class="icon-wrap [&_i]:size-6 cursor-pointer transition-transform hover:scale-110 [&_i]:text-[24px]"
                                                                        id="icon-wrap-{{ $child['id'] }}"
                                                                >
                                                                    @if($child['icon'])
                                                                        <x-dynamic-component
                                                                                stroke-width="1.5"
                                                                                :component="$child['icon']"
                                                                        />
                                                                    @else
                                                                        <div class="flex flex-col justify-content-center items-center">
                                                                            <svg stroke-width="1.5" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                                <path d="M12.802 2.165l5.575 2.389c.48 .206 .863 .589 1.07 1.07l2.388 5.574c.22 .512 .22 1.092 0 1.604l-2.389 5.575c-.206 .48 -.589 .863 -1.07 1.07l-5.574 2.388c-.512 .22 -1.092 .22 -1.604 0l-5.575 -2.389a2.036 2.036 0 0 1 -1.07 -1.07l-2.388 -5.574a2.036 2.036 0 0 1 0 -1.604l2.389 -5.575c.206 -.48 .589 -.863 1.07 -1.07l5.574 -2.388a2.036 2.036 0 0 1 1.604 0z"></path>
                                                                                <path d="M12 8v4"></path>
                                                                                <path d="M12 16h.01"></path>
                                                                            </svg>
                                                                            <small class="">none</small>
                                                                        </div>
                                                                    @endif
                                                                </button>
                                                            @endif
                                                            <input
                                                                    class="border-none bg-transparent"
                                                                    data-link="{{ route('dashboard.admin.menu.update', [$child['id'], 'label']) }}"
                                                                    data-item="input"
                                                                    type="text"
                                                                    name="label"
                                                                    value="{{ $child['label'] }}"
                                                                    x-data
                                                                    x-init="$el.style.width = `${$el.value.length + 3}ch`"
                                                                    x-on:keydown="$el.style.width = `${$el.value.length + 3}ch`"
                                                                    x-on:change="$el.style.width = `${$el.value.length + 3}ch`"
                                                            >

                                                            <input
                                                                    class="icon-name-input"
                                                                    id="icon-name-input-{{ $child['id'] }}"
                                                                    data-link="{{ route('dashboard.admin.menu.update', [$child['id'], 'icon']) }}"
                                                                    data-item="input"
                                                                    type="hidden"
                                                                    name="icon"
                                                                    value="{{ $child['icon'] }}"
                                                            >
                                                        @endif

                                                        @if ($item['custom_menu'])
                                                            <x-button
                                                                    class="size-6  "
                                                                    data-item="delete"
                                                                    variant="ghost"
                                                                    hover-variant="danger"
                                                                    size="none"
                                                                    href="{{ route('dashboard.admin.menu.delete', $child['id']) }}"
                                                                    onclick="return confirm('{{ __('Are you sure? This is permanent.') }}')"
                                                            >
                                                                <x-tabler-trash class="size-5" />
                                                            </x-button>
                                                        @endif
                                                    </div>

                                                    @if(isset($child['key']) && $child['key'] != 'menu_setting')
                                                        <div class="ms-auto flex items-center gap-2">
                                                            <x-forms.input
                                                                    class="h-4 w-8 bg-input-border [background-size:10px]"
                                                                    id="login_without_confirmation"
                                                                    data-href="{{ route('dashboard.admin.menu.status', $child['id']) }}"
                                                                    data-status="menu"
                                                                    type="checkbox"
                                                                    switcher
                                                                    type="checkbox"
                                                                    :checked="$child['is_active'] == '1'"
                                                            />
                                                        </div>
                                                    @endif

                                                </div>
                                            </li>
                                        @endif
                                    @endforeach
                                </ol>
                            @endif
                        </li>
                    @endif
                @endforeach
            </ol>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ custom_theme_url('/assets/libs/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/nested-sortable/jquery.mjs.nestedSortable.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/libs/universal-icon-picker-main/assets/js/universal-icon-picker.min.js') }}"></script>
    <script src="{{ custom_theme_url('/assets/js/panel/settings.js') }}"></script>

    <script>
        $(document).ready(function() {
            const $menuList = $('.lqd-menu-list');

            $('.menu-item-add-form').on('submit', function(event) {
                event.preventDefault();

                const form = event.currentTarget;
                const label = form.elements.label.value;
                const type = form.elements.type.value;
                const link = form.elements.link.value;


                $.ajax({
                    type: 'POST',
                    url: '{{ route('dashboard.admin.menu.store') }}',
                    data: {
                        _token: "{{ csrf_token() }}",
                        type,
                        label: type === 'divider' ? 'divider' : label,
                        link: type === 'item' ? link : null
                    },
                    dataType: "json",
                    success: function(resultData) {
                        toastr.success(resultData.message);
                        setTimeout(() => {
                            location.reload()
                        }, 1500);
                    }
                });
            })


            $('[data-status="menu"]').on('change', function() {

                let route = $(this).data('href');

                let colorDiv = $(this).data('color-div');

                if(colorDiv) {
                    $(`#${colorDiv}`).toggleClass('hidden');
                }

                $.ajax({
                    type: 'POST',
                    url: route,
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: "json",
                    success: function(resultData) {
                        toastr.success(resultData.message);
                    }
                });
            });

            $('[data-item="input"]').on('change', function() {

                let input = $(this);

                let value = input.val();

                if(value == '' && input.attr('name') !== 'icon') {
                    return;
                }

                let route = input.data('link');

                $.ajax({
                    type: 'POST',
                    url: route,
                    data: {
                        value: value,
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: "json",
                    success: function(resultData) {
                        toastr.success(resultData.message);
                    }
                });
            });


            $menuList.nestedSortable({
                handle: ".lqd-menu-item-handle",
                items: 'li',
                toleranceElement: '> div',
                maxLevels: 2,
                placeholder: 'lqd-menu-item-placeholder',
                forcePlaceholderSize: true,
                update: function() {
                    let menu_serialized = $menuList.nestedSortable("serialize");
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('dashboard.admin.menu.order') }}',
                        data: $menuList.nestedSortable("serialize"),
                        dataType: "text",
                        success: function(resultData) {
                            toastr.success(resultData.message && resultData.message.length ? resultData.message : '{{ __('Updated successfully') }}');
                        }
                    });
                },
                activate: function(event, ui) {
                    console.log(ui.item);
                }
            });

            document.querySelectorAll('.icon-wrap').forEach(el => {
                const elId = el.id;
                var uip = new UniversalIconPicker(`#${elId}`, {
                    iconLibraries: [
                        'tabler-icons.min.json',
                    ],
                    iconLibrariesCss: [
                        'tabler-icons.min.css',
                    ],
                    onSelect: function(jsonIconData) {
                        const parentEl = el.closest('li');

                        const iconName = parentEl.querySelector('.icon-name-input');

                        if(jsonIconData.iconHtml) {
                            el.innerHTML = jsonIconData.iconHtml;
                        }else{
                            el.innerHTML = '<div class="flex flex-col justify-content-center items-center">' +
                                '<svg stroke-width="1.5" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">' +
                                '<path d="M12.802 2.165l5.575 2.389c.48 .206 .863 .589 1.07 1.07l2.388 5.574c.22 .512 .22 1.092 0 1.604l-2.389 5.575c-.206 .48 -.589 .863 -1.07 1.07l-5.574 2.388c-.512 .22 -1.092 .22 -1.604 0l-5.575 -2.389a2.036 2.036 0 0 1 -1.07 -1.07l-2.388 -5.574a2.036 2.036 0 0 1 0 -1.604l2.389 -5.575c.206 -.48 .589 -.863 1.07 -1.07l5.574 -2.388a2.036 2.036 0 0 1 1.604 0z"></path>' +
                                '<path d="M12 8v4"></path>'+
                                '<path d="M12 16h.01"></path>' +
                                '</svg>' +
                                '<small class="">none</small>' +
                                '</div>';
                        }

                        if(jsonIconData.iconClass) {
                            iconName.value = jsonIconData.iconClass.replace('ti ti-', 'tabler-');
                        }else {
                            iconName.value = '';
                        }

                        iconName.dispatchEvent(new Event('change'));
                    },
                });
            })
        });
    </script>
@endpush