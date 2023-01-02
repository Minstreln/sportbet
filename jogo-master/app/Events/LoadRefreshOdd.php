<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class LoadRefreshOdd implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $match;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($match)
    {
        $this->match = $match;

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('refreshmatch');
    }

    public function broadcastWith()
    {
        return $this->match;
    }
}
