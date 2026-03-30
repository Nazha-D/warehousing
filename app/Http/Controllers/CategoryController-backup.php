<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequests\StoreCategoryRequest;

use App\Models\Category;
use App\Models\Item;
use App\Events\SomethingHappened;
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
                'onlyActive' => $request->query('onlyActive'),
            ];
            $categories = CategoryService::getAll($user->can('get all companies'), $user->company_id, $options);

            return $this->successResponse($categories, 'Categories are here', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function store(StoreCategoryRequest $request)
    {//return auth()->user()->company_id;


        DB::beginTransaction();
        try {


            $this->authorize('create', Category::class);
            $user = auth()->user();

            $category=Category::create([

                'category_name'=>$request->categoryName,


            ]);
            $category->company_id= $user->company_id;
            if(isset($request->parentId)) {
                $parent = Category::findOrFail($request->parentId);
                $category->appendToNode($parent);
            }
            $category->save();


//
            $message = 'Category created successfully';

            DB::commit();
            return $this->successResponse($category, $message, 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function update(StoreCategoryRequest $request,string $id)
    {//return auth()->user()->company_id;

        DB::beginTransaction();
        try {

            $category=Category::findOrFail($id);
            $this->authorize('update', $category);
            $user = auth()->user();


            $category->update([

                'category_name'=>$request->categoryName,


            ]);



//
            $message = 'Category name is updated successfully';
            DB::commit();
            return $this->successResponse($category, $message, 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    public function destroy(string $id)
    {

        try {

            $category = Category::findOrFail($id);
            $this->authorize('delete', $category);
            if (!$category) {
                return $this->errorResponse('Category not found.', Response::HTTP_NOT_FOUND);
            }
            if ($category->children()->exists()) {
                return $this->errorResponse('Category has subcategories and can not be deleted.', Response::HTTP_CONFLICT);
            }
            if ($category->category_name === 'STANDARD'){
                return $this->errorResponse('This category is the default category and can not be deleted.', Response::HTTP_CONFLICT);
            }
//            if ($category->items()->exists()) {
//                return $this->errorResponse('Category has items and can not be deleted.', Response::HTTP_CONFLICT);
//            }


            $category->delete();
            $message = 'Category deleted successfully.';

            return $this->successResponse(null, $message, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

}
