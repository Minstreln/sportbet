<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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

class LoadEventLive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $hoje;
    private $amanha;
    private $agora;
    private $token;
    private $matchs;

    public function __construct()
    {
        $this->hoje     = $hoje     = Carbon::today();
        $this->amanha   = $amanha   = Carbon::tomorrow();
        $this->agora    = $agora    = Carbon::now();
    }

    /**
     * Execute the job.
     *
     * @return void
     */

    public function liveFutebol() 
    {

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

$return = array();

$leagues = Match::select('league')
                ->where( 'time_status', 1)
                ->where('time', '<=',  $time_live)
                ->whereNotIn('league', $leageBlock)
                ->whereNotIn('event_id', $block_match)
                ->groupBy('league')
                ->orderBy('league', 'asc')
                ->get();



$i=0;

foreach($leagues  as $league) {


$return[$i]['league'] = $league->league;

$matchs = Match::where('league', $league->league)
                ->where('time_status' , 1)
                ->where('time', '<=',  $time_live)
                ->with('odds')
                ->get();

                $j=0;
                foreach($matchs as $match) {
                    $return[$i]['match'][$j]['id'] = $match->id;
                    $return[$i]['match'][$j]['event_id'] = $match->event_id;
                    $return[$i]['match'][$j]['sport'] = $match->sport_name;
                    $return[$i]['match'][$j]['confronto'] = $match->confronto;
                    $return[$i]['match'][$j]['home'] = $match->home;
                    $return[$i]['match'][$j]['image_id_home'] = $match->image_id_home;
                    $return[$i]['match'][$j]['away'] = $match->away;
                    $return[$i]['match'][$j]['image_id_away'] = $match->image_id_away;
                    $return[$i]['match'][$j]['score'] = $match->score;
                    $return[$i]['match'][$j]['date'] = $match->date;
                    $return[$i]['match'][$j]['time'] = $match->time;
                    $return[$i]['match'][$j]['halfTimeScoreHome']= $match->halfTimeScoreHome;
                    $return[$i]['match'][$j]['halfTimeScoreAway']= $match->halfTimeScoreAway;
                    $return[$i]['match'][$j]['fullTimeScoreHome']= $match->fullTimeScoreHome;
                    $return[$i]['match'][$j]['fullTimeScoreAway']= $match->fullTimeScoreAway;
                    $return[$i]['match'][$j]['numberOfCornersHome']= $match->numberOfCornersHome;
                    $return[$i]['match'][$j]['numberOfCornersAway']= $match->numberOfCornersAway;
                    $return[$i]['match'][$j]['numberOfYellowCardsHome']= $match->numberOfYellowCardsHome;
                    $return[$i]['match'][$j]['numberOfYellowCardsAway']= $match->numberOfYellowCardsAway;
                    $return[$i]['match'][$j]['numberOfRedCardsHome']= $match->numberOfRedCardsHome;
                    $return[$i]['match'][$j]['numberOfRedCardsAway']= $match->numberOfRedCardsAway;
                    $count = Odd::where('match_id', $match->id)->count();
                    $return[$i]['match'][$j]['count_odd'] = $count;


                    if($count == 0 || count($match->odds) == 0) {

                        for($q = 0; $q < 3; $q++) {
                           
                            $return[$i]['match'][$j]['odds'][$q]['id'] = $match->event_id.$match->id.$q;
                            $return[$i]['match'][$j]['odds'][$q]['group_opp'] = 'Vencedor do Encontro';
                            $return[$i]['match'][$j]['odds'][$q]['odd'] = $q;
                            $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                        }

                    } else {

                          $q=0;
                          foreach($match->odds as $odd) {
                                $redu = $odd->cotacao-1;
                                $odd_final = ($redu*$cotacao_live/100)+$redu+1;
                                if ($odd_final <= $odd_z) { 
                                    $cota =  0;
                                } 
                                else {
                                    $cota =  $odd_final;
                                }
                                $return[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;
                                $return[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                $return[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->mercado_name;
                                $return[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                                $return[$i]['match'][$j]['odds'][$q]['cotacao'] = round($cota, 2);
                                $return[$i]['match'][$j]['odds'][$q]['cotacaoOriginal'] = $odd->cotacao;
                                $return[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;


                          $q++;
                          }

                        }


                $j++;
                }





                $i++;
                }

                return $return;
    }

    public function handle()
    {
        broadcast(new  LiveFutebol($this->liveFutebol()));
    }
}
