<?php

use Illuminate\Database\Seeder;

class NotAvailableChangeStatusNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\LogisticRealizationItems::where('status', '=', 'not avalivable')->update(['status' => 'not_available']);
        \App\LogisticRealizationItems::where('status', '=', 'not_avalivable')->update(['status' => 'not_available']);
    }
}
