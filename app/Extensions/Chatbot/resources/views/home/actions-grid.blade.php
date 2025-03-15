{{-- Actions Grid --}}
<div class="grid grid-cols-1 gap-5 lg:grid-cols-2 lg:gap-11">
    {{-- Add new chatbot card --}}
    <x-card
        class:body="lg:p-16 p-9"
        class="text-center"
    >
        <figure class="size-40 mx-auto mb-6 inline-grid place-items-center rounded-full bg-heading-foreground/[3%]">
            <img
                src="{{ asset('vendor/chatbot/images/chatbot-create.png') }}"
                alt="Add New Chatbot"
            >
        </figure>
        <p class="mx-auto mb-6 max-w-[370px] font-heading text-xl font-semibold leading-[1.3em] text-heading-foreground">
            @lang('Create and configure a chatbot that interacts with your users.')
        </p>
        <x-button
            @click.prevent="setActiveChatbot('new_chatbot', 1, true);"
            variant="ghost-shadow"
            href="#"
        >
            <x-tabler-plus class="size-4" />
            @lang('Add New Chatbot')
        </x-button>
    </x-card>

    {{-- Show history card --}}
    <x-card
        class:body="lg:p-16 p-9"
        class="text-center"
    >
        <figure class="size-40 mx-auto mb-6 inline-grid place-items-center rounded-full bg-heading-foreground/[3%]">
            <img
                src="{{ asset('vendor/chatbot/images/chatbot-history.png') }}"
                alt="View Chat History"
            >
        </figure>
        <p class="mx-auto mb-6 max-w-[370px] font-heading text-xl font-semibold leading-[1.3em] text-heading-foreground">
            @lang('Explore recent conversations from your users.')
        </p>
        <x-button
            variant="ghost-shadow"
            href="#"
            @click.prevent="$store.externalChatbotHistory.setOpen(true)"
        >
            <x-tabler-plus class="size-4" />
            @lang('View Chat History')
        </x-button>
    </x-card>
</div>
