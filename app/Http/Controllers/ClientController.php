<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarRequests\StoreCarRequest;
use App\Http\Requests\CarRequests\UpdateCarRequest;
use App\Http\Requests\ClientRequests\UpdateClientRequest;
use App\Http\Requests\ClientRequests\StoreClientRequest;

use App\Http\Resources\ClientResource;
use App\Http\Resources\SalesOrderResource;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Services\ClientService;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\DB;
use Validator;
use Symfony\Component\HttpFoundation\Response;

use App\Traits\ApiResponseTrait;
class ClientController extends Controller
{
    use ApiResponseTrait;
    /**
     *
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Client::class);
            $user = auth()->user();
            $options = [
                'perPage' => $request->query('perPage'),
                'isPaginated' => $request->query('isPaginated'),
                'name' => $request->query('name'),
                'posClients' => $request->query('posClients'),

            ];
            $clients = ClientService::getAll($user->can('get all companies'), $user->company_id, $options);
            $message = 'Clients fetched successfully';
            return $this->successResponse($clients, $message, Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return mixed
     */
    public function show(Client $client,Request $request)
    {
        try {
            $request->merge(['detailed' => true]);
           $this->authorize('view',$client);
            $user = auth()->user();
            if ($client->company_id !== $user->company_id) {
                abort(403, 'Unauthorized');
            }
            $client= new ClientResource(
                $client->load([
                    'clientAddresses',
                    'cars',
                    'salesperson',
                    'clientCompany',
                    'company'
                ])
            );

            $message='Client retrieved successfully';
            return $this->successResponse ($client,$message,200);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @return mixed
     */
    public function create()
    {
        try {
            $this->authorize('create', Client::class);
            $user = \Auth::user();

            $data['clientNumber'] = ClientService::generateClientNumber($user->company_id);
            $data['clientCompanies'] = Client::where('type', 'company')
                ->where('company_id', $user->company_id)
                ->get(['id', 'name']);

            $data['salespeople'] = $user->company->salespeople()->get(['id', 'name']);
           // $data['paymentTerms'] = $user->company->paymentTerms()->get(['id', 'title']);
           // $data['priceLists'] = $user->company->pricelists()->get(['id', 'title','code']);
            $data['taxes'] = $user->company->taxationGroups()->get(['id', 'name']);
            $message = 'Data needed for client creation retrieved successfully';

            return $this->successResponse($data, $message, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreClientRequest $request)
    {
        try {
            $this->authorize('create', Client::class);

            $user = auth()->user();

            // 1️⃣ خذ فقط البيانات الموثوقة
            $data = $request->validated();

            // 2️⃣ الحالات الخاصة
            $data['company_id'] = $user->company_id;

            if (empty($data['client_number'])) {
                $data['client_number'] = ClientService::generateClientNumber($user->company_id);
                $data['auto_generated_number'] = true;
            } else {
                $data['auto_generated_number'] = false;
            }

            // 3️⃣ علاقات
            $addresses = $request->input('addresses', []);
            $cars      = $request->input('cars', []);

            // 4️⃣ Cars validation (كما هو)
            $validatedCars = [];
            foreach ($cars as $car) {
                $validator = Validator::make(
                    $car,
                    (new StoreCarRequest())->rules()
                );

                if ($validator->fails()) {
                    return $this->errorResponse($validator->errors()->first(), 422);
                }

                $validatedCars[] = $validator->validated();
            }

            // 5️⃣ إنشاء العميل
            $client = ClientService::createClientWithRelations(
                $data,
                $addresses,
                $validatedCars
            );

            return $this->successResponse(
                $client,
                'Client created successfully',
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @return mixed
     */
    public function edit(string $id)
    {
        try {
            $client = Client::find($id);
            if (! $client) {
                return $this->errorResponse('Client not found.', Response::HTTP_NOT_FOUND);
            }
            $this->authorize('update', $client);
            $user = \Auth::user();
            $data['client'] = $client;

            $data['clientCompanies'] = Client::where('type', 'company')
                ->where('company_id', $user->company_id)
                ->get(['id', 'name']);
            $data['salespeople'] = $user->company->salespeople()->get(['id', 'name']);
            $message = 'Data needed for client edit retrieved successfully';

            return $this->successResponse($data, $message, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function update(UpdateClientRequest $request, $id)
    {

        DB::beginTransaction();

        try {
            $client = Client::where('company_id', auth()->user()->company_id)
                ->find($id);

            if (! $client) {
                return $this->errorResponse(
                    'Client not found.',
                    Response::HTTP_NOT_FOUND
                );
            }

            $this->authorize('update', $client);

            $validated = $request->validated();

            // فصل العلاقات عن بيانات الكلاينت
            $addresses = $validated['addresses'] ?? [];
            $cars      = $validated['cars'] ?? [];

            unset($validated['addresses'], $validated['cars']);

            $client = ClientService::updateClientWithRelations(
               $client,
           $validated,
         $addresses,
             $cars
        );

        DB::commit();

        return $this->successResponse(
            $client->fresh(['clientAddresses', 'cars']),
            'Client updated successfully',
            Response::HTTP_OK
        );

    } catch (\Throwable $e) {
            DB::rollBack();

            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @return mixed
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            $client = Client::find($id);
            if (! $client) {
                return $this->errorResponse('Client not found.', Response::HTTP_NOT_FOUND);
            }
            if($client->is_cash_customer===true)
            {
                return $this->errorResponse('Client can not be deleted ... it is a cash customer.', Response::HTTP_CONFLICT);
            }
            $this->authorize('delete', $client);
            $client->cars()->delete();
            $client->clientAddresses()->delete();

            $client->delete();
            DB::commit();
            $message = 'Client deleted successfully.';

            return $this->successResponse(null, $message, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getClientTransactions($clientId)
    {

        if(!Client::find($clientId))  return $this->errorResponse('Client Not Found',Response::HTTP_CONFLICT);
        $transactions=ClientService::getClientTransactions($clientId);
        $message='Got Transactions Successfully';
        return $this->successResponse($transactions,$message,Response::HTTP_OK);
    }
//    public function getClientSalesOrders($clientId,Request $request)
//    {
//        $options = [
//            'perPage' => $request->query('perPage'),
//            'isPaginated' => $request->query('isPaginated'),
//            'name' => $request->query('status')
//        ];
//        $salesOrders=ClientService::getClientSalesOrders($clientId,$options);
//        return SalesOrderResource::collection($salesOrders);
//    }

}
