<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CashingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class CashingMethodController extends Controller
{
    use ApiResponseTrait;

    /**
     * عرض كل طرق الدفع
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CashingMethod::class);

        $companyId = auth()->user()->company_id;

        $query = CashingMethod::where('company_id', $companyId)
            ->orderBy('title');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $cashingMethods = $query->get();

        $message = 'Cashing Methods fetched Successfully';

        return $this->successResponse($cashingMethods, $message, 200);
    }

    /**
     * عرض طريقة دفع محددة
     */
    public function show(CashingMethod $cashingMethod, Request $request)
    {
        $this->authorize('view',$cashingMethod);
        $companyId = auth()->user()->company_id;



        if (!$cashingMethod) {
            return $this->notFound('Cashing method not found.');
        }
        $message='Cashing Method fetched Successfully';
        return $this->successResponse($cashingMethod,$message,200);
    }

    /**
     * إنشاء طريقة دفع جديدة
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
        $this->authorize('create',CashingMethod::class);
        $request->validate([
            'title' => 'required|string|max:255',
            'active' => 'boolean',
            'image' => 'nullable|file',
        ]);

        $companyId = auth()->user()->company_id;


            $cashingMethod = CashingMethod::create([
                'title' => $request->title,
                'active' => $request->active ?? true,
                'company_id' => $companyId,
              //  'image' => $request->image ?? null,
            ]);
            if($request->image) {
                $image = Image::read($request->image)
                    ->orient()
                    ->scaleDown(1920, 1920);

                // 2. التحويل إلى WebP
                $encoded = $image->toWebp(85);

                // 3. تجهيز المسار (بدون storage)
                $filename = uniqid('cashing_method_') . '.webp';
                $path = 'cashing_methods/' . $cashingMethod->id .'/' . $filename;

                // 4. التخزين على disk public
                Storage::disk('public')->put($path, $encoded->toString());
                $cashingMethod->update(['image'=>$path]);
            }
            DB::commit();
            $message='Cashing Methods created Successfully';
            return $this->successResponse($cashingMethod, $message,201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse( $e->getMessage(),500);
        }
    }

    /**
     * تعديل طريقة الدفع
     */
    public function update(Request $request, CashingMethod $cashingMethod)
    {

        $this->authorize('update',$cashingMethod);
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'active' => 'sometimes|boolean',
           // 'image' => 'nullable|string|max:255',
        ]);

        $companyId = auth()->user()->company_id;

       // $cashingMethod = CashingMethod::where('company_id', $companyId)->find($id);
        if (!$cashingMethod) {
            return $this->errorResponse('Cashing method not found.',404);
        }

        DB::beginTransaction();
        try {
            $cashingMethod->update($request->only(['title', 'active']));

            DB::commit();
            $message='Cashing Methods updated Successfully';
            return $this->successResponse($cashingMethod, $message,200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update cashing method.', $e->getMessage());
        }
    }

    /**
     * حذف طريقة الدفع
     */
    public function destroy(CashingMethod $cashingMethod, Request $request)
    {
        $this->authorize('delete',$cashingMethod);
        $companyId = auth()->user()->company_id;

      //  $cashingMethod = CashingMethod::where('company_id', $companyId)->find($id);
        if (!$cashingMethod) {
            return $this->errorResponse('Cashing method not found.',404);
        }

        DB::beginTransaction();
        try {
            $cashingMethod->delete(); // soft delete
            DB::commit();
            $message='Cashing Method deleted Successfully';
            return $this->successResponse(null, $message,200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete cashing method.', $e->getMessage());
        }
    }
}
