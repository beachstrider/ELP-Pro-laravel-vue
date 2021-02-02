<?php

use App\Domain\Models\LogisticType;
use Illuminate\Database\Seeder;

class LogisticTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        LogisticType::query()->truncate();
        factory(LogisticType::class, 4)->create();
        factory(LogisticType::class, 4)->create(['is_active' => 0]);
    }
}
