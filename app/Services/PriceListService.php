<?php
namespace App\services;
use App\Models\PriceList;
use Illuminate\Support\Facades\DB;

class PriceListService {


    public static function getAll( $userCompanyId = null, $options = [])
    {
        $perPageDefault = 10;
        $isPaginated = false;
        $searchDefault = '';
        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $search = $options['search'] ?? $searchDefault;

        $pricelists = Pricelist::active();
        $pricelists->filter($search);

            $pricelists = $pricelists->where('company_id', $userCompanyId);
            if ($isPaginated) {
                $pricelists = $pricelists->paginate($perPage);
            } else {
                $pricelists = $pricelists->get();
            }

        return $pricelists;
    }

    public function create(array $data): PriceList
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? null;
            $rules = $data['rules'] ?? [];
            unset($data['items'],$data['rules']);

            $priceList = PriceList::create($data);

//            foreach ($rules as $rule) {
//                $priceList->rules()->create($rule);
//            }
            if ($rules) {
                $priceList->rules()->createMany($rules);
            }
            if ($items) {
                $priceList->items()->createMany($items);
            }
            return $priceList->load('rules');
        });
    }
    public function update(PriceList $priceList, array $data): PriceList
    {
        return DB::transaction(function () use ($priceList, $data) {

            $rules = $data['rules'] ?? null;
            unset($data['rules']);

            if (! empty($data)) {
                $priceList->update($data);
            }

            if (is_array($rules)) {
                $this->syncRules($priceList, $rules);
            }

            return $priceList->load('rules');
        });
    }
    protected function syncRules(PriceList $priceList, array $rules): void
    {
        $existingIds = $priceList->rules()->pluck('id')->toArray();
        $incomingIds = collect($rules)->pluck('id')->filter()->toArray();

        // Delete removed rules
        $priceList->rules()
            ->whereIn('id', array_diff($existingIds, $incomingIds))
            ->delete();

        foreach ($rules as $rule) {

            if (isset($rule['id'])) {
                $priceList->rules()
                    ->where('id', $rule['id'])
                    ->update($rule);
            } else {
                $priceList->rules()->create($rule);
            }
        }
    }



}
