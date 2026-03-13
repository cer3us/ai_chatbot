<?php

namespace App\Livewire;


use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ConversationList extends Component
{
    public $conversations;
    public $selectedConversationId;

    protected $listeners = ['conversationCreated' => 'refreshList', 'conversationDeleted' => 'refreshList'];

    public function mount()
    {
        $this->loadConversations();
    }

    public function loadConversations()
    {
        $this->conversations = Conversation::where(
            Auth::check() ? ['user_id' => Auth::id()] : ['user_id' => null]
        )
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function selectConversation($id)
    {
        $this->selectedConversationId = $id;
        $this->dispatch('conversationSelected', conversationId: $id)->to('chat-interface');
    }

    public function createConversation()
    {
        $conversation = Conversation::create([
            'user_id' => Auth::check() ? Auth::id() : null,
            'title' => 'New Chat',
        ]);
        $this->dispatch('conversationCreated');
        $this->selectConversation($conversation->id);
    }

    public function deleteConversation($id)
    {
        $user_id = Auth::id(); // null if guest
        $conversation = Conversation::find($id);
        if ($conversation && $conversation->user_id === $user_id) {
            $conversation->delete();
            $this->dispatch('conversationDeleted');
            if ($this->selectedConversationId === $id) {
                // select another conversation or clear
                $first = Conversation::where('user_id', $user_id)->first();
                $this->selectConversation($first?->id);
            }
        }
    }

    public function refreshList()
    {
        $this->loadConversations();
    }

    public function render()
    {
        return view('livewire.conversation-list');
    }
}
