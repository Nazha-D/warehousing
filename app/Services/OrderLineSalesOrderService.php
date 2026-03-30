<?php

namespace App\Services;

use App\Constants\OrderLineLayoutConstants;
use App\Models\Combo;
use App\Models\Item;
use App\Models\LineType;
use App\Models\OrderLineSalesOrder;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class OrderLineSalesOrderService
{
    public static function createOrderLines($companyId, $user, SalesOrder $salesOrder, $orderLines)
    {
        $lineTypes = cache()->rememberForever('line_types', function () {
            return LineType::pluck('id', 'name');
        });

        if ($orderLines == null) {
            return;
        }

        foreach ($orderLines as $index => $orderLine) {

            $data = [
                'sales_order_id' => $salesOrder->id,
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
                $id = $orderLine['item_id'];
                $existingItem = Item::find($id);

                $extraData = [
                    'item_id' => $id,
                    'description' => $orderLine['description'] ?? null,
                    'quantity' => $orderLine['qty'],
                    'unit_price' => $orderLine['unit_price'] ?? $existingItem->unit_price,
                    'warehouse_id' => $orderLine['warehouse_id'] ?? null,
                    'discount' => $orderLine['discount'],
                    'total' => $orderLine['total'],
                ];
            }

            if ($orderLine['type'] == $lineTypes['combo']) {
                $id = $orderLine['combo_id'];
                $existingCombo = Combo::find($id);

                $extraData = [
                    'combo_id' => $id,
                    'description' => $orderLine['description'] ?? null,
                    'quantity' => $orderLine['qty'],
                    'unit_price' => $orderLine['unit_price'],
                    'discount' => $orderLine['discount'],
                    'combo_total' => $orderLine['total'],
                ];
            }

            if ($orderLine['type'] === $lineTypes['image']) {

                $image = $orderLine['image'];

                if ($image instanceof \Illuminate\Http\UploadedFile) {
                    $path = $image->store('storage/sales-orders/images/' . $salesOrder->id, 'public_disk');
                } else {
                    $path = $image;
                }

                $extraData = [
                    'image' => $path,
                ];
            }

            if ($orderLine['type'] == $lineTypes['note']) {
                $extraData = [
                    'note' => $orderLine['note'],
                ];
            }

            $data = array_merge($data, $extraData);

            SalesOrderLine::create($data);
        }
    }

    public static function checkOrderLineFlexibleLayout($line)
    {
        if ($line['layout'] == OrderLineLayoutConstants::TITLE_LAYOUT_TITLE) {
            return LineType::where('name', 'title')->first()->id;
        }
        if ($line['layout'] == OrderLineLayoutConstants::ITEM_LAYOUT_TITLE) {
            return LineType::where('name', 'item')->first()->id;
        }
        if ($line['layout'] == OrderLineLayoutConstants::COMBO_LAYOUT_TITLE) {
            return LineType::where('name', 'combo')->first()->id;
        }
        if ($line['layout'] == OrderLineLayoutConstants::NOTE_LAYOUT_TITLE) {
            return LineType::where('name', 'note')->first()->id;
        }
        if ($line['layout'] == OrderLineLayoutConstants::IMAGE_LAYOUT_TITLE) {
            return LineType::where('name', 'image')->first()->id;
        }
    }

    public static function calculateTotalBeforeDiscount(SalesOrder $salesOrder)
    {
        $orderLines = $salesOrder->orderLines;
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

    public static function formatOrderLinesForSalesOrder($orderLines)
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
                    'warehouse_id' => $orderLine['warehouse_id'],
                    'unit_price' => $orderLine['unit_price'],
                    'discount' => $orderLine['discount'],
                    'total' => $orderLine['total'],
                ];
            }

            if ($orderLine['line_type_id'] == $lineTypes['combo']) {
                $id = $orderLine['combo_id'];
                $existingCombo = Combo::find($id);

                $extraData = [
                    'item' => $existingCombo,
                    'description' => $orderLine['description'],
                    'quantity' => $orderLine['combo_quantity'],
                    'unit_price' => $orderLine['combo_unit_price'],
                    'discount' => $orderLine['combo_discount'],
                    'total' => $orderLine['combo_total'],
                ];
            }

            if ($orderLine['line_type_id'] == $lineTypes['image']) {
                $extraData = [
                    'image' => $orderLine['image'],
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
