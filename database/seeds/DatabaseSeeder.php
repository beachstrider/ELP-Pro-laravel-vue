<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Artisan::call('passport:install');
        Schema::disableForeignKeyConstraints();
        DB::table('role_user')->truncate();
        DB::table('permission_user')->truncate();
        DB::table('permission_role')->truncate();
        Role::truncate();
        Permission::truncate();
        $this->call(\RoleSeeder::class);
        $this->call(\UserSeeder::class);
        $this->call(\BrandSeeder::class);
        $this->call(\ModelSeeder::class);
        $this->call(\LocationTypeSeeder::class);
        $this->call(\ContactSeeder::class);
        $this->call(\LocationSeeder::class);
        $this->call(\LogisticTypeSeeder::class);
        $this->call(\ContractSeeder::class);
        $this->call(\TransportVehicleSeeder::class);
        $this->call(\DriverSeeder::class);
        $this->call(\RouteSeeder::class);
        $this->call(\ClientSeeder::class);
        $this->call(\PriceSeeder::class);
        $this->call(\ManufacturerSeeder::class);
    }
}
