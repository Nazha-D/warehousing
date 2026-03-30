<?php


namespace App\Services;

use App\Constants\PosInvoiceConstants;
use App\Constants\QuotationConstants;
use App\Models\PosCashTray;
use App\Models\PosInvoice;
use App\Models\PosPayment;
use App\Models\PosSession;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosInvoiceService
{

    public static function getAll( $userCompanyId, $options = [])
    {
        $perPageDefault = 10;
        $isPaginated = false;
        $searchDefault = '';
        //   $onlyActive = false;
        $cashCustomers=false;
        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $search = $options['search'] ?? $searchDefault;
        $searchByStatus = $options['searchByStatus'] ?? '';
        // $onlyActive = json_decode($options['onlyActive'] ?? $onlyActive);
        $cashCustomers=json_decode($options['cashCustomers'] ?? $cashCustomers);
        $invoices = PosInvoice::query()->with(['client','car','user','finishedByUser','currency']);

        $invoices->where('company_id', $userCompanyId);


        $invoices->filter($search,$searchByStatus);


        if($cashCustomers)
        {
            $invoices->WhereHas('client', function ($query) {
                $query->where('is_cash_customer','=','1');
            });
        }
        if ($isPaginated) {
            $orders = $invoices->paginate($perPage);
        } else {
            $orders = $invoices->get();
        }

        return $orders;
    }
    public function generateInvoiceNumber($companyId)
    {
        $currentYear = date('y');
        $prefix = PosInvoiceConstants::NUMBER_PREFIX . $currentYear;

        $latestInvoice = PosInvoice::where('company_id', $companyId)
            ->where('invoice_number', 'like', $prefix . '%')
            ->withTrashed()
            ->orderByDesc('invoice_number')
            ->first();

        if ($latestInvoice) {
            $lastNumber = (int) substr(
                $latestInvoice->invoice_number,
                strlen($prefix)
            );

            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix .
            str_pad(
                $newNumber,
                PosInvoiceConstants::NUMBER_MIN_LENGTH,
                PosInvoiceConstants::NUMBER_PAD_STR,
                STR_PAD_LEFT
            );
    }

    public  function generateDocNumber($companyId,$sessionNumber,$posNumber)
    {

        $latestOrder = PosInvoice::where('company_id', $companyId)->whereNotNull('invoice_number')->withTrashed()->latest()->first();

        if ($latestOrder) {
            if (str_contains($latestOrder->order_number, QuotationConstants::NUMBER_SEPARATOR)) {
                $lastNumber = (int)substr($latestOrder->order_number, 4);
            } else {
                $lastNumber = (int)substr($latestOrder->order_number, 3);
            }

            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        //  return QuotationConstants::ORDER_PREFIX . $currentYear . str_pad($newNumber, QuotationConstants::NUMBER_MIN_LENGTH, QuotationConstants::NUMBER_PAD_STR, STR_PAD_LEFT);
        return $sessionNumber.'-'.$posNumber.'-'.str_pad($newNumber, 2, '0', STR_PAD_LEFT);

    }
    public function create(array $data): PosInvoice
    {
        return DB::transaction(function () use ($data) {
$user=auth()->user();
            // 1️⃣ Create Invoice Header
            $invoice = PosInvoice::create([

                'company_id' =>$user ->company_id,
                'user_id'=>$user->id,
                'pos_terminal_id' => $data['pos_terminal_id'] ?? null,
                'session_id' => $data['session_id'],
               'pos_cash_tray_id'=>$data['pos_cash_tray_id'],
                'client_id' => $data['client_id'] ?? null,
                'car_id' => $data['car_id'] ?? null,
                'currency_id' => $data['currency_id'],
                'exchange_rate' => $data['exchange_rate'],
                'invoice_number' => $this->generateInvoiceNumber($user->company_id),
                'status' => PosInvoice::STATUS_DRAFT,
                'change_total'=>$data['change_total'],
                'note' => $data['note'] ?? null,
                'opened_at' => now(),
            ]);

            $subtotal = 0;
            $taxTotal = 0;
            $discountTotal = 0;

            // 2️⃣ Create Lines
            foreach ($data['lines'] as $line) {

                $lineSubtotal = $line['unit_price'] * $line['quantity'];
                $lineDiscountID = $line['discount_id'] ?? null;
                $lineDiscount = $line['discount_value'] ?? 0;
                $lineCustomDiscount = $line['custom_discount_value'] ?? 0;

                $lineTax = $line['tax_value'] ?? 0;

                $lineTotal = $lineSubtotal - $lineDiscount + $lineTax;

                $invoice->lines()->create([
                    'item_id' => $line['item_id'],
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'discount_id' => $lineDiscountID,
                    'discount_value' => $lineDiscount,
                    'custom_discount_value' => $lineCustomDiscount,
                    'tax_value' => $lineTax,
                    'line_total' => $lineTotal,

                ]);

                $subtotal += $lineSubtotal;
                $taxTotal += $lineTax;
                $discountTotal += $lineDiscount;
            }

            // 3️⃣ Update Totals
            $invoice->update([
                'subtotal' => $data['subtotal']??$subtotal,
                'tax_total' => $data['tax_total']??$taxTotal,
                'discount_total' =>$data['discount_total']?? $discountTotal,
                'custom_discount_total' =>$data['custom_discount_total']??0,
                'grand_total' => $data['grand_total']??$subtotal - $discountTotal + $taxTotal,
                'remaining_total' =>$data['remaining_total']?? $subtotal - $discountTotal + $taxTotal,
            ]);

            return $invoice;
        });
    }

    public function addPayments(array $data): PosInvoice
    {
        return DB::transaction(function () use ($data) {

            $invoice = PosInvoice::lockForUpdate()
                ->findOrFail($data['pos_invoice_id']);
             $tray=PosCashTray::find($data['pos_cash_tray_id']);
             $session=PosSession::find($data['pos_session_id']);
//             if($tray->status==='closed')
//             {
//                 throw new \Exception('You Cant pay using closed cash tray');
//             }
            if($session->status==='CLOSED')
            {
                throw new \Exception('Sorry This session is closed');
            }
            $this->guardInvoiceCanReceivePayment($invoice);

            foreach ($data['payments'] as $paymentData) {

             //   $this->assignCashTrayIfFirstPayment($invoice, $paymentData);

                $amountInInvoiceCurrency =
                    $paymentData['amount'] * $paymentData['exchange_rate'];

                PosPayment::create([
                    'company_id' => $invoice->company_id,
                    'pos_invoice_id' => $invoice->id,
                    'pos_session_id' => $paymentData['pos_session_id'],
                    'pos_cash_tray_id' => $paymentData['pos_cash_tray_id'],
                    'cashing_method_id' => $paymentData['cashing_method_id'],
                    'currency_id' => $paymentData['currency_id'],
                    'amount' => $paymentData['amount'],
                    'exchange_rate' => $paymentData['exchange_rate'],
                    'amount_in_invoice_currency' => $amountInInvoiceCurrency,
                    'type' => PosPayment::TYPE_PAYMENT,
                ]);

                $invoice->paid_total += $amountInInvoiceCurrency;
            }

            $remaining = $invoice->grand_total - $invoice->paid_total;

            $invoice->update([
                'remaining_total' => max($remaining, 0),
                'change_total' => $remaining < 0 ? abs($remaining) : 0,
                'status' => $this->resolveStatus($invoice, $remaining)
            ]);

            if ($invoice->status === PosInvoice::STATUS_PAID) {
                $this->finalizeIfFullyPaid($invoice);
            }
            //user may change client during the process
          if(isset($data['client_id']))
          {
              $invoice->update(['client_id'=>$data['client_id']]);
          }

            if(isset($data['remaining_total']))
            {
                $invoice->update(['remaining_total'=>$data['remaining_total']]);
            }
            if(isset($data['change_total']))
            {
                $invoice->update(['change_total'=>$data['change_total']]);
            }
            return $invoice->fresh();
        });
    }
    private function resolveStatus(PosInvoice $invoice, float $remaining): string
    {
        if ($remaining <= 0) {
            return PosInvoice::STATUS_PAID;
        }

        if ($remaining > 0 && $invoice->client_id) {
            return PosInvoice::STATUS_PARTIAL;
        }

        return PosInvoice::STATUS_DRAFT;
    }
    private function finalizeIfFullyPaid(PosInvoice $invoice): void
    {
        if ($invoice->closed_at) {
            return;
        }

        DB::transaction(function () use ($invoice) {

            $invoice->load([
                'lines',
                'posTerminal' // تأكد اسم العلاقة
            ]);

            $warehouseId = $invoice->posTerminal->warehouse_id;

            foreach ($invoice->lines as $line) {

                // 🔒 قفل الصفوف المرتبطة بالصنف في هذا المستودع
                $available = StockMovement::where('warehouse_id', $warehouseId)
                    ->where('item_id', $line->item_id)
                    ->lockForUpdate()
                    ->sum('quantity');

                if ($available < $line->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for item {$line->item_id}. Available: {$available}"
                    ]);
                }

                // ➖ تسجيل البيع (سالب)
                StockMovement::create([
                    'company_id' => $invoice->company_id,
                    'warehouse_id' => $warehouseId,
                    'item_id' => $line->item_id,
                    'reference_type' => 'pos_invoice',
                    'type'=>'out',
                    'reference_id' => $invoice->id,
                    'quantity' => -1 * $line->quantity,
                    'note' => 'POS Sale #' . $invoice->invoice_number,
                ]);
            }

            $invoice->update([
                'closed_at' => now(),
                'finished_by_user_id' => auth()->id(),
            ]);
        });
    }
    public function calculateTrayBalance(int $trayId): float
    {
        $payments = PosPayment::where('pos_cash_tray_id', $trayId)
            ->payments()
            ->sum('amount');

        $refunds = PosPayment::where('pos_cash_tray_id', $trayId)
            ->refunds()
            ->sum('amount');

        return $payments - $refunds;
    }

    public function refund(array $data): PosInvoice
    {
        return DB::transaction(function () use ($data) {

            $original = PosInvoice::with('lines')
                ->lockForUpdate()
                ->findOrFail($data['original_invoice_id']);

            if ($original->status !== PosInvoice::STATUS_PAID) {
                throw ValidationException::withMessages([
                    'invoice' => 'Only paid invoices can be refunded.'
                ]);
            }

            // 1️⃣ إنشاء فاتورة Refund
            $refund = PosInvoice::create([
                'uuid' => \Str::uuid(),
                'company_id' => $original->company_id,
                'pos_terminal_id' => $original->pos_terminal_id,
                'session_id' => $data['pos_session_id'],
                'client_id' => $original->client_id,
                'currency_id' => $original->currency_id,
                'exchange_rate' => $original->exchange_rate,
                'invoice_number' => 'REF-' . time(),
                'status' => PosInvoice::STATUS_PAID,
                'type' => PosInvoice::STATUS_REFUNDED,
                'parent_invoice_id' => $original->id,
                'grand_total' => 0,
                'paid_total' => 0,
                'remaining_total' => 0,
                'opened_at' => now(),
                'closed_at' => now(),
                'finished_by_user_id' => auth()->id(),
            ]);

            $totalRefund = 0;

            foreach ($data['lines'] as $lineData) {

                $originalLine = $original->lines
                    ->where('id', $lineData['line_id'])
                    ->first();

                $qty = $lineData['quantity'];

                $lineTotal = $originalLine->unit_price * $qty;

                $refund->lines()->create([
                    'item_id' => $originalLine->item_id,
                    'unit_price' => $originalLine->unit_price,
                    'quantity' => $qty,
                    'line_total' => $lineTotal,
                ]);

                // 2️⃣ Stock يرجع للمستودع (موجب)
                StockMovement::create([
                    'company_id' => $refund->company_id,
                    'item_id' => $originalLine->item_id,
                    'reference_type' => 'pos_invoice',
                    'reference_id' => $refund->id,
                    'quantity' => $qty, // موجب
                    'note' => 'Refund Invoice #' . $refund->invoice_number,
                ]);

                $totalRefund += $lineTotal;
            }

            // 3️⃣ تسجيل حركة مالية Refund
            PosPayment::create([
                'company_id' => $refund->company_id,
                'pos_invoice_id' => $refund->id,
                'pos_session_id' => $data['pos_session_id'],
                'pos_cash_tray_id' => $data['pos_cash_tray_id'],
                'cashing_method_id' => $data['cashing_method_id'],
                'currency_id' => $refund->currency_id,
                'amount' => $totalRefund,
                'exchange_rate' => 1,
                'amount_in_invoice_currency' => $totalRefund,
                'type' => PosPayment::TYPE_REFUND,
            ]);

            $refund->update([
                'grand_total' => $totalRefund,
                'paid_total' => $totalRefund,
            ]);

            return $refund;
        });
    }
    protected function getAvailableStock(int $warehouseId, int $itemId): float
    {
        return StockMovement::where('warehouse_id', $warehouseId)
            ->where('item_id', $itemId)
            ->sum('quantity');
    }
    protected function guardInvoiceCanReceivePayment(PosInvoice $invoice): void
    {
        if (in_array($invoice->status, [
            PosInvoice::STATUS_CANCELLED,
            PosInvoice::STATUS_REFUNDED,
        ])) {
            throw ValidationException::withMessages([
                'invoice' => 'Invoice cannot receive payments.',
            ]);
        }

        if ($invoice->status === PosInvoice::STATUS_PAID) {
            throw ValidationException::withMessages([
                'invoice' => 'Invoice already fully paid.',
            ]);
        }
    }
}
