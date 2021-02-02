<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class AddDefaultRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sattviki:default-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Used to create default Roles.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Artisan::call('sattviki:permission-sync');
        $superadmin = Role::firstOrCreate([
            'name' => 'SuperAdmin',
            'slug' => 'superadmin',
            'description' => 'SuperAdmin',
        ]);

        $permissions = [];
        $permissionsToExpectForAdmin = [];

        foreach (config('permission_collections') as $item => $value) {
            foreach ($value['permissions'] as $permission) {
                $permissions[] = Permission::where('name', $permission['label'])->first()->id;
            }
        }

        $superadmin->syncPermissions($permissions);

        foreach ($permissionsToExpectForAdmin as $pr) {
            $superadmin->detachPermission(Permission::query()->find($pr));
        }

        $driver = Role::firstOrCreate([
            'name' => 'Driver',
            'slug' => 'driver',
            'description' => 'driver',
        ]);

        $supplier = Role::firstOrCreate([
            'name' => 'Supplier',
            'slug' => 'supplier',
            'description' => 'supplier',
        ]);
    }
}
