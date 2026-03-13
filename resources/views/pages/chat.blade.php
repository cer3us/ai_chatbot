@extends('layouts.app')

@section('content')
<div
    x-data="{ sidebarOpen: false }"
    @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
    class="flex h-screen">

    <!-- Conversation List (Sidebar) -->
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-transform duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="w-64 bg-gray-50 border-r">
        <livewire:conversation-list />
    </div>

    <!-- Main Chat Area -->
    <div class="flex-1 transition-all duration-300" :class="{ 'ml-0': !sidebarOpen }">
        <livewire:chat-interface :conversation-id="$selectedConversationId ?? null" />
    </div>
</div>
@endsection