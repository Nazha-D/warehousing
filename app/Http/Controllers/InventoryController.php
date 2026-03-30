<?php

namespace App\Http\Controllers;

use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponseTrait;
class InventoryController extends Controller
{
    use ApiResponseTrait;
    public function getInventorySnapshot(Request $request)
    {
        try{
       $options['is_paginated']=$request->query('is_paginated');
        $options['per_page']=$request->query('per_page');
        $options['item_group_ids']=$request->query('item_group_ids');
        $options['category_id']=$request->query('category_id');
            $options['item_id']=$request->query('item_id');
        $options['search']=$request->query('search');


        return
      $this->successResponse(
        InventoryService::getInventorySnapshot(
            auth()->user()->company_id,
            $request->warehouse_id,
            $request->date,
            $options
        ),
      'Got data successfully',
      200);
        }
        catch(\Exception $exception){
            return $this->errorResponse($exception->getMessage(),500);
        }
    }
    public function saveInventoryCounts(Request $request)
    {
        try{
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'count_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.counted_quantity' => 'required|numeric',
            'items.*.notes' => 'nullable|string',
        ]);

        $companyId = auth()->user()->company_id;
        $userId = auth()->id();

        DB::transaction(function () use ($companyId, $userId, $request) {
            InventoryService::saveInventoryCounts(
                $companyId,
                $request->warehouse_id,
                $request->count_date,
                $userId,
                $request->items
            );
        });

        return $this->successResponse([], 'Inventory counts saved successfully',201);
        }
        catch(\Exception $exception){
            return $this->errorResponse($exception->getMessage(),500);
        }
        }

}
