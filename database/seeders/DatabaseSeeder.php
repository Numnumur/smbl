<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingLocalizationSeeder::class,
            UserSeeder::class,
            CustomerSeeder::class,
            OrderPackageSeeder::class,
            DiscountSeeder::class,
            OrderSeeder::class,
            ExpenseSeeder::class,
            PickupDeliverySeeder::class,

            PermissionSeeder::class,
            RoleSeeder::class,
            RoleHasPermissionSeeder::class,
            ModelHasRoleSeeder::class,
        ]);
    }
}
