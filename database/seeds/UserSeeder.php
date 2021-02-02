<?php

use App\Domain\Models\DeletedUser;
use App\Domain\Models\Document;
use App\Domain\Models\User;
use Illuminate\Database\Seeder;
use jeremykenedy\LaravelRoles\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();
        DeletedUser::truncate();
        Document::truncate();
        $superAdmin = Role::whereSlug('superadmin')->first();

        $user = factory(User::class)->create([
            'email' => 'admin@admin.com',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'name' => 'SuperAdmin',
        ]);
        $user->attachRole($superAdmin);


        $driver = Role::whereSlug('driver')->first();

        $user = factory(User::class)->create([
            'email' => 'driver@driver.com',
            'first_name' => 'Driver',
            'last_name' => 'Last',
            'name' => 'Driver Last',
        ]);
        $user->attachRole($driver);



        $user = factory(User::class)->create([
            'email' => 'john@driver.com',
            'first_name' => 'John',
            'last_name' => 'tom',
            'name' => 'John tom',
        ]);
        $user->attachRole($driver);



    }
}
