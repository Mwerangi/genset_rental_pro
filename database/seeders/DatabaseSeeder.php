<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\ChartOfAccountsSeeder;
use Database\Seeders\ExpenseCategorySeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            PermissionsSeeder::class,
            ChartOfAccountsSeeder::class,
            ExpenseCategorySeeder::class,
        ]);

        User::firstOrCreate(
            ['email' => 'admin@milelepower.co.tz'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password123'),
                'role'     => 'super_admin',
                'status'   => 'active',
            ]
        );
    }
}
