<?php

use Illuminate\Database\Seeder;
use App\Models\QuinaTaxa;

class QuninhaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {       
        $q=0;
            for($i=5; $i<=45; $i++) {
                QuinaTaxa::create([
                    'dezena' => $i,
                    'taxa'   => 0,
                    'status' => 1,
                    'site_id' => env('ID_SITE')
                ]);
             $q++;
            }
    }
}