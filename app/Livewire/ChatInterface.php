<?php

namespace App\Livewire;

use App\Events\MessageSent;
use App\Jobs\ProcessAIResponse;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\AIService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatInterface extends Component
{
    public $message = '';
    public $conversation;
    public $messages = [];
    public $isTyping = false;
    public $page = 1;
    public $hasMorePages = true;
    public $perPage = 50;
    public $search = '';
    public $searchResults = [];
    public $currentMatchIndex = -1; // -1 means no match

    // Optional: keep search in the URL so it survives page refresh
    protected $queryString = ['search'];

    public function mount($conversationId = null)
    {
        // $this->conversation = Conversation::firstOrCreate([
        //     'user_id' => Auth::id(),
        // ]);

        // allows guests with no auth:
        $this->conversation = Conversation::firstOrCreate(
            Auth::check() ? ['user_id' => Auth::id()] : ['user_id' => null]  // or use a session identifier
        );

        if ($conversationId) {
            $this->conversation = Conversation::findOrFail($conversationId);
        } else {
            // maybe create a new one or handle gracefully
            $this->conversation;
        }

        $this->loadMessages();
        $this->dispatch('scroll-to-bottom');
    }

    // A listener for conversation selection
    protected function getListeners()
    {
        return [
            'conversationSelected' => 'loadConversation',
        ];
    }

    public function loadConversation($conversationId)
    {
        $this->conversation = Conversation::findOrFail($conversationId);
        $this->page = 1;
        $this->search = '';
        $this->loadMessages();
        $this->dispatch('scroll-to-bottom');
    }

    public function loadMessages()
    {
        $query = $this->conversation
            ->messages()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->take($this->perPage * $this->page);

        // Apply search filter if there's a term
        if (!empty($this->search)) {
            $query->where('content', 'like', '%' . $this->search . '%');
        }

        $messages = $query->get()->reverse();

        if ($this->page === 1) {
            $this->messages = $messages->toArray();
        } else {
            // Prepend older messages
            $this->messages = array_merge($messages->toArray(), $this->messages);
        }

        // Check if there are more messages to load
        $total = $this->conversation->messages()
            ->when(!empty($this->search), fn($q) => $q->where('content', 'like', '%' . $this->search . '%'))
            ->count();
        $this->hasMorePages = ($this->page * $this->perPage) < $total;
    }

    public function loadMore()
    {
        if (!$this->hasMorePages) return;
        $this->page++;
        $this->loadMessages();
    }

    // When search changes, reset pagination and reload:
    public function updatedSearch()
    {
        $this->page = 1;

        if (empty($this->search)) {
            $this->searchResults = [];
            $this->currentMatchIndex = -1;
        } else {
            // Get all matching message IDs for this conversation, in chronological order
            $this->searchResults = $this->conversation->messages()
                ->where('content', 'like', '%' . $this->search . '%')
                ->orderBy('created_at')
                ->pluck('id')
                ->toArray();

            $this->currentMatchIndex = count($this->searchResults) > 0 ? 0 : -1;
        }

        $this->loadMessages();
    }

    public function nextMatch()
    {
        if ($this->currentMatchIndex < count($this->searchResults) - 1) {
            $this->currentMatchIndex++;
            $messageId = $this->searchResults[$this->currentMatchIndex];
            $this->dispatch('scroll-to-message', messageId: $messageId);
        }
    }

    public function prevMatch()
    {
        if ($this->currentMatchIndex > 0) {
            $this->currentMatchIndex--;
            $messageId = $this->searchResults[$this->currentMatchIndex];
            $this->dispatch('scroll-to-message', messageId: $messageId);
        }
    }

    public function sendMessage()
    {
        if (empty(trim($this->message))) {
            return;
        }

        // Create user message
        $userMessage = Message::create([
            'conversation_id' => $this->conversation->id,
            'user_id' => Auth::id(),
            'content' => $this->message,
            'is_ai' => false,
        ]);


        // Broadcast user message
        broadcast(new MessageSent($userMessage));

        // Clear input and show typing indicator
        $this->message = '';
        $this->isTyping = true;

        // Process AI response asynchronously
        $this->processAIResponse($userMessage->content);

        // Scroll to bottom after new message
        $this->dispatch('scroll-to-bottom');
    }

    #[On('echo:chat.{conversation.id},MessageSent')]
    public function messageReceived($event)
    {

        \Log::info('📨 Livewire received event', ['event' => $event]);

        $this->messages[] = $event;
        if ($event['is_ai'] ?? false) {
            $this->isTyping = false;
        }
        $this->dispatch('scroll-to-bottom');
    }

    public function render()
    {
        return view('livewire.chat-interface');
    }

    private function processAIResponse($userMessage)
    {
        ProcessAIResponse::dispatch($userMessage, $this->conversation);
    }

    //Typing Indicators and Auto-Scrolling
    public $showTypingIndicator = false;

    public function showTyping()
    {
        $this->showTypingIndicator = true;
        $this->dispatch('typing-started');
    }

    public function hideTyping()
    {
        $this->showTypingIndicator = false;
        $this->dispatch('typing-stopped');
    }

    #[On('echo:chat.{conversation.id},UserTyping')]
    public function userTyping($event)
    {
        if ($event['user_id'] !== auth()->id()) {
            $this->showTypingIndicator = true;
            // Hide after 3 seconds of inactivity
            $this->dispatch('hide-typing-after-delay');
        }
    }
}
