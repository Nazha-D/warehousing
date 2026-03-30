<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Permission Resources
    |--------------------------------------------------------------------------

    */
    'resources' => [

        'core' => [
            'user',
            'role',
            'permission',
        ],

        'master_data' => [
            'item',
            'category',
            'item_group',
            'client',
        ],

        'settings' => [
            'currency',
            'exchange_rate',
            'tax_rate',
            'taxation_group',
            'price_list',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Standard Actions
    |--------------------------------------------------------------------------

    */
    'actions' => [
        'view',
        'create',
        'update',
        'delete',
        'restore',
        'force_delete',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Permissions for Newly Created Roles
    |--------------------------------------------------------------------------
    | Principle of Least Privilege
    */
    'default_role_permissions' => [
        'item.view',
        'category.view',
        'client.view',
        'price_list.view',
    ],
    'assignable' => [
        'item.create',
        'item.update',
        'item.delete',

    ],
    
    'role_templates' => [

        'cashier' => [
            'item.view',
            'category.view',
            'client.view',
        ],

        'admin' => [
            'item.*',
            'category.*',
            'client.*',
            'price_list.view',
        ],



    ],

    /*
    |--------------------------------------------------------------------------
    | Super Admin Permission
    |--------------------------------------------------------------------------

    */
    'super_admin_permission' => 'system.super_admin',

];
