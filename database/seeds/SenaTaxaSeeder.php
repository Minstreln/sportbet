<?php

use Illuminate\Database\Seeder;
use App\Models\SenaTaxa;

class SenaTaxaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $q=0;
            for($i=6; $i<=40; $i++) {
                SenaTaxa::create([
                    'dezena' => $i,
                    'taxa'   => 0,
                    'status' => 1,
                    'site_id' => env('ID_SITE')
                ]);
             $q++;
            }
    }
}
