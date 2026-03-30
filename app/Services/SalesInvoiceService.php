<?php

namespace App\Services;

use App\Constants\SalesInvoiceConstants;
use App\Models\SalesInvoice;
use App\Models\OrderLineSalesInvoice;
use App\Models\DeliveryLine;
use App\Models\SalesInvoiceLine;
use Illuminate\Support\Facades\DB;
use Exception;

class SalesInvoiceService
{

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
        $status=$options['status'] ?? $statusDefault;
        $salesInvoices= SalesInvoice::query();

        if($exceptStatus!='')
        {$salesInvoices->whereNot('status', $exceptStatus);}

        if($status)
        {
            $salesInvoices->where('status', $status);
        }


            $salesInvoices->where('company_id', $userCompanyId);


        $salesInvoices->filter($search);



        if ($isPaginated) {
            $salesInvoices =  $salesInvoices->paginate($perPage);
        } else {
            $salesInvoices =  $salesInvoices->get();
        }

        return $salesInvoices;
    }
    public function generateSalesInvoiceNumber($companyId)
    {
        $currentYear = date('y');
        $latestInvoice = SalesInvoice::where('company_id', $companyId)
            ->withTrashed()
            ->whereNotNull('sales_invoice_number')
            ->where('sales_invoice_number', 'like', SalesInvoiceConstants::NUMBER_PREFIX . $currentYear . '%') // Filter by current year
            ->latest('sales_invoice_number') // Order by invoice number
            ->first();

        if ($latestInvoice) {
            $lastNumber = (int)substr($latestInvoice->sales_invoice_number, strlen(SalesInvoiceConstants::NUMBER_PREFIX . $currentYear));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1; // Start at 1 if no invoices exist for the year
        }

        return SalesInvoiceConstants::NUMBER_PREFIX
            . $currentYear
            . str_pad($newNumber, SalesInvoiceConstants::NUMBER_MIN_LENGTH, SalesInvoiceConstants::NUMBER_PAD_STR, STR_PAD_LEFT);
    }

    /**
     * Create a Sales Invoice from Delivery Lines
     *
     * @param array $data
     * @return SalesInvoice
     * @throws Exception
     */
    public function createFromDeliveryLines(array $data): SalesInvoice
    {
        return DB::transaction(function () use ($data) {

//            $deliveryLineIds = collect($data['delivery_lines'])->pluck('delivery_line_id')->all();
            $deliveryLineIds = collect($data['delivery_lines'])
                ->pluck('delivery_line_id')
                ->map(fn ($id) => (int) $id)
                ->all();
            // Step 1: Fetch delivery lines with lock
            $deliveryLines = DeliveryLine::whereIn('id', $deliveryLineIds)
                ->lockForUpdate()
                ->get();

            if ($deliveryLines->isEmpty()) {
                throw new Exception('Delivery Lines not found.');
            }

            // Step 2: Business Rules Validation
            $this->validateDeliveryLines($deliveryLines, $data);
             $user=auth()->user();
            // Step 3: Create SalesInvoice header
            $invoice = SalesInvoice::create([
                'company_id' => $user->company_id,
                'client_id' => $data['client_id'],
                'value_date' => $data['value_date'],
                'payment_term_id' => $data['payment_term_id'] ?? null,
                'currency_id' => $data['currency_id'] ?? null,
//                'sales_order_id' => $data['sales_order_id'] ?? null,
                'price_list_id' => $data['price_list_id'] ?? null,
                'salesperson_id' => $data['salesperson_id'] ?? null,
                'commission_method_id' => $data['commission_method_id'] ?? null,
                'cashing_method_id' => $data['cashing_method_id'] ?? null,
                'sales_invoice_number' => $this->generateSalesInvoiceNumber($user->company_id),
                'reference' => $data['reference'] ?? null,
                'terms_and_conditions' => $data['terms_and_conditions'] ?? null,
                'commission_rate' => $data['commission_rate'] ?? null,
                'commission_total' => $data['commission_total'] ?? null,
                'special_discount' => $data['special_discount'] ?? null,
                'special_discount_amount' => $data['special_discount_amount'] ?? null,
                'global_discount' => $data['global_discount'] ?? null,
                'global_discount_amount' => $data['global_discount_amount'] ?? null,
                'vat_lebanese' => $data['vat_lebanese'] ?? null,
                'vat' => $data['vat'] ?? null,
                'total' => $data['total'] ?? 0,
                'total_before_vat' => $data['total_before_vat'] ?? null,
                'vat_exempt' => $data['vat_exempt'] ?? false,
                'not_printed' => $data['not_printed'] ?? false,
                'printed_as_vat_exempt' => $data['printed_as_vat_exempt'] ?? false,
                'printed_as_percentage' => $data['printed_as_percentage'] ?? false,
                'vat_inclusive_prices' => $data['vat_inclusive_prices'] ?? false,
                'before_vat_prices' => $data['before_vat_prices'] ?? false,
                'code' => $data['code'] ?? null,
                'title' => $data['title'] ?? null,
                'delivered_from_warehouse_id' => $data['delivered_from_warehouse_id'] ?? null,
                'invoice_delivery_date' => $data['invoice_delivery_date'] ?? null,
                'input_date' => $data['input_date'] ?? now()->toDateString(),
                'company_header_id' => $data['company_header_id'] ?? null,
                'invoice_type' => $data['invoice_type'] ?? null,
                'car_id' => $data['car_id'] ?? null,
                'terms_and_condition_id' => $data['terms_and_condition_id'] ?? null,
                'status' => 'draft',
            ]);

            // Step 4: Create Invoice Lines
            foreach ($deliveryLines as $line) {
                $requestedQty = collect($data['delivery_lines'])
                    ->firstWhere('delivery_line_id', $line->id)['quantity'];

                // Update invoiced_qty
                $line->invoiced_qty += $requestedQty;
                $line->save();

                // Create Invoice Line snapshot
                SalesInvoiceLine::create([
                    'sales_invoice_id' => $invoice->id,
                    'delivery_line_id' => $line->id,
                    'line_type_id' => $line->line_type_id,
                    'item_id' => $line->item_id,
                    'description' => $line->description,
                    'quantity' => $requestedQty,
                    'total'=>$line->total,
                    'note'=>$line->note,
                    'image'=>$line->image
//                    'unit_price' => $line->unit_price ?? 1,
//                    'total' => $line->unit_price * $requestedQty - $line->discount,
                    // combos, note, image etc حسب الحاجة
                ]);
            }

            // Step 5: Calculate Totals
            $totals = $invoice->lines()->selectRaw('SUM(total) as total')->first();
            $invoice->total = $totals->total;
            $invoice->save();

            return $invoice;
        });
    }

    /**
     * Validate Delivery Lines according to Business Rules
     */
    protected function validateDeliveryLines($deliveryLines, $data)
    {
        $clientId = $data['client_id'];

        foreach ($deliveryLines as $line) {
            // Rule 1 & 2: available to invoice
            $requestedQty = collect($data['delivery_lines'])
                ->firstWhere('delivery_line_id', $line->id)['quantity'];
//            $requestedQty = collect($data['delivery_lines'])
//                ->firstWhere('delivery_line_id', $line->id)['quantity'];

            $available = $line->qty - $line->invoiced_qty;

            if ($requestedQty <= 0 || $requestedQty > $available) {
                throw new Exception("Quantity for delivery line {$line->id} exceeds available to invoice Or this line has already been invoiced.");
            }

            // Rule 3: same client
            if ($line->delivery->client_id != $clientId) {
                throw new Exception("Delivery line {$line->id} belongs to a different client.");
            }

            // Rule 4: same company
            if ($line->delivery->company_id != auth()->user()->company_id) {
                throw new Exception("Delivery line {$line->id} belongs to a different company.");
            }
        }
    }
    /**
     * Cancel a Sales Invoice
     *
     * @param SalesInvoice $invoice
     * @throws \Exception
     */
    public function cancelInvoice(SalesInvoice $invoice)
    {
        if ($invoice->status === 'cancelled') {
            throw new \Exception("Invoice already cancelled.");
        }

        DB::transaction(function () use ($invoice) {

            // Lock all invoice lines for update to prevent race conditions
            $invoiceLines = $invoice->lines()->lockForUpdate()->get();

            foreach ($invoiceLines as $line) {
                $deliveryLine = $line->deliveryLine;

                if (!$deliveryLine) {
                    throw new \Exception("Delivery Line {$line->delivery_line_id} not found.");
                }
                \Log::info('bf  '. $deliveryLine);

                // Subtract invoiced quantity
                $deliveryLine->invoiced_qty -= $line->quantity;
                if ($deliveryLine->invoiced_qty < 0) {
                    $deliveryLine->invoiced_qty = 0; // safety
                    \Log::info('af  '. $deliveryLine->invoiced_qty);
                    \Log::info('af  '. $line->quantity);
                }
                $deliveryLine->save();
            }

            // Update invoice status
            $invoice->status = 'cancelled';
            $invoice->save();
        });
        return $invoice;
    }

}
