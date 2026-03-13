<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The message instance.
     */
    public Message $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Use a PresenceChannel so only users in the conversation receive the message
        return [
            new Channel('chat.' . $this->message->conversation_id),
        ];
    }

    /**
     * The data to broadcast (optional – by default all public properties are sent).
     */
    public function broadcastWith(): array
    {
        return [
            'id'         => $this->message->id,
            'content'    => $this->message->content,
            'user_id'    => $this->message->user_id,
            'is_ai'      => $this->message->is_ai,
            'created_at' => $this->message->created_at->toISOString(),
            'user'       => $this->message->user ? ['name' => $this->message->user->name] : null,

        ];
    }
}