<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct()
    {
        //
    }

    /**
     * Broadcast di Public Channel 'admin-orders'
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('admin-orders'),
        ];
    }

    /**
     * Nama Event khusus untuk dipanggil di Laravel Echo JS
     */
    public function broadcastAs(): string
    {
        return 'OrderUpdated';
    }

    /**
     * Payload ringan untuk pemicu frontend reload
     */
    public function broadcastWith(): array
    {
        return [
            'updated' => true,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}