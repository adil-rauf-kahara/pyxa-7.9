@extends('panel.layout.settings', ['disable_tblr' => true, 'layout' => 'wide'])
@section('title', __('Onboarding'))
@section('titlebar_actions')
    <x-button
        href="{{ $app_is_demo ? '#' : route('dashboard.admin.onboarding-pro.introduction.customization') }}"
        onclick="{{ $app_is_demo ? 'return toastr.info(\'This feature is disabled in Demo version.\')' : '' }}"
    >
        <x-tabler-settings
            class="size-5"
            stroke-width="1.5"
        />
        {{ __('Customization') }}
    </x-button>
@endsection

@section('settings')
    <div class="mb-6">
        <h2 class="mb-3">
            {{ __('Manage Introductions') }}
        </h2>
        <p class="lg:w-10/12">
            {{ __('Manage the introductions displayed during onboarding. Change their order, activation status, and customize their content as needed.') }}
        </p>
    </div>

    <form
        class="space-y-5"
        method="POST"
        action="{{ route('dashboard.admin.onboarding-pro.introduction.save') }}"
        enctype="multipart/form-data"
        x-data="introduction"
    >
        @csrf

        @php
            $menusWithoutList = $menus
                ->filter(function ($menu) use ($list) {
                    return !$list->contains('key', $menu['data-name']);
                })
                ->map(function ($menu) {
                    $menu['is_menu'] = true;
                    return $menu;
                })
                ->toArray();

            $intros = array_merge($introductions->toArray(), $menusWithoutList);

            $lastItemKey = array_search('last', array_column($intros, 'key'));
            if ($lastItemKey !== false) {
                $lastItem = $intros[$lastItemKey];
                unset($intros[$lastItemKey]);
                $intros[] = $lastItem;
            }
        @endphp

        <ul
            class="flex flex-col gap-3"
            x-bind="list"
            x-ref="list"
            x-sort
            x-sort:config="{
				onMove: function (evt, originalEvent) {
					if (evt.related.classList.contains('ignore-order')) {
						return false
					}
				},
				onSort: function(event) {
					event.target.dispatchEvent(new CustomEvent('sortableUpdate', {detail: {sortable: this, event}}));
				},
			}"
        >
            @foreach ($intros as $intro_item)
                @php
                    $child_items = array_filter($intros, function ($item) use ($intro_item) {
                        return isset($intro_item['is_menu']) ? isset($item['is_menu']) && $item['parent_id'] == $intro_item['id'] : $item['parent_id'] == $intro_item['id'];
                    });
                @endphp

                @continue($intro_item['parent_id'])

                <li
                    data-id="{{ $intro_item['key'] }}"
                    data-active="{{ isset($intro_item['status']) && $intro_item['status'] ? 'true' : 'false' }}"
                    @class([
                        'flex flex-col items-start gap-3 rounded-xl border bg-background p-5 shadow-sm transition-colors',
                        'ignore-order' =>
                            $intro_item['key'] === 'initialize' || $intro_item['key'] === 'last',
                    ])
                    x-data="{
                        active: {{ isset($intro_item['status']) && $intro_item['status'] ? 'true' : 'false' }},
                        childItems: {{ json_encode(array_values($child_items)) }},
                        fileUrl: '{{ $intro_item['file_url'] ?? '' }}',
                    }"
                    :data-active="active ? 'true' : 'false'"
                    x-sort:item
                >
                    <div class="flex w-full gap-3">
                        @if ($intro_item['key'] !== 'initialize' && $intro_item['key'] !== 'last')
                            <div class="flex flex-col justify-between">
                                <span
                                    class="-ms-2 -mt-2 inline-grid size-10 cursor-grab place-items-center"
                                    x-sort:handle
                                >
                                    <x-tabler-grip-vertical class="size-5" />
                                </span>
                            </div>
                        @endif

                        <div class="flex grow flex-col gap-2">
                            <h4 class="mb-0 flex items-center justify-between gap-1 uppercase">
                                {{ str_replace('_', ' ', $intro_item['key']) }}

                                @if ($intro_item['key'] !== 'initialize')
                                    <x-forms.input
                                        type="checkbox"
                                        switcher
                                        ::checked="{{ isset($intro_item['status']) && $intro_item['status'] ? 1 : 0 }}"
                                        x-model="active"
                                        @change="setActiveItems()"
                                    />
                                @endif
                            </h4>

                            <hr>

                            <x-forms.input
                                size="lg"
                                value="{!! isset($intro_item['title']) ? $intro_item['title'] : '' !!}"
                                placeholder="{{ __('Enter a title') }}"
                                label="{{ __('Title') }}"
                                name="introductions[{{ $intro_item['key'] }}][title][0]"
                            />
                            <x-forms.input
                                size="lg"
                                value="{!! isset($intro_item['intro']) ? $intro_item['intro'] : '' !!}"
                                placeholder="{{ __('Enter a description') }}"
                                label="{{ __('Description') }}"
                                name="introductions[{{ $intro_item['key'] }}][intro][0]"
                            />
                            <div class="flex gap-3">
                                <x-forms.input
                                    class:container="grow"
                                    size="lg"
                                    type="file"
                                    label="{{ __('Upload Image') }}"
                                    name="introductions[{{ $intro_item['key'] }}][file][0]"
                                    accept="image/*"
                                    x-ref="file"
                                    @change="fileUrl = URL.createObjectURL($event.target.files[0])"
                                />

                                <div
                                    @class([
                                        'relative size-11 self-end',
                                        'hidden' => !filled($intro_item['key'] ?? ''),
                                    ])
                                    :class="{ 'hidden': !fileUrl || fileUrl === '' }"
                                >
                                    <img
                                        class="size-full rounded-full object-cover object-center"
                                        :src="fileUrl"
                                    />

                                    <a
                                        class="absolute -end-2 -top-2 flex size-5 items-center justify-center rounded-full bg-red-500/70 text-white backdrop-blur-2xl"
                                        href="#"
                                        title="{{ __('Remove Image') }}"
                                        @click.prevent="removeImage('{{ route('dashboard.admin.onboarding-pro.introduction.delete-image', ['key' => $intro_item['key']]) }}')"
                                    >
                                        <x-tabler-x class="size-4" />
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>

                    <template
                        x-for="(childItem, index) in childItems"
                        :key="index"
                    >
                        <ul
                            class="flex w-full flex-col gap-3"
                            x-sort:children
                        >
                            <li
                                class="flex flex-col items-start gap-3 rounded-xl border bg-background p-5 shadow-sm transition-colors"
                                :data-id="childItem['key']"
                                x-data="{ fileUrl: childItem['file_url'] }"
                                x-sort:item
                            >
                                <div class="flex w-full gap-3">
                                    <div class="flex flex-col items-center gap-3">
                                        <span
                                            class="-ms-1 -mt-3 inline-grid size-10 cursor-grab place-items-center"
                                            x-sort:handle
                                        >
                                            <x-tabler-grip-vertical class="size-5" />
                                        </span>

                                        <x-button
                                            class="size-8"
                                            size="none"
                                            variant="danger"
                                            type="button"
                                            title="{{ __('Remove Slideshow Item') }}"
                                            @click.prevent="childItems.splice(index, 1)"
                                        >
                                            <x-tabler-trash class="size-4" />
                                        </x-button>
                                    </div>

                                    <div class="flex grow flex-col gap-2">
                                        <x-forms.input
                                            size="lg"
                                            ::value="childItem['title'] ?? ''"
                                            placeholder="{{ __('Enter a title') }}"
                                            label="{{ __('Title') }}"
                                            name="introductions[{{ $intro_item['key'] }}][title][index]"
                                            x-init="$el.name = $el.name.replace('index', index + 1)"
                                        />
                                        <x-forms.input
                                            size="lg"
                                            ::value="childItem['intro'] ?? ''"
                                            placeholder="{{ __('Enter a description') }}"
                                            label="{{ __('Description') }}"
                                            name="introductions[{{ $intro_item['key'] }}][intro][index]"
                                            x-init="$el.name = $el.name.replace('index', index + 1)"
                                        />

                                        <div class="flex gap-3">
                                            <x-forms.input
                                                class:container="grow"
                                                size="lg"
                                                type="file"
                                                label="{{ __('Upload Image') }}"
                                                name="introductions[{{ $intro_item['key'] }}][file][index]"
                                                x-init="$el.name = $el.name.replace('index', index + 1)"
                                                x-ref="file"
                                                accept="image/*"
                                                @change="fileUrl = URL.createObjectURL($event.target.files[0])"
                                            />

                                            <div
                                                class="relative size-11 self-end"
                                                :class="{ 'hidden': !fileUrl || fileUrl === '' }"
                                            >
                                                <img
                                                    class="size-full rounded-full object-cover object-center"
                                                    :src="fileUrl"
                                                />

                                                <a
                                                    class="absolute -end-2 -top-2 flex size-5 items-center justify-center rounded-full bg-red-500/70 text-white backdrop-blur-2xl"
                                                    href="#"
                                                    title="{{ __('Remove Image') }}"
                                                    @click.prevent="removeImage('{{ route('dashboard.admin.onboarding-pro.introduction.delete-image', ['key' => $intro_item['key']]) }}', index + 1)"
                                                >
                                                    <x-tabler-x class="size-4" />
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </template>

                    {{-- TODO: remove condition if we want slideshow for all items --}}
                    @if ($intro_item['key'] === 'initialize')
                        <hr class="w-full">

                        <x-button
                            type="button"
                            variant="success"
                            @click.prevent="childItems.push({ title: '', intro: '', file: '', file_url: '' })"
                        >
                            <x-tabler-plus class="size-4" />
                            {{ __('Add Slideshow Item') }}
                        </x-button>
                    @endif
                </li>
            @endforeach
        </ul>

        <input
            type="hidden"
            name="order"
            :value="JSON.stringify(order)"
        >
        <input
            type="hidden"
            name="active_items"
            :value="JSON.stringify(activeItems)"
        >

        <x-button
            class="sticky bottom-6 w-full bg-primary/70 backdrop-blur-xl"
            size="lg"
            type="submit"
        >
            {{ __('Save Changes') }}
        </x-button>
    </form>
@endsection

@push('script')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('introduction', () => ({
                listItems: [],
                order: [],
                activeItems: [],
                init() {
                    document.querySelector('.lqd-page-content-wrap')?.classList?.remove('overflow-hidden');

                    this.setListItems();
                    this.setOrder();
                    this.setActiveItems();
                },
                list: {
                    '@sortableUpdate'() {
                        this.setOrder();
                    },

                },
                async removeImage(url, index = null) {
                    const finalUrl = index !== null ? `${url}-${index}` : url;

                    const response = await fetch(finalUrl, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    await response.json();

                    if (index !== null && this.childItems) {
                        this.childItems[index].file_url = '';
                    } else {
                        this.fileUrl = '';
                    }

                    const fileInput = this.$refs.file;
                    if (fileInput) {
                        fileInput.value = '';
                    }

                    toastr.success('Image successfully deleted');

                },
                setListItems() {
                    this.listItems = [...this.$refs.list.querySelectorAll(':scope > li')];
                },
                setOrder() {
                    this.setListItems();
                    this.order = this.listItems.map(item => item.dataset.id);
                },
                setActiveItems() {
                    this.setListItems();
                    this.activeItems = this.listItems.filter(item => item.dataset.active === 'true').map(item => item.dataset.id);
                }
            }))
        })
    </script>
@endpush
