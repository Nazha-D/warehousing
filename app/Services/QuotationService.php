<?php

namespace App\Services;

use App\Constants\QuotationConstants;
use App\Models\Quotation;
//use App\Models\SalesOrder;
use App\Models\SalesOrder;
use App\Models\UserSalespersonConfiguration;
use App\Services\OrderLineQuotationService;
use App\Services\OrderLineSalesOrderService;
use App\Services\SalesOrderService;

use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\QuotationMail;
use Illuminate\Support\Str;

class QuotationService
{
    public function createQuotation($user, array $data): Quotation
    {
        return DB::transaction(function () use ($user, $data) {
            $quotationNumber = QuotationService::generateDraftNumber($user->company_id);

            $quotation = Quotation::create(array_merge($data, [
                'company_id' => $user->company_id,
                'created_by' => $user->id,
                'quotation_number' => $quotationNumber,
                'status' => 'DRAFT'
            ]));

            OrderLineQuotationService::createOrderLines($user->company_id, $user, $quotation, $data['order_lines']);

            return $quotation->refresh();
        });
    }

    public function updateQuotation($user, Quotation $quotation, array $data): Quotation
    {
        return DB::transaction(function () use ($user, $quotation, $data) {
            $this->checkSalespersonPermissions($user, $quotation, $data);

            $quotation->update($data);

            $quotation->quotationLines()->delete();
            OrderLineQuotationService::createOrderLines($user->company_id, $user, $quotation, $data['order_lines']);

            return $quotation->refresh();
        });
    }

    private function checkSalespersonPermissions($user, $quotation, array $data)
    {
        if (!isset($data['salesperson_id'])) return;

        $salespersonConfig = UserSalespersonConfiguration::where('user_id', $data['salesperson_id'])->first();

        $permissions = [
            'cash' => $user->can('edit salesperson cashing method in quotation'),
            'method' => $user->can('edit salesperson commission method in quotation'),
            'rate' => $user->can('edit salesperson commission in quotation')
        ];
//
//        if (!$permissions['cash'] && ($data['cashing_method_id'] ?? $quotation->cashing_method_id) != $salespersonConfig->cashing_method_id) {
//            throw new AuthorizationException("Cannot change salesperson cashing method.");
//        }
//
//        if (!$permissions['method'] && ($data['commission_method_id'] ?? $quotation->commission_method_id) != $salespersonConfig->commission_method_id) {
//            throw new AuthorizationException("Cannot change salesperson commission method.");
//        }
//
//        if (!$permissions['rate'] && ($data['commission_rate'] ?? $quotation->commission_rate) != $salespersonConfig->commission) {
//            throw new AuthorizationException("Cannot change salesperson commission rate.");
//        }
    }

    public function changeStatus(Quotation $quotation, string $newStatus, ?string $reason = null)
    {
        return DB::transaction(function () use ($quotation, $newStatus, $reason) {

            $currentStatus = $quotation->status->value;
            $newStatus = strtoupper($newStatus);

            if ($currentStatus === 'DRAFT') throw new \Exception('Drafts can only be sent');
            if (in_array($currentStatus, ['LOST', 'CANCELLED'])) throw new \Exception('Cannot change LOST or CANCELLED quotation');

            if ($currentStatus === 'SENT' && $newStatus === 'WIN') {
                $quotation->update(['status' => 'WIN']);
                $this->createSalesOrderFromQuotation($quotation);
            } else if ($currentStatus === 'SENT' && $newStatus === 'LOST') {
                $quotation->update(['status' => 'LOST', 'cancellation_reason' => $reason]);
            } else if ($currentStatus === 'WIN' && $newStatus === 'LOST') {
                $quotation->update(['status' => 'CANCELLED', 'cancellation_reason' => $reason]);
                if ($quotation->salesOrder) {
                    $quotation->salesOrder->orderLines()->forceDelete();
                    $quotation->salesOrder->forceDelete();
                }
            } else {
                throw new \Exception('Invalid status change');
            }

            return $quotation->refresh();
        });
    }

    public function CreateSalesOrderFromQuotation(Quotation $quotation): SalesOrder
    {


        $quotation->load('lines.item');

        $data = [
            'quotation_id' => $quotation->id,
            'client_id' => $quotation->client_id,
            'currency_id' => $quotation->currency_id,
            'price_list_id' => $quotation->price_list_id,
            'payment_term_id' => $quotation->payment_term_id,
            'salesperson_id' => $quotation->salesperson_id,
            'commission_method_id' => $quotation->commission_method_id,
            'cashing_method_id' => $quotation->cashing_method_id,
            'company_header_id' => $quotation->company_header_id,

            'reference' => $quotation->reference,
            'code' => $quotation->code,
            'title' => $quotation->title,

            'input_date' => now(),
            'validity' => $quotation->validity,

            'special_discount' => $quotation->special_discount,
            'special_discount_amount' => $quotation->special_discount_amount,
            'global_discount' => $quotation->global_discount,
            'global_discount_amount' => $quotation->global_discount_amount,

            'vat' => $quotation->vat,
            'vat_lebanese' => $quotation->vat_lebanese,
            'vat_exempt' => $quotation->vat_exempt,
            'vat_inclusive_prices' => $quotation->vat_inclusive_prices,
            'before_vat_prices' => $quotation->before_vat_prices,

            'total_before_vat' => $quotation->total_before_vat,
            'total' => $quotation->total,

            'not_printed' => $quotation->not_printed,
            'printed_as_vat_exempt' => $quotation->printed_as_vat_exempt,
            'printed_as_percentage' => $quotation->printed_as_percentage,

            'terms_and_conditions' => $quotation->terms_and_conditions,

            // أهم جزء 👇
//            'lines' => $quotation->lines->map(function ($line) {
//                return [
//                    'line_type_id' => $line->line_type_id,
//                    'item_id' => $line->item_id,
//                    'warehouse_id' => $line->warehouse_id,
//                    'description' => $line->description,
//                    'quantity' => $line->quantity,
//                    'unit_price' => $line->unit_price,
//                    'discount' => $line->discount,
//                    'discount_amount' => $line->discount_amount,
//                    'total_before_vat' => $line->total_before_vat,
//                    'vat' => $line->vat,
//                    'total' => $line->total,
//                ];
//            })->toArray(),
//        ];
            'lines' => $this->mapQuotationLinesToSalesOrderPayload($quotation->lines)];

        // استدعاء السيرفيس الصحيح
        $salesOrder = app(SalesOrderService::class)->create($data);

        // ربط الكوتيشن بالأوردر
        $quotation->update([
            'status' => 'WIN',
        'sales_order_id' => $salesOrder->id,
    ]);

    return $salesOrder;
}


    public function sendQuotationEmail(Quotation $quotation)
    {
        return DB::transaction(function () use ($quotation) {
            if (!$quotation->client || !$quotation->client->email) {
                throw new \Exception('No customer email found.');
            }

            if ($quotation->status->value === 'DRAFT') {
                $quotation->update([
                    'status' => 'SENT',
                    'quotation_number' => QuotationService::generateQuotationNumber($quotation->company_id)
                ]);
            }

            Mail::to($quotation->client->email)->send(new QuotationMail($quotation));

            return $quotation->refresh();
        });
    }
    public static function getAll($canViewAllCompanies, $userCompanyId = null, $options = [])
    {
        $perPage = $options['perPage'] ?? 10;
        $isPaginated = isset($options['isPaginated']) ? json_decode($options['isPaginated']) : true;
        $search = $options['search'] ?? '';
        $status = $options['status'] ?? '';
        $exceptStatus = $options['exceptStatus'] ?? '';

        $query = Quotation::query();

        if (!$canViewAllCompanies && $userCompanyId) {
            $query->where('company_id', $userCompanyId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($exceptStatus) {
            $query->whereNot('status', $exceptStatus);
        }

        if ($search) {
            $query->filter($search); // assuming a scopeFilter exists on the model
        }

        return $isPaginated ? $query->paginate($perPage) : $query->get();
    }

    // ==========================
    // 🔹 Retrieve Quotations by Client
    // ==========================
    public static function getAllByClient(Client $client, $userCompanyId = null, $options = [])
    {
        $perPage = $options['perPage'] ?? 10;
        $isPaginated = isset($options['isPaginated']) ? json_decode($options['isPaginated']) : true;
        $search = $options['search'] ?? '';

        $query = $client->quotations();

        if ($search) {
            $query->filter($search);
        }

        return $isPaginated ? $query->paginate($perPage) : $query->get();
    }

    // ==========================
    // 🔹 Generate Numbers
    // ==========================
    public static function generateQuotationNumber($companyId)
    {
        return self::generateNumber($companyId, QuotationConstants::NUMBER_PREFIX);
    }

    public static function generateDraftNumber($companyId)
    {
        return self::generateNumber($companyId, 'QD');
    }

    private static function generateNumber($companyId, $prefix)
    {
        $currentYear = date('y');
        $minLength = QuotationConstants::NUMBER_MIN_LENGTH;
        $padStr = QuotationConstants::NUMBER_PAD_STR;

        $latestQuotation = Quotation::where('company_id', $companyId)
            ->whereNotNull('quotation_number')
            ->where('quotation_number', 'like', $prefix . $currentYear . '%')
            ->withTrashed()
            ->latest('id')
            ->first();

        $newNumber = 1;

        if ($latestQuotation) {
            $latestNumberStr = Str::replaceFirst($prefix, '', $latestQuotation->quotation_number);
            $latestYear = substr($latestNumberStr, 0, 2);
            $latestSerial = (int) substr($latestNumberStr, 2);

            $newNumber = ($latestYear == $currentYear) ? $latestSerial + 1 : 1;
        }

        $serialLength = max($minLength, strlen((string)$newNumber));
        $numberStr = str_pad($newNumber, $serialLength, $padStr, STR_PAD_LEFT);

        return $prefix . $currentYear . $numberStr;
    }
    private function mapQuotationLinesToSalesOrderPayload($quotationLines): array
    {
        return $quotationLines->map(function ($line) {

            $payload = [
                'type' => $line->line_type_id,
            ];

            if ($line->item_id) {
                $payload += [
                    'item_id' => $line->item_id,
                    'description' => $line->description,
                    'qty' => $line->quantity,
                    'warehouse_id' => $line->warehouse_id,
                    'unit_price' => $line->unit_price,
                    'discount' => $line->discount,
                    'total' => $line->total,
                ];
            }

            if ($line->combo_id) {
                $payload += [
                    'combo_id' => $line->combo_id,
                    'description' => $line->description,
                    'qty' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'discount' => $line->discount,
                    'total' => $line->combo_total,
                ];
            }

            if ($line->title) {
                $payload += [
                    'title' => $line->title,
                ];
            }

            if ($line->note) {
                $payload += [
                    'note' => $line->note,
                ];
            }

            if ($line->image_path) {
                $payload += [
                    'image' => $line->image_path,
                ];
            }

            return $payload;

        })->toArray();
    }

}
