<?php

namespace App\Http\Controllers;

use App\Http\Requests\PosInvoiceRequests\AddPosPaymentRequest;
use App\Http\Requests\PosInvoiceRequests\CreatePosInvoiceRequest;
use App\Http\Resources\PosInvoiceResource;
use App\Models\PosInvoice;
use App\Services\PosInvoiceService;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
class PosInvoiceController extends Controller
{
    use ApiResponseTrait;

    protected PosInvoiceService $service;

    public function __construct()
    {
        $this->service=new PosInvoiceService();
    }


    public function index(Request $request)
    {
      //  return $this->service->generateInvoiceNumber(1);
        try {
             $this->authorize('viewAny', PosInvoice::class);
            $user = auth()->user();
            $options = [
                'perPage' => $request->query('perPage'),
                'isPaginated' => $request->query('isPaginated'),
                'search' => $request->query('search'),
                'searchByStatus'=> $request->query('searchByStatus'),
                'cashCustomers' => $request->query('cashCustomers'),
            ];
            $orders = $this->service->getAll( $user->company_id, $options);

            return $this->successResponse( PosInvoiceResource::collection($orders), 'Orders are here', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function show(Request $request,PosInvoice $posInvoice)
    {
        //  return $this->service->generateInvoiceNumber(1);
        try {
            $this->authorize('view', $posInvoice);
            $user = auth()->user();
            $request->merge(['detailed'=>true]);
           $posInvoice->load(['user','client','currency','payments'
               ,'lines','lines.item','lines.discount','payments.currency',
               'payments.cashingMethod','car','finishedByUser']);
            return $this->successResponse( new PosInvoiceResource($posInvoice), 'Data is here', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(CreatePosInvoiceRequest $request)
    {
        try{
            $this->authorize('create',PosInvoice::class);
            $invoice=$this->service->create($request->validated());
            return $this->successResponse( $invoice,'Created Successfully!',201);
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }
    }
    public function addPayment(AddPosPaymentRequest $request)
    {
        try {

            $invoice = PosInvoice::find($request->pos_invoice_id);
            $this->authorize('update', $invoice);
            $invoice = $this->service->addPayments(
                $request->validated()
            );

            return $this->successResponse([],'Payment Done Successfully!',200);
        }
        catch (\Exception $exception)
        {
            return  $this->errorResponse($exception->getMessage(),500);
        }
    }

}
