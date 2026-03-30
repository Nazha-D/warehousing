<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemGroupRequests\StoreItemGroupRequest;
use App\Http\Requests\ItemGroupRequests\UpdateItemGroupRequest;
use App\Http\Resources\ItemGroupResource;
use App\Models\ItemGroup;
use App\Services\ItemGroupService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ItemGroupController extends Controller
{
    use ApiResponseTrait;
    public function index(Request $request)
    {
        try {


             $this->authorize('viewAny', ItemGroup::class);
            $user = auth()->user();
            $options = [
                'perPage' => $request->query('perPage'),
                'isPaginated' => $request->query('isPaginated'),
                'search' => $request->query('search'),
            ];
            $itemGroups = ItemGroupService::getAll($user->can('get all companies'), $user->company_id, $options);
            $message = 'Item Groups fetched successfully';
            return $this->successResponse( ItemGroup::where('company_id',$user->company_id)->get()->toTree(),$message,Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(StoreItemGroupRequest  $request)
    {
        try{

            DB::beginTransaction();
            $this->authorize('create',ItemGroup::class);
            $user=auth()->user();
            $groups = $request->groups; // array of groups from frontend

            $createdGroups = [];

            foreach ($groups as $groupData) {

                $created = ItemGroupService::createGroupWithChildren($groupData, null, $user->company_id);
                $createdGroups=array_merge($createdGroups, $created);
            }

            $createdIds = collect($createdGroups)->pluck('id');

            //
            $message = 'Item Group created successfully';

            DB::commit();
            $createdTree = ItemGroup::whereIn('id', $createdIds)
                ->with('childrenRecursive') // use your recursive relationship
                ->whereNull('parent_id')    // only top-level nodes
                ->get();

            return $this->successResponse($createdTree, $message, 201);
        }
        catch(\Exception $e){
            DB::rollBack();
            return $this->errorResponse($e->getMessage(),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateItemGroupRequest  $request,ItemGroup $itemGroup)
    {
        try {
            DB::beginTransaction();

          $this->authorize('update',$itemGroup);
            $user = auth()->user();


            $itemGroup->update([
                'name' => $request->name ?? $itemGroup->name,
                'code' => $request->code ?? $itemGroup->code,
                //'active' => $request->active ?? $itemGroup->active,
            ]);


            if (isset($request['children']) && is_array($request['children'])) {
                $this->updateOrAddChildren($itemGroup, $request['children'], $user);
            }

            DB::commit();
            return $this->successResponse($itemGroup->fresh('children'), 'ItemGroup updated successfully', 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy(ItemGroup $itemGroup)
    {
        try {

             $this->authorize('delete',$itemGroup);

            if (!$itemGroup) {
                return $this->errorResponse('Item Group not found.', Response::HTTP_NOT_FOUND);
            }
            if ($itemGroup->children()->exists()) {
                return $this->errorResponse('Item Group  has subGroups and can not be deleted.', Response::HTTP_CONFLICT);
            }

//            if ($itemGroup->items()->exists()) {
//                return $this->errorResponse('Item Group  has items and can not be deleted.', Response::HTTP_CONFLICT);
//            }
            //  $this->authorize('delete', $category);

            $itemGroup->delete();
            $message = 'ItemGroup deleted successfully.';

            return $this->successResponse(null, $message, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(),500);
        }
    }

    protected function updateOrAddChildren(ItemGroup $parent, array $children, $user)
    {
        foreach ($children as $childData) {

            if (isset($childData['id'])) {
                $child = ItemGroup::find($childData['id']);
                if ($child) {
                    $child->update([
                        'name' => $childData['name'] ?? $child->name,
                        'code' => $childData['code'] ?? $child->code,
                        //  'active' => $childData['active'] ?? $child->active,
                    ]);
                }
            }

            else {
                $child = ItemGroup::create([
                    'name' => $childData['name'],
                    'code' => $childData['code'] ?? null,
                    'company_id' => $user->company_id,
                    'active' => $childData['active'] ?? 1,
                ]);
                $child->appendToNode($parent)->save();
            }


            if (isset($childData['children']) && is_array($childData['children'])) {
                $this->updateOrAddChildren($child, $childData['children'], $user);
            }
        }
    }

}
