<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class LiveFutebol implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $valor;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($valor)
    {
      $this->valor = $valor;
    }


    public function broadcastOn()
    {
        return new Channel('live-futebol-live');
    }

    public function broadcastWith()
    {
        return $this->valor;
    }
}
