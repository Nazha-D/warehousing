<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\Company;
use App\Models\Currency;
use App\Models\TaxationGroup;
use App\Models\TaxRate;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CompanyObserver
{
    public function created(Company $company)
    {
//        // 1. تحديد الشركة الحالية للـ Spatie
        app(PermissionRegistrar::class)
            ->setPermissionsTeamId($company->id);

        // 2. إنشاء رول Super Admin للشركة
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'company_id' => $company->id,
            'guard_name' => 'web',
        ]);
//

        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@' . strtolower($company->name) . '.com',
            'password' => Hash::make('password123'),
            'company_id' => $company->id,
        ]);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);

        $user->assignRole($superAdminRole);
        $usd = Currency::where('code', 'USD')->first();

        $company->currencies()->attach($usd->id, [
            'is_default' => true
        ]);
        $taxationGroup = TaxationGroup::create([
            'company_id' => $company->id,
            'name' => 'Standard',
            'code' => 'STANDARD',
            'active' => true,

        ]);

        TaxRate::create([
            'taxation_group_id' => $taxationGroup->id,
            'tax_rate' => 11.00,
            'start_date' => now(),
        ]);
        Category::create(
            [
                'category_name'=>'STANDARD',
                'company_id'=>$company->id
            ]
        );


    }
}
