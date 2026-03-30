<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComboRequests\StoreComboRequest;
use App\Http\Requests\ComboRequests\UpdateComboRequest;
use App\Http\Resources\ComboResource;
use App\Models\Combo;
use App\Models\Item;
use App\Models\Currency;
use App\Services\ComboService;
use App\Services\ItemService;
use App\Http\Resources\ItemResource;
use Auth;
use Exception;
use File;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use App\Traits\ApiResponseTrait;
class ComboController extends Controller
{
    use ApiResponseTrait;
    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', Combo::class);
            $user = auth()->user();
            $options = [
                'perPage' => $request->query('perPage'),
                'isPaginated' => $request->query('isPaginated'),
                'search' => $request->query('search'),
                'onlyActive' => $request->query('onlyActive'),
            ];
            $combos = ComboService::getAll($user->can('view company'), $user->company_id, $options);
            //   return $this->successResponse(new ComboResource($combos), $message, Response::HTTP_OK);

            return $this->successResponse(ComboResource::collection($combos),'',200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(),500);
        }
    }

    /**
     * @return mixed
     */
    public function show(string $id)
    {
        try {
            $combo = Combo::find($id);
            if (! $combo) {
                return $this->errorResponse('Combo not found.', Response::HTTP_NOT_FOUND);
            }
            $this->authorize('view', $combo);

            $message = 'Combo retrieved successfully.';

            return $this->successResponse(new ComboResource($combo), $message, Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @return mixed
     */
    public function create()
    {
        try {
            $this->authorize('create', Combo::class);
            $user = Auth::user();
            $options['isPaginated']=0;

            $data['items'] =ItemResource::collection(ItemService::getAll($user->can('view company'), $user->company_id,$options));
            $data['code']=ComboService::generateComboCode($user->company_id);
            $message = 'Data needed for combo creation retrieved successfully';

            return $this->successResponse($data, $message, Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(),500);
        }
    }

    /**
     * @return mixed
     *
     * @throws Throwable
     */
    public function store(StoreComboRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->authorize('create', Combo::class);

            $user = Auth::user();
            $path='';
            $data = [
                'company_id' => $user->company_id,
                'name' => $request->name,
                'code'=>ComboService::generateComboCode($user->company_id),
                'description'=>$request->description,
                'currency_id'=>$request->currency_id,
                'brand'=>$request->brand,
                'total'=>$request->total,

            ];

            $combo = Combo::create($data);
            if(isset($request['image']))
            {
                $path= $request->file('image')->store('combos/'.$combo->id,'public');
            }
            $items = $request->input('items');

            foreach ($items as $item) {
                $id = $item['id'];
                $description = $item['description'];
                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $discount = $item['discount'];
//                  $total = $item['total'];
                $currencyId= $request->currency_id;
                $itemModel = Item::findOrFail($id);
                $currency=Currency::find($currencyId);
                $combo->items()->create([
                    'item_id' => $id,
                    'description' => $description,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'discount' => $discount,
                    'total' => ($unitPrice * $quantity) - ($unitPrice * $quantity* ($discount / 100)),
                ]);
                // if ($discount > $itemModel->line_discount_limit) {
                //     throw ValidationException::withMessages(['items' => 'Item '.$itemModel->main_code.' has a discount greater than the line discount limit'])->status(Response::HTTP_UNPROCESSABLE_ENTITY);
                // }
//                if ($currency->company_id!=$combo->company_id) {
//                    throw ValidationException::withMessages(['Chosen currency belongs to another company'])->status(Response::HTTP_UNPROCESSABLE_ENTITY);
//                }
//                if (! $user->can('edit item unit price in combo') && $unitPrice != $itemModel->unit_price) {
//                    throw new AuthorizationException('You do not have permission to change the unit price of item: '.$itemModel->main_code, Response::HTTP_FORBIDDEN);
//                }

//                if (! $user->can('edit item description in combo') && $description != $itemModel->main_description) {
//                    throw new AuthorizationException('You do not have permission to change the description of item: '.$itemModel->main_code, Response::HTTP_FORBIDDEN);
//                }

                if ($itemModel->company_id === $combo->company_id) {

                    $isAttached = $combo->items()->where('item_id', $id)->exists();
                    if (! $isAttached) {

                       // $combo->items()->attach($id, ['description' => $description, 'unit_price'=> $unitPrice, 'quantity' => $quantity, 'discount' => $discount]);

                        $combo->save();
                    }
                }
            }

             $comboTotal = $combo->items->sum('total');
            $combo->total = $comboTotal;
            $combo->img_path=$path;
            $combo->save();
            $message = 'Combo created successfully';

            DB::commit();

            return $this->successResponse(new ComboResource($combo), $message, Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            $status = $e instanceof ValidationException ? $e->status : Response::HTTP_INTERNAL_SERVER_ERROR;

            return $this->errorResponse($e->getMessage(), $status);
        }
    }

    /**
     * @return mixed
     */
    public function edit(string $id)
    {
        try {
            $combo = Combo::find($id);
            if (! $combo) {
                return $this->errorResponse('Client not found.', Response::HTTP_NOT_FOUND);
            }
            $this->authorize('update', $combo);
            $user = Auth::user();
            $data['combo'] = $combo;
            $data['items'] = ItemService::getAll($user->can('view company'), $user->company_id);

            $message = 'Data needed for combo edit retrieved successfully';

            return $this->successResponse($data, $message, Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return mixed
     */
    public function update(UpdateComboRequest $request, string $id)
    {
        DB::beginTransaction();

        try {
            $combo = Combo::find($id);

            if (! $combo) {
                return $this->errorResponse('Combo not found.', Response::HTTP_NOT_FOUND);
            }

            $this->authorize('update', $combo);

            $user = Auth::user();

            // ------------------------------------
            // Update only provided combo fields
            // ------------------------------------
            $comboData = $request->only([
                'name',
                'code',
                'description',
                'currency_id',
                'brand',
            ]);

            if (! empty($comboData)) {
                $combo->update($comboData);
            }

            // ------------------------------------
            // Image update (only if provided)
            // ------------------------------------
            if ($request->hasFile('image')) {

                if ($combo->img_path) {
                    Storage::disk('public')->delete($combo->img_path);
                }

                $combo->img_path = $request
                    ->file('image')
                    ->store('combos/'.$combo->id, 'public');

                $combo->save();
            }

            // ------------------------------------
            // Update combo items (partial update)
            // ------------------------------------
            $items = $request->input('items', []);

            foreach ($items as $itemData) {

                $comboItem = $combo->items()
                    ->where('item_id', $itemData['id'])
                    ->first();

                if (! $comboItem) {
                    continue; // item غير موجود بالـ combo → لا نعدله
                }



//
//                if (
//                    ! $user->can('edit item unit price in combo') &&
//                    isset($itemData['unitPrice']) &&
//                    $itemData['unitPrice'] != $itemModel->unit_price
//                ) {
//                    throw new AuthorizationException(
//                        'You do not have permission to change the unit price of item: ' . $itemModel->main_code,
//                        Response::HTTP_FORBIDDEN
//                    );
//                }
//
//                if (
//                    ! $user->can('edit item description in combo') &&
//                    isset($itemData['description']) &&
//                    $itemData['description'] != $itemModel->main_description
//                ) {
//                    throw new AuthorizationException(
//                        'You do not have permission to change the description of item: ' . $itemModel->main_code,
//                        Response::HTTP_FORBIDDEN
//                    );
//                }

                // Apply partial update
                $comboItem->update([
                    'description' => $itemData['description'] ?? $comboItem->description,
                    'unit_price'  => $itemData['unit_price'] ?? $comboItem->unit_price,
                    'quantity'    => $itemData['quantity'] ?? $comboItem->quantity,
                    'discount'    => $itemData['discount'] ?? $comboItem->discount,
                ]);

                // Recalculate line total
                $subTotal = $comboItem->unit_price * $comboItem->quantity;
                $discountAmount = $subTotal * ($comboItem->discount / 100);
                $comboItem->total =
                $total = $subTotal - $discountAmount;

                $comboItem->save();
            }

            // ------------------------------------
            // Recalculate combo total
            // ------------------------------------
            $combo->total = $combo->items()->sum('total');
            $combo->save();

            DB::commit();

            return $this->successResponse(
                new ComboResource($combo->fresh('items')),
                'Combo updated successfully',
                Response::HTTP_OK
            );

        } catch (Exception $e) {
            DB::rollBack();

            $status = $e instanceof ValidationException
                ? $e->status
                : Response::HTTP_INTERNAL_SERVER_ERROR;

            return $this->errorResponse($e->getMessage(), $status);
        }
    }


    /**
     * @return mixed
     */
    public function destroy(string $id)
    {
        try {
            $combo = Combo::find($id);
            if (! $combo) {
                return $this->errorResponse('Combo not found.', Response::HTTP_NOT_FOUND);
            }
            $this->authorize('delete', $combo);


            $combo->items()->delete();
            $combo->delete();
            $message = 'Combo deleted successfully.';

            return $this->successResponse(null, $message, Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
