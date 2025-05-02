@php
    $generatedContent = cache('generated_content');
    $all = $generatedContent->count();
    $all = $all == 0 ? 1 : $all;

    $textPercent = round(
        ($generatedContent->filter(fn($entity) => $entity->generator?->type == 'text')->count() / $all) * 100,
        0,
    );
    $ImagePercent = round(
        ($generatedContent->filter(fn($entity) => $entity->generator?->type == 'image')->count() / $all) * 100,
        0,
    );
    $audioPercent = round(
        ($generatedContent->filter(fn($entity) => $entity->generator?->type == 'audio')->count() / $all) * 100,
        0,
    );
    $videoPercent = round(
        ($generatedContent->filter(fn($entity) => $entity->generator?->type == 'video')->count() / $all) * 100,
        0,
    );
    $codePercent = round(
        ($generatedContent->filter(fn($entity) => $entity->generator?->type == 'code')->count() / $all) * 100,
        0,
    );
@endphp
<x-card
    class="flex flex-col"
    class:body="flex flex-col justify-center grow"
    id="{{ 'admin-card-' . ($widget?->name?->value ?? 'generated-content') }}"
>
    <x-slot:head
        class="flex items-center justify-between px-5 py-3.5"
    >
        <div class="flex items-center gap-4">
            <x-lqd-icon class="bg-background text-heading-foreground dark:bg-foreground/5">
                <x-tabler-clipboard-text
                    class="size-6"
                    stroke-width="1.5"
                />
            </x-lqd-icon>
            <h4 class="m-0 flex items-center gap-1 text-base font-medium">
                {{ __('Generated Content') }}
                <x-info-tooltip text="{{ __('Track how much and what kind of content users are creating.') }}" />
            </h4>
        </div>
    </x-slot:head>

    <div class="flex flex-col gap-4">
        <div class="flex w-full rounded-7xl border p-2.5">
            <div class="flex h-3 w-full flex-nowrap gap-0.5 overflow-hidden rounded-7xl">
                @if ($textPercent != 0)
                    <span
                        class="bg-[#3C82F6]"
                        style="width: {{ $textPercent }}%"
                    ></span>
                @endif
                @if ($ImagePercent != 0)
                    <span
                        class="bg-[#9F77F8]"
                        style="width: {{ $ImagePercent }}%"
                    ></span>
                @endif
                @if ($audioPercent != 0)
                    <span
                        class="bg-[#60A5FA]"
                        style="width: {{ $audioPercent }}%"
                    ></span>
                @endif
                @if ($videoPercent != 0)
                    <span
                        class="bg-[#20C69F]"
                        style="width: {{ $videoPercent }}%"
                    ></span>
                @endif
                @if ($codePercent != 0)
                    <span
                        class="bg-[#E0B43E]"
                        style="width: {{ $codePercent }}%"
                    ></span>
                @endif
            </div>
        </div>
        <ul class="flex flex-col gap-2.5">
            <li class="flex items-center justify-between border-b border-card-border py-2">
                <div class="flex items-center gap-2.5">
                    <span class="size-2.5 rounded-sm bg-[#3C82F6]"></span>
                    <p class="mb-0 text-[15px] font-medium text-foreground">{{ __('Text') }}</p>
                </div>
                <span class="text-center text-foreground/50">{{ $textPercent }}%</span>
            </li>
            <li class="flex items-center justify-between border-b border-card-border py-2">
                <div class="flex items-center gap-2.5">
                    <span class="size-2.5 rounded-sm bg-[#9F77F8]"></span>
                    <p class="mb-0 text-[15px] font-medium text-foreground">{{ __('Image') }}</p>
                </div>
                <span class="text-center text-foreground/50">{{ $ImagePercent }}%</span>
            </li>
            <li class="flex items-center justify-between border-b border-card-border py-2">
                <div class="flex items-center gap-2.5">
                    <span class="size-2.5 rounded-sm bg-[#60A5FA]"></span>
                    <p class="mb-0 text-[15px] font-medium text-foreground">{{ __('Audio') }}</p>
                </div>
                <span class="text-center text-foreground/50">{{ $audioPercent }}%</span>
            </li>
            <li class="flex items-center justify-between border-b border-card-border py-2">
                <div class="flex items-center gap-2.5">
                    <span class="size-2.5 rounded-sm bg-[#20C69F]"></span>
                    <p class="mb-0 text-[15px] font-medium text-foreground">{{ __('Video') }}</p>
                </div>
                <span class="text-center text-foreground/50">{{ $videoPercent }}%</span>
            </li>
            <li class="flex items-center justify-between border-b border-card-border py-2">
                <div class="flex items-center gap-2.5">
                    <span class="size-2.5 rounded-sm bg-[#E0B43E]"></span>
                    <p class="mb-0 text-[15px] font-medium text-foreground">{{ __('Code') }}</p>
                </div>
                <span class="text-center text-foreground/50">{{ $codePercent }}%</span>
            </li>
        </ul>
    </div>
</x-card>
