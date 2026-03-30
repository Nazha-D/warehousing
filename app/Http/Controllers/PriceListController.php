<?php

namespace App\Http\Controllers;

use App\DTO\ResolvedPrice;
use App\Http\Requests\PriceListRequests\StorePriceListRequest;

use App\Http\Requests\PriceListRequests\UpdatePriceListRequest;
use App\Models\Item;
use App\Models\PriceList;
use App\Models\Client;
use App\Models\PriceListRule;
use App\Services\PriceListService;
use App\Services\Pricing\PreviewPriceService;
use App\Services\Pricing\PriceCalculator;
use App\Services\Pricing\RuleResolver;
use App\Traits\ApiResponseTrait;
use App\Enums\Pricing\BaseSourceEnum;
use Illuminate\Http\Request;

class PriceListController extends Controller
{
    use ApiResponseTrait;
    protected $service=null;
    protected $previewPriceService=null;
    public function __construct() {
        $this->service=new PriceListService();
        $this->previewPriceService= new PreviewPriceService();
    }

    public function index(Request $request)
    {
        try {
            // $this->authorize('viewAny', Pricelist::class);
            $user = auth()->user();
            $options = [
                'perPage' => $request->query('perPage'),
                'isPaginated' => $request->query('isPaginated'),
                'search'=>$request->query('search'),

            ];
            $priceLists = PricelistService::getAll($user->company_id, $options);

            return $this->successResponse($priceLists,'Got price lists successfully',200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }


    public function create()
    {
        try {

            $this->authorize('create', Pricelist::class);
            $user = \Auth::user();
            $data['price_lists'] =$user->company->pricelists()->get(['id','name']);

            $message = 'Data needed for price list creation retrieved successfully';

            return $this->successResponse($data, $message, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(),  500);
        }
    }

    public function show(PriceList $priceList)
    {
        try {

            $this->authorize('view', $priceList);
            $user = \Auth::user();
            $priceList->load(['items','rules','rules','rules.category','rules.item','parent','currency','client']);

            $message = 'Data retrieved successfully';

            return $this->successResponse($priceList, $message, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(),  500);
        }
    }
public function store(StorePriceListRequest $request)
{
   $this->authorize('create',PriceList::class);
    $priceList = $this->service->create(
        $request->validated()
    );
    $priceList->update(['company_id'=>auth()->user()->company_id]);
    return $this->successResponse(
        $priceList,
        'Price list created successfully',
        201
    );
}

    public function update(
        UpdatePriceListRequest $request,
        PriceList $priceList
    ) {
        $this->authorize('update',$priceList);
        $priceList = $this->service->update(
            $priceList,
            $request->validated()
        );

        return $this->successResponse(
            $priceList,
            'Price list updated successfully'
        );
    }
    public function preview(
        Request $request,
        PriceList $priceList
    ) {
//        $validated = $request->validate([
//            'item_id' => ['nullable', 'integer'],
//        ]);

        // دالة preview بالسيرفيس
        $resolvedPrices =$this->previewPriceService->preview(
            $priceList,
            $request->item_ids ?? null
        );

        // تحويل ResolvedPrice objects لمصفوفة مناسبة للفرونت
        $items = array_map(fn($resolved) => [
            'item_id' => $resolved->item->id,
            'unit_price' => $resolved->unitPrice,
            'currency_id' => $resolved->currencyId,
            'source' => $resolved->source,
            'is_fallback' => $resolved->isFallback,
            'applied_rules' => $resolved->appliedRules,
            'price_list' => $resolved->priceList ? [
                'id' => $resolved->priceList->id,
                'name' => $resolved->priceList->name,
                'company_id' => $resolved->priceList->company_id,
            ] : null,
        ], $resolvedPrices);

        return $this->successResponse([
            'items' => $items
        ]);


      // $client= Client::find(1);
     // return  RuleResolver::getApplicableRules($item,$priceList);



}

}
