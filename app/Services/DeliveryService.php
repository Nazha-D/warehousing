<?php

namespace App\Services;

use App\Constants\DeliveryConstants;
use App\Models\Delivery;
use App\Models\DeliveryLine;
use App\Models\SalesOrderLine;
use App\Models\StockReservation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryService
{
    public static function getAll($userCompanyId, $options = [])
    {
        $perPageDefault = 10;
        $isPaginated = true;
        $searchDefault = '';
        $exceptStatusDefault='';
        $statusDefault='';
        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $search = $options['search'] ?? $searchDefault;
        $exceptStatus=$options['exceptStatus'] ?? $exceptStatusDefault;
        $status=$options['status'] ?? $statusDefault;
        $deliveries= Delivery::query();

        if($exceptStatus!='')
        {$deliveries->whereNot('status', $exceptStatus);}

        if($status)
        {
            $deliveries->where('status', $status);
        }


            $deliveries->where('company_id', $userCompanyId);


        $deliveries->filter($search);



        if ($isPaginated) {
            $deliveries =  $deliveries->paginate($perPage);
        } else {
            $deliveries =  $deliveries->get();
        }

        return $deliveries;
    }
    /**
     * إنشاء Delivery من مجموعة Reservations مختارة
     * لا يتم خصم مخزون هنا
     */
    public function createFromSalesOrderLines(
        int $companyId,
        int $clientId,
        array $lines, // sales_order_line_id + qty
        array $meta = []
    ): Delivery {

        return DB::transaction(function () use ($companyId, $clientId, $lines, $meta) {

            if (empty($lines)) {
                throw ValidationException::withMessages([
                    'lines' => 'No lines provided.'
                ]);
            }

            // جلب كل SalesOrderLines
            $soLineIds = collect($lines)->pluck('sales_order_line_id')->toArray();

            $soLines = SalesOrderLine::with('salesOrder')
                ->whereIn('id', $soLineIds)
                ->get()
                ->keyBy('id');

            if ($soLines->count() !== count($soLineIds)) {
                throw ValidationException::withMessages([
                    'lines' => 'Invalid sales order lines.'
                ]);
            }

            // تأكد أن كل الخطوط لنفس العميل
            foreach ($soLines as $line) {
                if ($line->salesOrder->client_id !== $clientId) {
                    throw ValidationException::withMessages([
                        'lines' => 'Lines belong to different client.'
                    ]);
                }
            }

            // جلب الـ Reservations المرتبطة بهالخطوط
            $reservations = StockReservation::where('source_type', SalesOrderLine::class)
                ->whereIn('source_id', $soLineIds)
                ->active()
                ->get()
                ->keyBy('source_id');

            // إنشاء Delivery
            $delivery = Delivery::create([
                'company_id'        => $companyId,
                'client_id'         => $clientId,
                'driver_id'         => $meta['driver_id'] ?? null,
                'sales_invoice_id'  => null,
                'delivery_number'   => $this->generateDeliveryNumber($companyId),
                'reference'         => $meta['reference'] ?? null,
                'date'              => $meta['date'] ?? now()->toDateString(),
                'expected_delivery' => $meta['expected_delivery'] ?? null,
                'total'             => 0,
                'status'            => 'processing',
            ]);

            $total = 0;

            foreach ($lines as $input) {

                $soLine = $soLines[$input['sales_order_line_id']];
                $reservation = $reservations[$soLine->id] ?? null;

                if (!$reservation) {
                    throw ValidationException::withMessages([
                        'reservation' => 'No active reservation for this line.'
                    ]);
                }

                $qty = $input['qty'];

                if ($qty <= 0) {
                    throw ValidationException::withMessages([
                        'qty' => 'Quantity must be greater than zero.'
                    ]);
                }

                if ($qty > $reservation->reserved_quantity) {
                    throw ValidationException::withMessages([
                        'qty' => 'Quantity exceeds reserved quantity.'
                    ]);
                }

                $unitPrice = $soLine->unit_price ?? 0;
                $lineTotal = $qty * $unitPrice;
                $total += $lineTotal;

                // ✅ إنشاء DeliveryLine كسطر Snapshot حقيقي
                DeliveryLine::create([
                    'delivery_id'          => $delivery->id,
                    'stock_reservation_id' => $reservation->id,

                    // ---------- Operational (مهم للستوك لاحقًا) ----------
                    'item_id'      => $reservation->item_id,
                    'warehouse_id' => $reservation->warehouse_id,

                    // ---------- Snapshot Identity ----------
                    'line_type_id' => $soLine->line_type_id,
                    'combo_id'  => $soLine->combo_id,

                    // ---------- Snapshot Data ----------
                    'note'          => $soLine->note,
                    'description'    => $soLine->description,
                    'image'          => $soLine->image,
                    'unit_price'     => $soLine->unit_price,
                    'total'     => $soLine->total,
                    'warehouse_name' => $soLine->warehouse_name,
                    'package_name'   => $soLine->package_name,

                    // ---------- Quantities ----------
                    'qty' => $qty,
                    'invoiced_qty' => 0,
                ]);

                // ⭐ إنقاص الـ reservation
//            $reservation->decrement('reserved_quantity', $qty);
            }

            $delivery->update(['total' => $total]);

            return $delivery;
        });
    }

    public function generateDeliveryNumber($companyId)
    {
        $currentYear = date('y');
        $latestDelivery = Delivery::where('company_id', $companyId)
            ->withTrashed()
            ->whereNotNull('delivery_number')
            ->where('delivery_number', 'like', DeliveryConstants::NUMBER_PREFIX . $currentYear . '%') // Filter by current year
            ->latest('delivery_number') // Order by delivery number
            ->first();

        if ($latestDelivery) {
            //   $lastNumber = (int)substr($latestDelivery->sales_invoice_number, strlen(DeliveryConstants::NUMBER_PREFIX . $currentYear));
            $lastNumber = (int)substr($latestDelivery->delivery_number, strlen(DeliveryConstants::NUMBER_PREFIX . $currentYear));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1; // Start at 1 if no invoices exist for the year
        }

        return DeliveryConstants::NUMBER_PREFIX
            . $currentYear
            . str_pad($newNumber, DeliveryConstants::NUMBER_MIN_LENGTH, DeliveryConstants::NUMBER_PAD_STR, STR_PAD_LEFT);
    }
    /**
     * تحويل Delivery من Processing إلى Delivered
     * هنا يتم خصم المخزون فعليًا
     */
    public function markAsDelivered(Delivery $delivery): void
    {
        if ($delivery->status !== 'processing') {
            throw new \Exception("Only deliveries with 'processing' status can be marked as delivered.");
        }

        DB::transaction(function () use ($delivery) {

            $totalStockDeducted = 0;

            foreach ($delivery->deliveryLines as $line) {

                $reservation = $line->reservation;

                // خصم الكمية من الـ reservation
                $reservation->update([
                    'status' => StockReservation::STATUS_CONSUMED,
                ]);

                // إنشاء سجل حركة المخزون (StockMovement)
                $line->warehouse->stockMovements()->create([
                    'company_id'   => $delivery->company_id,
                    'item_id'      => $line->item_id,
                    'quantity'     => -1 * $line->qty,
                    'type'         => 'delivery_out', // ثابت لتوثيق خروج مخزون
                    'reference_id' => $delivery->id,
                    'reference_type' => Delivery::class,
                ]);

                $totalStockDeducted += $line->qty;
            }

            // تحديث حالة الـ Delivery
            $delivery->update([
                'status' => 'delivered',
                'total'  => $delivery->total, // بالفعل محسوب عند الإنشاء
            ]);
            app(\App\Services\SalesOrderDeliverySynchronizer::class)
                ->syncFromDelivery($delivery);
        });
    }

    /**
     * رفع POD وتحويل الحالة إلى Completed
     */
    public function markAsCompleted(Delivery $delivery, string $podPath): void
    {
        if ($delivery->status !== 'delivered') {
            throw new \Exception("Only deliveries with 'delivered' status can be marked as completed.");
        }

        // Transaction لضمان atomic operation
        DB::transaction(function () use ($delivery, $podPath) {

            // تخزين مسار الـ POD
            $delivery->update([
                'status' => 'completed',
                'pod_file_path' => $podPath,
            ]);

            // هنا ممكن إضافة event لاحقاً لإعلام المحاسبة أو النظام بأن الـ Delivery اكتملت
        });
    }


    /**
     * فشل التوصيل أو رفض الزبون
     * يتم إعادة الكميات إلى Reservations
     */
    public function failOrReject(Delivery $delivery, string $reason): void
    {
        $allowedStatuses = ['processing', 'delivered'];
        if (!in_array($delivery->status, $allowedStatuses)) {
            throw new \Exception("Only deliveries with 'processing' or 'delivered' status can be failed or rejected.");
        }

        DB::transaction(function () use ($delivery, $reason) {

            foreach ($delivery->deliveryLines as $line) {

                $reservation = $line->reservation;

                // إعادة حالة الـ reservation إلى active
                $reservation->update([
                    'status' => StockReservation::STATUS_ACTIVE,
                ]);

                // إذا كان المخزون خصم بالفعل (status delivered)
                if ($delivery->status === 'delivered') {
                    // إضافة حركة مخزون IN
                    $line->warehouse->stockMovements()->create([
                        'company_id'    => $delivery->company_id,
                        'item_id'       => $line->item_id,
                        'quantity'      => $line->qty,
                        'type'          => 'delivery_return', // لتوثيق إعادة المخزون
                        'reference_id'  => $delivery->id,
                        'reference_type'=> Delivery::class,
                    ]);
                }
            }

            // تحديث حالة الـ Delivery حسب السبب
            $delivery->update([
                'status' => 'Failed', // 'delivery_failed' أو 'rejected'
                 'reason'=>$reason
            ]);
            app(\App\Services\SalesOrderDeliverySynchronizer::class)
                ->syncFromDelivery($delivery);
        });
    }

    /**
     * إلغاء Delivery وهو ما زال Processing
     * بدون أي أثر مخزني
     */
    public function cancel(Delivery $delivery,$reason): void
    {
        if ($delivery->status !== 'processing') {
            throw new \Exception("Only deliveries with 'processing' status can be canceled.");
        }

        DB::transaction(function () use ($delivery,$reason) {

            // تحديث الحالة بدل الحذف النهائي (soft delete)
            $delivery->update([
                'status' => 'canceled',
                'reason'=>$reason
            ]);

            // إذا بدنا، ممكن نعمل soft delete على الـ DeliveryLines
            $delivery->deliveryLines()->delete();
        });
    }

}
