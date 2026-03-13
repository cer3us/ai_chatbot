<div class="border-r h-full bg-gray-50 w-64 flex flex-col">
    <div class="p-2">
        <button wire:click="createConversation" class="w-full bg-emerald-600 text-white py-2 px-4 my-5 rounded-lg hover:bg-emerald-700">
            + New Chat
        </button>
    </div>
    <div class="flex-1 overflow-y-auto">
        @foreach($conversations as $conv)
        <div
            wire:key="conv-{{ $conv->id }}"
            wire:click="selectConversation({{ $conv->id }})"
            class="p-3 cursor-pointer hover:bg-gray-200 {{ $selectedConversationId === $conv->id ? 'bg-blue-100' : '' }} flex justify-between items-center">
            <span class="truncate">{{ $conv->title ?? 'Chat ' . $conv->id }}</span>
            <button wire:click.stop="deleteConversation({{ $conv->id }})" class="text-red-500 hover:text-red-700 text-sm">×</button>
        </div>
        @endforeach
    </div>
</div>