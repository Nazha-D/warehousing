<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesInvoiceRequests\StoreSalesInvoiceRequest;
use App\Http\Resources\SalesInvoiceResource;
use App\Models\LineType;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\SalesInvoiceService;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
class SalesInvoiceController extends Controller
{
    use ApiResponseTrait;

    private $service;
    public function __construct()
    {
     $this->service=new SalesInvoiceService();
    }
    public function index(Request $request){
        try {
            $this->authorize('viewAny', SalesInvoice::class);
            $user = auth()->user();
            $options = [
                'perPage' => $request->query('perPage'),
                'isPaginated' => $request->query('isPaginated'),
                'search'=> $request->query('search'),
                'exceptStatus'=>$request->query('exceptStatus')
            ];
            //   return SalesOrder::with('orderLines')->get();
            $salesInvoices = $this->service->getAll( $user->company_id, $options);
            $message = 'Sales Invoices are fetched successfully';

            return $this->successResponse(SalesInvoiceResource::collection( $salesInvoices), $message, 200);
        } catch (\Exception $e) {

            return $this->errorResponse($e->getMessage(), 500);
        }

    }
    public function create()
    {
        try {
            $this->authorize('create', SalesInvoice::class);

            $user =auth()->user();

            $data['sales_invoice_number'] = $this->service->generateSalesInvoiceNumber($user->company_id);
          //  $data['price_lists'] =$user->company->pricelists()->get(['id', 'title','code']);
            $data['clients'] =$user->company->clients;
            $data['company_headers'] =$user->company->companyHeaders;
            $data['line_types'] = LineType::all();

            $data['price_lists'] = $user->company->priceLists;

            $data['payment_terms'] = $user->company->paymentTerms;

            $data['commission_methods'] = $user->company->commissionMethods;

            $data['cashing_methods'] =  $user->company->cashingMethods;

            $data['currencies'] =  $user->company->currencies;
            $data['warehouses'] =$user->company->warehouses()->where('active',true)->get();

            $data['salespeople'] =User::where('company_id',$user->company_id)->where('is_salesperson',true)->get();

            //  $data['items'] = ItemResource::collection(ItemService::getAll($user->can('view company'), $user->company_id, ['isPaginated' => false, 'onlyActive' => true]));

//            $data['combos'] = ComboResource::collection(ComboService::getAll($user->can('view company'), $user->company_id, ['isPaginated' => false, 'onlyActive' => true]));



            $message = 'Data needed for sales invoice creation retrieved successfully';

            return $this->successResponse($data, $message, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function show(string $id ,Request $request)
    {
        try {
            $salesInvoice = SalesInvoice::findOrFail($id);
            $this->authorize('view', $salesInvoice);
            $request->merge(['detailed' => true]);
            return $this->successResponse(
                new SalesInvoiceResource($salesInvoice),
                'Data retrieved successfully',
                200
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 200);
        }
    }
    public function store(StoreSalesInvoiceRequest $request)
    {
        try {
            $this->authorize('create', SalesInvoice::class);

            $invoice = $this->service->createFromDeliveryLines($request->validated());

            return $invoice;
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }
        }

    public function cancelInvoice(SalesInvoice $salesInvoice)
    {

try{
    $this->authorize('update', $salesInvoice);

    return $this->service->cancelInvoice($salesInvoice);
    }
catch (\Exception $exception)
{
return $this->errorResponse($exception->getMessage(),500);
}
    }
}
