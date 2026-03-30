<?php

namespace App\Http\Controllers;


use App\Http\Requests\ReplenishmentRequests\StoreReplenishmentWithItemsRequest;
use App\Http\Requests\ReplenishmentRequests\UpdateReplenishmentRequest;
use App\Models\Replenishment;
use App\Models\Warehouse;
use App\Services\ReplenishmentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReplenishmentController extends Controller
{ use ApiResponseTrait;
    protected ReplenishmentService $service;
    public function __construct()
    {
        $this->service=new ReplenishmentService();
    }

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Replenishment::class);

            $user = auth()->user();

            $options = [
                'perPage'      => $request->query('perPage'),
                'isPaginated'  => $request->query('isPaginated'),
                'search'       => $request->query('search'),

            ];

            $replenishments = ReplenishmentService::getAll(
                $user->company_id,
                $options
            );

            return $this->successResponse(
                $replenishments,
                'Replenishments fetched successfully',
                200
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    public function show(Replenishment $replenishment)
    {
        try {
            $this->authorize('view', $replenishment);

            $replenishment->load([
                'warehouse',
                'lines.item',
            ]);

            return $this->successResponse(
                $replenishment,
                'Replenishment fetched successfully',
                200
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }
/**
 * Create replenishment with items (auto confirmed)
 */
public function storeWithItems(StoreReplenishmentWithItemsRequest $request)
{
    try {
        $this->authorize('create', Replenishment::class);
        $replenishment = $this->service->createAndPost(
            $request->validated()
        );
        $message = 'Replenishment created successfully';
        return $this->successResponse(
            $replenishment->load('lines'),
            $message,
            201);
    }
    catch(\Exception $exception){
       return  $this->errorResponse($exception->getMessage(),500);
    }
}

  public function create()
  {
      $this->authorize('create',Replenishment::class);
      $user=auth()->user();
      $data['replenishment_number']=ReplenishmentService::generateNumber();
      $data['warehouses']=Warehouse::active()->where('company_id','=',$user->company_id)->get(['id','warehouse_number','name']);
      $data['currencies']=$user->company->currencies()->get(['currency_id','code']);
      $message='Got data for replenishment creation successfully';
      return $this->successResponse($data,$message,200);
  }

  public function getItemsForReplenishment(Request $request)
  {
      $companyId = auth()->user()->company_id;

      $warehouseId = $request->get('warehouse_id');

      if (!$warehouseId) {
          return $this->errorResponse('warehouse_id is required', 422);
      }

      $items = $this->service->getPaginatedForWarehouse(
          $companyId,
          $warehouseId,
          $request->query()
      );

      return $this->successResponse($items,'',200);
  }

    public function update(
        UpdateReplenishmentRequest $request,
        Replenishment $replenishment
    ) {
        try {
            $this->authorize('update',$replenishment);
            $updated = app(ReplenishmentService::class)
                ->update($replenishment, $request->validated());

            return $this->successResponse(
              $updated, 'Replenishment updated successfully',
           200);

        } catch (\DomainException $e) {

            return $this->errorResponse(
                $e->getMessage(),
             422  );

        } catch (\Exception $e) {

            return $this->errorResponse(
                $e->getMessage(),500);
        }
    }
}
