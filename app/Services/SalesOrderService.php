<?php

namespace App\Services;

use App\Constants\SalesOrderConstants;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Services\StockReservationService;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Enums\SalesOrderStatusEnum;
class SalesOrderService
{
    protected StockReservationService $stockReservationService;

    public function __construct(StockReservationService $stockReservationService)
    {
        $this->stockReservationService = $stockReservationService;
    }
    public  function getAll( $userCompanyId, $options = [])
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
        $status=$options['status']??$statusDefault;
        $salesOrders = SalesOrder::query();

        if($exceptStatus!='')
        {
            $salesOrders->whereNot('status', $exceptStatus);
        }
        if($status)
        {
            $salesOrders->where('status', $status);
        }

            $salesOrders->where('company_id', $userCompanyId);


        $salesOrders->filter($search);



        if ($isPaginated) {
            $salesOrders =  $salesOrders->paginate($perPage);
        } else {
            $salesOrders =  $salesOrders->get();
        }

        return $salesOrders;
    }
    public  function generateNumber($companyId)
    {
        $currentYear = date('y');
        $prefixLength = strlen(SalesOrderConstants::NUMBER_PREFIX); // e.g., 2 for "SO"
        $yearLength = 2;

        $latestOrder = SalesOrder::where('company_id', $companyId)
            ->whereNotNull('sales_order_number')
            ->whereRaw("SUBSTRING(sales_order_number, {$prefixLength} + 1, {$yearLength}) = ?", [$currentYear])
            ->latest()
            ->first();

        if ($latestOrder) {
            // Extract numeric part AFTER the prefix and year
            $numberPart = substr($latestOrder->sales_order_number, $prefixLength + $yearLength);
            $lastNumber = (int)$numberPart;
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return SalesOrderConstants::NUMBER_PREFIX
            . $currentYear
            . str_pad($newNumber, SalesOrderConstants::NUMBER_MIN_LENGTH, SalesOrderConstants::NUMBER_PAD_STR, STR_PAD_LEFT);

    }
    public function create(array $data): SalesOrder
    {
        return DB::transaction(function () use ($data) {

            $companyId = auth()->user()->company_id;
            $user = auth()->user();

            // 1) Create Sales Order
            $order = SalesOrder::create([
                'company_id' => $companyId,
                'quotation_id' => $data['quotation_id'] ?? null,
                'client_id' => $data['client_id'],
                'currency_id' => $data['currency_id'],
                'price_list_id' => $data['price_list_id'] ?? null,
                'payment_term_id' => $data['payment_term_id'] ?? null,
                'salesperson_id' => $data['salesperson_id'] ?? null,
                'commission_method_id' => $data['commission_method_id'] ?? null,
                'cashing_method_id' => $data['cashing_method_id'] ?? null,
                'company_header_id' => $data['company_header_id'] ?? null,

                'sales_order_number' => self::generateNumber($companyId),
                'reference' => $data['reference'] ?? null,
                'code' => $data['code'] ?? null,
                'title' => $data['title'] ?? null,

                'input_date' => $data['input_date'] ?? now(),
                'validity' => $data['validity'] ?? null,

                'special_discount' => $data['special_discount'] ?? 0,
                'special_discount_amount' => $data['special_discount_amount'] ?? 0,
                'global_discount' => $data['global_discount'] ?? 0,
                'global_discount_amount' => $data['global_discount_amount'] ?? 0,

                'vat' => $data['vat'] ?? 0,
                'vat_lebanese' => $data['vat_lebanese'] ?? 0,
                'vat_exempt' => $data['vat_exempt'] ?? false,
                'vat_inclusive_prices' => $data['vat_inclusive_prices'] ?? false,
                'before_vat_prices' => $data['before_vat_prices'] ?? false,

                'total_before_vat' => $data['total_before_vat'] ?? 0,
                'total' => $data['total'] ?? 0,

                'not_printed' => $data['not_printed'] ?? false,
                'printed_as_vat_exempt' => $data['printed_as_vat_exempt'] ?? false,
                'printed_as_percentage' => $data['printed_as_percentage'] ?? false,

                'status' => SalesOrderStatusEnum::PROCESSING->value,
            'terms_and_conditions' => $data['terms_and_conditions'] ?? null,
        ]);

        // 2) Create Lines
        OrderLineSalesOrderService::createOrderLines(
            $companyId,
            $user,
            $order,
            $data['lines']
        );

        $order->load('lines.item');

        // ---------------------------------------------------
        // 3) ORDER-WIDE AVAILABILITY VALIDATION (NO RESERVE)
        // ---------------------------------------------------

        $requirements = [];

        foreach ($order->lines as $line) {
            if ($line->line_type_id === 2) {

                $key = $line->item_id . '-' . $line->warehouse_id;

                if (!isset($requirements[$key])) {
                    $requirements[$key] = [
                        'item_id' => $line->item_id,
                        'warehouse_id' => $line->warehouse_id,
                        'required_qty' => 0,
                        'item_name' => $line->item->item_name,
                    ];
                }

                $requirements[$key]['required_qty'] += $line->quantity;
            }
        }

        foreach ($requirements as $req) {

            $available = $this->getAvailableQtyByItemWarehouse(
                $req['item_id'],
                $req['warehouse_id']
            );

            if ($req['required_qty'] > $available) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        "Insufficient stock for item {$req['item_name']} in warehouse. "
                        . "Required: {$req['required_qty']}, Available: {$available}",
                ]);
            }
        }

        // ---------------------------------------------------
        // 4) RESERVATION PASS (AFTER ALL CHECKS PASSED)
        // ---------------------------------------------------

        foreach ($order->lines as $line) {
            if ($line->line_type_id === 2) {
                $this->stockReservationService
                    ->reserveForSalesOrderLine($line, $line->quantity);
            }
        }

        return $order->load('lines');
    });
    }

    private function getAvailableQtyByItemWarehouse(int $itemId, int $warehouseId): float
    {
        $companyId = auth()->user()->company_id;

        $onHand = StockMovement::query()
            ->where('company_id', $companyId)
            ->where('warehouse_id', $warehouseId)
            ->where('item_id', $itemId)
            ->sum('quantity');

        $reserved = $this->stockReservationService
            ->getReservedQty($itemId, $warehouseId);

        return max($onHand - $reserved, 0);
    }
    /**
     * Confirm a Draft Sales Order → creates Stock Reservations
     */

    public function confirm(SalesOrder $order): SalesOrder
    {
        if ($order->status !==  'processing') {
            throw ValidationException::withMessages([
                'status' => 'Only draft orders can be confirmed.',
            ]);
        }

        DB::transaction(function () use ($order) {
            foreach ($order->lines as $line) {
                if($line->line_type_id===2)
                {  $available = $this->getAvailableQty($line);

                if ($line->quantity > $available) {
                    throw ValidationException::withMessages([
                        'quantity' => "Insufficient stock for item {$line->item->item_name}. Available: {$available}",
                    ]);

                }

                $this->stockReservationService->reserveForSalesOrderLine($line, $line->quantity);

            }}

            $order->status = SalesOrderStatusEnum::COMPLETED->value;
           // $order->confirmed_at = now();
            $order->save();
        });

        return $order->load('lines');
    }

    /**
     * Cancel a Sales Order → cancels all reservations
     */
    public function cancel(SalesOrder $order): SalesOrder
    {
        if (!in_array($order->status, ['processing'])) {
            throw ValidationException::withMessages([
                'status' => 'Only draft orders can be cancelled.',
            ]);
        }


        foreach ($order->lines as $line) {
            $this->stockReservationService->cancelReservation($line);
        }


            $order->status = 'cancelled';
            //$order->cancelled_at = now();
            $order->save();

        return $order->load('lines');
    }

    /**
     * Update quantity of a specific line → adjusts reservation
     */
    public function updateLineQty(SalesOrderLine $line, float $newQty): SalesOrderLine
    {
        $available = $this->getAvailableQty($line);
        if ($newQty > $available) {
            throw ValidationException::withMessages([
                'quantity' => "Insufficient stock. Available: {$available}",
            ]);
        }

        DB::transaction(function () use ($line, $newQty) {
            $line->qty = $newQty;
            $line->save();

            // Update reservation if confirmed
            if ($line->salesOrder->status === SalesOrder::STATUS_CONFIRMED) {
                $this->stockReservationService->cancelReservation($line);
                $this->stockReservationService->reserveForSalesOrderLine($line, $newQty);
            }
        });

        return $line;
    }

    /**
     * Calculate available quantity for a line
     */
    public function getAvailableQty(SalesOrderLine $line): float
    {

        $onHand = StockMovement::query()
            ->where('company_id', auth()->user()->company_id)
            ->where('warehouse_id', $line->warehouse_id)
            ->where('item_id', $line->item_id)
            ->sum('quantity');
        $reserved = $this->stockReservationService
            ->getReservedQty($line->item_id, $line->warehouse_id);

        return max($onHand - $reserved, 0);
        // + $line->qty لأنه إذا الخط موجود مسبقًا، ما نحسبه مرتين
    }
}
