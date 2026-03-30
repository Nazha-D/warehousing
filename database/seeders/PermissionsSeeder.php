<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources = [
            'user',
            'role',
            'client',
            'item',
            'category',
            'item_group',
            'price_list',
            'currency',
            'exchange_rate',
            'tax_rate',
            'taxation_group',
            'cashing_method',
            'warehouse',
            'replenishment',
            'transfer',
            'combo',
            'quotation',
            'company_headers',
            'delivery',
            'sales_order',
            'sales_invoice',
            'pos_terminal',
            'pos_session',
            'pos_invoice'
        ];

        $actions = [
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'force_delete',

        ];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$resource}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }
        $customFieldPermissions = [
            'quotation.edit_item_price',
            'quotation.edit_item_description',
            'quotation.edit_combo_price',
            'quotation.edit_combo_description',
            'sales_order.edit_item_price',
            'sales_order.edit_item_description',
            'sales_order.edit_combo_price',
            'sales_order.edit_combo_description',
        ];

        foreach ($customFieldPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }

}
