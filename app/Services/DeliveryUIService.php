<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\Delivery;
use App\Constants\DeliveryConstants;

class DeliveryUIService
{
    protected DeliveryService $deliveryService;

    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    /**
     * تحضير البيانات للواجهة لإنشاء Delivery
     */
    public function getDeliveryDataForClient(int $companyId, int $clientId): array
    {
        // توليد رقم Delivery جديد باستخدام دالة السيرفيس الموجودة
//        $deliveryNumber = $this->deliveryService->generateDeliveryNumber($companyId);

        // جلب Sales Orders الخاصة بالزبون للحالة processing أو partially_delivered
        $salesOrders = SalesOrder::with(['lines.item'])
            ->where('client_id', $clientId)
            ->whereIn('status', ['processing', 'partially_delivered'])
            ->get()
            ->map(function ($so) {
                // لكل خط في SO، نحسب الكمية المتبقية للتسليم
                $so->lines->transform(function ($line) {
                    $deliveredQty = $line->stockReservations()
                        ->where('status', 'consumed')
                        ->sum('reserved_quantity');

                    $line->remaining_qty = max($line->quantity - $deliveredQty, 0);

                    return $line;
                });

                return $so;
            });

        return [

            'sales_orders'    => $salesOrders,
        ];
    }
}
