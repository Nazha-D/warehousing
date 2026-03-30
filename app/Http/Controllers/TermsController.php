<?php

namespace App\Http\Controllers;

use App\Models\TermsAndCondition;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Traits\ApiResponseTrait;

class TermsController extends Controller
{
    use ApiResponseTrait;
    public  function index()
    {

        try {

            $user=auth()->user();

            $termsAndConditions=$user->company->termsAndConditions()->get();

            return $this->successResponse($termsAndConditions,'terms and conditions fetched successfully',Response::HTTP_OK);
        }
        catch (\Exception $e)
        {

            return $this->errorResponse($e->getMessage(),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public  function store(Request $request)
    {

        try {
            DB::beginTransaction();
            $user=auth()->user();
            $request->validate([
                'name'=>['required',Rule::unique('terms_and_conditions', 'name')
                    ->where(function ($query) {
                        $query->where('company_id', auth()->user()->company_id)
                            ->whereNull('deleted_at');
                    })],
                'company_id'=>Rule::exists('companies')->where(function ($query) {
                    $query->where('id', auth()->user()->company_id)->whereNull('deleted_at');
                }),
                'terms_and_conditions'=>['required']
            ]);
            $termsAndConditions=TermsAndCondition::create([
                'company_id'=>$user->company_id,
                'terms_and_conditions'=>$request->terms_and_conditions,
                'name'=>$request->name
            ]);
            DB::commit();
            return $this->successResponse($termsAndConditions,'terms and conditions added successfully',Response::HTTP_OK);
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public  function update(Request $request,$id)
    {

        try {
            DB::beginTransaction();
            $user=auth()->user();
            $termsAndConditions=TermsAndCondition::find($id);
            $validated=  $request->validate([
                'name'=>['sometimes',Rule::unique('terms_and_conditions', 'name')
                    ->ignore($termsAndConditions->id)
                    ->where(function ($query) {
                        $query->where('company_id', auth()->user()->company_id)   ->whereNull('deleted_at');
                    })],
                'company_id'=>Rule::exists('companies')->where(function ($query) {
                    $query->where('id', auth()->user()->company_id)->whereNull('deleted_at');
                }),
                'terms_and_conditions'=>['sometimes','required']
            ]);
            $termsAndConditions->update($validated);
            DB::commit();
            return $this->successResponse($termsAndConditions,'terms and conditions updated successfully',Response::HTTP_OK);
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $user=auth()->user();

            $termsAndConditions=TermsAndCondition::find($id);
            if (!$termsAndConditions) {
                return $this->errorResponse('Object not found.', Response::HTTP_NOT_FOUND);
            }
            if($termsAndConditions->quotations()->exists())
                return $this->errorResponse('Deletion not allowed: This terms has dependent quotations.', Response::HTTP_CONFLICT);
//            if($termsAndConditions->salesInvoices()->exists())
//                return $this->errorResponse('Deletion not allowed: This terms has dependent sales invoices.', Response::HTTP_CONFLICT);
            $termsAndConditions->delete();
            DB::commit();
            return $this->successResponse([],'terms and conditions deleted successfully',Response::HTTP_OK);
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(),Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }



}
