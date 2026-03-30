<?php

namespace App\Services;

use App\Constants\TransferConstants;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Warehouse;
use App\Models\Item;
use App\Models\Package;
use App\Models\StockMovement;
use App\Services\PackageQuantityCalculator;
use App\Services\StockReservationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
//use App\Services\PackageQuantityCalculator;

class TransferService
{
    protected  $stockReservationService=null;
    public function __construct()
    {
        $this->stockReservationService=new StockReservationService();
    }

    public static function getAll( $userCompanyId, $options = [])
    {
        $perPageDefault = 10;
        $isPaginatedDefault = true;
        $numberDefault = null;


        $perPage     = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginatedDefault);
        $transferNumber= $options['search'] ?? $numberDefault;

        $query = Transfer::query()->with(['sendingUser','receivingUser'])
            ->where('company_id', $userCompanyId);






        if ($transferNumber) {
            $query->where('transfer_number', 'like', "%{$transferNumber}%");
        }




        if ($isPaginated) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }
    /**
     * Create transfer and immediately send it (transfer_out)
     */
    public function createAndSend(array $data): Transfer
    {
        return DB::transaction(function () use ($data) {

            $srcWarehouse  = Warehouse::findOrFail($data['src_warehouse_id']);
            $destWarehouse = Warehouse::findOrFail($data['dest_warehouse_id']);

            if ($srcWarehouse->id === $destWarehouse->id) {
                throw ValidationException::withMessages([
                    'warehouse' => 'Source and destination warehouses must be different.',
                ]);
            }

            // Prevent duplicate items
            $itemIds = collect($data['items'])->pluck('item_id');
            if ($itemIds->duplicates()->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Duplicate items are not allowed in the same transfer.',
                ]);
            }

            $transfer = Transfer::create([
                'company_id'        => auth()->user()->company_id,
                'sending_user_id'   =>auth()->user()->id,
                'date'              => $data['date'],
                'manual_reference'  => $data['manual_reference'] ?? null,
                'src_warehouse_id'  => $srcWarehouse->id,
                'dest_warehouse_id' => $destWarehouse->id,
                'transfer_number'   => $this->generateNumber(),
                'status'            => 'sent',
                'sent_at'           => now(),
            ]);

            foreach ($data['items'] as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                $availableQty = $this->getAvailableStock(
                    $srcWarehouse->id,
                    $item->id
                );
                $actualQty = PackageQuantityCalculator::calculate(
                    $item,
                    $itemData['package_id'],
                    $itemData['qty_to_transfer']
                );
                $this->stockReservationService->assertCanMoveOut($itemData['item_id'],  $srcWarehouse->id, $actualQty, $availableQty);

                if ($actualQty > $availableQty) {
                    throw ValidationException::withMessages([
                        'quantity' => "Insufficient stock for item {$item->name} in source warehouse.",
                    ]);
                }

                $line = $transfer->items()->create([
                    'item_id'                    => $item->id,
                    'item_description'           => $item->description,
                    'transferred_qty'            => $itemData['qty_to_transfer'],
                    'transferred_qty_package_id' =>  $itemData['package_id'],
                    'note'                       => $itemData['note'] ?? null,
                ]);

                StockMovement::create([
                    'company_id'     => $transfer->company_id,
                    'warehouse_id'   => $srcWarehouse->id,
                    'item_id'        => $item->id,
                    'package_id'     => $itemData['package_id'],
                    'quantity'       => -1 * $actualQty,
                    'type'           => 'transfer_out',
                    'reference_type' => Transfer::class,
                    'reference_id'   => $line->id,
                ]);
            }

            return $transfer->load('items');
        });
    }

    /**
     * Receive full transfer (transfer_in)
     */
    public function receive(Transfer $transfer, array $data):Transfer
    {
        if ($transfer->status !== 'sent') {
            throw ValidationException::withMessages([
                'transfer' => 'Only sent transfers can be received.',
            ]);
        }

        return DB::transaction(function () use ($transfer, $data) {

            $receivedItems = collect($data['received_items']);

            if ($receivedItems->count() !== $transfer->items()->count()) {
                throw ValidationException::withMessages([
                    'received_items' => 'All transfer items must be received.',
                ]);
            }

            foreach ($transfer->items as $itemLine) {

                $receivedLine = $receivedItems->firstWhere(
                    'transfer_item_id',
                    $itemLine->item_id
                );

                if (!$receivedLine) {
                    throw ValidationException::withMessages([
                        'received_items' => "Missing received quantity for item {$itemLine->item_description}.",
                    ]);
                }

                $itemLine->update([
                    'received_qty'            => $receivedLine['received_qty'],
                    'received_qty_package_id' => $receivedLine['package_id'],
                ]);

                $actualQty = PackageQuantityCalculator::calculate(
                    $itemLine->item,
                    $receivedLine['package_id'],
                    $receivedLine['received_qty']
                );

                StockMovement::create([
                    'company_id'     => $transfer->company_id,
                    'warehouse_id'   => $transfer->dest_warehouse_id,
                    'item_id'        => $itemLine->item_id,
                    'package_id'     =>  $receivedLine['package_id'],
                    'quantity'       => $actualQty,
                    'type'           => 'transfer_in',
                    'reference_type' => Transfer::class,
                    'reference_id'   => $itemLine->id,
                ]);
            }

            $transfer->update([
                'status'      => 'received',
                'received_at' => now(),
                'receiving_user_id'=>auth()->user()->id
            ]);

            return $transfer->fresh(['items']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */


    protected function getAvailableStock(int $warehouseId, int $itemId): float
    {
        return StockMovement::where('warehouse_id', $warehouseId)
            ->where('item_id', $itemId)
            ->sum('quantity');
    }

    static function generateNumber(): string
    {
        $year = now()->format('y'); // 26

        $prefix = implode(
            TransferConstants::CODE_SEPARATOR,
            [
                TransferConstants::NUMBER_PREFIX . $year
            ]
        );

        $last = Transfer::where('transfer_number', 'like', $prefix . '%')
            ->where('company_id', auth()->user()->company_id)
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $nextSequence = 1;

        if ($last) {
            $lastSeq = (int) substr(
                $last->transfer_number,
                -TransferConstants::NUMBER_MIN_LENGTH
            );

            $nextSequence = $lastSeq + 1;
        }

        $padded = str_pad(
            (string) $nextSequence,
            TransferConstants::NUMBER_MIN_LENGTH,
            TransferConstants::NUMBER_PAD_STR,
            STR_PAD_LEFT
        );

        return implode(
            TransferConstants::CODE_SEPARATOR,
            [
                TransferConstants::NUMBER_PREFIX . $year,
                $padded
            ]
        );
    }

    public static function getPaginatedForWarehouse(
        int $companyId,
        int $warehouseId,
        array $options = []
    ) {
        $perPageDefault = 25;
        $perPageMax = 100;
        $isPaginated = !empty($options['isPaginated']) && $options['isPaginated'] == 1;
        $perPage = min(
            $options['perPage'] ?? $perPageDefault,
            $perPageMax
        );

        $query = Item::query()
            ->with('defaultTransactionPackage')
            ->where('company_id', $companyId)
            ->where('active', true);

        // 🔎 Search
        if (!empty($options['search'])) {
            $search = $options['search'];
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('main_code', 'like', "%{$search}%");
            });
        }
        if (!empty($options['code'])) {
            $code = $options['code'];
            $query->whereHas('barCodes',function ($q) use ($code) {
                $q->where('code', 'like', "%{$code}%");
                    })
                    ->orWhereHas('alternativeCodes',function ($q) use ($code) {
                        $q->where('code', 'like', "%{$code}%");
                            })
                            ->orWhereHas('supplierCodes', function ($q) use ($code) {
                                $q->where('code', 'like', "%{$code}%");
                            });}

        // 🗂️ Category filter
        if (!empty($options['category_id'])) {
            $query->where('category_id', $options['category_id']);
        }

        $items = $isPaginated ? $query->paginate($perPage) : $query->get();

        $itemIds = $items->pluck('id');

        $quantities = StockMovement::query()
            ->where('company_id', $companyId)
            ->where('warehouse_id', $warehouseId)
            ->whereIn('item_id', $itemIds)
            ->groupBy('item_id')
            ->selectRaw('item_id, SUM(quantity) as available_qty')
            ->pluck('available_qty', 'item_id');
        $itemsCollection = $items instanceof \Illuminate\Pagination\AbstractPaginator
            ? $items->getCollection()
            : $items;

        $items->transform(function ($item) use ($quantities) {
            return [
                'id' => $item->id,
                'main_code' => $item->main_code,
              //  'unit_cost'=>$item->unit_cost,
                'item_name' => $item->item_name,
                'main_description'=> $item->main_description,
                'package_id'=> $item->package_id,
                'unit_name' => $item->package_unit_name,
                'unit_quantity' => $item->package_unit_quantity,
                'set_name' => $item->package_set_name,
                'set_quantity' => $item->package_set_quantity,
                'superset_name' => $item->package_superset_name,
                'superset_quantity' => $item->package_superset_quantity,
                'palette_name' => $item->package_palette_name,
                'palette_quantity' => $item->package_palette_quantity,
                'container_name' => $item->package_container_name,
                'container_quantity' => $item->package_container_quantity,
                'default_transaction_package'=>$item->defaultTransactionPackage,
                'qty_on_hand' => (float) ($quantities[$item->id] ?? 0),
            ];
        });
        if ($items instanceof \Illuminate\Pagination\AbstractPaginator) {
            $items->setCollection($itemsCollection);
        } else {
            $items = $itemsCollection;
        }

        return $items;
    }

}
