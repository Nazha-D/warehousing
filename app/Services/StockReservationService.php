<?php

namespace App\Services;

use App\Models\StockReservation;
use App\Models\SalesOrderLine;
use Illuminate\Support\Facades\DB;
use Exception;

class StockReservationService
{
    /**
     * Create reservation for a SalesOrderLine
     */
    public function reserveForSalesOrderLine(SalesOrderLine $line, float $quantity): StockReservation
    {
        if ($quantity <= 0) {
            throw new Exception("Quantity to reserve must be greater than zero");
        }

        return DB::transaction(function () use ($line, $quantity) {
            return StockReservation::create([
                'company_id' => auth()->user()->company_id,
                'warehouse_id' => $line->warehouse_id,
                'item_id' => $line->item_id,
                'reserved_quantity' => $quantity,
                'source_type' => SalesOrderLine::class,
                'source_id' => $line->id,
                'status' => StockReservation::STATUS_ACTIVE,
            ]);
        });
    }

    /**
     * Consume reservation for delivery
     */
    public function consumeReservation(SalesOrderLine $line, float $quantity): void
    {
        DB::transaction(function () use ($line, $quantity) {
            $reservation = StockReservation::where('source_type', SalesOrderLine::class)
                ->where('source_id', $line->id)
                ->where('status', StockReservation::STATUS_ACTIVE)
                ->first();

            if (!$reservation) {
                throw new Exception("No active reservation found for this line");
            }

            if ($quantity > $reservation->reserved_quantity) {
                throw new Exception("Cannot consume more than reserved quantity");
            }

            $reservation->reserved_quantity -= $quantity;

            if ($reservation->reserved_quantity <= 0) {
                $reservation->status = StockReservation::STATUS_CONSUMED;
            }

            $reservation->save();
        });
    }

    /**
     * Cancel reservation (Sales order cancelled)
     */
    public function cancelReservation(SalesOrderLine $line): void
    {
        StockReservation::where('source_type', SalesOrderLine::class)
            ->where('source_id', $line->id)
            ->where('status', StockReservation::STATUS_ACTIVE)
            ->update(['status' => StockReservation::STATUS_CANCELLED]);
    }

    /**
     * Get total reserved quantity for an item in a warehouse
     */
    public function getReservedQty(int $itemId, int $warehouseId): float
    {
        return (float) StockReservation::where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->where('status', StockReservation::STATUS_ACTIVE)
            ->sum('reserved_quantity');
    }

    /**
     * Check if enough available quantity for an OUT movement
     */
    public function assertCanMoveOut(int $itemId, int $warehouseId, float $requestedQty, float $onHandQty): void
    {
        $reservedQty = $this->getReservedQty($itemId, $warehouseId);
        $effectiveAvailable = $onHandQty - $reservedQty;

        if ($requestedQty > $effectiveAvailable) {
            throw new \Exception("Cannot move out {$requestedQty} units. {$reservedQty} units are reserved for sales.");
        }
    }
}
