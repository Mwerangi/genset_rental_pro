<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['key' => 'super_admin',        'label' => 'Super Admin',        'badge_color' => 'bg-red-100 text-red-800',     'is_system' => true,  'sort_order' => 1],
            ['key' => 'admin',              'label' => 'Admin',              'badge_color' => 'bg-orange-100 text-orange-800','is_system' => true,  'sort_order' => 2],
            ['key' => 'finance_manager',    'label' => 'Finance Manager',    'badge_color' => 'bg-green-100 text-green-800', 'is_system' => false, 'sort_order' => 3],
            ['key' => 'operations_manager', 'label' => 'Operations Manager', 'badge_color' => 'bg-blue-100 text-blue-800',   'is_system' => false, 'sort_order' => 4],
            ['key' => 'sales_manager',      'label' => 'Sales Manager',      'badge_color' => 'bg-purple-100 text-purple-800','is_system' => false,'sort_order' => 5],
            ['key' => 'dispatcher',         'label' => 'Dispatcher',         'badge_color' => 'bg-yellow-100 text-yellow-800','is_system' => false,'sort_order' => 6],
            ['key' => 'driver',             'label' => 'Driver',             'badge_color' => 'bg-cyan-100 text-cyan-800',   'is_system' => false, 'sort_order' => 7],
            ['key' => 'technician',         'label' => 'Technician',         'badge_color' => 'bg-teal-100 text-teal-800',   'is_system' => false, 'sort_order' => 8],
            ['key' => 'accountant',         'label' => 'Accountant',         'badge_color' => 'bg-indigo-100 text-indigo-800','is_system' => false,'sort_order' => 9],
            ['key' => 'staff',              'label' => 'Staff',              'badge_color' => 'bg-gray-100 text-gray-700',   'is_system' => false, 'sort_order' => 10],
        ];

        foreach ($roles as $data) {
            Role::firstOrCreate(['key' => $data['key']], $data);
        }

        Role::clearCache();
    }
}
