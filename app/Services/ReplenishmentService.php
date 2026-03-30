<?php

namespace App\Services;

use App\Constants\ReplenishmentConstants;
use App\Models\Item;
use App\Models\Package;
use App\Models\Replenishment;
use App\Models\ReplenishmentLine;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\PackageQuantityCalculator;

use DomainException;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use App\Exceptions\BusinessException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ReplenishmentService
{
    public static function getAll($userCompanyId, $options = [])
    {
        $perPageDefault = 10;
        $isPaginatedDefault = true;
        $numberDefault = null;
        $warehouseIdDefault = null;

        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginatedDefault);
        $replenishmentNumber = $options['search'] ?? $numberDefault;

        $query = Replenishment::query()
            ->whereHas('warehouse', function ($query) use ($userCompanyId) {
                $query->where('company_id', $userCompanyId);
            }
            );


        // 🔎 Search by replenishment number
        if ($replenishmentNumber) {
            $query->where('replenishment_number', 'like', "%{$replenishmentNumber}%");
        }


        // 📄 Pagination
        if ($isPaginated) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }
    /* ================= Create ================= */

    /**
     * Create a new replenishment document
     */
    public function create(array $data): Replenishment
    {
        return DB::transaction(function () use ($data) {

            $warehouse = Warehouse::findOrFail($data['warehouse_id']);

            // Generate unique number
            $data['replenishment_number'] = $this->generateNumber();

            return Replenishment::create($data);
        });
    }

    /* ================= Lines ================= */

    public function addLine(Replenishment $replenishment, array $data): ReplenishmentLine
    {
        $this->ensureDraft($replenishment);

        return $replenishment->lines()->create($data);
    }

    public function updateLine(ReplenishmentLine $line, array $data): ReplenishmentLine
    {
        $this->ensureDraft($line->replenishment);

        $line->update($data);

        return $line;
    }

    public function removeLine(ReplenishmentLine $line): void
    {
        $this->ensureDraft($line->replenishment);

        $line->delete();
    }

    /* ================= Confirm ================= */

    public function confirm(Replenishment $replenishment): void
    {
        if ($replenishment->isConfirmed()) {
            return;
        }

        $this->ensureDraft($replenishment);

        DB::transaction(function () use ($replenishment) {

            foreach ($replenishment->lines as $line) {

                $item = $line->item;
                $package = $line->package; // ممكن null

                $actualQuantity = PackageQuantityCalculator::calculate(
                    $item,
                    $package,
                    $line->quantity
                );

                StockMovement::create([
                    'warehouse_id' => $replenishment->warehouse_id,
                    'item_id' => $line->item_id,
                    'package_id' => $line->package_id,
                    'quantity' => $actualQuantity,
                    'movement_type' => 'replenishment',
                    'reference_type' => ReplenishmentLine::class,
                    'reference_id' => $line->id,
                ]);
            }

            $replenishment->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);
        });
    }

    /* ================= Cancel ================= */

    public function cancel(Replenishment $replenishment): void
    {
        if ($replenishment->status === 'cancelled') {
            return;
        }

        if (!$replenishment->isConfirmed()) {
            throw ValidationException::withMessages([
                'replenishment' => 'Only confirmed replenishments can be cancelled.',
            ]);
        }

        DB::transaction(function () use ($replenishment) {

            $movements = StockMovement::where(
                'reference_type',
                ReplenishmentLine::class
            )->whereIn(
                'reference_id',
                $replenishment->lines->pluck('id')
            )->get();

            foreach ($movements as $movement) {
                StockMovement::create([
                    'warehouse_id' => $movement->warehouse_id,
                    'item_id' => $movement->item_id,
                    'package_id' => $movement->package_id,
                    'quantity' => bcmul($movement->quantity, -1, 4),
                    'movement_type' => 'replenishment_reverse',
                    'reference_type' => Replenishment::class,
                    'reference_id' => $replenishment->id,
                ]);
            }

            $replenishment->update([
                'status' => 'cancelled',
            ]);
        });
    }

    /* ================= Helpers ================= */

    private function ensureDraft(Replenishment $replenishment): void
    {
        if (!$replenishment->isDraft()) {
            throw ValidationException::withMessages([
                'replenishment' => 'This replenishment is not editable.',
            ]);
        }
    }

    /* ================= Number Generation ================= */

    static function generateNumber(): string
    {
        $prefix = implode(
            ReplenishmentConstants::CODE_SEPARATOR,
            [
                ReplenishmentConstants::NUMBER_PREFIX,

            ]
        );

        $last = Replenishment::where(
            'replenishment_number',
            'like',
            $prefix . '%'
        )
            ->whereHas('warehouse', function ($query) {
                $query->where('company_id', '=', auth()->user()->company_id);
            })
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $nextSequence = 1;

        if ($last) {
            $lastSeq = (int)substr(
                $last->replenishment_number,
                -ReplenishmentConstants::NUMBER_MIN_LENGTH
            );
            $nextSequence = $lastSeq + 1;
        }

        $padded = str_pad(
            (string)$nextSequence,
            ReplenishmentConstants::NUMBER_MIN_LENGTH,
            ReplenishmentConstants::NUMBER_PAD_STR,
            STR_PAD_LEFT
        );

        return implode(
            ReplenishmentConstants::CODE_SEPARATOR,
            [
                ReplenishmentConstants::NUMBER_PREFIX,

                $padded
            ]
        );
    }

    public function createAndPost(array $data): Replenishment
    {
        return DB::transaction(function () use ($data) {

            $warehouse = Warehouse::findOrFail($data['warehouse_id']);


            $replenishment = Replenishment::create([
                'warehouse_id' => $warehouse->id,
                'currency_id' => $data['currency_id'],
                'manual_reference' => $data['manual_reference'] ?? null,
                'date' => $data['date'],
                'replenishment_number' => $this->generateNumber(),
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'created_by' => auth()->user()->id
            ]);

            foreach ($data['items'] as $itemData) {

                $line = $replenishment->lines()->create([
                    'item_id' => $itemData['item_id'],
                    'package_id' => $itemData['package_id'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $itemData['unit_cost'],
                    'notes' => $itemData['notes']
                ]);

                $actualQuantity = PackageQuantityCalculator::calculate(
                    $line->item,
                    $line->package_id,
                    $line->quantity
                );

                StockMovement::create([
                    'company_id' => auth()->user()->id,
                    'warehouse_id' => $warehouse->id,
                    'item_id' => $line->item_id,
                    'package_id' => $line->package_id,
                    'quantity' => $actualQuantity,
                    'type' => 'replenishment',
                    'reference_type' => Replenishment::class,
                    'reference_id' => $line->id,

                ]);
            }

            return $replenishment;
        });
    }

    private function reverseStock(Replenishment $replenishment): void
    {
        foreach ($replenishment->stockMovements as $movement) {

            StockMovement::create([
                'company_id' => $movement->company_id,
                'warehouse_id' => $movement->warehouse_id,
                'item_id' => $movement->item_id,
                'quantity' => $movement->quantity * -1,
                'type' => 'replenishment_reversal',
                'reference_id' => $replenishment->id,
                'reference_type' => Replenishment::class,
                'reversed_from_id' => $movement->id,
                'reference_line_id' => $movement->id,

            ]);
        }
    }

    private function applyStock(Replenishment $replenishment): void
    {
        foreach ($replenishment->lines as $line) {
            $actualQuantity = PackageQuantityCalculator::calculate(
                $line->item,
                $line->package_id,
                $line->quantity
            );
            StockMovement::create([
                'company_id' => $replenishment->warehouse->company_id,
                'warehouse_id' => $replenishment->warehouse_id,
                'item_id' => $line->item_id,
                'quantity' => $actualQuantity,
                'type' => 'replenishment',
                'reference_id' => $replenishment->id,
                'reference_type' => Replenishment::class,
                'reference_line_id' => $line->id,
            ]);
        }
    }

    public function update(Replenishment $replenishment, array $data): Replenishment
    {
        $this->ensureCanEdit($replenishment);
try {
    return DB::transaction(function () use ($replenishment, $data) {

        // 1️⃣ Reverse old stock if exists

        if ($replenishment->hasStockMovements()) {
            $this->reverseStock($replenishment);
        }

        // 2️⃣ Update header
        $replenishment->update(
            $this->editableFields($data)
        );

        // 3️⃣ Replace lines (clean approach)
        $replenishment->lines()->delete();

        foreach ($data['items'] as $item) {

            $replenishment->lines()->create([
                'item_id' => $item['item_id'],
                'package_id' => $item['package_id'],
                'quantity' => $item['quantity'],
                'unit_cost' => $item['unit_cost'],
                'notes' => $item['notes']
            ]);
        }

        // 4️⃣ Apply new stock
        $this->applyStock($replenishment);

        return $replenishment->load(['lines', 'warehouse']);
    });
}
catch (QueryException $e) {


    $isDuplicate = $e->errorInfo[1] == 1062;
    $isStockMovementUnique =
        str_contains($e->getMessage(), 'stock_movements_ref_unique');

    if ($isDuplicate && $isStockMovementUnique) {
        throw new ConflictHttpException(
            'This replenishment already affected stock and cannot be modified again.'
        ,null,422);
    }

    throw $e;
}
    }

    private function editableFields(array $data): array
    {
        return Arr::only($data, [
            'currency_id',
            'warehouse_id',
            'unit_cost',
            'notes',
            'manual_reference'
        ]);
    }

    private function ensureCanEdit(Replenishment $replenishment): void
    {
        if (!$replenishment->created_at->isSameDay(now())) {
            throw new DomainException(
                'Replenishment can only be edited on the same day it was created.'
            );
        }
    }

    public static function getPaginatedForWarehouse(
        int $companyId,
        int $warehouseId,
        array $options = []
    ) {
        $perPageDefault = 25;
        $perPageMax = 100;

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
      $items = $query->paginate($perPage);

        $itemIds = $items->pluck('id');

        $quantities = StockMovement::query()
            ->where('company_id', $companyId)
            ->where('warehouse_id', $warehouseId)
            ->whereIn('item_id', $itemIds)
            ->groupBy('item_id')
            ->selectRaw('item_id, SUM(quantity) as available_qty')
            ->pluck('available_qty', 'item_id');

        $items->getCollection()->transform(function ($item) use ($quantities) {
            return [
                'id' => $item->id,
                'main_code' => $item->main_code,
                'unit_cost'=>$item->unit_cost,
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
                'available_qty_at_warehouse' => (float) ($quantities[$item->id] ?? 0),
            ];
        });

        return $items;
    }

}
