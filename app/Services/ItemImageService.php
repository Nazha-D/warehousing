<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Support\Facades\Storage;

use Intervention\Image\Laravel\Facades\Image;


class ItemImageService
{
    /**
     * Store multiple images for an item
     *
     * @param Item $item
     * @param array $imagesFiles
     */
    public static function storeMany(Item $item, array $imagesFiles): array
    {
        $uploadedImages = [];

        foreach ($imagesFiles as $file) {
            $uploadedImages[] = self::storeSingle($item, $file);
        }

        return $uploadedImages;
    }

    /**
     * Store single image for an item
     *
     * @param Item $item
     * @param \Illuminate\Http\UploadedFile $file
     * @return ItemImage
     */
    public static function storeSingle(Item $item, $file): ItemImage
    {
        // 1. قراءة ومعالجة الصورة
        $image = Image::read($file)
            ->orient()
            ->scaleDown(1920, 1920);

        // 2. التحويل إلى WebP
        $encoded = $image->toWebp(85);

        // 3. تجهيز المسار (بدون storage)
        $filename = uniqid('item_') . '.webp';
        $path = 'items/' . $item->id . '/images/' . $filename;

        // 4. التخزين على disk public
        Storage::disk('public')->put($path, $encoded->toString());

        // 5. حفظ DB
        return ItemImage::create([
            'item_id' => $item->id,
            'path'    => $path,
        ]);
    }

    /**
     * Delete an item image (DB + file)
     *
     * @param ItemImage $image
     */
    public static function delete(ItemImage $image): void
    {
        // حذف الملف من storage
        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        // حذف السجل من قاعدة البيانات
        $image->delete();
    }
}
