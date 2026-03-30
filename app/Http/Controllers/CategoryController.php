<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequests\StoreCategoryRequest;

use App\Http\Requests\CategoryRequests\UpdateCategoryRequest;
use App\Models\Category;


use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    use ApiResponseTrait;
    public function index(Request $request)
    {
        try {


            $this->authorize('viewAny', Category::class);
            $user = auth()->user();
            $options = [
                'perPage' => $request->query('perPage'),
                'isPaginated' => $request->query('isPaginated'),
                'search' => $request->query('search'),
            ];
            $categories = CategoryService::getAll($user->can('get all companies'), $user->company_id, $options);
            $message = 'categories fetched successfully';
            return $this->successResponse( Category::where('company_id',$user->company_id)->get()->toTree(),$message,Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(StoreCategoryRequest  $request)
    {
        try{

            DB::beginTransaction();
            $this->authorize('create',Category::class);
            $user=auth()->user();
            $categories = $request->categories; // array of groups from frontend

            $createdCategories = [];

            foreach ($categories as $categoryData) {

                $created = CategoryService::createCategoryWithChildren($categoryData, null, $user->company_id);
                $createdCategories=array_merge($createdCategories, $created);
            }

            $createdIds = collect($createdCategories)->pluck('id');

            //
            $message = 'Category created successfully';

            DB::commit();
            $createdTree = Category::whereIn('id', $createdIds)
                ->with('childrenRecursive') // use your recursive relationship
                //->whereNull('parent_id')    // only top-level nodes
                ->get();

            return $this->successResponse($createdTree, $message, 201);
        }
        catch(\Exception $e){
            DB::rollBack();
            return $this->errorResponse($e->getMessage(),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateCategoryRequest  $request,Category $category)
    {
        try {
            DB::beginTransaction();

          $this->authorize('update',$category);
            $user = auth()->user();

            $category->update([
                'category_name' => $request->name ?? $category->name,

                //'active' => $request->active ?? $category->active,
            ]);


            if (isset($request['children']) && is_array($request['children'])) {
                $this->updateOrAddChildren($category, $request['children'], $user);
            }

            DB::commit();
            return $this->successResponse($category->fresh('children'), 'Category updated successfully', 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy(Category $category)
    {
        try {

            $this->authorize('delete',$category);

            if (!$category) {
                return $this->errorResponse('Category not found.', Response::HTTP_NOT_FOUND);
            }
            if ($category->children()->exists()) {
                return $this->errorResponse('Category  has sub categories and can not be deleted.', Response::HTTP_CONFLICT);
            }

//            if ($category->items()->exists()) {
//                return $this->errorResponse('Category  has items and can not be deleted.', Response::HTTP_CONFLICT);
//            }
            //  $this->authorize('delete', $category);

            $category->delete();
            $message = 'Category deleted successfully.';

            return $this->successResponse(null, $message, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    protected function updateOrAddChildren(Category $parent, array $children, $user)
    {
        foreach ($children as $childData) {

            if (isset($childData['id'])) {
                $child = Category::find($childData['id']);
                if ($child) {
                    $child->update([
                        'category_name' => $childData['name'] ?? $child->name,

                        //  'active' => $childData['active'] ?? $child->active,
                    ]);
                }
            }

            else {
                $child = Category::create([
                    'category_name' => $childData['name'],

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
