<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MenuUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;

    public mixed $produkData;

    /**
     * Create a new event instance.
     */
    public function __construct(string $action = 'updated', mixed $produkData = null)
    {
        $this->action = $action;
        $this->produkData = $produkData;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('menu-updates'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'menu.updated';
    }
}
