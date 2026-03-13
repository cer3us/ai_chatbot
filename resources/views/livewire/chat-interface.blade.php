<div class="flex flex-col h-screen max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
    <!-- Chat Header -->
    <div class="bg-blue-600 text-white pt-4 pb-2 px-4 flex items-center justify-between">

        <div class="flex flex-column">
            <div class="flex mb-2 items-center space-x-3">
                <!-- Avatar and Title -->
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z" />
                        <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold">AI Assistant</h3>
                    <p class="text-sm text-blue-200 m-0">Always here to help</p>
                </div>
            </div>
            <div class="flex justtify-start">
                <!-- Toggle Sidebar Button (visible on all screens) -->
                <button
                    @click="$dispatch('toggle-sidebar')"
                    class="p-2 bg-grey-700 hover:bg-blue-700 rounded-lg transition-colors mr-2"
                    title="Toggle conversation list">
                    <svg x-show="sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                    <svg x-show="!sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Typing indicator -->
        <div class="flex items-center space-x-2">
            @if($isTyping)
            <div class="flex space-x-1">
                <div class="w-2 h-2 bg-white rounded-full animate-bounce"></div>
                <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
            </div>
            <span class="text-sm">AI is typing...</span>
            @endif
        </div>
    </div>

    <!-- Search Bar -->
    <div class="p-2 bg-white border-b flex items-center gap-2">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search in conversation..."
            class="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        @if(!empty($searchResults))
        <span class="text-sm text-gray-600">
            {{ $currentMatchIndex + 1 }} of {{ count($searchResults) }}
        </span>
        <button
            wire:click="prevMatch"
            @disabled($currentMatchIndex <=0)
            class="px-2 py-1 border rounded disabled:opacity-50">↑</button>
        <button
            wire:click="nextMatch"
            @disabled($currentMatchIndex>= count($searchResults) - 1)
            class="px-2 py-1 border rounded disabled:opacity-50"
            >↓</button>
        @endif
    </div>

    <!-- Messages Container -->
    <!-- x-data : lazy scroll for load more messages -->
    <!-- scroll-to-message: scroll to the found match -->
    <div
        x-data="{
        scrollContainer: null,
        init() {
            this.scrollContainer = this.$el;
            this.scrollContainer.addEventListener('scroll', () => {
                if (this.scrollContainer.scrollTop < 100 && !$wire.hasMorePages) {
                    $wire.loadMore();
                }
            });
        }
    }"
        x-on:scroll-to-message.window="setTimeout(() => {
        document.getElementById('message-' + $event.detail.messageId)?.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }, 100)"
        class="flex-1 overflow-y-auto p-5 space-y-4"
        id="messages-container">

        @if($hasMorePages)
        <div class="mx-auto w-64 text-center bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
            <button wire:click="loadMore" class="text-sm hover:underline">Load older messages</button>
        </div>
        @endif

        @php
        function highlight($text, $search) {
        if (empty($search)) return e($text);
        return preg_replace('/(' . preg_quote($search, '/') . ')/iu', '<mark>$1</mark>', e($text));
        }
        @endphp

        @forelse($messages as $msg)
        <div
            class="flex flex-col {{ $msg['is_ai'] ? 'items-start' : 'items-end' }} mb-4"
            id="message-{{ $msg['id'] }}">
            <div class="w-fit max-w-[85%] sm:max-w-md md:max-w-lg px-4 py-2 rounded-lg {{ $msg['is_ai'] ? 'bg-gray-200 text-gray-800' : 'bg-emerald-600 text-white' }}">
                <p class="text-sm break-words whitespace-pre-wrap">{!! highlight($msg['content'], $search) !!}</p>
                <p class="text-xs mt-1 opacity-70 text-right">
                    {{ \Carbon\Carbon::parse($msg['created_at'])->format('d-m-Y H:i') }}
                </p>
            </div>
        </div>
        @empty
        <div class="text-center text-gray-500 py-8">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
            </svg>
            <p class="text-lg font-medium">Start a conversation</p>
            <p class="text-sm">Send a message to begin chatting with the AI assistant</p>
        </div>
        @endforelse

    </div>

    <!-- Message Input -->
    <div class="border-t bg-gray-50 p-4">
        <form wire:submit="sendMessage" class="flex space-x-2">
            <input
                type="text"
                wire:model="message"
                placeholder="Type your message..."
                class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                maxlength="1000">
            <button
                type="submit"
                class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                wire:loading.attr="disabled">
                <span wire:loading.remove>Send</span>
                <span wire:loading>
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('scroll-to-bottom', () => {
            const container = document.getElementById('messages-container');
            container.scrollTop = container.scrollHeight;
        });
    });
</script>