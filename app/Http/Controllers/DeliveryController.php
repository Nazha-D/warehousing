<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeliveryRequests\StoreDeliveryRequest;
use App\Http\Resources\DeliveryResource;
use App\Models\Delivery;
use App\Services\ClientService;
use App\Services\DeliveryService;
use App\Services\DeliveryUIService;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    use ApiResponseTrait;
    protected DeliveryService $service;
    protected DeliveryUIService $uiService;
    public function __construct(DeliveryService $service,DeliveryUiService $uiService)
    {
        $this->service = $service;
        $this->uiService=$uiService;
    }
    public function index(Request $request){//call the getall in service and pass options parameter



        $this->authorize('viewAny', Delivery::class);
            $user = auth()->user();
            $options = [
                'perPage' => $request->query('perPage'),
                'isPaginated' => $request->query('isPaginated'),
                'search'=> $request->query('search'),
                'exceptStatus'=>$request->query('exceptStatus'),
                 'status'=>$request->query('status')
            ];

            $deliveries = DeliveryService::getAll( $user->company_id, $options);
            $message = 'Deliveries are fetched successfully';

            return $this->successResponse( DeliveryResource::collection($deliveries), $message, 200);

    }
    public function create()
    {
        try {
            $this->authorize('create', Delivery::class);
            $user = auth()->user();

        //    $data = $this->uiService->getDeliveryDataForClient($companyId, $clientId);
            $data['clients'] =$user->company->clients ;
            $data['delivery_number'] = $this->service->generateDeliveryNumber($user->company_id);

            return $this->successResponse($data,'Got Data Successfully!');
        }

        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }
    }
    public function getSalesOrdersForDelivery(int $clientId)
    {
        try {
            $this->authorize('create', Delivery::class);
            $companyId = auth()->user()->company_id;

            $data = $this->uiService->getDeliveryDataForClient($companyId, $clientId);


            return $this->successResponse($data,'Got Data Successfully!');
        }
    catch (\Exception $exception)
    {
        return $this->errorResponse($exception->getMessage(),500);
    }}
    public function show(Delivery $delivery,Request $request)
    {
try{
    $this->authorize('view',$delivery);
        $request->merge(['detailed' => true]);

$delivery->load(['deliveryLines','deliveryLines.item']);
$message='Got Data Successfully!';
        return $this->successResponse(new DeliveryResource($delivery),$message,200);
}
catch (\Exception $exception)
{
    return $this->errorResponse($exception->getMessage(),500);
}
}


    /**
     * إنشاء Delivery جديد
     */
    public function store(StoreDeliveryRequest $request): JsonResponse
    {
try{
    $this->authorize('create',Delivery::class);
        $validated = $request->validated();
        $user=auth()->user();
        $delivery = $this->service->createFromSalesOrderLines(
            $user->company_id,
            $validated['client_id'], // client_id للـ DeliveryService
            $request->lines,      // مجموعة SalesOrderLine IDs
            [
                'driver_id'        => $validated['driver_id'] ?? null,
                'reference'        => $validated['reference'] ?? null,
                'date'             => $validated['date'] ?? now()->toDateString(),
                'expected_delivery'=> $validated['expected_delivery'] ?? null,
            ]
        );

        return $this->successResponse(
           $delivery->load('deliveryLines'),
            'Delivery created successfully.',
           201
        );
}
catch (\Exception $exception)
{
    return $this->errorResponse($exception->getMessage(),500);
}
}

    /**
     * إلغاء Delivery
     */
    public function cancel(Delivery $delivery, Request $request): JsonResponse
    {
        try{
            $this->authorize('update',$delivery);
        $this->service->cancel($delivery,$request->query('reason'));

        return $this->successResponse(
           $delivery,
             "Delivery canceled successfully.",
            200
        );
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }
    }

    /**
     * تحويل Delivery من Processing إلى Delivered
     */
    public function deliver(Delivery $delivery): JsonResponse
    {
        try{
            $this->authorize('update',$delivery);
        $this->service->markAsDelivered($delivery);

        return $this->successResponse([
            'success' => true,
            'message' => "Delivery marked as delivered.",
            'delivery' => $delivery->load('deliveryLines'),
        ]);}
        catch (\Exception $exception){
            return $this->errorResponse($exception->getMessage(),500);
        }
    }

    /**
     * إكمال التسليم مع رفع POD
     */
    public function complete(Delivery $delivery,Request $request): JsonResponse
    {
        try{
            $this->authorize('update',$delivery);
        $request->validate([
            'pod_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $path = $request->file('pod_file')->store(
            "deliveries/pod/{$delivery->company_id}/{$delivery->id}",
            'public'
        );
        $this->service->markAsCompleted($delivery, $path);

        return $this->successResponse([
            $delivery,
            "Delivery completed with POD uploaded.",
          200
        ]);
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }
    }

    /**
     * فشل أو رفض التسليم
     */
    public function failOrReject(Delivery $delivery, Request $request): JsonResponse
    {
        try{
            $this->authorize('update',$delivery);
        $this->service->failOrReject($delivery, $request->query('reason'));

        return $this->successResponse(
            $delivery,
             "Delivery marked as Failed or Rejected.",
            200
        );
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }
    }
}
