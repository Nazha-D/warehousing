<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemImage;
use App\Services\ItemImageService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemImageController extends Controller
{
    use ApiResponseTrait;

    /**
     * Add one or many images to an item
     */
    public function store(Request $request, Item $item)
    {

        $this->authorize('update',$item);
        $request->validate([
            'images'   => 'required|array|min:1',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120', // 5MB max
        ]);

        DB::beginTransaction();

        try {
            $uploadedImages = ItemImageService::storeMany($item, $request->file('images'));
            $baseUrl = 'https://backoffice.vita-erp.com/vita_erp_backend_test/storage/app/public/';
            $uploadedImagesWithUrl = collect($uploadedImages)->map(function ($image) use ($baseUrl) {
                return [
                    'id' => $image->id,
                    'path' => $baseUrl . $image->path,

                ];
            });

            DB::commit();

            return $this->successResponse($uploadedImagesWithUrl, 'Images uploaded successfully', 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Delete an item image (DB + file)
     */
    public function destroy(ItemImage $image)
    {
        DB::beginTransaction();

        try {
            ItemImageService::delete($image);

            DB::commit();

            return $this->successResponse(null, 'Image deleted successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
