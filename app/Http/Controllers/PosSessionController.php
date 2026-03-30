<?php

namespace App\Http\Controllers;

use App\Models\PosSession;
use App\Models\PosTerminal;
use App\Services\PosSessionService;
use App\Http\Requests\PosSessionRequests\StorePosSessionRequest;
use App\Http\Requests\PosSessionRequests\UpdatePosSessionRequest;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
class PosSessionController extends Controller
{
    use ApiResponseTrait;
    protected PosSessionService $service;
    public function __construct()
    {
        $this->service=new PosSessionService();
    }

public function index(Request $request)
{

    try {
        $this->authorize('viewAny', PosSession::class);
        $user = auth()->user();
        $options = [
            'perPage' => $request->query('perPage'),
            'isPaginated' => $request->query('isPaginated'),
            'search'=> $request->query('search'),
            'posTerminalId'=> $request->query('posTerminalId')
        ];
        $sessions = $this->service->getAll($user->company_id, $options);
        $message = 'Sessions fetched successfully';

        return $this->successResponse($sessions, $message, 200);
    } catch (\Exception $e) {

        return $this->errorResponse($e->getMessage(), 500);
    }

}

public function store(StorePosSessionRequest $request)
{

try {
    $this->authorize('create',PosSession::class);
    $session = $this->service->open($request->validated());

    return$this->successResponse(
         $session,
        'Session opened successfully.',
      201);
}
catch(\Exception $exception)
{
    return $this->errorResponse($exception->getMessage(),500);

}
}

public function show(PosSession $posSession)
{
    try {
        $this->authorize('view',$posSession);
    return $posSession;
    }
    catch(\Exception $exception)
    {
        return $this->errorResponse($exception->getMessage(),500);

    }
    }

public function update(UpdatePosSessionRequest $request, PosSession $posSession)
{
    try {
        $this->authorize('update',$posSession);
    $session = $this->service->update($posSession, $request->validated());

    return $this->successResponse(
        $session, 'Session updated successfully.',200);
    }
    catch(\Exception $exception)
    {
        return $this->errorResponse($exception->getMessage(),500);

    }
}

public function destroy(PosSession $posSession)
{
    try {
        $this->authorize('delete',$posSession);
    $this->service->delete($posSession);

    return $this->successResponse(
         [],'Session deleted successfully.',200
    );
    }
    catch(\Exception $exception)
    {
        return $this->errorResponse($exception->getMessage(),500);

    }
}

// Endpoint لإغلاق Session
public function close(PosSession $posSession)
{
    try {
        $this->authorize('update',$posSession);
    $session = $this->service->close($posSession, auth()->id());

    return $this->successResponse(
        $session,
        'Session closed successfully.',
     200
    );
    }
    catch(\Exception $exception)
    {
        return $this->errorResponse($exception->getMessage(),500);

    }
}
    public function getOpenSession( $posTerminal)
    {
        try{

        $companyId = auth()->user()->company_id;

        $session = PosSessionService::getOpenSessionForTerminal($companyId, $posTerminal);
         //   $this->authorize('view',$session);
        return $this->successResponse(
             $session? $session: null,'Got data successfully',200
        );
        }
        catch(\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);

        }
    }
}
