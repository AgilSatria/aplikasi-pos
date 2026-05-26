<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $permissions = [

            // Dashboard
            'menu_dashboard',
            'view_dashboard_statistics',

            // Product Categories
            'menu_product_categories',
            'view_product_categories',
            'create_product_categories',
            'edit_product_categories',
            'delete_product_categories',

            // Products
            'menu_products',
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',

            // Customers
            'menu_customers',
            'view_customers',
            'create_customers',
            'edit_customers',
            'delete_customers',

            // Transaction
            'menu_transactions',
            'menu_pos',
            'view_transactions',
            'create_transactions',

        ];


        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $adminRole->syncPermissions(Permission::all());


        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);
        $cashierRole->syncPermissions([
            'menu_dashboard',
            'view_dashboard_statistics',
            'view_product_categories',
            'view_products',
            'view_customers',
            'create_customers',
            'menu_pos',
            'menu_transactions',
            'view_transactions',
            'create_transactions'
        ]);
    }
}
