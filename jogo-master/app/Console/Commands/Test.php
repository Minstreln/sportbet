<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ConfigMercados;
use App\Models\ConfigOdd;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:teste';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $comissao = 10/100;

        $valor = 50;

        echo $result = $valor * $comissao;
        //  $mercados_alterados = ConfigMercados::where('site_id', env('ID_SITE'))
        //                         //->where('name', $mercado->mercado_name)
        //                         ->where('status', 1)
        //                         ->where('porcentagem', '!=', 0)
        //                         ->where('user_id', env('ID_USER'))
        //                         ->get();

        //  $m_g = array();
        //  foreach($mercados_alterados as $m) {
        //      $m_g[] = $m->name;
        //  }

           

        //   if(in_array('Vencedor do Encontro', $m_g)) {
        //       echo "Edstá aqui dentro\n";
        //   }

        // $m_g = array();                        
        // foreach($m_porcents_geral as $m_geral) {
        //     $m_g = $m_geral->name;
        // }


        //                         if(in_array('Vencedor do Encontro', $m_g)) {
        //                             echo "Esta dentro\n";
        //                         }

      // print_r($m_porcents_geral);
// $valueToSearch = "a";
// $arrayToSearch = array("a", "b", "c");
// dd(in_array($valueToSearch, $arrayToSearch));

//   $odd_alteradas = ConfigOdd::where('site_id', env('ID_SITE'))
//                     ->where('status', 1)
//                     ->where('porcentagem', '!=', 0)
//                     ->where('user_id', env('ID_USER'))
//                     ->get();

//                     //dd(count($odd_alteradas));

//             $o_g = array();
//             foreach($odd_alteradas as $o) {
//                 $o_g[] = $o->name;
//             }

//             dd(in_array("Casa", $o_g));

    }
}
