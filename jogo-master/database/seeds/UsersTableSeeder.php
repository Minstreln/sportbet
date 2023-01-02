<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Models\Adm;
use App\Models\Gerente;
use App\Models\Cambista;
use App\Models\Aposta;
use App\Models\Palpite;
use App\Models\Configuracao;
use App\Models\ConfigMercados;
use App\Models\ConfigOdd;
use App\Models\OddMarcket;
use App\Models\Marcket;


class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'adm_id' => 0,
            'gerente_id'=> 0 ,
            'name'	=> 'Admistrador',
            'username'=> env('ID_SITE'),
            'email'	=> env('ID_SITE').'@gmail.com',
            'password'=>  bcrypt('123456'),
            'nivel'	=> 'adm',
            'site_id' => env('ID_SITE'),
            'situacao'=> 'ativo',
            'contato'	=> '0',
            'endereco'=> 'Ruda G',
            'comissao1'=> 0,
            'comissao2'=> 0,
            'comissao3'=> 0,
            'comissao4'=> 0,
            'comissao5'=> 0,
            'comissao6'=> 0,
            'comissao7'=> 0,
            'comissao8'=> 0,
            'comissao9'=> 0,
            'comissao10'=> 0,
            'saldo_casadinha'=> 0,
            'saldo_simples'	=> 0,
            'saldo_gerente'=> 0,
            'comissao_gerente' => 0,
            'entradas' => 0,
            'entradas_abertas' => 0,
            'saidas' => 0,
            'lancamentos' => 0,
            'comissoes' => 0,
        ]);

        echo $user->id;

        $mercados = Marcket::orderBy('order', 'ASC')->get();

        foreach($mercados as $mercado) {

            $configMercado = ConfigMercados::where('name', $mercado->name)->where('site_id', $user->site_id)->first();

            if($configMercado) {

            } else {
                echo $mercado->name."\n";
                ConfigMercados::create([
                    'user_id'   => $user->id,
                    'site_id' => $user->site_id,
                    'name' => $mercado->name,
                    'porcentagem' => 0,
                    'status' => 1
                ]);
            }
        }

        $odds = OddMarcket::get();

        foreach($odds as $odd) {

            $odd_marcket = ConfigOdd::where('name', $odd->odd)->where('site_id', $user->site_id)->first();

            if($odd_marcket) {

            } else {
                echo $odd->odd."\n";
                ConfigOdd::create([
                    'mercado_name' => $odd->mercado,
                    'user_id'   => $user->id,
                    'site_id' => $user->site_id,
                    'porcentagem' => 0,
                    'header' => 0,
                    'mercado_full_name' => $odd->odd,
                    'name' =>  $odd->odd,
                    'status' => 1
                ]);
            }
        }

    }
}


