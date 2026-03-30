<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyHeaderRequests\StoreCompanyHeaderRequest;
use App\Http\Requests\CompanyHeaderRequests\UpdateCompanyHeaderRequest;
use App\Http\Resources\CompanyHeaderResource;
use App\Models\CompanyHeader;
use File;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class CompanyHeaderController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        try {
          $user=auth()->user();
          $headers=$user->company->companyHeaders()->get();
          return $this->successResponse($headers,'got company headers successfully',200);
        }
        catch(\Exception $e){
            return $this->errorResponse($e->getMessage(),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    public function store(StoreCompanyHeaderRequest $request)
    {

        try{
            $user=auth()->user();
            DB::beginTransaction();

            $this->authorize('create',CompanyHeader::class);


         //   $usdCurrency=$user->company->currencies()->where('name','like','%usd%')->first();
            $data=$request->validated();
            $data['company_id']=$user->company_id;
            $companyHeader=CompanyHeader::create($data);
            $path ='';

            if(isset($request['logo']))
            {
                $image = Image::read($request['logo'])
                    ->orient()
                    ->scaleDown(1920, 1920);

                // 2. التحويل إلى WebP
                $encoded = $image->toWebp(85);

                // 3. تجهيز المسار (بدون storage)
                $filename = uniqid('headers_') . '.webp';
                $path = 'company-headers/' . $companyHeader->id.'/'. $filename;

                // 4. التخزين على disk public
                Storage::disk('public')->put($path, $encoded->toString());

            }

            $companyHeader->update(['logo'=>$path]);
            DB::commit();

            $message='Company Header is Added Successfully';
            return $this->successResponse($companyHeader,$message,Response::HTTP_CREATED);
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(),Response::HTTP_INTERNAL_SERVER_ERROR);
        }



    }
    public function update(UpdateCompanyHeaderRequest $request, $id)
    {

        try{
            $user=auth()->user();
            DB::beginTransaction();
            $companyHeader=CompanyHeader::findOrFail($id);
               $this->authorize('update',$companyHeader);

            $path=null;

            if(isset($request['logo']))
            {
                  // File::delete( public_path(str_replace('/', DIRECTORY_SEPARATOR, $companyHeader->logo)));
                Storage::disk('public')->delete($companyHeader->logo);

                $path=$request->file('logo')->store('company-headers/'.$companyHeader->id,'public');
            }
            else
            $path= $companyHeader->logo;


            $companyHeader->update($request->validated());
            $companyHeader->update(['logo'=>$path]);
            DB::commit();

            $message='Company Header is Updated Successfully';
            return $this->successResponse($companyHeader,$message,Response::HTTP_OK);
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(),Response::HTTP_INTERNAL_SERVER_ERROR);
        }



    }

    public  function destroy(string $id)
    {
        try{
            DB::beginTransaction();
            $companyHeader=CompanyHeader::findOrFail($id);
          $this->authorize('delete',$companyHeader);
            if( !$companyHeader)
            {
                return $this->errorResponse('header not found',404);
            }
            Storage::disk('public')->delete($companyHeader->logo);
            $companyHeader->delete();
            DB::commit();
            return $this->successResponse([],'Header deleted successfully',200);
        }
        catch(\Exception $e){
           DB::rollBack();
           return $this->errorResponse($e->getMessage(),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
