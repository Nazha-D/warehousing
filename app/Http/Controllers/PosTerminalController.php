<?php

namespace App\Http\Controllers;

use App\Models\PosTerminal;
use App\Services\PosTerminalService;
use App\Http\Requests\PosTerminalRequests\StorePosTerminalRequest;
use App\Http\Requests\PosTerminalRequests\UpdatePosTerminalRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;


class PosTerminalController extends Controller
{
    use ApiResponseTrait;
    protected  $service;
    public function __construct()
    {
        $this->service=new PosTerminalService();
    }

public function index(Request $request)
{
    try {
        $this->authorize('viewAny', PosTerminal::class);
        $user = auth()->user();
        $options = [
            'perPage' => $request->query('perPage'),
            'isPaginated' => $request->query('isPaginated'),
            'search' => $request->query('search'),
        ];
        $posTerminals =$this->service->getAll( $user->company_id, $options);
        $message = 'Pos Terminals fetched successfully';
        return $this->successResponse($posTerminals,'Got data successfully',200);
    }
    catch (\Exception $exception)
    {
        return $this->errorResponse($exception->getMessage(),500);
    }
    }
    public function show(PosTerminal $posTerminal)
    {
        try{
            $this->authorize('view',$posTerminal);
          $posTerminal->load(['warehouse','sessions','company']);
          return $this->successResponse($posTerminal,'Got data successfully',200);
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }
    }
    public function create()
    {
        try{
            $this->authorize('create',PosTerminal::class);
            $user=auth()->user();
            $data['pos_number']=$this->service->generatePosNumber($user->company_id);
            $data['warehouses']=$user->company->warehouses;
            return $this->successResponse($data,'Got data successfully',200);
        }
        catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(),500);
        }
    }


public function store(StorePosTerminalRequest $request)
{
    try {
        $this->authorize('create',PosTerminal::class);
        $terminal = $this->service->store($request->validated());

        return $this->successResponse(
            $terminal,
            'POS Terminal created successfully.', 201);
    }
    catch (\Exception $exception)
    {
        return $this->errorResponse($exception->getMessage(),500);
    }

}

public function update(UpdatePosTerminalRequest $request, PosTerminal $posTerminal)
{
    try{
        $this->authorize('update',$posTerminal);
    $terminal = $this->service->update($posTerminal, $request->validated());

    return $this->successResponse(
         $terminal,
        'POS Terminal updated successfully.',200
    );
    }
    catch (\Exception $exception)
    {
        return $this->errorResponse($exception->getMessage(),500);
    }
}

public function destroy(PosTerminal $posTerminal)
{
    try{
        $this->authorize('delete',$posTerminal);
    $this->service->delete($posTerminal);

    return $this->successResponse(
        [],
        'POS Terminal deleted successfully.',
        200
    );
    }
    catch (\Exception $exception)
    {
        return $this->errorResponse($exception->getMessage(),500);
    }
}
}
