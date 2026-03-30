<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Roles
        $roles = [

            'warehouse-manager' => 'sanctum',
            'sales-rep' => 'sanctum',
            'accountant' => 'sanctum',
            'delivery-staff' => 'sanctum',
        ];

        foreach ($roles as $roleName => $guard) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);

            // Assign permissions to each role
            switch ($roleName) {

                case 'warehouse-manager':
                    $role->givePermissionTo([
                        'view_items', 'create_items', 'edit_items', 'adjust_stock', 'view_stock'
                    ]);
                    break;
                case 'sales-rep':
                    $role->givePermissionTo([
                        'view_orders', 'create_orders', 'view_items', 'view_stock'
                    ]);
                    break;
                case 'accountant':
                    $role->givePermissionTo([
                        'view_orders', 'approve_orders', 'view_reports', 'create_invoices'
                    ]);
                    break;
                case 'delivery-staff':
                    $role->givePermissionTo([
                        'view_delivery', 'post_delivery', 'edit_delivery_status'
                    ]);
                    break;
            }
        }
    }
    }

