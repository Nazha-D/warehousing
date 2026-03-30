<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesOrderRequests\StoreSalesOrderRequest;
use App\Http\Resources\SalesOrderResource;
use App\Models\CashingMethod;
use App\Models\CommissionMethod;
use App\Models\DeliveryTerm;
use App\Models\LineType;
use App\Models\PaymentTerm;
use App\Models\PriceList;
use App\Models\SalesOrder;
use App\Services\ClientService;
use App\Services\SalesOrderService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    use ApiResponseTrait;
    protected SalesOrderService $service;

    public function __construct(SalesOrderService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request){//call the getall in service and pass options parameter


        try {
           $this->authorize('viewAny', SalesOrder::class);
            $user = auth()->user();
            $options = [
                'perPage' => $request->query('perPage'),
                'isPaginated' => $request->query('isPaginated'),
                'search'=> $request->query('search'),
                'exceptStatus'=>$request->query('exceptStatus'),
                'status'=>$request->query('status')

            ];
            //   return SalesOrder::with('orderLines')->get();
            $salesOrders = $this->service->getAll( $user->company_id, $options);
            $message = 'Sales Orders are fetched successfully';
            return $this->successResponse( SalesOrderResource::collection($salesOrders), $message, 200);
        } catch (\Exception $e) {

            return $this->errorResponse($e->getMessage(), 500);
        }

    }

    public function create(): JsonResponse
    {
        try {
            $user=auth()->user();
            $this->authorize('create',SalesOrder::class);
            $data['sales_order_number']=$this->service->generateNumber($user->company_id);

         //   $data['companyHeaders'] =$user->company->companyHeaders()->with('defaultQuotationCurrency')->get();
            $data['clients'] = ClientService::getAll($user->can('view company'), $user->company_id, ['isPaginated' => false]);

            $data['line_types'] = LineType::all();

            $data['price_lists'] = PriceList::with('items')->where('company_id','=',$user->company_id)->get();
            $data['paymentTerms'] = PaymentTerm::where('company_id',$user->company_id)->get();

           // $data['deliveryTerms'] = DeliveryTerm::where('company_id', $user->company_id)->get();

            $data['commission_methods'] = CommissionMethod::get(['id','title']);

            $data['cashing_methods'] = CashingMethod::where('company_id','=',$user->company_id)->get(['id','title']);

            $data['currencies'] =$user->company->currencies;
            $data['warehouses'] =$user->company->warehouses()->where('active',true)->get();

            $data['salespeople'] = $user->company->salespeople()->active()->get();
            $data['abilities'] = [

                'edit_item_price' => $user?->can('sales_order.edit_item_price'),
    'edit_item_description' => $user?->can('sales_order.edit_item_description'),
             'edit_combo_price' => $user?->can('sales_order.edit_combo_price'),
    'edit_combo_description' => $user?->can('sales_order.edit_combo_description'),
];
          return  $this->successResponse($data,'Got Data Successfully!',200);
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }

    }

    public function show(SalesOrder $order,Request $request): JsonResponse
    {
        try {
            $this->authorize('view',$order);
            $request->merge(['detailed'=>true]);
            return $this->successResponse(new SalesOrderResource($order->load('lines.item')), 'Got data Successfully', 200);

        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }

        }

    public function store(StoreSalesOrderRequest $request)
    {
try {
    $this->authorize('create',SalesOrder::class);
    $order = $this->service->create($request->validated());
      //  return $order->lines->last()->combo->with('items')->first();
    return $this->successResponse($order,'Created Successfully!', 201);
}
catch(\Exception $exception)
{
    return $this->errorResponse($exception->getMessage(),500);
}
    }

    public function cancel(SalesOrder $order): JsonResponse
    {
        try{
        $order = $this->service->cancel($order);
        return $this->successResponse($order,'Order Cancelled Successfully');
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }
    }

    public function updateLineQty(SalesOrder $order, int $lineId, float $newQty): JsonResponse
    {
        $line = $order->lines()->findOrFail($lineId);
        $updatedLine = $this->service->updateLineQty($line, $newQty);
        return response()->json($updatedLine);
    }
}
