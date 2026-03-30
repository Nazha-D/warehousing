<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuotationRequests\GetQuotationsByClientRequest;
use App\Http\Requests\QuotationRequests\StoreQuotationRequest;
use App\Http\Requests\QuotationRequests\UpdateQuotationRequest;
use App\Http\Resources\QuotationResource;
use App\Models\CashingMethod;
use App\Models\Client;
use App\Models\CommissionMethod;
use App\Models\DeliveryTerm;
use App\Models\LineType;
use App\Models\PaymentTerm;
use App\Models\PriceList;
use App\Models\Quotation;
use App\Services\ClientService;
use App\Services\CompanyCurrencyService;
use App\services\PriceListService;
use App\Enums\QuotationStatusEnum;
use App\Services\QuotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Access\AuthorizationException;
use App\Traits\ApiResponseTrait;
class QuotationController extends Controller
{
    use ApiResponseTrait;
  private  $service=null;
    public function __construct()
    {
        $this->service=new QuotationService();
    }
    // ==========================
    // 🔹 List Quotations
    // ==========================
    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Quotation::class);
            $user = Auth::user();

            $options = [
                'perPage' => $request->query('per_page'),
                'isPaginated' => $request->query('is_paginated'),
                'search' => $request->query('search'),
                'exceptStatus' => $request->query('except_status'),
                'status' => $request->query('status'),
            ];

            $quotations = QuotationService::getAll($user->can('view all companies'), $user->company_id, $options);

            return $this->successResponse( QuotationResource::collection($quotations),'Got Data successfully',200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ==========================
    // 🔹 Show Single Quotation
    // ==========================
    public function show(string $id ,Request $request)
    {
        try {
            $quotation = Quotation::findOrFail($id);
            $this->authorize('view', $quotation);
            $request->merge(['detailed' => true]);
            return $this->successResponse(
                new QuotationResource($quotation),
                'Quotation retrieved successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    // ==========================
    // 🔹 Create Quotation Data
    // ==========================
    public function create()
    {
        try {
            $this->authorize('create', Quotation::class);
            $user = Auth::user();
            $data['quotation_number']=QuotationService::generateDraftNumber($user->company_id);

            $data['companyHeaders'] =$user->company->companyHeaders()->with('defaultQuotationCurrency')->get();
            $data['clients'] = ClientService::getAll($user->can('view company'), $user->company_id, ['isPaginated' => false]);

            $data['line_types'] = LineType::all();

            $data['price_lists'] = PriceList::with('items')->where('company_id','=',$user->company_id)->get();
            $data['paymentTerms'] = PaymentTerm::where('company_id',$user->company_id)->get();

            $data['deliveryTerms'] = DeliveryTerm::where('company_id', $user->company_id)->get();

            $data['commission_methods'] = CommissionMethod::get(['id','title']);

            $data['cashing_methods'] = CashingMethod::where('company_id','=',$user->company_id)->get(['id','title']);

            $data['currencies'] =$user->company->currencies;

            $data['salespeople'] = $user->company->salespeople()->active()->get();
            $data['abilities'] = [

    'edit_item_price' => $user?->can('quotation.edit_item_price'),
    'edit_item_description' => $user?->can('quotation.edit_item_description'),
             'edit_combo_price' => $user?->can('quotation.edit_combo_price'),
    'edit_combo_description' => $user?->can('quotation.edit_combo_description'),
];
            return $this->successResponse($data, 'Data retrieved for quotation creation', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ==========================
    // 🔹 Store Quotation
    // ==========================
    public function store(StoreQuotationRequest $request)
    {
        DB::beginTransaction();

        try {
//            $this->authorize('create', Quotation::class);
            $user = auth()->user();

            $quotation = $this->service->createQuotation($user, $request->validated());

            DB::commit();

            return $this->successResponse(
             new QuotationResource(  $quotation),
                'Quotation draft created successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ==========================
    // 🔹 Edit Quotation Data
    // ==========================
    public function edit(string $id)
    {
        try {
            $quotation = Quotation::findOrFail($id);
            $this->authorize('update', $quotation);
            $user = Auth::user();

            $data = QuotationService::getDataForEdit($user, $quotation);

            return $this->successResponse($data, 'Data retrieved for quotation edit', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    // ==========================
    // 🔹 Update Quotation
    // ==========================
    public function update(UpdateQuotationRequest $request, string $id)
    {
        DB::beginTransaction();

        try {
            $quotation = Quotation::findOrFail($id);
            $this->authorize('update', $quotation);
            $user = Auth::user();
            if($quotation->status!==QuotationStatusEnum::Draft)
            {
               return $this->errorResponse('Only quotations in Draft status can be modified',422);
            }
            $quotation = $this->service->updateQuotation($user, $quotation, $request->validated());

            DB::commit();

            return $this->successResponse(
                QuotationResource::make($quotation),
                'Quotation updated successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ==========================
    // 🔹 Delete Quotation
    // ==========================
    public function destroy(string $id)
    {
        try {
            $quotation = Quotation::findOrFail($id);
            $this->authorize('delete', $quotation);

            $this->service->deleteQuotation($quotation);

            return $this->successResponse(null, 'Quotation deleted successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // ==========================
    // 🔹 Get Quotations by Client
    // ==========================
    public function getQuotationsByClientId(Request $request, string $clientId)
    {
        try {
            $client = Client::findOrFail($clientId);
            $this->authorize('view', $client);
            $user = Auth::user();

            $quotations = QuotationService::getAllByClient($client, $user->company_id, [
                'perPage' => $request->query('perPage'),
                'isPaginated' => $request->query('isPaginated'),
                'search' => $request->query('search'),
            ]);

            return QuotationResource::collection($quotations);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function changeStatus(Request $request,Quotation $quotation)
    {
        try{
          //  $this->authorize('update',$quotation);
            $user=auth()->user();
            $this->service->changeStatus($quotation,$request->status,$request->reason);
            return $this->successResponse([],'Status Changed Successfully',200);
        }
        catch(\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }
    }
    public function sendMail(Request $request,Quotation $quotation)
    {
        try{
            //  $this->authorize('update',$quotation);
            $user=auth()->user();
            $this->service->sendQuotationEmail($quotation);
            return $this->successResponse([],'Mail sent Successfully',200);
        }
        catch(\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }
    }
}
