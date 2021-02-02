<?php

use Illuminate\Database\Seeder;
use App\Domain\Models\Client;
use App\Domain\Models\ClientBrand;
use App\Domain\Models\ClientContact;
use App\Domain\Models\Location;
use App\Domain\Models\User;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        Client::query()->truncate();
        ClientBrand::query()->truncate();
        ClientContact::query()->truncate();
        DB::table('client_brand_models')->truncate();
        DB::table('client_contact_locations')->truncate();
        DB::table('client_dealers')->truncate();
        DB::table('client_locations')->truncate();
        factory(Client::class, 1)
        ->create()
        ->each(function($client) {
            $client->main_location_id = Location::query()->inRandomOrder()->first()->id;
            $client->save();
            $client->dealers()->attach(User::query()->inRandomOrder()->limit(2)->get()->pluck('id') );
        });

        DB::commit();
    }
}
