<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemRequests\StoreItemRequest;
use App\Http\Requests\ItemRequests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\AlternativeCode;
use App\Models\BarCode;
use App\Models\Currency;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemType;
use App\Models\Package;
use App\Models\Role;
use App\Models\Subref;
use App\Models\SupplierCode;
use App\Models\TaxationGroup;
use App\Services\CategoryService;
use App\Services\ItemService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class ItemController extends Controller
{
    use ApiResponseTrait;

    /**
     * عرض جميع المنتجات مع فلترة، pagination، خيارات warehouse
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Item::class);
            $user = auth()->user();

            $options = [
                'perPage' => $request->query('perPage'),
                'isPaginated'   => $request->query('isPaginated'),
                'onlyActive' => $request->query('onlyActive'),
                'searchByCat' => $request->query('searchByCat'),
                'posProducts' => $request->query('posProducts'),
                'search' => $request->query('search'),
                'barcode' => $request->query('barcode'),
                'warehouseId' => $request->query('warehouseId'),
                'withVirtual' => $request->query('withVirtual')
            ];

            $items = ItemService::getAll($user->can('get all companies'), $user->company_id, $options);
           // dd(get_class($items));
            $message='Items retrieved successfully';
           // return ItemResource::collection($items);
            return $this->successResponse( ItemResource::collection($items),$message,200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * بيانات إنشاء منتج (dropdowns, codes, etc)
     */
    public function create()
    {
        try {
            $this->authorize('create',Item::class);


            $user = Auth::user();

            $data['categories'] = CategoryService::getAll($user->can('view company'), $user->company_id, [])
                ->map(fn($cat) => ['id' => $cat->id, 'name' => $cat->category_name])
                ->toArray();

            $data['itemTypes'] = ItemType::where('name', '!=', 'Virtual')->get(['id', 'name']);
            $data['taxationGroups'] = TaxationGroup::whereHas('taxRates')
                ->where('company_id', $user->company_id)
                ->get(['id', 'code'])
                ->sortBy(fn($tx) => $tx->code === "STANDARD" ? 0 : 1)
                ->values();

            $data['mainCode'] = ItemService::generateMainCode($user->company_id);
          $data['currencies'] =  Currency::query()
              ->whereHas('companies', function ($q) use ($user) {
                  $q->where('company_id', $user->company_id);
              })
              ->get(['id','name','symbol']);
            $data['itemGroups'] = ItemGroup::where('company_id', $user->company_id)->get()->toTree();
            $data['packages'] = Package::get(['id', 'name']);
            $data['subrefs'] = Subref::get(['id', 'name']);

            return $this->successResponse($data, 'Data needed for item creation retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * عرض منتج محدد
     */
    public function show(Item $item,Request $request)
    {
        try {
            $this->authorize('view',$item);

            $request->merge(['detailed' => true]);

            $user = auth()->user();
            if ($item->company_id !== $user->company_id) {
                abort(403, 'Unauthorized');
            }
            $item= new ItemResource(
                $item->load([
                    'supplierCodes',
                    'alternativeCodes',
                    'barcodes',
                    'taxationGroup.taxRates',
                    'itemImages',
                    'subref'
                ])
            );

            $message='Item retrieved successfully';
            return $this->successResponse ($item,$message,200);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * إنشاء منتج جديد
     */
    public function store(StoreItemRequest $request)
    {
        try {
            $user = auth()->user();
            DB::beginTransaction();
             $this->authorize('create',Item::class);
            $item=Item::create($request->validated());

            $item->update(['company_id'=>$user->company_id]);
            if(! isset($request['main_code']))
            {
               $item->update([
                   'main_code'=> ItemService::generateMainCode($user->company_id),

               ]);
            }else
            {
                $item->update([
                    'auto_generated_code'=>false,

                ]);
            }
//            return $request->item_codes;
            ItemService::syncItemCodesAndGroups(
                $item,
                $request->item_codes ?? [],
                $request->item_groups  ?? []
            );
            $uploadedImages = [];
            if ($request->hasFile('images')) {
                $uploadedImages = \App\Services\ItemImageService::storeMany($item, $request->file('images'));
            }

            DB::commit();
            $message='Item created successfully';
            return$this->successResponse( (new ItemResource($item))->additional(['detailed' => 1]),$message,201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * تعديل منتج موجود
     */
    public function update(UpdateItemRequest $request, Item $item)
    {
        try {
            $this->authorize('update',$item);
            $user = auth()->user();
            $item->update($request->validated());
            $item->supplierCodes()->forceDelete();
            $item->alternativeCodes()->forceDelete();
            $item->barcodes()->delete();
            $item->itemGroups()->detach();
            ItemService::syncItemCodesAndGroups($item, $request->item_codes ?? [],  $request->item_groups  ?? []);

            DB::commit();

            return $this->successResponse( new ItemResource($item),'Item Updated successfully',200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * حذف منتج (soft delete)
     */
    public function destroy(Item $item)
    {
        try {
            $user = auth()->user();
           $this->authorize('delete',$item);
            $item->update(['discontinued' => true]);
            $item->itemImages()->delete();
            $item->delete();

            return $this->successResponse(null, 'Item deleted successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

}
