<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Match;
use App\Models\Odd;
use App\Models\TomorowMatchFlash;
use App\Models\AmanhaMatch;
use App\Models\AferTomorowMatchFlash;
use App\Models\HomeMatchFlash;
use App\Models\BlockLeague;
use App\Models\BlockMatch;
use App\Models\ConfigMercados;
use App\Models\ConfigOdd;
use App\Models\Configuracao;
use App\Models\BlockOddMatch;
use App\Models\MainLeague;
use App\Models\LiveMatch;
use App\User;

use App\Jobs\LoadEventHoje;
use App\Jobs\LoadEventAmanha;
use App\Jobs\LoadEventAfer;

use Illuminate\Support\Facades\Cache;

class MatchController extends Controller
{

    private $arr = array();
    private $hoje;
    private $amanha;
    private $agora;
    private $token;
    private $matchs;
    private $block;


    public function __construct
        (
         Cache $cache,
         Configuracao $configuration,
         BlockLeague $leaguesBloqueadas,
         BlockMatch $matchsBlock
        )
    {

        $this->hoje     = $hoje     = Carbon::today();
        $this->amanha   = $amanha   = Carbon::tomorrow();
        $this->agora    = $agora    = Carbon::now();
        $this->cache    = $cache;

        $this->configuration        = $configuration;
        $this->leaguesBloqueadas    = $leaguesBloqueadas;
        $this->matchsBlock          = $matchsBlock;
        $this->matchsBloqueadas     = array();
        $this->arr                  = array();
        $this->leagueBloqueadas     = array();

    }



    public function loadLiveHome()
    {
        LoadEventHoje::dispatchNow();
        //LoadEventAmanha::dispatchNow();
        //LoadEventAfer::dispatchNow();
    }


    //Pesquisa liga
    public function searchLeague(Request $request)
    {
        $return = array();
        $block_match = array();

        $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();



        foreach ($matchs_bloqueadas as $match_bloqueada) {

            $block_match[] = $match_bloqueada->event_id;
        }

        //Mercado Bloqueados
        $merc_blocks = ConfigMercados::where('status', 0)
                                    ->where('site_id', env('ID_SITE'))
                                    ->get();

        $block_merc = array();

        foreach ($merc_blocks as $merc_block) {

            $block_merc[] = $merc_block->name;
        }


        //Odd Bloqueadas
        $odd_blocks = ConfigOdd::where('status', 0)
                                ->where('site_id', env('ID_SITE'))
                                ->where('user_id', env('ID_USER'))
                                ->get();

        $block_odd = array();

        foreach ($odd_blocks as $odd_block) {

            $block_odd[] = $odd_block->name;
        }

        //Bloqueio de odd limite data limites
        $confs = Configuracao::where('site_id', env('ID_SITE'))
                              ->get();


        foreach ($confs as $conf) {

            $odd_z =  $conf->bloquear_odd_abaixo;
            $date_limite_matchs = $conf->data_limite_jogos;
            $odd_m = $conf->travar_odd_acima;
        }

        //Odds alteradas
        $odds_alteradas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 1)
            ->get();

        $arr_odd_alterada = array();

        foreach ($odds_alteradas as $odd_alterada) {
            $arr_odd_alterada[] = $odd_alterada->odd_id . $odd_alterada->odd;
        }

        //Odds bloqeuadas por partida
        $odds_bloqueadas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 0)
            ->get();


        $arr_odd_b  = array();
        foreach ($odds_bloqueadas as $odd_bloqueada) {

            $arr_odd_b[] = $odd_bloqueada->odd_uid;
        }



        $leagues = Match::select('league')
                        ->where('league', $request->league)
                        ->where('date', '>=', $this->agora)
                        ->where('date', '<=',  $date_limite_matchs . ' 23:59:00')
                        ->where('visible', 'Sim')
                        ->groupBy('league')
                        ->with('odds')
                        ->orderBy('league', 'asc')
                        ->get();

        $i = 0;
        foreach ($leagues  as $league) {


            $return[$i]['league'] = $league->league;

            $matchs = Match::where('league', $league->league)
                            ->where('date', '>=', $this->agora)
                            ->where('date', '<=',  $date_limite_matchs . ' 23:59:00')
                            ->where('visible', 'Sim')
                            ->whereNotIn('event_id', $block_match)
                            ->get();

            $j = 0;
            foreach ($matchs as $match) {
                $return[$i]['match'][$j]['id'] = $match->id;
                $return[$i]['match'][$j]['event_id'] = $match->event_id;
                $return[$i]['match'][$j]['sport'] = $match->sport_name;
                $return[$i]['match'][$j]['confronto'] = $match->confronto;
                $return[$i]['match'][$j]['home'] = $match->home;
                $return[$i]['match'][$j]['image_id_home'] = $match->image_id_home;
                $return[$i]['match'][$j]['away'] = $match->away;
                $return[$i]['match'][$j]['image_id_away'] = $match->image_id_away;
                $return[$i]['match'][$j]['date'] = $match->date;
                $count = Odd::where('match_id', $match->id)->count();
                $return[$i]['match'][$j]['count_odd'] = $count;

                if ($count == 0 || count($match->odds) == 0) {
                    for ($q = 0; $q < 3; $q++) {
                        $return[$i]['match'][$j]['odds'][$q]['type'] = 'pre';
                        $return[$i]['match'][$j]['odds'][$q]['id'] = $match->event_id . $match->id . $q;
                        $return[$i]['match'][$j]['odds'][$q]['group_opp'] = 'Vencedor do Encontro';
                        $return[$i]['match'][$j]['odds'][$q]['odd'] = $q;
                        $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                    }
                } else {
                    $q = 0;
                    foreach ($match->odds as $odd) {
                        $return[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;

                        $return[$i]['match'][$j]['odds'][$q]['cotacaoOriginal'] = $odd->cotacao;
                        if (in_array($odd->mercado_name, $block_merc)) {

                            $return[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                            $return[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->mercado_name;
                            $return[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                            $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                        } else {

                            $m_porcents_geral = ConfigMercados::where('site_id', env('ID_SITE'))
                                ->where('name', $odd->mercado_name)
                                ->where('status', 1)
                                ->where('user_id', env('ID_USER'))
                                ->first();

                            $m_geral = $m_porcents_geral->porcentagem;

                            $o_porcents_geral = ConfigOdd::where('site_id', env('ID_SITE'))
                                ->where('status', 1)
                                ->where('mercado_full_name', $odd->odd)
                                ->where('user_id', env('ID_USER'))
                                ->first();

                            $o_geral = $o_porcents_geral->porcentagem;





                            if (in_array($odd->id, $arr_odd_b)) {
                                $return[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                $return[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->mercado_name;
                                $return[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                                $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                            } else {
                                $return[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                $return[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->mercado_name;
                                $return[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                                if (in_array($odd->odd, $block_odd)) {
                                    $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                } else {
                                    if ($odd->cotacao <= $odd_z) {
                                        $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                    } else {

                                        //Odds Alteradas
                                        if (in_array($match->event_id . $odd->odd, $arr_odd_alterada)) {

                                            $odd_alt = BlockOddMatch::where('odd_id',  $match->event_id)->where('odd', $odd->odd)->get();

                                            foreach ($odd_alt as $odd_at) {
                                                $cotacao = round($odd_at->cotacao, 2);
                                            }
                                        } else {
                                            $cotacao = round($odd->cotacao, 2);
                                        }
                                        $v_porcent_total_geral = ($m_geral + $o_geral) / 100;


                                        $redu = $cotacao - 1;
                                        $odd_final = ($redu * $v_porcent_total_geral) + $redu + 1;

                                        if ($odd_final >= $odd_m) {
                                            $return[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_m, 2);
                                        } else if ($odd_final <= $odd_z) {
                                            $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                        } else {
                                            $return[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_final, 2);
                                        }
                                    }
                                }
                            }
                        }

                        $q++;
                    }
                }


                $j++;
            }

            $i++;
        }

        return $return;
    }


    public function getLeagues()
    {
        $mainleagues = MainLeague::where('site_id', env('ID_SITE'))->get();


        $liga = array();
        foreach ($mainleagues as $league) {
            $liga[] = $league->league;
        }

        //Bloqueio de odd limite data limites
        $confs = Configuracao::where('site_id', env('ID_SITE'))
            ->get();


        foreach ($confs as $conf) {

            $odd_z =  $conf->bloquear_odd_abaixo;
            $date_limite_matchs = $conf->data_limite_jogos;
        }

        $bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();

        $arr = array();


        foreach ($bloqueadas as $bloqueada) {

            $arr[] = $bloqueada->league;
        }

//        \DB::enableQueryLog();
        $leagues = Match::select('league')
                                ->where('date', '>=', $this->agora->format('Y-m-d H:i:s'))
                                ->where('date', '<=',  $date_limite_matchs . ' 23:59:00')
                                ->where('visible', 'Sim')
                                ->whereNotIn('league', $arr)
                                ->whereNotIn('league', $liga)
                                ->groupBy('league')
                                ->orderBy('league', 'asc')
                                ->limit(30)
                                ->get();
//        dd(\DB::getQueryLog());

        return $leagues;
    }

    public function getLeaguesMain()
    {
        $mainleagues = MainLeague::where('site_id', env('ID_SITE'))->get();
        $liga = array();
        foreach ($mainleagues as $league) {
            $liga[] = $league->league;
        }

        //Bloqueio de odd limite data limites
        $confs = Configuracao::where('site_id', env('ID_SITE'))
            ->get();

        foreach ($confs as $conf) {
            $odd_z =  $conf->bloquear_odd_abaixo;
            $date_limite_matchs = $conf->data_limite_jogos;
        }

        $bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();

        $arr = array();

        foreach ($bloqueadas as $bloqueada) {

            $arr[] = $bloqueada->league;
        }
        $leagues = Match::select('league')
                                ->where('date', '>=', $this->agora)
                                ->where('date', '<=',  $date_limite_matchs . ' 23:59:00')
                                ->where('visible', 'Sim')
                                ->whereNotIn('league', $arr)
                                ->whereIn('league', $liga)
                                ->groupBy('league')
                                ->orderBy('league', 'asc')
                                ->limit(30)
                                ->get();
        return $leagues;
    }

    public function showOddsLive($id)
    {
        $live = $this->configuration::where('site_id', env('ID_SITE'))->first();
        $matchs = Match::where('id', $id)->get();
        $i = 0;
        foreach ($matchs as $match) {
            $this->arr[$i]['id'] = $match->id;
            $this->arr[$i]['event_id'] = $match->event_id;
            $this->arr[$i]['sport'] = $match->sport_name;
            $this->arr[$i]['confronto'] = $match->confronto;
            $this->arr[$i]['home'] = $match->home;
            $this->arr[$i]['image_id_home'] = $match->image_id_home;
            $this->arr[$i]['away'] = $match->away;
            $this->arr[$i]['image_id_away'] = $match->image_id_away;
            $this->arr[$i]['score'] = $match->score;
            $this->arr[$i]['date'] = $match->date;
            $this->arr[$i]['time'] = $match->time;
            $this->arr[$i]['halfTimeScoreHome'] = $match->halfTimeScoreHome;
            $this->arr[$i]['halfTimeScoreAway'] = $match->halfTimeScoreAway;
            $this->arr[$i]['fullTimeScoreHome'] = $match->fullTimeScoreHome;
            $this->arr[$i]['fullTimeScoreAway'] = $match->fullTimeScoreAway;
            $this->arr[$i]['numberOfCornersHome'] = $match->numberOfCornersHome;
            $this->arr[$i]['numberOfCornersAway'] = $match->numberOfCornersAway;
            $this->arr[$i]['numberOfYellowCardsHome'] = $match->numberOfYellowCardsHome;
            $this->arr[$i]['numberOfYellowCardsAway'] = $match->numberOfYellowCardsAway;
            $this->arr[$i]['numberOfRedCardsHome'] = $match->numberOfRedCardsHome;
            $this->arr[$i]['numberOfRedCardsAway'] = $match->numberOfRedCardsAway;

            $mercados = Odd::select('mercado_name')
                            ->where('match_id', $id)
                            ->groupBy('order')
                            ->groupBy('mercado_name')
                            ->orderBy('order')
                            ->get();

            $j = 0;
            foreach ($mercados as $mercado) {
                $this->arr[$i]['mercados'][$j]['match_id'] = $id;
                $this->arr[$i]['mercados'][$j]['name'] = $mercado->mercado_name;

                $odds = Odd::where('match_id', $id)
                    ->where('mercado_name', $mercado->mercado_name)
                    ->orderBy('header', 'asc')
                    ->orderBy('goals', 'asc')
                    ->get();

                    $q = 0;
                    foreach ($odds as $odd) {
                        $redu = $odd->cotacao - 1;
                        $odd_final = ($redu * $live->cotacao_live / 100) + $redu + 1;
                        $this->arr[$i]['mercados'][$j]['odds'][$q]['type'] = $odd->type;
                        $this->arr[$i]['mercados'][$j]['odds'][$q]['id'] = $odd->id;
                        $this->arr[$i]['mercados'][$j]['odds'][$q]['group_opp'] = $odd->mercado_name;
                        $this->arr[$i]['mercados'][$j]['odds'][$q]['event_id'] = $odd->event_id;
                        $this->arr[$i]['mercados'][$j]['odds'][$q]['odd'] = $odd->odd;
                        $this->arr[$i]['mercados'][$j]['odds'][$q]['cotacao'] = round($odd_final, 2);
                        $this->arr[$i]['mercados'][$j]['odds'][$q]['cotacaoOriginal'] = $odd->cotacao;
                        $this->arr[$i]['mercados'][$j]['odds'][$q]['type'] = $odd->type;
                        $q++;
                    }

                $j++;
            }
      $i++;
    }

        return $this->arr;
    }


    public function showOdds($id)
    {
        //Mercado Bloqueados
        $merc_blocks = ConfigMercados::where('status', 0)
            ->where('site_id', env('ID_SITE'))
            ->where('user_id', env('ID_USER'))
            ->get();

        $block_merc = array();

        foreach ($merc_blocks as $merc_block) {

            $block_merc[] = $merc_block->name;
        }


        //Odd Bloqueadas
        $odd_blocks = ConfigOdd::where('status', 0)
            ->where('site_id', env('ID_SITE'))
            ->where('user_id', env('ID_USER'))
            ->get();

        $block_odd = array();

        foreach ($odd_blocks as $odd_block) {

            $block_odd[] = $odd_block->name;
        }

        //Bloqueio de odd limite
        $odd_zerada = Configuracao::where('site_id', env('ID_SITE'))
            ->get();


        foreach ($odd_zerada as $zerada) {

            $odd_z =  $zerada->bloquear_odd_abaixo;
            $odd_m = $zerada->travar_odd_acima;
        }



        //Odds alteradas
        $odds_alteradas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 1)
            ->get();

        $arr_odd_alterada = array();
        $arr_odd_alt      = array();
        foreach ($odds_alteradas as $odd_alterada) {
            $arr_odd_alterada[] = $odd_alterada->odd_id . $odd_alterada->odd;
        }

        //Odds bloqeuadas por partida
        $odds_bloqueadas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 0)
            ->get();

        $arr_odd_b  = array();
        foreach ($odds_bloqueadas as $odd_bloqueada) {

            $arr_odd_b[] = $odd_bloqueada->odd_uid;
        }

        $mercados = Odd::select('mercado_name')
            ->where('match_id', $id)
            ->groupBy('order')
            ->groupBy('mercado_name')
            ->whereNotIn('mercado_name', $block_merc)
            ->whereNotIn('id', $arr_odd_b)
            ->orderBy('order')
            ->get();


        $i = 0;
        foreach ($mercados as $mercado) {
            $this->arr[$i]['match_id'] = $id;
            $this->arr[$i]['name'] = $mercado->mercado_name;


            $odds = Odd::where('match_id', $id)
                ->where('mercado_name', $mercado->mercado_name)
                ->orderBy('header', 'asc')
                ->orderBy('goals', 'asc')
                ->whereNotIn('odd', $block_odd)
                ->whereNotIn('id', $arr_odd_b)
                ->get();

                    $mercados_alterados = ConfigMercados::where('site_id', env('ID_SITE'))
                                //->where('name', $mercado->mercado_name)
                                ->where('status', 1)
                                ->where('porcentagem', '!=', 0)
                                ->where('user_id', env('ID_USER'))
                                ->get();

                        $m_g = array();
                        foreach($mercados_alterados as $m) {
                            $m_g[] = $m->name;
                        }

                        $m_geral = 0;
                        if(in_array($mercado->mercado_name, $m_g)) {

                            $m_porcents_geral = ConfigMercados::where('site_id', env('ID_SITE'))
                                            ->where('name', $mercado->mercado_name)
                                            ->where('status', 1)
                                            ->where('porcentagem', '!=', 0)
                                            ->where('user_id', env('ID_USER'))
                                            ->first();

                            $m_geral =  $m_porcents_geral->porcentagem;
                        }

            $j = 0;

            foreach ($odds as $odd) {

                $odd_alteradas = ConfigOdd::where('site_id', env('ID_SITE'))
                    ->where('status', 1)
                    ->where('porcentagem', '!=', 0)
                    ->where('user_id', env('ID_USER'))
                    ->get();

                $o_g = array();
                foreach($odd_alteradas as $o) {
                    $o_g[] = $o->name;
                }

                $o_geral = 0;
                if(in_array($odd->odd, $o_g)) {

                    $o_porcents_geral = ConfigOdd::where('site_id', env('ID_SITE'))
                                        ->where('status', 1)
                                        ->where('name', $odd->odd)
                                        ->where('porcentagem', '!=', 0)
                                        ->where('user_id', env('ID_USER'))
                                        ->first();

                    $o_geral = $o_porcents_geral->porcentagem;
                }


                $this->arr[$i]['odds'][$j]['id'] = $odd->id;
                $this->arr[$i]['odds'][$j]['group_opp'] = $odd->mercado_name;
                $this->arr[$i]['odds'][$j]['odd'] = $odd->odd;
                $this->arr[$i]['odds'][$j]['type'] =  $odd->type;
                $this->arr[$i]['odds'][$j]['cotacaoOriginal'] =  $odd->cotacao;


                if ($odd->cotacao <= $odd_z) {
                    $this->arr[$i]['odds'][$j]['cotacao'] = 0;
                } else {
                    if (in_array($odd->event_id . $odd->odd, $arr_odd_alterada)) {
                        $odd_alt = BlockOddMatch::where('odd_id', $odd->event_id)->where('status', 1)->where('odd', $odd->odd)->first();
                        $cotacao = round($odd_alt->cotacao, 2);
                        // if (auth()->user()) {
                        //     $v_porcent_total_geral = ($m_geral + $o_geral + $m_user + $o_user) / 100;
                        // } else {
                        //     $v_porcent_total_geral = ($m_geral + $o_geral) / 100;
                        // }

                        $v_porcent_total_geral = ($m_geral + $o_geral) / 100;
                        $redu = $cotacao - 1;
                        $odd_final = ($redu * $v_porcent_total_geral) + $redu + 1;
                        if ($odd_final >= $odd_m) {
                            $this->arr[$i]['odds'][$j]['cotacao'] = round($odd_m, 2);
                        } else if ($odd_final <= $odd_z) {
                            $this->arr[$i]['odds'][$j]['cotacao'] = 0;
                        } else {
                            $this->arr[$i]['odds'][$j]['cotacao'] = round($odd_final, 2);
                        }

                        $this->arr[$i]['odds'][$j]['id'] = $odd->id;
                        $this->arr[$i]['odds'][$j]['group_opp'] = $odd->mercado_name;
                        $this->arr[$i]['odds'][$j]['odd'] = $odd->odd;
                        $this->arr[$i]['odds'][$j]['type'] =  $odd->type;
                    } else {
                        $cotacao = $odd->cotacao;
                        // if (auth()->user()) {
                        //     $v_porcent_total_geral = ($m_geral + $o_geral + $m_user + $o_user) / 100;
                        // } else {
                        //     $v_porcent_total_geral = ($m_geral + $o_geral) / 100;
                        // }
                        $v_porcent_total_geral = ($m_geral + $o_geral) / 100;
                        $redu = $cotacao - 1;
                        $odd_final = ($redu * $v_porcent_total_geral) + $redu + 1;
                        if ($odd_final >= $odd_m) {
                            $this->arr[$i]['odds'][$j]['cotacao'] = round($odd_m, 2);
                        } else if ($odd_final <= $odd_z) {
                            $this->arr[$i]['odds'][$j]['cotacao'] = 0;
                        } else {
                            $this->arr[$i]['odds'][$j]['cotacao'] = round($odd_final, 2);
                        }
                    }
                }

                $j++;
            }

            $i++;
        }

        $this->arr = array_filter($this->arr);
        $this->arr = array_values($this->arr);

        return (array) $this->arr;
    }

    public function showHoje()
    {

        $mainleagues = MainLeague::where('site_id', env('ID_SITE'))->get();

        $liga = array();
        foreach ($mainleagues as $league) {
            $liga[] = $league->league;
        }
        //config gerais
        $league_bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();

        $block_league = array();
        foreach ($league_bloqueadas  as $league_bloqueada) {

            $block_league[] = $league_bloqueada->league;
        }

        //return $block_league;
        $block_match = array();

        $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();

        foreach ($matchs_bloqueadas as $match_bloqueada) {

            $block_match[] = $match_bloqueada->event_id;
        }

        //Mercado Bloqueados
        $merc_blocks = ConfigMercados::where('status', 0)
            //->where('')
            ->where('site_id', env('ID_SITE'))
            ->where('user_id', env('ID_USER'))
            ->get();

        $block_merc = array();

        foreach ($merc_blocks as $merc_block) {

            $block_merc[] = $merc_block->name;
        }


        //Odd Bloqueadas
        $odd_blocks = ConfigOdd::where('status', 0)
            ->where('site_id', env('ID_SITE'))
            ->where('user_id', env('ID_USER'))
            ->get();

        $block_odd = array();

        foreach ($odd_blocks as $odd_block) {

            $block_odd[] = $odd_block->name;
        }


        //Bloqueio de odd limite
        $odd_zerada = Configuracao::where('site_id', env('ID_SITE'))
            ->get();


        foreach ($odd_zerada as $zerada) {

            $odd_z =  $zerada->bloquear_odd_abaixo;
            $date_limite_matchs = $zerada->data_limite_jogos;
            $odd_m = $zerada->travar_odd_acima;
        }


        //Odds alteradas
        $odds_alteradas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 1)
            ->get();

        $arr_odd_alterada = array();

        foreach ($odds_alteradas as $odd_alterada) {
            $arr_odd_alterada[] = $odd_alterada->odd_id . $odd_alterada->odd;
        }

        //Odds bloqeuadas por partida
        $odds_bloqueadas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 0)
            ->get();


        $arr_odd_b  = array();
        foreach ($odds_bloqueadas as $odd_bloqueada) {

            $arr_odd_b[] = $odd_bloqueada->odd_uid;
        }




        $arr[] = array();

        $all = HomeMatch::select('dados')
            ->orderBy('id', 'desc')
            ->get();


        $dados = json_decode($all[0]['dados']);




        if ($this->agora->format('Y-m-d') <= $date_limite_matchs) {

            $i = 0;
            foreach ($dados as $leagues) {

                if (in_array($leagues->league, $liga)) {
                } else if (in_array($leagues->league, $block_league)) {
                } else {



                    $j = 0;

                    foreach ($leagues->match as $match) {


                        if (in_array($match->event_id, $block_match)) {
                            $leagues->match = array_filter($leagues->match);
                            $leagues->match = array_values($leagues->match);
                        } else {

                            $arr[$i]['league'] = $leagues->league;

                            $arr[$i]['match'][$j]['id'] = $match->id;
                            $arr[$i]['match'][$j]['event_id'] = $match->event_id;
                            $arr[$i]['match'][$j]['sport'] = $match->sport;
                            $arr[$i]['match'][$j]['confronto'] = $match->confronto;
                            $arr[$i]['match'][$j]['home'] = $match->home;
                            $arr[$i]['match'][$j]['image_id_home'] = $match->image_id_home;
                            $arr[$i]['match'][$j]['away'] = $match->away;
                            $arr[$i]['match'][$j]['image_id_away'] = $match->image_id_away;
                            $arr[$i]['match'][$j]['date'] = $match->date;
                            $arr[$i]['match'][$j]['count_odd'] = $match->count_odd;


                            $q = 0;
                            foreach ($match->odds as $odd) {
                                $arr[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;
                                // $arr[$i]['match'][$j]['odds'][$q]['cotacaoOriginal'] = $odd->cotacao;
                                if (in_array('Vencedor do Encontro', $block_merc)) {

                                    $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                    $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                    $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                                    $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                } else {
                                    $m_porcents_geral = ConfigMercados::where('site_id', env('ID_SITE'))
                                        ->where('name', $odd->group_opp)
                                        ->where('status', 1)
                                        ->where('user_id', env('ID_USER'))
                                        ->first();

                                    $m_geral = $m_porcents_geral->porcentagem;

                                    $o_porcents_geral = ConfigOdd::where('site_id', env('ID_SITE'))
                                        ->where('status', 1)
                                        ->where('mercado_full_name', $odd->odd)
                                        ->where('user_id', env('ID_USER'))
                                        ->first();
                                    if ($o_porcents_geral) {
                                        $o_geral = $o_porcents_geral->porcentagem;

                                        if (auth()->user()) {
                                            $m_porcents_user = ConfigMercados::where('site_id', env('ID_SITE'))
                                                ->where('name', $odd->group_opp)
                                                ->where('status', 1)
                                                ->where('user_id', auth()->user()->id)
                                                ->first();

                                            $m_user = $m_porcents_user->porcentagem;

                                            $o_porcents_user = ConfigOdd::where('site_id', env('ID_SITE'))
                                                ->where('status', 1)
                                                ->where('mercado_full_name', $odd->odd)
                                                ->where('user_id', auth()->user()->id)
                                                ->first();

                                            $o_user = $o_porcents_user->porcentagem;
                                        }
                                        if (in_array($odd->id, $arr_odd_b)) {
                                            $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                            $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                            $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;

                                            $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                        } else {
                                            $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                            $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                            $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;

                                            if ($odd->cotacao <= $odd_z) {
                                                $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                            } else {

                                                //Odds Alteradas
                                                if (in_array($match->event_id . $odd->odd, $arr_odd_alterada)) {

                                                    $odd_alt = BlockOddMatch::where('odd_id',  $match->event_id)->where('odd', $odd->odd)->get();

                                                    foreach ($odd_alt as $odd_at) {
                                                        $cotacao = round($odd_at->cotacao, 2);
                                                    }
                                                } else {
                                                    $cotacao = $odd->cotacao;
                                                }

                                                if (auth()->user()) {
                                                    $v_porcent_total_geral = ($m_geral + $o_geral + $m_user + $o_user) / 100;
                                                } else {
                                                    $v_porcent_total_geral = ($m_geral + $o_geral) / 100;
                                                }
                                                $redu = $cotacao - 1;

                                                $odd_final = ($redu * $v_porcent_total_geral) + $redu + 1;


                                                if ($odd_final >= $odd_m) {
                                                    $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_m, 2);
                                                } else if ($odd_final <= $odd_z) {
                                                    $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                                } else {
                                                    $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_final, 2);
                                                }
                                            }
                                        }
                                    } else {
                                        $o_geral = 0;
                                        $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                        $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                        $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                                        $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                    }
                                }


                                $q++;
                            }

                            $j++;
                        }
                    }
                }



                $i++;
            }
            $arr = array_filter($arr);
            $arr = array_values($arr);

            return $arr;
        } else {
        }
    }



    public function showHojeMain()
    {

        $mainleagues = MainLeague::where('site_id', env('ID_SITE'))->get();


        $liga = array();
        foreach ($mainleagues as $league) {
            $liga[] = $league->league;
        }

        //config gerais
        $league_bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();

        $block_league = array();


        foreach ($league_bloqueadas  as $league_bloqueada) {

            $block_league[] = $league_bloqueada->league;
        }

        //return $block_league;

        $block_match = array();

        $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();



        foreach ($matchs_bloqueadas as $match_bloqueada) {

            $block_match[] = $match_bloqueada->event_id;
        }

        //Mercado Bloqueados
        $merc_blocks = ConfigMercados::where('status', 0)
            //->where('')
            ->where('site_id', env('ID_SITE'))
            ->where('user_id', env('ID_USER'))
            ->get();

        $block_merc = array();

        foreach ($merc_blocks as $merc_block) {

            $block_merc[] = $merc_block->name;
        }


        //Odd Bloqueadas
        $odd_blocks = ConfigOdd::where('status', 0)
            ->where('site_id', env('ID_SITE'))
            ->where('user_id', env('ID_USER'))
            ->get();

        $block_odd = array();

        foreach ($odd_blocks as $odd_block) {

            $block_odd[] = $odd_block->name;
        }

        //Bloqueio de odd limite
        $odd_zerada = Configuracao::where('site_id', env('ID_SITE'))
            ->get();


        foreach ($odd_zerada as $zerada) {

            $odd_z =  $zerada->bloquear_odd_abaixo;
            $date_limite_matchs = $zerada->data_limite_jogos;
            $odd_m = $zerada->travar_odd_acima;
        }


        //Odds alteradas
        $odds_alteradas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 1)
            ->get();

        $arr_odd_alterada = array();

        foreach ($odds_alteradas as $odd_alterada) {
            $arr_odd_alterada[] = $odd_alterada->odd_id . $odd_alterada->odd;
        }

        //Odds bloqeuadas por partida
        $odds_bloqueadas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 0)
            ->get();


        $arr_odd_b  = array();
        foreach ($odds_bloqueadas as $odd_bloqueada) {

            $arr_odd_b[] = $odd_bloqueada->odd_uid;
        }

        $percent_merc_user = 0;
        $percent_odd_user = 0;






        $arr[] = array();

        $all = HomeMatch::select('dados')
            ->orderBy('id', 'desc')
            ->get();


        $dados = json_decode($all[0]['dados']);




        if ($this->agora->format('Y-m-d') <= $date_limite_matchs) {


            $i = 0;
            foreach ($dados as $leagues) {

                if (in_array($leagues->league, $block_league)) {
                } else if (in_array($leagues->league, $liga)) {
                    $j = 0;

                    foreach ($leagues->match as $match) {


                        if (in_array($match->event_id, $block_match)) {

                            $leagues->match = array_filter($leagues->match);
                            $leagues->match = array_values($leagues->match);
                        } else {

                            $arr[$i]['league'] = $leagues->league;

                            $arr[$i]['match'][$j]['id'] = $match->id;
                            $arr[$i]['match'][$j]['event_id'] = $match->event_id;
                            $arr[$i]['match'][$j]['sport'] = $match->sport;
                            $arr[$i]['match'][$j]['confronto'] = $match->confronto;
                            $arr[$i]['match'][$j]['home'] = $match->home;
                            $arr[$i]['match'][$j]['image_id_home'] = $match->image_id_home;
                            $arr[$i]['match'][$j]['away'] = $match->away;
                            $arr[$i]['match'][$j]['image_id_away'] = $match->image_id_away;
                            $arr[$i]['match'][$j]['date'] = $match->date;
                            $arr[$i]['match'][$j]['count_odd'] = $match->count_odd;


                            $q = 0;
                            foreach ($match->odds as $odd) {

                                $arr[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;
                                if (in_array('Vencedor do Encontro', $block_merc)) {

                                    $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                    $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                    $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                                    $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                } else {


                                    if (in_array($odd->id, $arr_odd_b)) {
                                        $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                        $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                        $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;

                                        $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                    } else {


                                        $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                        $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                        $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;

                                        if ($odd->cotacao <= $odd_z) {
                                            $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                        } else {

                                            $m_porcents_geral = ConfigMercados::where('site_id', env('ID_SITE'))
                                                ->where('name', $odd->group_opp)
                                                ->where('status', 1)
                                                ->where('user_id', env('ID_USER'))
                                                ->first();

                                            $m_geral = $m_porcents_geral->porcentagem;

                                            $o_porcents_geral = ConfigOdd::where('site_id', env('ID_SITE'))
                                                ->where('status', 1)
                                                ->where('mercado_full_name', $odd->odd)
                                                ->where('user_id', env('ID_USER'))
                                                ->first();

                                            $o_geral = $o_porcents_geral->porcentagem;


                                            if (auth()->user()) {
                                                $m_porcents_user = ConfigMercados::where('site_id', env('ID_SITE'))
                                                    ->where('name', $odd->group_opp)
                                                    ->where('status', 1)
                                                    ->where('user_id', auth()->user()->id)
                                                    ->first();

                                                $m_user = $m_porcents_user->porcentagem;

                                                $o_porcents_user = ConfigOdd::where('site_id', env('ID_SITE'))
                                                    ->where('status', 1)
                                                    ->where('mercado_full_name', $odd->odd)
                                                    ->where('user_id', auth()->user()->id)
                                                    ->first();

                                                $o_user = $o_porcents_user->porcentagem;
                                            }




                                            //Odds Alteradas
                                            if (in_array($match->event_id . $odd->odd, $arr_odd_alterada)) {

                                                $odd_alt = BlockOddMatch::where('odd_id',  $match->event_id)->where('odd', $odd->odd)->get();

                                                foreach ($odd_alt as $odd_at) {
                                                    $cotacao = round($odd_at->cotacao, 2);
                                                }
                                            } else {
                                                $cotacao = $odd->cotacao;
                                            }


                                            if (auth()->user()) {
                                                $v_porcent_total_geral = ($m_geral + $o_geral + $m_user + $o_user) / 100;
                                            } else {
                                                $v_porcent_total_geral = ($m_geral + $o_geral) / 100;
                                            }
                                            $redu = $cotacao - 1;

                                            $odd_final = ($redu * $v_porcent_total_geral) + $redu + 1;



                                            $redu = $cotacao - 1;
                                            $odd_final = ($redu * $v_porcent_total_geral) + $redu + 1;

                                            if ($odd_final >= $odd_m) {
                                                $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_m, 2);
                                            } else if ($odd_final <= $odd_z) {
                                                $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                            } else {
                                                $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_final, 2);
                                            }
                                        }
                                    }
                                }


                                $q++;
                            }



                            $j++;
                        }
                    }
                } else {
                }



                $i++;
            }
            $arr = array_filter($arr);
            $arr = array_values($arr);

            return $arr;
        } else {
        }
    }

    public function showHomeHoje()
    {

        $all = HomeMatchFlash::where('site_id', env('ID_SITE'))->first();
        return json_decode($all->dados);
        //return array_merge($this->showHojeMain(), $this->showHoje());


    }

    public function showAmanha()
    {


        $mainleagues = MainLeague::where('site_id', env('ID_SITE'))->get();


        $liga = array();
        foreach ($mainleagues as $league) {
            $liga[] = $league->league;
        }

        //config gerais
        $league_bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();

        $block_league = array();


        foreach ($league_bloqueadas  as $league_bloqueada) {

            $block_league[] = $league_bloqueada->league;
        }

        //return $block_league;

        $block_match = array();

        $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();



        foreach ($matchs_bloqueadas as $match_bloqueada) {

            $block_match[] = $match_bloqueada->event_id;
        }

        //Mercado Bloqueados
        $merc_blocks = ConfigMercados::where('status', 0)
            //->where('')
            ->where('site_id', env('ID_SITE'))
            ->where('user_id', env('ID_USER'))
            ->get();

        $block_merc = array();

        foreach ($merc_blocks as $merc_block) {

            $block_merc[] = $merc_block->name;
        }


        //Odd Bloqueadas
        $odd_blocks = ConfigOdd::where('status', 0)
            ->where('site_id', env('ID_SITE'))
            ->where('user_id', env('ID_USER'))
            ->get();

        $block_odd = array();

        foreach ($odd_blocks as $odd_block) {

            $block_odd[] = $odd_block->name;
        }


        //Bloqueio de odd limite
        $odd_zerada = Configuracao::where('site_id', env('ID_SITE'))
            ->get();


        foreach ($odd_zerada as $zerada) {

            $odd_z =  $zerada->bloquear_odd_abaixo;
            $date_limite_matchs = $zerada->data_limite_jogos;
            $odd_m = $zerada->travar_odd_acima;
        }


        //Odds alteradas
        $odds_alteradas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 1)
            ->get();

        $arr_odd_alterada = array();

        foreach ($odds_alteradas as $odd_alterada) {
            $arr_odd_alterada[] = $odd_alterada->odd_id . $odd_alterada->odd;
        }

        //Odds bloqeuadas por partida
        $odds_bloqueadas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 0)
            ->get();


        $arr_odd_b  = array();
        foreach ($odds_bloqueadas as $odd_bloqueada) {

            $arr_odd_b[] = $odd_bloqueada->odd_uid;
        }




        $arr[] = array();

        $all = AmanhaMatch::select('dados')
            ->orderBy('id', 'desc')
            ->get();


        $dados = json_decode($all[0]['dados']);



        if ($this->agora->format('Y-m-d') < $date_limite_matchs) {


            $i = 0;
            foreach ($dados as $leagues) {

                if (in_array($leagues->league, $liga)) {
                } else if (in_array($leagues->league, $block_league)) {
                } else {



                    $j = 0;

                    foreach ($leagues->match as $match) {


                        if (in_array($match->event_id, $block_match)) {

                            $leagues->match = array_filter($leagues->match);
                            $leagues->match = array_values($leagues->match);
                        } else {

                            $arr[$i]['league'] = $leagues->league;

                            $arr[$i]['match'][$j]['id'] = $match->id;
                            $arr[$i]['match'][$j]['event_id'] = $match->event_id;
                            $arr[$i]['match'][$j]['sport'] = $match->sport;
                            $arr[$i]['match'][$j]['confronto'] = $match->confronto;
                            $arr[$i]['match'][$j]['home'] = $match->home;
                            $arr[$i]['match'][$j]['image_id_home'] = $match->image_id_home;
                            $arr[$i]['match'][$j]['away'] = $match->away;
                            $arr[$i]['match'][$j]['image_id_away'] = $match->image_id_away;
                            $arr[$i]['match'][$j]['date'] = $match->date;
                            $arr[$i]['match'][$j]['count_odd'] = $match->count_odd;


                            $q = 0;
                            foreach ($match->odds as $odd) {
                                $arr[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;
                                if (in_array('Vencedor do Encontro', $block_merc)) {

                                    $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                    $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                    $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                                    $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                } else {
                                    $m_porcents_geral = ConfigMercados::where('site_id', env('ID_SITE'))
                                        ->where('name', $odd->group_opp)
                                        ->where('status', 1)
                                        ->where('user_id', env('ID_USER'))
                                        ->first();

                                    $m_geral = $m_porcents_geral->porcentagem;

                                    $o_porcents_geral = ConfigOdd::where('site_id', env('ID_SITE'))
                                        ->where('status', 1)
                                        ->where('mercado_full_name', $odd->odd)
                                        ->where('user_id', env('ID_USER'))
                                        ->first();
                                    if ($o_porcents_geral) {
                                        $o_geral = $o_porcents_geral->porcentagem;

                                        if (auth()->user()) {
                                            $m_porcents_user = ConfigMercados::where('site_id', env('ID_SITE'))
                                                ->where('name', $odd->group_opp)
                                                ->where('status', 1)
                                                ->where('user_id', auth()->user()->id)
                                                ->first();

                                            $m_user = $m_porcents_user->porcentagem;

                                            $o_porcents_user = ConfigOdd::where('site_id', env('ID_SITE'))
                                                ->where('status', 1)
                                                ->where('mercado_full_name', $odd->odd)
                                                ->where('user_id', auth()->user()->id)
                                                ->first();

                                            $o_user = $o_porcents_user->porcentagem;
                                        }
                                        if (in_array($odd->id, $arr_odd_b)) {
                                            $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                            $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                            $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;

                                            $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                        } else {
                                            $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                            $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                            $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;

                                            if ($odd->cotacao <= $odd_z) {
                                                $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                            } else {

                                                //Odds Alteradas
                                                if (in_array($match->event_id . $odd->odd, $arr_odd_alterada)) {

                                                    $odd_alt = BlockOddMatch::where('odd_id',  $match->event_id)->where('odd', $odd->odd)->get();

                                                    foreach ($odd_alt as $odd_at) {
                                                        $cotacao = round($odd_at->cotacao, 2);
                                                    }
                                                } else {
                                                    $cotacao = $odd->cotacao;
                                                }

                                                if (auth()->user()) {
                                                    $v_porcent_total_geral = ($m_geral + $o_geral + $m_user + $o_user) / 100;
                                                } else {
                                                    $v_porcent_total_geral = ($m_geral + $o_geral) / 100;
                                                }
                                                $redu = $cotacao - 1;

                                                $odd_final = ($redu * $v_porcent_total_geral) + $redu + 1;


                                                if ($odd_final >= $odd_m) {
                                                    $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_m, 2);
                                                } else if ($odd_final <= $odd_z) {
                                                    $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                                } else {
                                                    $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_final, 2);
                                                }
                                            }
                                        }
                                    } else {
                                        $o_geral = 0;
                                        $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                        $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                        $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                                        $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                    }
                                }


                                $q++;
                            }

                            $j++;
                        }
                    }
                }



                $i++;
            }
            $arr = array_filter($arr);
            $arr = array_values($arr);

            return $arr;
        } else {
        }
    }

    public function showAmanhaMain()
    {

        $mainleagues = MainLeague::where('site_id', env('ID_SITE'))->get();


        $liga = array();
        foreach ($mainleagues as $league) {
            $liga[] = $league->league;
        }
        //config gerais
        $league_bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();

        $block_league = array();


        foreach ($league_bloqueadas  as $league_bloqueada) {

            $block_league[] = $league_bloqueada->league;
        }

        //return $block_league;

        $block_match = array();

        $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();



        foreach ($matchs_bloqueadas as $match_bloqueada) {

            $block_match[] = $match_bloqueada->event_id;
        }

        //Mercado Bloqueados
        $merc_blocks = ConfigMercados::where('status', 0)
            //->where('')
            ->where('site_id', env('ID_SITE'))
            ->where('user_id', env('ID_USER'))
            ->get();

        $block_merc = array();

        foreach ($merc_blocks as $merc_block) {

            $block_merc[] = $merc_block->name;
        }


        //Odd Bloqueadas
        $odd_blocks = ConfigOdd::where('status', 0)
            ->where('site_id', env('ID_SITE'))
            ->where('user_id', env('ID_USER'))
            ->get();

        $block_odd = array();

        foreach ($odd_blocks as $odd_block) {

            $block_odd[] = $odd_block->name;
        }

        //Bloqueio de odd limite
        $odd_zerada = Configuracao::where('site_id', env('ID_SITE'))
            ->get();


        foreach ($odd_zerada as $zerada) {

            $odd_z =  $zerada->bloquear_odd_abaixo;
            $date_limite_matchs = $zerada->data_limite_jogos;
            $odd_m = $zerada->travar_odd_acima;
        }


        //Odds alteradas
        $odds_alteradas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 1)
            ->get();

        $arr_odd_alterada = array();

        foreach ($odds_alteradas as $odd_alterada) {
            $arr_odd_alterada[] = $odd_alterada->odd_id . $odd_alterada->odd;
        }

        //Odds bloqeuadas por partida
        $odds_bloqueadas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 0)
            ->get();


        $arr_odd_b  = array();
        foreach ($odds_bloqueadas as $odd_bloqueada) {

            $arr_odd_b[] = $odd_bloqueada->odd_uid;
        }

        $percent_merc_user = 0;
        $percent_odd_user = 0;






        $arr[] = array();



        $all = AmanhaMatch::select('dados')
            ->orderBy('id', 'desc')
            ->get();


        $dados = json_decode($all[0]['dados']);




        if ($this->agora->format('Y-m-d') < $date_limite_matchs) {


            $i = 0;
            foreach ($dados as $leagues) {

                if (in_array($leagues->league, $block_league)) {
                } else if (in_array($leagues->league, $liga)) {
                    $j = 0;

                    foreach ($leagues->match as $match) {


                        if (in_array($match->event_id, $block_match)) {

                            $leagues->match = array_filter($leagues->match);
                            $leagues->match = array_values($leagues->match);
                        } else {

                            $arr[$i]['league'] = $leagues->league;

                            $arr[$i]['match'][$j]['id'] = $match->id;
                            $arr[$i]['match'][$j]['event_id'] = $match->event_id;
                            $arr[$i]['match'][$j]['sport'] = $match->sport;
                            $arr[$i]['match'][$j]['confronto'] = $match->confronto;
                            $arr[$i]['match'][$j]['home'] = $match->home;
                            $arr[$i]['match'][$j]['image_id_home'] = $match->image_id_home;
                            $arr[$i]['match'][$j]['away'] = $match->away;
                            $arr[$i]['match'][$j]['image_id_away'] = $match->image_id_away;
                            $arr[$i]['match'][$j]['date'] = $match->date;
                            $arr[$i]['match'][$j]['count_odd'] = $match->count_odd;


                            $q = 0;
                            foreach ($match->odds as $odd) {
                                $arr[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;
                                if (in_array('Vencedor do Encontro', $block_merc)) {

                                    $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                    $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                    $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                                    $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                } else {


                                    if (in_array($odd->id, $arr_odd_b)) {
                                        $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                        $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                        $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;

                                        $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                    } else {


                                        $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                        $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                        $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;

                                        if ($odd->cotacao <= $odd_z) {
                                            $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                        } else {

                                            $m_porcents_geral = ConfigMercados::where('site_id', env('ID_SITE'))
                                                ->where('name', $odd->group_opp)
                                                ->where('status', 1)
                                                ->where('user_id', env('ID_USER'))
                                                ->first();

                                            $m_geral = $m_porcents_geral->porcentagem;

                                            $o_porcents_geral = ConfigOdd::where('site_id', env('ID_SITE'))
                                                ->where('status', 1)
                                                ->where('mercado_full_name', $odd->odd)
                                                ->where('user_id', env('ID_USER'))
                                                ->first();

                                            $o_geral = $o_porcents_geral->porcentagem;


                                            if (auth()->user()) {
                                                $m_porcents_user = ConfigMercados::where('site_id', env('ID_SITE'))
                                                    ->where('name', $odd->group_opp)
                                                    ->where('status', 1)
                                                    ->where('user_id', auth()->user()->id)
                                                    ->first();

                                                $m_user = $m_porcents_user->porcentagem;

                                                $o_porcents_user = ConfigOdd::where('site_id', env('ID_SITE'))
                                                    ->where('status', 1)
                                                    ->where('mercado_full_name', $odd->odd)
                                                    ->where('user_id', auth()->user()->id)
                                                    ->first();

                                                $o_user = $o_porcents_user->porcentagem;
                                            }




                                            //Odds Alteradas
                                            if (in_array($match->event_id . $odd->odd, $arr_odd_alterada)) {

                                                $odd_alt = BlockOddMatch::where('odd_id',  $match->event_id)->where('odd', $odd->odd)->get();

                                                foreach ($odd_alt as $odd_at) {
                                                    $cotacao = round($odd_at->cotacao, 2);
                                                }
                                            } else {
                                                $cotacao = $odd->cotacao;
                                            }


                                            if (auth()->user()) {
                                                $v_porcent_total_geral = ($m_geral + $o_geral + $m_user + $o_user) / 100;
                                            } else {
                                                $v_porcent_total_geral = ($m_geral + $o_geral) / 100;
                                            }
                                            $redu = $cotacao - 1;

                                            $odd_final = ($redu * $v_porcent_total_geral) + $redu + 1;



                                            $redu = $cotacao - 1;
                                            $odd_final = ($redu * $v_porcent_total_geral) + $redu + 1;

                                            if ($odd_final >= $odd_m) {
                                                $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_m, 2);
                                            } else if ($odd_final <= $odd_z) {
                                                $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                            } else {
                                                $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_final, 2);
                                            }
                                        }
                                    }
                                }


                                $q++;
                            }



                            $j++;
                        }
                    }
                } else {
                }



                $i++;
            }
            $arr = array_filter($arr);
            $arr = array_values($arr);

            return $arr;
        } else {
        }
    }


    public function showHomeAmanha()
    {
        $all = TomorowMatchFlash::where('site_id', env('ID_SITE'))->get();
        return json_decode($all[0]['dados']);
    }

    //depois de amanhã
    public function showDepoisAmanha()
    {



        $mainleagues = MainLeague::where('site_id', env('ID_SITE'))->get();


        $liga = array();
        foreach ($mainleagues as $league) {
            $liga[] = $league->league;
        }

        //config gerais
        $league_bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();

        $block_league = array();


        foreach ($league_bloqueadas  as $league_bloqueada) {

            $block_league[] = $league_bloqueada->league;
        }

        //return $block_league;

        $block_match = array();

        $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();



        foreach ($matchs_bloqueadas as $match_bloqueada) {

            $block_match[] = $match_bloqueada->event_id;
        }

        //Mercado Bloqueados
        $merc_blocks = ConfigMercados::where('status', 0)
            //->where('')
            ->where('site_id', env('ID_SITE'))
            ->where('user_id', env('ID_USER'))
            ->get();

        $block_merc = array();

        foreach ($merc_blocks as $merc_block) {

            $block_merc[] = $merc_block->name;
        }


        //Odd Bloqueadas
        $odd_blocks = ConfigOdd::where('status', 0)
            ->where('site_id', env('ID_SITE'))
            ->where('user_id', env('ID_USER'))
            ->get();

        $block_odd = array();

        foreach ($odd_blocks as $odd_block) {

            $block_odd[] = $odd_block->name;
        }


        //Bloqueio de odd limite
        $odd_zerada = Configuracao::where('site_id', env('ID_SITE'))
            ->get();


        foreach ($odd_zerada as $zerada) {

            $odd_z =  $zerada->bloquear_odd_abaixo;
            $date_limite_matchs = $zerada->data_limite_jogos;
            $odd_m = $zerada->travar_odd_acima;
        }


        //Odds alteradas
        $odds_alteradas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 1)
            ->get();

        $arr_odd_alterada = array();

        foreach ($odds_alteradas as $odd_alterada) {
            $arr_odd_alterada[] = $odd_alterada->odd_id . $odd_alterada->odd;
        }

        //Odds bloqeuadas por partida
        $odds_bloqueadas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 0)
            ->get();


        $arr_odd_b  = array();
        foreach ($odds_bloqueadas as $odd_bloqueada) {

            $arr_odd_b[] = $odd_bloqueada->odd_uid;
        }




        $arr[] = array();

        $all = AferTomorrow::select('dados')
            ->orderBy('id', 'desc')
            ->get();


        $dados = json_decode($all[0]['dados']);




        if ($this->agora->format('Y-m-d') < $date_limite_matchs) {


            $i = 0;
            foreach ($dados as $leagues) {

                if (in_array($leagues->league, $liga)) {
                } else if (in_array($leagues->league, $block_league)) {
                } else {



                    $j = 0;

                    foreach ($leagues->match as $match) {


                        if (in_array($match->event_id, $block_match)) {

                            $leagues->match = array_filter($leagues->match);
                            $leagues->match = array_values($leagues->match);
                        } else {

                            $arr[$i]['league'] = $leagues->league;

                            $arr[$i]['match'][$j]['id'] = $match->id;
                            $arr[$i]['match'][$j]['event_id'] = $match->event_id;
                            $arr[$i]['match'][$j]['sport'] = $match->sport;
                            $arr[$i]['match'][$j]['confronto'] = $match->confronto;
                            $arr[$i]['match'][$j]['home'] = $match->home;
                            $arr[$i]['match'][$j]['image_id_home'] = $match->image_id_home;
                            $arr[$i]['match'][$j]['away'] = $match->away;
                            $arr[$i]['match'][$j]['image_id_away'] = $match->image_id_away;
                            $arr[$i]['match'][$j]['date'] = $match->date;
                            $arr[$i]['match'][$j]['count_odd'] = $match->count_odd;


                            $q = 0;
                            foreach ($match->odds as $odd) {
                                $arr[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;
                                if (in_array('Vencedor do Encontro', $block_merc)) {

                                    $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                    $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                    $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                                    $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                } else {
                                    $m_porcents_geral = ConfigMercados::where('site_id', env('ID_SITE'))
                                        ->where('name', $odd->group_opp)
                                        ->where('status', 1)
                                        ->where('user_id', env('ID_USER'))
                                        ->first();

                                    $m_geral = $m_porcents_geral->porcentagem;

                                    $o_porcents_geral = ConfigOdd::where('site_id', env('ID_SITE'))
                                        ->where('status', 1)
                                        ->where('mercado_full_name', $odd->odd)
                                        ->where('user_id', env('ID_USER'))
                                        ->first();
                                    if ($o_porcents_geral) {
                                        $o_geral = $o_porcents_geral->porcentagem;

                                        if (auth()->user()) {
                                            $m_porcents_user = ConfigMercados::where('site_id', env('ID_SITE'))
                                                ->where('name', $odd->group_opp)
                                                ->where('status', 1)
                                                ->where('user_id', auth()->user()->id)
                                                ->first();

                                            $m_user = $m_porcents_user->porcentagem;

                                            $o_porcents_user = ConfigOdd::where('site_id', env('ID_SITE'))
                                                ->where('status', 1)
                                                ->where('mercado_full_name', $odd->odd)
                                                ->where('user_id', auth()->user()->id)
                                                ->first();

                                            $o_user = $o_porcents_user->porcentagem;
                                        }
                                        if (in_array($odd->id, $arr_odd_b)) {
                                            $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                            $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                            $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;

                                            $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                        } else {
                                            $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                            $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                            $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;

                                            if ($odd->cotacao <= $odd_z) {
                                                $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                            } else {

                                                //Odds Alteradas
                                                if (in_array($match->event_id . $odd->odd, $arr_odd_alterada)) {

                                                    $odd_alt = BlockOddMatch::where('odd_id',  $match->event_id)->where('odd', $odd->odd)->get();

                                                    foreach ($odd_alt as $odd_at) {
                                                        $cotacao = round($odd_at->cotacao, 2);
                                                    }
                                                } else {
                                                    $cotacao = $odd->cotacao;
                                                }

                                                if (auth()->user()) {
                                                    $v_porcent_total_geral = ($m_geral + $o_geral + $m_user + $o_user) / 100;
                                                } else {
                                                    $v_porcent_total_geral = ($m_geral + $o_geral) / 100;
                                                }
                                                $redu = $cotacao - 1;

                                                $odd_final = ($redu * $v_porcent_total_geral) + $redu + 1;


                                                if ($odd_final >= $odd_m) {
                                                    $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_m, 2);
                                                } else if ($odd_final <= $odd_z) {
                                                    $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                                } else {
                                                    $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_final, 2);
                                                }
                                            }
                                        }
                                    } else {
                                        $o_geral = 0;
                                        $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                        $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                        $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                                        $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                    }
                                }


                                $q++;
                            }

                            $j++;
                        }
                    }
                }



                $i++;
            }
            $arr = array_filter($arr);
            $arr = array_values($arr);

            return $arr;
        } else {
        }
    }

    public function showDepoisAmanhaMain()
    {


        $mainleagues = MainLeague::where('site_id', env('ID_SITE'))->get();


        $liga = array();
        foreach ($mainleagues as $league) {
            $liga[] = $league->league;
        }

        //config gerais
        $league_bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();

        $block_league = array();


        foreach ($league_bloqueadas  as $league_bloqueada) {

            $block_league[] = $league_bloqueada->league;
        }

        //return $block_league;

        $block_match = array();

        $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();



        foreach ($matchs_bloqueadas as $match_bloqueada) {

            $block_match[] = $match_bloqueada->event_id;
        }

        //Mercado Bloqueados
        $merc_blocks = ConfigMercados::where('status', 0)
            //->where('')
            ->where('site_id', env('ID_SITE'))
            ->where('user_id', env('ID_USER'))
            ->get();

        $block_merc = array();

        foreach ($merc_blocks as $merc_block) {

            $block_merc[] = $merc_block->name;
        }


        //Odd Bloqueadas
        $odd_blocks = ConfigOdd::where('status', 0)
            ->where('site_id', env('ID_SITE'))
            ->where('user_id', env('ID_USER'))
            ->get();

        $block_odd = array();

        foreach ($odd_blocks as $odd_block) {

            $block_odd[] = $odd_block->name;
        }

        //Bloqueio de odd limite
        $odd_zerada = Configuracao::where('site_id', env('ID_SITE'))
            ->get();


        foreach ($odd_zerada as $zerada) {

            $odd_z =  $zerada->bloquear_odd_abaixo;
            $date_limite_matchs = $zerada->data_limite_jogos;
            $odd_m = $zerada->travar_odd_acima;
        }


        //Odds alteradas
        $odds_alteradas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 1)
            ->get();

        $arr_odd_alterada = array();

        foreach ($odds_alteradas as $odd_alterada) {
            $arr_odd_alterada[] = $odd_alterada->odd_id . $odd_alterada->odd;
        }

        //Odds bloqeuadas por partida
        $odds_bloqueadas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 0)
            ->get();


        $arr_odd_b  = array();
        foreach ($odds_bloqueadas as $odd_bloqueada) {

            $arr_odd_b[] = $odd_bloqueada->odd_uid;
        }

        $percent_merc_user = 0;
        $percent_odd_user = 0;






        $arr[] = array();

        $all = AferTomorrow::select('dados')
            ->orderBy('id', 'desc')
            ->get();


        $dados = json_decode($all[0]['dados']);




        if ($this->agora->format('Y-m-d') <= $date_limite_matchs) {


            $i = 0;
            foreach ($dados as $leagues) {

                if (in_array($leagues->league, $block_league)) {
                } else if (in_array($leagues->league, $liga)) {
                    $j = 0;

                    foreach ($leagues->match as $match) {


                        if (in_array($match->event_id, $block_match)) {

                            $leagues->match = array_filter($leagues->match);
                            $leagues->match = array_values($leagues->match);
                        } else {

                            $arr[$i]['league'] = $leagues->league;

                            $arr[$i]['match'][$j]['id'] = $match->id;
                            $arr[$i]['match'][$j]['event_id'] = $match->event_id;
                            $arr[$i]['match'][$j]['sport'] = $match->sport;
                            $arr[$i]['match'][$j]['confronto'] = $match->confronto;
                            $arr[$i]['match'][$j]['home'] = $match->home;
                            $arr[$i]['match'][$j]['image_id_home'] = $match->image_id_home;
                            $arr[$i]['match'][$j]['away'] = $match->away;
                            $arr[$i]['match'][$j]['image_id_away'] = $match->image_id_away;
                            $arr[$i]['match'][$j]['date'] = $match->date;
                            $arr[$i]['match'][$j]['count_odd'] = $match->count_odd;


                            $q = 0;
                            foreach ($match->odds as $odd) {
                                $arr[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;
                                if (in_array('Vencedor do Encontro', $block_merc)) {

                                    $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                    $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                    $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                                    $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                } else {


                                    if (in_array($odd->id, $arr_odd_b)) {
                                        $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                        $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                        $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;

                                        $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                    } else {


                                        $arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                        $arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->group_opp;
                                        $arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;

                                        if ($odd->cotacao <= $odd_z) {
                                            $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                        } else {

                                            $m_porcents_geral = ConfigMercados::where('site_id', env('ID_SITE'))
                                                ->where('name', $odd->group_opp)
                                                ->where('status', 1)
                                                ->where('user_id', env('ID_USER'))
                                                ->first();

                                            $m_geral = $m_porcents_geral->porcentagem;

                                            $o_porcents_geral = ConfigOdd::where('site_id', env('ID_SITE'))
                                                ->where('status', 1)
                                                ->where('mercado_full_name', $odd->odd)
                                                ->where('user_id', env('ID_USER'))
                                                ->first();

                                            $o_geral = $o_porcents_geral->porcentagem;


                                            if (auth()->user()) {
                                                $m_porcents_user = ConfigMercados::where('site_id', env('ID_SITE'))
                                                    ->where('name', $odd->group_opp)
                                                    ->where('status', 1)
                                                    ->where('user_id', auth()->user()->id)
                                                    ->first();

                                                $m_user = $m_porcents_user->porcentagem;

                                                $o_porcents_user = ConfigOdd::where('site_id', env('ID_SITE'))
                                                    ->where('status', 1)
                                                    ->where('mercado_full_name', $odd->odd)
                                                    ->where('user_id', auth()->user()->id)
                                                    ->first();

                                                $o_user = $o_porcents_user->porcentagem;
                                            }




                                            //Odds Alteradas
                                            if (in_array($match->event_id . $odd->odd, $arr_odd_alterada)) {

                                                $odd_alt = BlockOddMatch::where('odd_id',  $match->event_id)->where('odd', $odd->odd)->get();

                                                foreach ($odd_alt as $odd_at) {
                                                    $cotacao = round($odd_at->cotacao, 2);
                                                }
                                            } else {
                                                $cotacao = $odd->cotacao;
                                            }


                                            if (auth()->user()) {
                                                $v_porcent_total_geral = ($m_geral + $o_geral + $m_user + $o_user) / 100;
                                            } else {
                                                $v_porcent_total_geral = ($m_geral + $o_geral) / 100;
                                            }
                                            $redu = $cotacao - 1;

                                            $odd_final = ($redu * $v_porcent_total_geral) + $redu + 1;



                                            $redu = $cotacao - 1;
                                            $odd_final = ($redu * $v_porcent_total_geral) + $redu + 1;

                                            if ($odd_final >= $odd_m) {
                                                $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_m, 2);
                                            } else if ($odd_final <= $odd_z) {
                                                $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                            } else {
                                                $arr[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_final, 2);
                                            }
                                        }
                                    }
                                }


                                $q++;
                            }



                            $j++;
                        }
                    }
                } else {
                }



                $i++;
            }
            $arr = array_filter($arr);
            $arr = array_values($arr);

            return $arr;
        } else {
        }
    }


    public function depoisAmanha()
    {

        $all =  AferTomorowMatchFlash::where('site_id', env('ID_SITE'))->get();
        return json_decode($all[0]['dados']);
    }



    public function liveFutebol()
    {

        $dados_live = Configuracao::where('site_id', env('ID_SITE'))
            ->get();

        foreach ($dados_live as $live) {
            $cotacao_live =  $live->cotacao_live;
            $time_live = $live->time_live;
        }



        $bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();

        $leageBlock = array();


        foreach ($bloqueadas as $bloqueada) {

            $leageBlock[] = $bloqueada->league;
        }

        $block_match = array();

        $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();



        foreach ($matchs_bloqueadas as $match_bloqueada) {

            $block_match[] = $match_bloqueada->event_id;
        }

        $block_match = array();

        $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();



        foreach ($matchs_bloqueadas as $match_bloqueada) {

            $block_match[] = $match_bloqueada->event_id;
        }

        //Mercado Bloqueados
        $merc_blocks = ConfigMercados::where('status', 0)
            ->where('site_id', env('ID_SITE'))
            ->get();

        $block_merc = array();

        foreach ($merc_blocks as $merc_block) {

            $block_merc[] = $merc_block->name;
        }


        //Odd Bloqueadas
        $odd_blocks = ConfigOdd::where('status', 0)
            ->where('site_id', env('ID_SITE'))
            ->where('user_id', env('ID_USER'))
            ->get();

        $block_odd = array();

        foreach ($odd_blocks as $odd_block) {

            $block_odd[] = $odd_block->name;
        }

        //Bloqueio de odd limite data limites
        $confs = Configuracao::where('site_id', env('ID_SITE'))
            ->get();


        foreach ($confs as $conf) {

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

        foreach ($odds_alteradas as $odd_alterada) {
            $arr_odd_alterada[] = $odd_alterada->odd_id . $odd_alterada->odd;
        }

        //Odds bloqeuadas por partida
        $odds_bloqueadas = BlockOddMatch::where('site_id', env('ID_SITE'))
            ->where('status', 0)
            ->get();


        $arr_odd_b  = array();
        foreach ($odds_bloqueadas as $odd_bloqueada) {

            $arr_odd_b[] = $odd_bloqueada->odd_uid;
        }

        $return = array();

        $leagues = Match::select('league')
            ->where('time_status', 1)
            ->where('time', '<=',  $time_live)
            ->whereNotIn('league', $leageBlock)
            ->whereNotIn('event_id', $block_match)
            ->groupBy('league')
            ->orderBy('league', 'asc')
            ->get();

        $i = 0;

        foreach ($leagues  as $league) {


            $return[$i]['league'] = $league->league;

            $matchs = Match::where('league', $league->league)
                ->where('time_status', 1)
                ->where('time', '<=',  $time_live)
                ->whereNotIn('event_id', $block_match)
                ->with('odds')
                ->get();

            $j = 0;
            foreach ($matchs as $match) {
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
                $return[$i]['match'][$j]['halfTimeScoreHome'] = $match->halfTimeScoreHome;
                $return[$i]['match'][$j]['halfTimeScoreAway'] = $match->halfTimeScoreAway;
                $return[$i]['match'][$j]['fullTimeScoreHome'] = $match->fullTimeScoreHome;
                $return[$i]['match'][$j]['fullTimeScoreAway'] = $match->fullTimeScoreAway;
                $return[$i]['match'][$j]['numberOfCornersHome'] = $match->numberOfCornersHome;
                $return[$i]['match'][$j]['numberOfCornersAway'] = $match->numberOfCornersAway;
                $return[$i]['match'][$j]['numberOfYellowCardsHome'] = $match->numberOfYellowCardsHome;
                $return[$i]['match'][$j]['numberOfYellowCardsAway'] = $match->numberOfYellowCardsAway;
                $return[$i]['match'][$j]['numberOfRedCardsHome'] = $match->numberOfRedCardsHome;
                $return[$i]['match'][$j]['numberOfRedCardsAway'] = $match->numberOfRedCardsAway;
                $count = Odd::where('match_id', $match->id)->count();
                $return[$i]['match'][$j]['count_odd'] = $count;


                if ($count == 0 || count($match->odds) == 0) {

                    for ($q = 0; $q < 3; $q++) {
                        $return[$i]['match'][$j]['odds'][$q]['id'] = $match->event_id . $match->id . $q;
                        $return[$i]['match'][$j]['odds'][$q]['group_opp'] = 'Vencedor do Encontro';
                        $return[$i]['match'][$j]['odds'][$q]['odd'] = $q;
                        $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                    }
                } else {

                    $q = 0;
                    foreach ($match->odds as $odd) {
                        $redu = $odd->cotacao - 1;
                        $odd_final = ($redu * $cotacao_live / 100) + $redu + 1;
                        if ($odd_final <= $odd_z) {
                            $cota =  0;
                        } else {
                            $cota =  $odd_final;
                        }
                        $return[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;
                        $return[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                        $return[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->mercado_name;
                        $return[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                        $return[$i]['match'][$j]['odds'][$q]['cotacao'] =  round($cota, 2);
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

    //Dias da semna
    public function diasFutebol()
    {

        for ($i = 0; $i < 3; $i++) {
            $data = date('D', strtotime('+' . $i . 'day'));

            $semana = array(
                'Sun' => 'Domingo',
                'Mon' => 'Segunda-Feira',
                'Tue' => 'Terca-Feira',
                'Wed' => 'Quarta-Feira',
                'Thu' => 'Quinta-Feira',
                'Fri' => 'Sexta-Feira',
                'Sat' => 'Sábado'
            );

            $this->arr[$i]['day'] = $semana["$data"];
            $this->arr[$i]['num'] = $i;
        }

        return $this->arr;
    }

    public function allMatchs()
    {

        // if(!Cache::has('jogosHoje')) {
        //     Cache::put('jogosHoje',  array_merge((array)$this->showHojeMain(), (array)$this->showHoje()), now()->addMinutes(1));
        //  }
        //  $jogos = Cache::get('jogosHoje');

        //  return $jogos;

        //     if(!Cache::has('allMatchs')) {


        //     $bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();

        //     $leageBlock = array();


        //     foreach($bloqueadas as $bloqueada) {

        //         $leageBlock[] = $bloqueada->league;

        //     }

        //     $block_match = array();

        //     $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();



        //     foreach($matchs_bloqueadas as $match_bloqueada) {

        //         $block_match[] = $match_bloqueada->event_id;

        //     }

        //     $block_match = array();

        //     $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();



        //     foreach($matchs_bloqueadas as $match_bloqueada) {

        //         $block_match[] = $match_bloqueada->event_id;

        //     }

        //      //Mercado Bloqueados
        //      $merc_blocks = ConfigMercados::where('status', 0)
        //                                       ->where('site_id', env('ID_SITE'))
        //                                       ->get();

        //       $block_merc = array();

        //       foreach($merc_blocks as $merc_block) {

        //           $block_merc[] = $merc_block->name;

        //       }


        //       //Odd Bloqueadas
        //       $odd_blocks = ConfigOdd::where('status', 0)
        //                                  ->where('site_id', env('ID_SITE'))
        //                                  ->where('user_id', env('ID_USER'))
        //                                  ->get();

        //       $block_odd= array();

        //       foreach($odd_blocks as $odd_block) {

        //           $block_odd[] = $odd_block->name;

        //       }

        //   //Bloqueio de odd limite data limites
        //   $confs = Configuracao::where('site_id', env('ID_SITE'))
        //                                   ->get();


        //   foreach($confs as $conf) {

        //       $odd_z =  $conf->bloquear_odd_abaixo;
        //       $date_limite_matchs = $conf->data_limite_jogos;
        //       $odd_m = $conf->travar_odd_acima;

        //   }

        //   $percent_merc_user = 0;
        //   $percent_odd_user = 0;


        //   //Odds alteradas
        //   $odds_alteradas = BlockOddMatch::where('site_id', env('ID_SITE'))
        //                                   ->where('status', 1)
        //                                   ->get();

        //   $arr_odd_alterada = array();

        //   foreach($odds_alteradas as $odd_alterada) {
        //       $arr_odd_alterada[] = $odd_alterada->odd_id.$odd_alterada->odd;
        //   }

        //   //Odds bloqeuadas por partida
        //   $odds_bloqueadas = BlockOddMatch::where('site_id', env('ID_SITE'))
        //                                   ->where('status', 0)
        //                                   ->get();


        //   $arr_odd_b  = array();
        //   foreach($odds_bloqueadas as $odd_bloqueada) {

        //       $arr_odd_b[] = $odd_bloqueada->odd_uid;

        //   }

        //     $return = array();

        //     $leagues = Match::select('league')
        //                         ->where('date' ,'>=', $this->agora)
        //                         ->where('date', '<=',  $date_limite_matchs." 23:59:59")
        //                         ->where('visible' , 'Sim')
        //                         ->whereNotIn('league', $leageBlock)
        //                         ->whereNotIn('event_id', $block_match)
        //                         ->groupBy('league')
        //                         ->orderBy('league', 'asc')
        //                         ->get();

        //     $i=0;

        //     foreach($leagues  as $league) {


        //                $return[$i]['league'] = $league->league;

        //                 $matchs = Match::where('league', $league->league)
        //                                 ->where('visible' , 'Sim')
        //                                 ->where('date' ,'>=', $this->agora)
        //                                 ->where('date', '<=',  $date_limite_matchs." 23:59:59")
        //                                 ->whereNotIn('event_id', $block_match)
        //                                 ->orderBy('date', 'asc')
        //                                 ->with('odds')
        //                                 ->get();

        //                 $j=0;
        //                 foreach($matchs as $match) {
        //                     $return[$i]['match'][$j]['id'] = $match->id;
        //                     $return[$i]['match'][$j]['event_id'] = $match->event_id;
        //                     $return[$i]['match'][$j]['sport'] = $match->sport_name;
        //                     $return[$i]['match'][$j]['confronto'] = $match->confronto;
        //                     $return[$i]['match'][$j]['home'] = $match->home;
        //                     $return[$i]['match'][$j]['image_id_home'] = $match->image_id_home;
        //                     $return[$i]['match'][$j]['away'] = $match->away;
        //                     $return[$i]['match'][$j]['image_id_away'] = $match->image_id_away;
        //                     $return[$i]['match'][$j]['score'] = $match->score;
        //                     $return[$i]['match'][$j]['date'] = $match->date;
        //                     $return[$i]['match'][$j]['time'] = $match->time;
        //                     $count = Odd::where('match_id', $match->id)->count();
        //                     $return[$i]['match'][$j]['count_odd'] = $count;

        //                     if($count == 0) {

        //                         for($q = 0; $q < 3; $q++) {
        //                             $return[$i]['match'][$j]['odds'][$q]['id'] = $match->event_id.$match->id.$q;
        //                             $return[$i]['match'][$j]['odds'][$q]['group_opp'] = 'Vencedor do Encontro';
        //                             $return[$i]['match'][$j]['odds'][$q]['odd'] = $q;
        //                             $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
        //                         }

        //                     } else {

        //                           $q=0;
        //                           foreach($match->odds as $odd) {

        //                                 $return[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;
        //                                 $return[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
        //                                 $return[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->mercado_name;
        //                                 $return[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
        //                                 $return[$i]['match'][$j]['odds'][$q]['cotacao'] =   round($odd->cotacao, 2);
        //                                 $return[$i]['match'][$j]['odds'][$q]['cotacaoOriginal'] = $odd->cotacao;
        //                                 $return[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;


        //                           $q++;
        //                           }

        //                     }

        //             $j++;
        //             }


        //        $i++;
        //     }

        //  Cache::put('allMatchs', $return, now()->addMinutes(1));

        // }
        //   return $jogos = Cache::get('allMatchs');


    }



}
