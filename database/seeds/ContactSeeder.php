<?php

use Illuminate\Database\Seeder;
use App\Domain\Models\Contact;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Contact::query()->truncate();

        factory(Contact::class, 12)->create();
    }
}
