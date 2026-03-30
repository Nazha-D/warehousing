<?php
// database/seeders/InitialSeeder.php
namespace Database\Seeders;
use App\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use Spatie\Permission\Models\Role as SpatieRole;

class InitialSeeder extends Seeder
{
public function run()
         {
         $company = Company::firstOrCreate([
             'name' => 'Default Company',
              'email' => 'company@example.com',
                   ]);
             SpatieRole::firstOrCreate([
                 'name'=>'system-admin',

                 'guard_name'=>'web'
             ]);
               $admin = User::firstOrCreate([
                      'email' => 'system-admin@email.com',
                 'name' => 'System Admin',
                 'password' => bcrypt('password123'),
                 //'company_id' => $company->id,
                 'is_active' => true,
                   ]);
            app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
               $admin->assignRole('system-admin');
}
}
