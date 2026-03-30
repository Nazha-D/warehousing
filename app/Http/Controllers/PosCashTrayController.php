<?php

namespace App\Http\Controllers;

use App\Http\Requests\CashTrayRequests\OpenCashTrayRequest;
use App\Models\PosSession;
use App\Models\PosCashTray;
use App\Services\CashTrayReportService;
use App\Services\CashTrayService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponseTrait;

class PosCashTrayController extends Controller
{
    use ApiResponseTrait;
    protected CashTrayService $service;
    public function __construct()
    {
        $this->service=new CashTrayService();
    }

    /**
     * List trays for session
     */
    public function index(Request $request)
    {

        try {
//            $this->authorize('viewAny', CashTray::class);
            $user = auth()->user();
            $options = [
                'perPage' => $request->query('perPage'),
                'isPaginated' => $request->query('isPaginated'),
                'search' => $request->query('search'),
                'onlyClosed' => $request->query('onlyClosed'),
                'onlyOpen' => $request->query('onlyOpen'),
            ];

            $trays = $this->service->getAll( $user->company_id, $options);

            return $this->successResponse($trays, 'Trays are here', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function create($sessionId)
    {
        try{
            $user=auth()->user();
//            $this->authorize('create', CashTray::class);
            $data['tray_number']=$this->service->generateTrayNumber($user->company_id,$sessionId);
            //   return CashTray::where('company_id', 1)->where('session_id', 49)->whereNotNull('tray_number')->latest()->first();
            return $this->successResponse($data,'Got Tray Number Successfully',200);
        }
        catch(\Exception $ex){
            return $this->errorResponse($ex->getMessage(),500);
        }
    }

    public function store(OpenCashTrayRequest $request, PosSession $session): JsonResponse
    {
        try {


            $validated = $request->validated();

            $tray = $this->service->openTray($session, $validated);

            return $this->successResponse($tray, 'Cash tray opened Successfully!', 201);
        }catch (\Exception $exception)
        {
            return  $this->errorResponse($exception->getMessage(),500);
        }
        }
    /**
 * Close tray
 */
public function close(Request $request, PosCashTray $tray): JsonResponse
{
    try{
    $validated = $request->validate([
        'counted_balances' => 'required|array|min:1',
        'counted_balances.*.currency_id' => 'required|integer',
        'counted_balances.*.amount' => 'required|numeric|min:0',
    ]);

    $closedTray = $this->service->closeTray($tray, $validated['counted_balances']);

    return $this->successResponse([],'Cash Tray closed successfully',200);
    }catch (\Exception $exception)
    {
        return  $this->errorResponse($exception->getMessage(),500);
    }
}

/**
 * Show single tray
 */
//public function show(PosCashTray $tray): JsonResponse
//{
//    return response()->json([
//        'success' => true,
//        'data' => $tray
//    ]);
//}
    public function closingReport(PosCashTray $tray)
    {
        try {
            $service = new CashTrayReportService();

            return $this->successResponse($service->generate($tray), 'Got Data Successfully', 200);
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }
        }

   public function getOpenedTray($sessionId)
   {
       try{
           $tray=$this->service->getOpenTrayBySession($sessionId);
       return $this->successResponse($tray,'Got data successfully',200);
       }
       catch (\Exception $exception)
       {
           return $this->errorResponse($exception->getMessage(),500);
       }
   }
}
