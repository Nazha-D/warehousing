<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\DeliveryLine;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;

class SalesOrderDeliverySynchronizer
{
    public function syncFromDelivery(Delivery $delivery): void
    {
        DB::transaction(function () use ($delivery) {

            // كل الـ Sales Orders المتأثرة بهذا الـ Delivery
            $salesOrders = $delivery->deliveryLines
                ->map(fn ($line) => $line->reservation->source->salesOrder)
                ->unique('id');

            foreach ($salesOrders as $salesOrder) {
                $this->recalculateSalesOrderStatus($salesOrder);
            }
        });
    }
//
//    private function recalculateSalesOrderStatus(SalesOrder $salesOrder): void
//    {
//        $lines = $salesOrder->lines;
//
//        $allDelivered = true;
//        $anyDelivered = false;
//
//        foreach ($lines as $line) {
//
//            $deliveredQty = StockReservation::where('source_type', get_class($line))
//                ->where('source_id', $line->id)
//                ->where('status', StockReservation::STATUS_CONSUMED)
//                ->sum('reserved_quantity');
//
//            if ($deliveredQty > 0) {
//                $anyDelivered = true;
//            }
//
//            if ($deliveredQty < $line->quantity) {
//                $allDelivered = false;
//            }
//        }
//
//        $newStatus = match (true) {
//        $allDelivered => 'completed',
//            $anyDelivered => 'partially_delivered',
//            default => 'processing',
//        };
//
//        if ($salesOrder->status !== $newStatus) {
//            $salesOrder->update(['status' => $newStatus]);
//        }
//    }
    private function recalculateSalesOrderStatus(SalesOrder $salesOrder): void
    {
        $lines = $salesOrder->lines;

        $allDelivered = true;
        $anyDelivered = false;

        foreach ($lines as $line) {

            $deliveredQty = DeliveryLine::whereHas('delivery', function ($q) {
                $q->whereIn('status', ['delivered', 'completed']);
            })
                ->whereHas('reservation', function ($q) use ($line) {
                    $q->where('source_type', SalesOrderLine::class)
                        ->where('source_id', $line->id);
                })
                ->sum('qty');

            if ($deliveredQty > 0) {
                $anyDelivered = true;
            }

            if ($deliveredQty < $line->quantity) {
                $allDelivered = false;
            }
        }

        $newStatus = match (true) {
        $allDelivered => 'completed',
        $anyDelivered => 'partially_delivered',
        default => 'processing',
    };

    if ($salesOrder->status !== $newStatus) {
        $salesOrder->update(['status' => $newStatus]);
    }
}

}
