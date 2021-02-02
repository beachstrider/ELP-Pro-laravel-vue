<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use jeremykenedy\LaravelRoles\Models\Permission;

class SyncPermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sattviki:permission-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Used to sync system permission with database from permission_collections config file.';

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
     * @return mixed
     */
    public function handle()
    {
        $permissions = config('permission_collections', []);
        foreach ($permissions as $key => $value) {
            foreach ($value['permissions'] as $p_key => $permission) {
                Permission::firstOrCreate([
                    'name' => $permission['label'],
                    'slug' => $permission['permission'],
                    'description' => $permission['permission'],
                    'model' => 'Permission',
                ]);
            }
        }

        echo 'Permission table refreshed.';

        return true;
    }
}
