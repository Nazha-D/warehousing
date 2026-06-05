<?php

namespace App\Listeners;

use App\Events\ItemStockChanged;
use App\Mail\LowStockNotificationMail;
use App\Models\Item;
use App\Models\Warehouse;
use App\Services\ItemService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class CheckStockLevel implements ShouldQueue
{

    protected  ItemService $itemService;
    /**
     * Create the event listener.
     */
    public function __construct(ItemService $itemService)
    {
        $this->itemService=$itemService;
    }

    /**
     * Handle the event.
     */
    public function handle(ItemStockChanged $event): void
    {
        $itemId=$event->itemId;
        $warehouseId=$event->warehouseId;
        $qty=$this->itemService->getAvailableStock($warehouseId,$itemId);
      //  $qty=$data['qtyOnHand'];
        $item=Item::find($itemId);
        $warehouse=Warehouse::find($warehouseId);
        $alertLevel=$item->alert_level;
        \Log::error('dddd'.$qty);
        if($qty<=$alertLevel) {
            \Log::error("المستودع رقم{$warehouseId} لديه نقص في المنتج رقم {$itemId}. الكمية الحالية: {$qty}");
            Mail::to('nezhadarbooly@outlook.com')->send(new LowStockNotificationMail($item, $qty, $warehouse));
       //just testing for my email , later it would be client's email
        }
        }
}
