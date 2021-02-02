<?php

use App\Domain\Models\Contract;
use App\Domain\Models\User;
use Illuminate\Database\Seeder;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Contract::query()->truncate();
        factory(Contract::class, 3)->create(['supplier_id' => User::query()->inRandomOrder()->first()->id]);
        factory(Contract::class, 3)->create(['supplier_id' => User::query()->inRandomOrder()->first()->id]);
        factory(Contract::class, 3)->create(['supplier_id' => User::query()->inRandomOrder()->first()->id]);
        factory(Contract::class, 3)->create(['supplier_id' => User::query()->inRandomOrder()->first()->id]);
    }
}
