<?php
namespace App\Services;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;

class CompanyCurrencyService
{


    public static function getAll( $userCompanyId, $options = [])
    {
        $perPageDefault = 10;
        $isPaginated = true;
        $searchDefault = '';


        $perPage = $options['perPage'] ?? $perPageDefault;
        $isPaginated = json_decode($options['isPaginated'] ?? $isPaginated);
        $search = $options['search'] ?? $searchDefault;


        $currencies = Currency::query();


        $currencies->where('company_id', $userCompanyId);
        $currencies->filter($search);



        //$roles->filter($search);


        if ($isPaginated) {
            $currencies = $currencies->with('permissions')->paginate($perPage);
        } else {
//            $roles = $roles->with(['permissions' => function ($query) use ($search) {
//                $query->where('name', 'like', $search); // Load only permissions that match "edit"
//            }])->get();
            $currencies = $currencies->with('permissions')->get();
        }

        return $currencies;
    }


    public function store(int $companyId, array $currencyIds): void
    {
        DB::table('company_currencies')
            ->where('company_id', $companyId)
            ->delete();

        $rows = collect($currencyIds)->map(fn ($id) => [
            'company_id'  => $companyId,
            'currency_id' => $id,
        ])->toArray();

        DB::table('company_currencies')->insert($rows);
    }


    public function syncCurrencies(int $companyId, array $currencyIds): void
    {
        DB::table('company_currencies')
            ->where('company_id', $companyId)
            ->delete();

        $rows = collect($currencyIds)->map(fn ($id) => [
            'company_id'  => $companyId,
            'currency_id' => $id,
        ])->toArray();

        DB::table('company_currencies')->insert($rows);
    }
}
