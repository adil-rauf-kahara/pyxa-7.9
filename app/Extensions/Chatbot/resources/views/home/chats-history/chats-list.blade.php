<ul class="lqd-ext-chatbot-history-list flex flex-col gap-2">
    <template
        x-for="(chatItem, index) in chatsList"
        x-show="chatsList.length"
    >
        <li
            class="lqd-ext-chatbot-history-list-item group/chat-item relative rounded-xl px-5 py-3.5 text-heading-foreground transition-colors hover:bg-heading-foreground/5 [&.lqd-active]:bg-heading-foreground/5"
            :class="{ 'lqd-active': index === 0, }"
        >
            <div class="flex items-center gap-3">
                <svg
                    class="shrink-0"
                    width="25"
                    height="24"
                    viewBox="0 0 25 24"
                    fill="currentColor"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M12.5 0C5.86792 0 0.5 5.367 0.5 12C0.5 14.1048 1.04895 16.1605 2.09137 17.9736L0.545227 22.775C0.437378 23.1097 0.526001 23.4767 0.774658 23.7253C1.02093 23.9716 1.38696 24.0635 1.72497 23.9548L6.52636 22.4086C8.33947 23.451 10.3952 24 12.5 24C19.1321 24 24.5 18.633 24.5 12C24.5 5.36792 19.133 0 12.5 0ZM12.5 22.125C10.5988 22.125 8.7456 21.5945 7.14068 20.5909C6.90979 20.4466 6.62304 20.4076 6.35626 20.4935L2.89026 21.6097L4.00647 18.1437C4.09106 17.8808 4.05536 17.5937 3.90887 17.3593C2.90546 15.7544 2.375 13.9012 2.375 12C2.375 6.41711 6.91711 1.875 12.5 1.875C18.0829 1.875 22.625 6.41711 22.625 12C22.625 17.5829 18.0829 22.125 12.5 22.125ZM13.6719 12C13.6719 12.6471 13.1473 13.1719 12.5 13.1719C11.8527 13.1719 11.3281 12.6471 11.3281 12C11.3281 11.3527 11.8527 10.8281 12.5 10.8281C13.1473 10.8281 13.6719 11.3527 13.6719 12ZM18.3594 12C18.3594 12.6471 17.8348 13.1719 17.1875 13.1719C16.5402 13.1719 16.0156 12.6471 16.0156 12C16.0156 11.3527 16.5402 10.8281 17.1875 10.8281C17.8348 10.8281 18.3594 11.3527 18.3594 12ZM8.98437 12C8.98437 12.6471 8.45977 13.1719 7.8125 13.1719C7.1654 13.1719 6.64062 12.6471 6.64062 12C6.64062 11.3527 7.1654 10.8281 7.8125 10.8281C8.45977 10.8281 8.98437 11.3527 8.98437 12Z"
                    />
                </svg>
                <div class="grow">
                    <p
                        class="mb-0.5 text-xs"
                        x-text="chatItem.lastMessage?.message ? chatItem.lastMessage?.message :  '{{ __('Chat history item') }}'"
                        :class="{ 'font-semibold text-heading-foreground': !chatItem.lastMessage?.read_at }"
                        :class="{ 'text-heading-foreground/30': chatItem.lastMessage?.read_at ? true : false }"
                    ></p>
                    <p
                        class="mb-0 text-xs text-heading-foreground/30"
                        x-text="`${new Date(chatItem?.lastMessage?.created_at || chatItem.created_at).toLocaleString()} - ${chatItem.chatbot.title}`"
                    ></p>
                </div>

                <x-tabler-chevron-right
                    class="invisible ms-auto size-4 shrink-0 -translate-x-1 opacity-0 transition-all group-hover/chat-item:visible group-hover/chat-item:translate-x-0 group-hover/chat-item:opacity-100 group-[&.lqd-active]/chat-item:visible group-[&.lqd-active]/chat-item:translate-x-0 group-[&.lqd-active]/chat-item:opacity-100"
                />
            </div>
            <a
                class="lqd-ext-chatbot-history-list-item-trigger absolute start-0 top-0 inline-block h-full w-full"
                :data-id="chatItem.id"
                href="#"
                title="{{ __('Open Chat History') }}"
                @click.prevent="(e) => { setActiveChat(e); mobileDropdownOpen = false }"
            ></a>
        </li>
    </template>
    <template x-if="!chatsList.length">
        <p class="mb-0.5 font-semibold text-heading-foreground">
            {{ __('No chat history found.') }}
        </p>
    </template>
</ul>
