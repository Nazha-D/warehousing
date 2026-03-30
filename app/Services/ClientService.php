<?php

namespace App\Services;

use App\Constants\ClientConstants;
use App\Http\Resources\OrderResource;
use App\Http\Resources\QuotationResource;
use App\Models\Client;
use App\Models\User;

use Illuminate\Support\Facades\DB;

class ClientService
{
    public static function getAll($canViewAllCompanies, $userCompanyId, $options = [])
    {
        $perPageDefault = 10;
        $isPaginated = true;
        $nameDefault = '';
        $posClientsDefault=false;

        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $name = $options['name'] ?? $nameDefault;
        $posClients = $options['posClients'] ?? $posClientsDefault;

        $clients = Client::active()->withoutTrashed();
        if ($canViewAllCompanies) {
            $clients->filter($name);
            if($posClients==true)
                $clients->pos();

            if ($isPaginated) {
                $clients = $clients->paginate($perPage);
            } else {
                $clients = $clients->get();
            }
        } else {
            $clients = $clients->where('company_id', $userCompanyId)->filter($name);
            if($posClients==true)
                $clients->pos();
            if ($isPaginated) {
                $clients = $clients->paginate($perPage);
            } else {
                $clients = $clients->get();
            }
        }

        return $clients;
    }


    public static function generateClientNumber($companyId)
    {
        $prefixLength = strlen(ClientConstants::NUMBER_PREFIX);

        $latestClient = Client::where('company_id', $companyId)
            ->where('auto_generated_number', true)
            ->whereNotNull('client_number')
            ->withTrashed()
            ->selectRaw("MAX(CAST(SUBSTRING(client_number, ? + 1) AS UNSIGNED)) as max_number", [$prefixLength])
            ->first();
//        return Client::where('company_id', $companyId)
//            ->where('auto_generated_number', true)
//            ->whereNotNull('client_number')
//            ->withTrashed()->selectRaw("MAX(CAST(SUBSTRING(client_number, ? + 1) AS UNSIGNED)) as max_number", [$prefixLength]) ->first();
        $newNumber = $latestClient->max_number ? $latestClient->max_number + 1 : 1;

        return ClientConstants::NUMBER_PREFIX
            . str_pad($newNumber, ClientConstants::NUMBER_MIN_LENGTH, ClientConstants::NUMBER_PAD_STR, STR_PAD_LEFT);
    }

    public static function getCompanyClients(User $user)
    {
        if ($user->can('view_nova')) {
            $clients = Client::where('type', 'company')->get();
        } else {
            $clients = Client::where('type', 'company')
                ->where('company_id', $user->company_id)
                ->get();
        }

        return $clients;
    }

    public static function getIndividualClients(User $user)
    {
        if ($user->can('view_nova')) {
            $clients = Client::where('type', 'individual')->get();
        } else {
            $clients = Client::where('type', 'individual')
                ->whereHas('user', function ($query) use ($user) {
                    $query->where('company_id', $user->company_id);
                })->get();
        }

        return $clients;
    }

//    public static function getClientTransactions($clientId)
//    {$data=[];
//        $transactions = collect();
//        $client = Client::find($clientId);
//        if ($client->quotations()->exists()) {
//            $quotations = $client->quotations()->get();
//
//            $transactions = $transactions->merge(
//                $quotations->map(function ($quotation) use ($client){
//                    return [
//                        'client' => $client->name,
//                        'ref' => $quotation->quotation_number,
//                        'manualRef'=>$quotation->reference,
//                        'doctype' => 'quotation',
//                        'currency'=>$quotation->currency()->first()->name,
//                        'otherCurrency'=>null,
//                        'usdTotal'=>null,
//                        'otherCurrencyTotal'=>null,
//                        'value'=>$quotation->total,
//                        'date' => $quotation->created_at->toDateString(),
//
//                    ];
//                })
//            );
//        }
//        if ($client->orders()->exists()) {
//            $orders = $client->orders()->get();
//            $transactions = $transactions->merge(
//                $orders->map(function ($order) use ($client){
//                    $currency='';$otherCurrency=null;
//                    if($order->cashingMethods()->exists())
//                    {
//                        if($order->cashingMethods()->sum('primary_currency_amount')>0 && $order->cashingMethods()->sum('pos_currency_amount')>0)
//                        {
//                            $currency='USD';
//                            $otherCurrency='LBP';
//                        }
//                        else  if($order->cashingMethods()->sum('primary_currency_amount')>0)
//                            $currency='USD';
//                        else if($order->cashingMethods()->sum('pos_currency_amount')>0)
//                            $currency='LPB';
//                    }
//                    return [
//                        'client' => $client->name,
//                        'ref' => $order->order_number,
//                        'manualRef'=>null,
//                        'doctype' => 'order',
//                        'currency'=>$currency,
//                        'otherCurrency'=>$otherCurrency,
//                        'usdTotal'=>$order->primary_currency_total,
//                        'otherCurrencyTotal'=>$order->pos_currency_total,
//                        'value'=>null,
//                        'date' =>  $order->created_at->toDateString(),
//                    ];
//                })
//            );
//        }
//        return ['quotations'=>QuotationResource::collection($client->quotations()->get()),
//            'orders'=>OrderResource::collection($client->orders()->get())];
//        return $transactions->toArray();
//
//    }
//    public static function getClientSalesOrders($clientId, $options = [])
//    {
//        $perPage = $options['perPage'] ?? 10;
//        $isPaginated = $options['isPaginated'] ?? true;
//        $status = $options['status'] ?? null;
//
//        $query = Client::find($clientId)
//            ->salesOrders()
//            ->with(['orderLines.item', 'orderLines.combo']);
//
//        if ($status) {
//            $query->whereHas('orderLinesSalesOrder', function($q) use ($status) {
//                $q->where('status', $status);
//            });
//        }
//
//        if ($isPaginated) {
//            return $query->paginate($perPage);
//        }
//
//        return $query->get();
//    }
    public static function createClientWithRelations(array $clientData, array $addresses = [], array $cars = []): Client
    {
        return DB::transaction(function () use ($clientData, $addresses, $cars) {

            $client = Client::create($clientData);

            if ($addresses)
            {       foreach ($addresses as $address) {
                    $client->clientAddresses()->create([
                        'company_id' => $client->company_id,
                        'type' => $address['type'],
                        'name' => $address['name'] ?? null,
                        'title' => $address['title'] ?? null,
                        'job_position' => $address['job_position'] ?? null,
                        'phone_code' => $address['phone_code'] ?? null,
                        'phone_number' => $address['phone_number'] ?? null,
                        'extension' => $address['extension'] ?? null,
                        'mobile_code' => $address['mobile_code'] ?? null,
                        'mobile_number' => $address['mobile_number'] ?? null,
                        'email' => $address['email'] ?? null,
                        'delivery_address' => $address['delivery_address'] ?? null,
                        'note' => $address['note'] ?? null,
                        'internal_note' => $address['internal_note'] ?? null,
                    ]);
                }
        }
            if ($cars) {
                foreach ($cars as $car) {

                    $client->cars()->create([
                        'car_brand_id' => $car['car_brand_id'],
                        'car_model_id' => $car['car_model_id'],
                        'car_color_id' => $car['car_color_id'],
                        'car_technician_id' => $car['car_technician_id'],
                        'plate_number' => $car['plate_number'],
                        'chassis_number' => $car['chassis_number'],
                        'car_fax' => $car['car_fax'] ?? null,
                        'year' => $car['year'],
                        'rating' => $car['rating'],
                        'odometer' => $car['odometer'] ?? 0,
                        'comment' => $car['comment'] ?? null,
                    ]);
                }
            }
            return $client;
        });
    }



    public static function updateClientWithRelations(Client $client, array $clientData, array $addresses = [], array $cars = []): Client
    {
        return DB::transaction(function () use ($client, $clientData, $addresses, $cars) {


            $client->update($clientData);


            $client->clientAddresses()->delete();
            foreach ($addresses as $address) {
                $client->clientAddresses()->create([
                    'company_id'       => $client->company_id,
                    'type'             => $address['type'],
                    'name'             => $address['name'] ?? null,
                    'title'            => $address['title'] ?? null,
                    'job_position'     => $address['job_position'] ?? null,
                    'phone_code'       => $address['phone_code'] ?? null,
                    'phone_number'     => $address['phone_number'] ?? null,
                    'extension'        => $address['extension'] ?? null,
                    'mobile_code'      => $address['mobile_code'] ?? null,
                    'mobile_number'    => $address['mobile_number'] ?? null,
                    'email'            => $address['email'] ?? null,
                    'delivery_address' => $address['delivery_address'] ?? null,
                    'note'             => $address['note'] ?? null,
                    'internal_note'    => $address['internal_note'] ?? null,
                ]);
            }

            // $cars = $request->cars ?? [];
            $existingCarIds = collect($cars)->pluck('id')->filter()->toArray();
            $client->cars()->whereNotIn('id', $existingCarIds)->delete();

            foreach ($cars as $car) {
                if (!empty($car['id'])) {
                    //car exists just update it
                    $existingCar = $client->cars()->find($car['id']);
                    if ($existingCar) {
                        $existingCar->update([
                            'car_brand_id'      => $car['car_brand_id'],
                            'car_model_id'      => $car['car_model_id'],
                            'car_color_id'      => $car['car_color_id'],
                            'car_technician_id' => $car['car_technician_id'],
                            'plate_number'      => $car['plate_number'],
                            'chassis_number'    => $car['chassis_number'],
                            'car_fax'           => $car['car_fax'] ?? null,
                            'year'              => $car['year'],
                            'rating'            => $car['rating'],
                            'odometer'          => $car['odometer'] ?? 0,
                            'comment'           => $car['comment'] ?? null,
                        ]);
                    }
                } else {
                    //new car add it
                    $client->cars()->create([
                        'car_brand_id'      => $car['car_brand_id'],
                        'car_model_id'      => $car['car_model_id'],
                        'car_color_id'      => $car['car_color_id'],
                        'car_technician_id' => $car['car_technician_id'],
                        'plate_number'      => $car['plate_number'],
                        'chassis_number'    => $car['chassis_number'],
                        'car_fax'           => $car['car_fax'] ?? null,
                        'year'              => $car['year'],
                        'rating'            => $car['rating'],
                        'odometer'          => $car['odometer'] ?? 0,
                        'comment'           => $car['comment'] ?? null,
                    ]);
                }
            }


            return $client->fresh(['clientAddresses', 'cars']);
        });
    }
}
