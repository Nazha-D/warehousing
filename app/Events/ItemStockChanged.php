<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemStockChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
public  $itemId;
public $warehouseId;
    /**
     * Create a new event instance.
     */
    public function __construct(int $itemId,int $warehouseId)
    {
        $this->itemId=$itemId;
        $this->warehouseId=$warehouseId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
