<?php

namespace App\Services;

use App\Constants\OrderLineLayoutConstants;
use App\Constants\QuotationConstants;
use App\Models\Combo;
use App\Models\Item;
use App\Models\LineType;
use App\Models\QuotationLine;
use App\Models\Quotation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;
use Symfony\Component\HttpFoundation\Response;

class OrderLineQuotationService
{
    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public static function createOrderLines($companyId, $user, Quotation $quotation, $orderLines)
    {
        $lineTypes = cache()->rememberForever('line_types', function () {
            return LineType::pluck('id', 'name');
        });

//        $orderLinesEditPermissions = [
//            'edit item description in quotation' => $user->can('edit item description in quotation'),
//            'edit item unit price in quotation' => $user->can('edit item unit price in quotation'),
//            'edit combo description in quotation' => $user->can('edit combo description in quotation'),
//        ];

        if ($orderLines == null) {
            return;
        }

        foreach ($orderLines as $index => $orderLine) {
            $data = [
                'quotation_id' => $quotation->id,
                'line_type_id' => $orderLine['type'],
                'order_index' => $index,
            ];
            $extraData = [];
            if ($orderLine['type'] == $lineTypes['title']) {
                $extraData = [
                    'title' => $orderLine['title'],
                ];
            }
            if ($orderLine['type'] == $lineTypes['item']) {
                $id = $orderLine['item'];
                $existingItem = Item::find($id);

//                if ($existingItem && $existingItem->company_id === $companyId) {
//
//                    if(!($existingItem->item_type_id==4))
//                    {
//                        if ($existingItem->line_discount_limit && ($orderLine['discount'] > $existingItem->line_discount_limit)) {
//                            throw ValidationException::withMessages(['items' => 'Item ' . $existingItem->main_code . ' discount greater than the line discount limit'])->status(Response::HTTP_UNPROCESSABLE_ENTITY);
//                        }
//                        if ($existingItem->main_description && (!$orderLinesEditPermissions['edit item description in quotation'] && $orderLine['description'] != $existingItem->main_description)) {
//                            throw new AuthorizationException('You do not have permission to change the description of item: ' . $existingItem->main_code, Response::HTTP_FORBIDDEN);
//                        }
//                        if ($existingItem->unit_price && (!$orderLinesEditPermissions['edit item unit price in quotation'] && $orderLine['unitPrice'] != $existingItem->unit_price)) {
//                            throw new AuthorizationException('You do not have permission to change the unit price of item: ' . $existingItem->main_code, Response::HTTP_FORBIDDEN);
//                        }
//                    }

                    $packageId=null;
                    $actualQty=$orderLine['quantity'];
                    //       $totalAfterDiscount=$this->calculateTotal($actualQty, $existingItem->unit_price, $orderLine['discount']);

                    if(isset($orderLine['itemPkgName']))
                    {
                        $packageId=ItemService::getPackageIdByName($orderLine['itemPkgName'],$existingItem);
                        $qtyCalc=PackageQuantityCalculator::calculate($existingItem,$packageId,$orderLine['quantity']);
                        $actualQty=$qtyCalc;
                    }
                    $extraData = [
                        'item_id' => $id,
                        'package_id'=>$packageId,
                        'description' => $orderLine['description'] ?? null,
                        'item_main_code'=> $orderLine[ 'mainCode'] ?? null,
                        'quantity' => $actualQty,
                        'unit_price' => $orderLine['unitPrice'] ?? $existingItem->unit_price,
                        'discount' => $orderLine['discount'],
                        'total' => $orderLine['total'] ,
                    ];

                } else {
                    throw ValidationException::withMessages(['items' => 'Item not found']);
                }
            }
            if ($orderLine['type'] == $lineTypes['combo']) {
                $id = $orderLine['combo'];
                $existingCombo = Combo::find($id);
//                if ($existingCombo && $existingCombo->company_id === $companyId) {
//                    if (!$orderLinesEditPermissions['edit combo description in quotation'] && $orderLine['description'] != $existingCombo->description) {
//                        throw new AuthorizationException('You do not have permission to change the description of combo: ' . $existingCombo->code, Response::HTTP_FORBIDDEN);
//                    }
                    $extraData = [
                        'combo_id' => $id,
                        'description' => $orderLine['description'] ?? null,
                        'quantity' => $orderLine['quantity'],
                        'unit_price' => $orderLine['unitPrice'],
                        'discount' => $orderLine['discount'],
                        'total' => $orderLine['total'] ?? calculate_total($orderLine['quantity'], $existingCombo->unit_price, $orderLine['discount']),
                    ];
                } else {
                    throw ValidationException::withMessages(['combos' => 'Combo not found']);
                }
            }
            if ($orderLine['type'] == $lineTypes['image']) {
                $image = $orderLine['image'];
                $image = Image::read($image)
                    ->orient()
                    ->scaleDown(1920, 1920);

                // 2. التحويل إلى WebP
                $encoded = $image->toWebp(85);

                // 3. تجهيز المسار (بدون storage)
                $filename = uniqid('quot_') . '.webp';
                $path = 'quotations/' . $quotation->id . '/images/' . $filename;

                // 4. التخزين على disk public
                Storage::disk('public')->put($path, $encoded->toString());

                // $path = $image->store('storage/quotations/images/'.$quotation->id,'public');
                $extraData = [
                    'image_path' => $path,
                ];
            }
            if ($orderLine['type'] == $lineTypes['note']) {
                $extraData = [
                    'note' => $orderLine['note'],
                ];
            }
            $data = array_merge($data, $extraData);
            QuotationLine::create($data);
        }
    }

    public static function calculateTotalBeforeDiscount(Quotation $quotation)
    {
        $orderLines = $quotation->orderLines;
        $sum = 0;
        foreach ($orderLines as $orderLine) {
            if ($orderLine->line_type_id == LineType::where('name', 'item')->first()->id) {
                $sum += $orderLine->item_total;
            }
            if ($orderLine->line_type_id == LineType::where('name', 'combo')->first()->id) {
                $sum += $orderLine->combo_total;
            }
        }

        return $sum;
    }
    function calculateTotal(
        float|int $quantity,
        float|int $unitPrice,
        float|int $discountPercentage = 0
    ): float {
        $subTotal = $quantity * $unitPrice;

        if ($discountPercentage > 0) {
            $discountAmount = $subTotal * ($discountPercentage / 100);
            $subTotal -= $discountAmount;
        }

        return round(max(0, $subTotal), 2);
    }


    public static function formatOrderLinesForQuotation($orderLines)
    {
        $lineTypes = cache()->rememberForever('line_types', function () {
            return LineType::pluck('id', 'name');
        });

        $data = [];

        foreach ($orderLines as $index => $orderLine) {
            $data[$index] = [
                'type' => $orderLine['line_type_id'],
            ];
            $extraData = [];
            if ($orderLine['line_type_id'] == $lineTypes['title']) {
                $extraData = [
                    'title' => $orderLine['title'],
                ];
            }
            if ($orderLine['line_type_id'] == $lineTypes['item']) {
                $id = $orderLine['item_id'];
                $existingItem = Item::find($id);
                $extraData = [
                    'item' => $existingItem,
                    'description' => $orderLine['description'],
                    'quantity' => $orderLine['quantity'],
                    'unitPrice' => $orderLine['unit_price'],
                    'discount' => $orderLine['discount'],
                    'total' => $orderLine['total'],
                ];
            }
            if ($orderLine['line_type_id'] == $lineTypes['combo']) {
                $id = $orderLine['combo'];
                $existingCombo = Combo::find($id);
                $extraData = [
                    'item' => $existingCombo,
                    'description' => $orderLine['description'],
                    'quantity' => $orderLine['quantity'],
                    'unitPrice' => $orderLine['unit_price'],
                    'discount' => $orderLine['discount'],
                    'total' => $orderLine['total'],
                ];
            }
            if ($orderLine['line_type_id'] == $lineTypes['image']) {
                $image = $orderLine['image'];
                $extraData = [
                    'image' => $image,
                ];
            }
            if ($orderLine['line_type_id'] == $lineTypes['note']) {
                $extraData = [
                    'note' => $orderLine['note'],
                ];
            }
            $data[$index] = array_merge($data[$index], $extraData);
        }
        return $data;
    }
}
