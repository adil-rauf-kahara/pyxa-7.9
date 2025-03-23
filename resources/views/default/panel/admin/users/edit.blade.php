@extends('panel.layout.settings')
@section('title', __('Edit') . ' ' . $user->fullName())
@section('titlebar_actions', '')

@section('settings')
@php

   use Illuminate\Support\Str;

    // Decode user JSON fields safely
    $userPlanAiTools = json_decode($user->plan_ai_tools, true) ?? [];
    $userPlanFeatures = json_decode($user->plan_features, true) ?? [];
    $userOpenAiItems = json_decode($user->open_ai_items, true) ?? [];
    
    
        

@endphp
    <form >
        <div class="space-y-7">
            <div class="grid grid-cols-2 gap-x-4 gap-y-5">
                <x-forms.input-
                    id="name"
                    type="text"
                    name="name"
                    size="lg"
                    label="{{ __('Name') }}"
                    value="{{ $user->name }}"
                />

                <x-forms.input
                    id="surname"
                    type="text"
                    name="surname"
                    size="lg"
                    label="{{ __('Surname') }}"
                    value="{{ $user->surname }}"
                />

                <x-forms.input
                    id="phone"
                    data-mask="+0000000000000"
                    type="text"
                    name="phone"
                    size="lg"
                    placeholder="+000000000000"
                    label="{{ __('Phone') }}"
                    value="{{ $user->phone }}"
                />

                <x-forms.input
                    id="email"
                    type="email"
                    name="email"
                    size="lg"
                    label="{{ __('Email') }}"
                    value="{{ $user->email }}"
                />

                <x-forms.input
                    id="country"
                    container-class="w-full col-span-2"
                    type="select"
                    name="country"
                    size="lg"
                    label="{{ __('Country') }}"
                >
                    @include('panel.admin.users.countries')
                </x-forms.input>

                <x-forms.input
                    id="type"
                    type="select"
                    name="type"
                    size="lg"
                    label="{{ __('Role') }}"
                >
                    @foreach (App\Enums\Roles::cases() as $role)
                        <option
                            value="{{ $role }}"
                            {{ $user->type === $role ? 'selected' : '' }}
                        >
                            {{ $role->label() }}
                        </option>
                    @endforeach
                </x-forms.input>

                <x-forms.input
                    id="status"
                    type="select"
                    name="status"
                    size="lg"
                    label="{{ __('Status') }}"
                >
                    <option
                        value="1"
                        {{ $user->status == 1 ? 'selected' : '' }}
                    >
                        {{ __('Active') }}
                    </option>
                    <option
                        value="0"
                        {{ $user->status == 0 ? 'selected' : '' }}
                    >
                        {{ __('Passive') }}
                    </option>
                </x-forms.input>
            </div>
            
        {{-- AI Tools Section --}}
        
        @if (!empty($userPlanAiTools))
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                <x-form-step class="col-span-2 m-0" step="1" label="{{ __('AI Tools') }}" />
                @foreach ($userPlanAiTools as $key => $value)
                    <x-form.group class="col-span-2 sm:col-span-1" no-group-label :error="'plan.plan_ai_tools.' . $key">
                        <input type="hidden" name="plan_ai_tools[{{ $key }}]" value="false">
                        <x-form.checkbox
                            class="border-input rounded-input border !px-2.5 !py-3"
                            name="plan_ai_tools[{{ $key }}]" 
                            value="true" 
                            label="{{ ucfirst(str_replace('_', ' ', $key)) }}"
                            tooltip="{{ ucfirst(str_replace('_', ' ', $key)) }}"
                            checked="{{ $value ? 'checked' : '' }}"
                        />
                    </x-form.group>
                @endforeach
            </div>
        @endif

       {{-- Features Section --}}
        @if (!empty($userPlanFeatures))
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                <x-form-step class="col-span-2 m-0" step="2" label="{{ __('Features') }}" />
                @foreach ($userPlanFeatures as $key => $value)
                    <x-form.group class="col-span-2 sm:col-span-1" no-group-label :error="'plan.plan_features.' . $key">
                        <input type="hidden" name="plan_features[{{ $key }}]" value="false">
                        <x-form.checkbox
                            class="border-input rounded-input border !px-2.5 !py-3"
                            name="plan_features[{{ $key }}]"  
                            value="true" 
                            label="{{ ucfirst(str_replace('_', ' ', $key)) }}"
                             tooltip="{{ ucfirst(str_replace('_', ' ', $key)) }}"
                            checked="{{ $value ? 'checked' : '' }}"
                        />
                    </x-form.group>
                @endforeach
            </div>
        @endif
        
        
        

        {{-- Open AI Items Section --}}
        @if (!empty($userOpenAiItems))
            <div class="space-y-8">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                    <x-form-step class="col-span-2 m-0" step="3" label="{{ __('Open AI Items') }}" />
                    @foreach ($userOpenAiItems as $key => $value)
                        <x-form.group class="col-span-2 sm:col-span-1" no-group-label :error="'plan.open_ai_items.' . $key">
                            <input type="hidden" name="open_ai_items[{{ $key }}]" value="false">
                            <x-form.checkbox
                                class="border-input rounded-input border !px-2.5 !py-3"
                                name="open_ai_items[{{ $key }}]"  
                                value="true" 
                                label="{{ ucfirst(str_replace('_', ' ', $key)) }}"
                                tooltip="{{ ucfirst(str_replace('_', ' ', $key)) }}"
                                switcher
                                checked="{{ $value ? 'checked' : '' }}"
                            />
                        </x-form.group>
                    @endforeach
                </div>
            </div>
        @endif
        
        


            <div x-data="{ showContent: false }">
                <x-button
                    class="flex w-full items-center justify-between gap-7 py-3 text-2xs"
                    type="button"
                    variant="link"
                    @click="showContent = !showContent"
                >
                    <span class="h-px grow bg-current opacity-10"></span>
                    <span class="flex items-center gap-3">
                        {{ __('Credits') }}
                        <x-tabler-chevron-down
                            class="size-4 transition"
                            ::class="{ 'rotate-180': showContent }"
                        />
                    </span>
                    <span class="h-px grow bg-current opacity-10"></span>
                </x-button>
                <div
                    class="hidden pt-5"
                    :class="{ hidden: !showContent }"
                >
                    @livewire('assign-view-credits', ['entities' => $user->entity_credits])
                </div>
            </div>
        <x-button id="user_edit_button"  class="w-full"  size="lg" type="button" onclick="userSave({{ $user->id }})">
            {{ __('Save') }}
        </x-button>
        </div>
    </form>
@endsection


@push('script')
 <script>
     console.log("JavaScript file loaded!");
 </script>
    <script src="{{ custom_theme_url('/assets/js/panel/user.js') }}?v={{ time() }}"></script>
@endpush