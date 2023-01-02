<?php

use Illuminate\Database\Seeder;
use App\Models\InfoBanca;

class InfoBancaClasse extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        InfoBanca::create([
            'logo'  => 0, 
            'banner1' => 0, 
            'banner2' => 0, 
            'banner3' => 0, 
            'regulamento' => 'texto', 
            'site_id' => env('ID_SITE'),
        ]);
    }
}
