<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Match;
use App\Models\Mercado;
use App\Models\Odd;
use App\Models\HomeMatch;
use App\Models\AmanhaMatch;
use App\Models\AferTomorrow;
use App\Models\BlockLeague;
use App\Models\BlockMatch;
use App\Models\ConfigMercados;
use App\Models\ConfigOdd;
use App\Models\Configuracao;
use App\Models\BlockOddMatch;
use App\Models\MainLeague;
use App\Models\LiveMatch;
use App\User;

use App\Events\LoadLigasMain;

class LigasMain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:loadLigasMain';

    private $arr = array();
    private $hoje;
    private $amanha;
    private $agora;
    private $token;
    private $matchs;
   

   

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
        $this->hoje     = $hoje     = Carbon::today();
        $this->amanha   = $amanha   = Carbon::tomorrow();
        $this->agora    = $agora    = Carbon::now();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */



    public function getLeaguesMain() 
    {
         $mainleagues = MainLeague::where('site_id', env('ID_SITE'))->get();


            $liga = array();
            foreach($mainleagues as $league) {
                $liga[] =$league->league;
            }

            //Bloqueio de odd limite data limites
            $confs = Configuracao::where('site_id', env('ID_SITE'))
                                        ->get();


            foreach($confs as $conf) {

                $odd_z =  $conf->bloquear_odd_abaixo;
                $date_limite_matchs = $conf->data_limite_jogos;

            }

           $bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();

            $arr = array();
               
           
            foreach($bloqueadas as $bloqueada) {

                    $arr[] = $bloqueada->league;
            
            }
               
              return  $leagues = Match::select('league')
                                ->where('date' ,'>=', $this->agora)
                                ->where('date', '<=' ,  $date_limite_matchs.' 23:59:00')
                                ->where('visible', 'Sim')
                                ->whereNotIn('league', $arr)
                                ->whereIn('league', $liga)
                                ->groupBy('league')
                                ->orderBy('league', 'asc')
                                //->limit(30)
                                ->get();

    }

    public function handle()
    {
        broadcast(new LoadLigasMain($this->getLeaguesMain()->toArray()));
    }
}
