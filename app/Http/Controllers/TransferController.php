<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequests\StoreTransferRequest;
use App\Http\Requests\TransferRequests\ReceiveTransferRequest;
use App\Models\Warehouse;
use App\Services\TransferService;
use App\Models\Transfer;
use Illuminate\Http\JsonResponse;
use \App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class TransferController extends Controller
{
  use  ApiResponseTrait;

    protected TransferService $service;

    public function __construct(TransferService $service)
    {
        $this->service = $service;
    }
    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Transfer::class);

            $user = auth()->user();

            $options = [
                'perPage'      => $request->query('perPage'),
                'isPaginated'  => $request->query('isPaginated'),
                'search'       => $request->query('search'),

            ];

            $transfers = $this->service->getAll(
                $user->company_id,
                $options
            );

            return $this->successResponse(
                $transfers,
                'Transfers fetched successfully',
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
     * Show single transfer
     */
    public function create()
    {
        try {
            $this->authorize('create', Transfer::class);
            $user = auth()->user();
            $data['transfer_number'] = TransferService::generateNumber();
            $data['warehouses'] = Warehouse::where('company_id', '=', $user->company_id)->get(['id', 'warehouse_number', 'name']);
            $message = 'Got Data for transfer creation successfully';
            return $this->successResponse($data,
                $message,
                200);
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }
        }



    /**
     * Show single transfer
     */
    public function show(Transfer $transfer): JsonResponse
    {
        try {
            $this->authorize('view', $transfer);

            $transfer->load([
                'items.item:id,main_code,main_description',
                'items.transferredPackage:id,name',
                'items.receivedPackage:id,name','sendingUser','receivingUser'
            ]);
            return $this->successResponse(
                $transfer,
                'Transfer details retrieved successfully'
            );
        }
        catch(\Exception $exception){
            return $this->errorResponse($exception->getMessage(),500);
        }
    }


    /**
     * Create and send transfer
     */
    public function store(StoreTransferRequest $request): JsonResponse
    {
        try {
            $this->authorize('create', Transfer::class);

            $transfer = $this->service->createAndSend($request->validated());

            return $this->successResponse(
                $transfer->load('items'),
                'Transfer created and sent successfully'
            );
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }
    }

    /**
     * Receive transfer
     */
    public function receive(ReceiveTransferRequest $request, Transfer $transfer): JsonResponse
    {
        try{

        $this->authorize('update',$transfer);
        $transfer = $this->service->receive($transfer, $request->validated());

        return $this->successResponse(
            $transfer,
            'Transfer received successfully'
        );
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }
    }
    public function getItemsForTransfer(Request $request)
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

}
