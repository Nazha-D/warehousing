<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseRequests\StoreWarehouseRequest;
use App\Http\Requests\WarehouseRequests\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    use ApiResponseTrait;

    /* =========================
     *  LIST
     * ========================= */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Warehouse::class);

        $user = auth()->user();
        $options['isPaginated']=$request->query('isPaginated');
        $options['perPage']=$request->query('perPage');
        $options['search']=$request->query('search');
        $warehouses = WarehouseService::getAll(

            $user->company_id,
            $options
        );
        $message='Warehouses fetched successfully';
        return $this->successResponse(WarehouseResource::collection($warehouses)
                                       ,$message,200);
    }

    /* =========================
     *  CREATE (Frontend helper)
     * ========================= */
    public function create()
    {
        $this->authorize('create', Warehouse::class);

        $companyId = auth()->user()->company_id;

        return $this->successResponse([
            'warehouse_number' =>
                WarehouseService::generateWarehouseNumber($companyId),
        ]);
    }

    /* =========================
     *  STORE
     * ========================= */
    public function store(StoreWarehouseRequest $request)
    {
        $this->authorize('create', Warehouse::class);

        DB::beginTransaction();

        try {
            $data = $request->validated();

           $companyId=auth()->user()->company_id;
            $data['warehouse_number'] =
                WarehouseService::generateWarehouseNumber($companyId);
            $data['company_id'] =$companyId;
            $warehouse = Warehouse::create($data);

            DB::commit();

            return $this->successResponse($warehouse, 'Warehouse created successfully');
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->errorResponse(
                $e->getMessage(),
                500

            );
        }
    }

    /* =========================
     *  SHOW
     * ========================= */
    public function show(Warehouse $warehouse,Request $request)
    {

        $this->authorize('view', $warehouse);
        $request['detailed']=1;
        $message='Got warehouse successfully';
        return $this->successResponse(new WarehouseResource($warehouse),$message,200);
    }

    /* =========================
     *  UPDATE
     * ========================= */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {

        $this->authorize('update', $warehouse);

        DB::beginTransaction();

        try {
            $warehouse->update($request->validated());

            DB::commit();

            return $this->successResponse($warehouse, 'Warehouse updated successfully');
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->errorResponse(
                $e->getMessage(),
                500

            );
        }
    }

    /* =========================
     *  DELETE
     * ========================= */
    public function destroy(Warehouse $warehouse)
    {


        $this->authorize('delete', $warehouse);

        DB::beginTransaction();

        try {
            if($warehouse->items()->exists())
                return $this->errorResponse(
                    'Warehouse contains items and cannot be deleted',
                    500

                );
            $warehouse->delete();

            DB::commit();

            return $this->successResponse(null, 'Warehouse deleted successfully');
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->errorResponse(
                $e->getMessage(),
                500

            );
        }
    }
}
