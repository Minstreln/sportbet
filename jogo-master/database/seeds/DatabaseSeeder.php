<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            InfoBancaClasse::class,
            ConfiguracaoSeeder::class,
            SenaTaxaSeeder::class,
            QuninhaSeeder::class
        ]);
        
      
    }
}
