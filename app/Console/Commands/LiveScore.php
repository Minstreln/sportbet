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
use App\Events\LiveFutebol;
use App\Events\LoadMatchLiveScore;
use App\Jobs\LoadMatchLive;

class LiveScore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:liveScore';

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
            //  while(1) {
                    $dados_live = Configuracao::where('site_id', env('ID_SITE'))
                                            ->get();

                        foreach($dados_live as $live) {
                        $cotacao_live =  $live->cotacao_live;
                        $time_live = $live->time_live;
                        }



                        $bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();

                        $leageBlock = array();


                        foreach($bloqueadas as $bloqueada) {

                        $leageBlock[] = $bloqueada->league;

                        }

                        $block_match = array();

                        $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();



                        foreach($matchs_bloqueadas as $match_bloqueada) {

                        $block_match[] = $match_bloqueada->event_id;

                        }

                        $block_match = array();

                        $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();



                        foreach($matchs_bloqueadas as $match_bloqueada) {

                        $block_match[] = $match_bloqueada->event_id;

                        }

                        //Mercado Bloqueados
                        $merc_blocks = ConfigMercados::where('status', 0)
                        ->where('site_id', env('ID_SITE'))
                        ->get();

                        $block_merc = array();

                        foreach($merc_blocks as $merc_block) {

                        $block_merc[] = $merc_block->name;

                        }


                        //Odd Bloqueadas
                        $odd_blocks = ConfigOdd::where('status', 0)
                        ->where('site_id', env('ID_SITE'))
                        ->where('user_id', env('ID_USER'))  
                        ->get();

                        $block_odd= array();

                        foreach($odd_blocks as $odd_block) {

                        $block_odd[] = $odd_block->name;

                        }

                        //Bloqueio de odd limite data limites
                        $confs = Configuracao::where('site_id', env('ID_SITE'))
                        ->get();


                        foreach($confs as $conf) {

                        $odd_z =  $conf->bloquear_odd_abaixo;
                        $date_limite_matchs = $conf->data_limite_jogos;
                        $odd_m = $conf->travar_odd_acima;

                        }

                        $percent_merc_user = 0;
                        $percent_odd_user = 0;


                        //Odds alteradas
                        $odds_alteradas = BlockOddMatch::where('site_id', env('ID_SITE'))
                        ->where('status', 1)
                        ->get();

                        $arr_odd_alterada = array();

                        foreach($odds_alteradas as $odd_alterada) {
                        $arr_odd_alterada[] = $odd_alterada->odd_id.$odd_alterada->odd;
                        }

                        //Odds bloqeuadas por partida
                        $odds_bloqueadas = BlockOddMatch::where('site_id', env('ID_SITE'))
                        ->where('status', 0)
                        ->get();


                        $arr_odd_b  = array();
                        foreach($odds_bloqueadas as $odd_bloqueada) {

                        $arr_odd_b[] = $odd_bloqueada->odd_uid;   

                        }

                        $matchs = Match::where( 'time_status', 1)
                                        ->where('time', '=<',  $time_live)
                                        ->whereNotIn('league', $leageBlock)
                                        ->whereNotIn('event_id', $block_match)
                                        ->orderBy('league', 'asc')
                                        ->get();

                        foreach($matchs as $match) {
                        $event = Match::find($match->id);
                        broadcast(new LoadMatchLiveScore($event));
                        LoadMatchLive::dispatchNow($event, $match->id);
                        }

            //     sleep(25);
            //  }               
               
      
     

    }
}
