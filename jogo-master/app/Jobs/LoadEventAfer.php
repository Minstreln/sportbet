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
use App\Models\AferTomorowMatchFlash;
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
use App\Events\LiveAfeterTomorowFutebol;

class LoadEventAfer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $this->hoje     = $hoje     = Carbon::today();
        $this->amanha   = $amanha   = Carbon::tomorrow();
        $this->agora    = $agora    = Carbon::now();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
        //depois de amanhã
        public function showDepoisAmanha()
        {   
            
        $mainleagues = MainLeague::where('site_id', env('ID_SITE'))->get();


        $liga = array();
        foreach($mainleagues as $league) {
            $liga[] =$league->league_id;
        }     
           
        //config gerais
        $league_bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();

        $block_league = array();
           
       
        foreach($league_bloqueadas  as $league_bloqueada) {

                $block_league[] = $league_bloqueada->league;
        
        }

        //return $block_league;
    
        $block_match = array();

        $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();

    

        foreach($matchs_bloqueadas as $match_bloqueada) {

            $block_match[] = $match_bloqueada->event_id;
    
        }

       //Mercado Bloqueados
       $merc_blocks = ConfigMercados::where('status', 0)
                                        //->where('')
                                        ->where('site_id', env('ID_SITE'))
                                        ->where('user_id', env('ID_USER'))       
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

        //Bloqueio de odd limite
        $odd_zerada = Configuracao::where('site_id', env('ID_SITE'))
                                        ->get();


        foreach($odd_zerada as $zerada) {

            $odd_z =  $zerada->bloquear_odd_abaixo;
            $date_limite_matchs = $zerada->data_limite_jogos;
            $odd_m = $zerada->travar_odd_acima;

        }

      
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

    $percent_merc_user = 0;
    $percent_odd_user = 0;



    $data_hoje =  date('Y-m-d', strtotime($this->hoje));


    $arr[] = array();

    $data_hoje =  date('Y-m-d', strtotime($this->hoje));
    $return = array();

    $data_hoje =  date('Y-m-d', strtotime($this->hoje));
    $data_amanha = date('Y-m-d', strtotime($this->amanha));
    $return = array();


    $aferTomorow = $this->amanha->addDay()->format('Y-m-d');


        $leagues = Match::select('league')
                        ->where('date' ,'>', $aferTomorow.' 00:00:01')
                        ->where('date' ,'<=', $aferTomorow.' 23:59:00')
                        ->where( 'visible', 'Sim')
                        ->whereNotIn('league_id', $liga)
                        ->groupBy('league')
                        ->orderBy('league', 'asc')
                        ->get();

                          $i=0;

                          foreach($leagues  as $league) {


                                      $return[$i]['league'] = $league->league;

                                      $matchs = Match::where('league', $league->league)
                                                        ->where('date' ,'>', $aferTomorow.' 00:00:01')
                                                        ->where('date' ,'<=', $aferTomorow.' 23:59:00')
                                                        ->where( 'visible', 'Sim')
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
                                          $return[$i]['match'][$j]['date'] = $match->date;
                                          
                                          $count = Odd::where('match_id', $match->id)->count();
                                          $return[$i]['match'][$j]['count_odd'] = $count;

                                          if($count == 0) {

                                            for($q = 0; $q < 3; $q++) {
                                                $return[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;
                                                $return[$i]['match'][$j]['odds'][$q]['id'] = $match->event_id.$match->id.$q;
                                                $return[$i]['match'][$j]['odds'][$q]['group_opp'] = 'Vencedor do Encontro';
                                                $return[$i]['match'][$j]['odds'][$q]['odd'] = $q;
                                                $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                            }
                
                                        } else {

                                            $q=0;
                                            foreach($match->odds as $odd) {
                                                $return[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;
                                                if(in_array('Vencedor do Encontro', $block_merc)) {
                        
                                                    $return[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                                    $return[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->mercado_name;
                                                    $return[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                                                    $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0; 
                                                    
                                                }else{
                
                                                   
                                                    if (in_array($odd->id ,$arr_odd_b)) { 
                                                        $return[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                                        $return[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->mercado_name;
                                                        $return[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                            
                                                        $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                                    } else {
                
                
                                                    $return[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                                    $return[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->mercado_name;
                                                    $return[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                        
                                                       if($odd->cotacao <= $odd_z) {
                                                                $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                                        }else{
                
                                                            $m_porcents_geral = ConfigMercados::where('site_id', env('ID_SITE'))
                                                                                ->where('name', $odd->mercado_name)
                                                                                ->where('status', 1)  
                                                                                ->where('user_id', env('ID_USER'))                     
                                                                                ->first();

                                                                if($m_porcents_geral) {
                                                                    $m_geral = $m_porcents_geral->porcentagem;
                                                                } else {
                                                                    $m_geral = 0;    
                                                                }


                
                                                          
                
                                                             $o_porcents_geral = ConfigOdd::where('site_id', env('ID_SITE'))
                                                                                        ->where('status', 1)
                                                                                        ->where('mercado_full_name', $odd->odd)
                                                                                        ->where('user_id', env('ID_USER'))         
                                                                                        ->first();  

                                                            if($o_porcents_geral) {
                                                                $o_geral = $o_porcents_geral->porcentagem;
                                                            } else {
                                                                $o_geral = 0;
                                                            }
                
                                                                
                
                
                                                          
                                             
                    
                                                                         
                    
                                                                            //Odds Alteradas
                                                                            if(in_array( $match->event_id.$odd->odd ,$arr_odd_alterada) ) {
                        
                                                                                $odd_alt = BlockOddMatch::where('odd_id',  $match->event_id)->where('odd', $odd->odd)->get();
                        
                                                                                foreach($odd_alt as $odd_at) {
                                                                                    $cotacao = round($odd_at->cotacao, 2); 
                                                                                }
                        
                                                                            }else{
                                                                                $cotacao = $odd->cotacao;
                                                                            }  
                        
                        
                                                                          
                                                                            $v_porcent_total_geral = ($m_geral+$o_geral)/100;
                                                                            
                                                                            $redu = $cotacao-1;
                    
                                                                            $odd_final = ($redu*$v_porcent_total_geral)+$redu+1;
                        
                                                                            
                                                                            
                                                                            $redu = $cotacao-1;
                                                                            $odd_final = ($redu*$v_porcent_total_geral)+$redu+1;
                    
                                                                            if($odd_final >= $odd_m) {
                                                                                $return[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_m, 2);
                                                                            } else if($odd_final <= $odd_z) {
                                                                               $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                                                            }
                                                                            else{
                                                                               $return[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_final, 2);
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
    
        public function showDepoisAmanhaMain()
        {
    
            $mainleagues = MainLeague::where('site_id', env('ID_SITE'))->get();


            $liga = array();
            foreach($mainleagues as $league) {
                $liga[] =$league->league_id;
            }     
               
            //config gerais
            $league_bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();
    
            $block_league = array();
               
           
            foreach($league_bloqueadas  as $league_bloqueada) {
    
                    $block_league[] = $league_bloqueada->league;
            
            }
    
            //return $block_league;
        
            $block_match = array();
    
            $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();
    
        
    
            foreach($matchs_bloqueadas as $match_bloqueada) {
    
                $block_match[] = $match_bloqueada->event_id;
        
            }
    
           //Mercado Bloqueados
           $merc_blocks = ConfigMercados::where('status', 0)
                                            //->where('')
                                            ->where('site_id', env('ID_SITE'))
                                            ->where('user_id', env('ID_USER'))       
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
    
            //Bloqueio de odd limite
            $odd_zerada = Configuracao::where('site_id', env('ID_SITE'))
                                            ->get();
    
    
            foreach($odd_zerada as $zerada) {
    
                $odd_z =  $zerada->bloquear_odd_abaixo;
                $date_limite_matchs = $zerada->data_limite_jogos;
                $odd_m = $zerada->travar_odd_acima;
    
            }
    
          
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
    
        $percent_merc_user = 0;
        $percent_odd_user = 0;
    
    
    
        $data_hoje =  date('Y-m-d', strtotime($this->hoje));
    
    
        $arr[] = array();
    
        $data_hoje =  date('Y-m-d', strtotime($this->hoje));
        $data_amanha = date('Y-m-d', strtotime($this->amanha));
        $return = array();
    
    
    
        $aferTomorow = $this->amanha->addDay()->format('Y-m-d');


        $leagues = Match::select('league')
                        ->where('date' ,'>', $aferTomorow.' 00:00:01')
                        ->where('date' ,'<=', $aferTomorow.' 23:59:00')
                        ->where( 'visible', 'Sim')
                        ->whereIn('league_id', $liga)
                        ->groupBy('league')
                        ->orderBy('league', 'asc')
                        ->get();
    
                              $i=0;
    
                              foreach($leagues  as $league) {
    
    
                                          $return[$i]['league'] = $league->league;
    
                                          $matchs = Match::where('league', $league->league)
                                                            ->where('date' ,'>', $aferTomorow.' 00:00:01')
                                                            ->where('date' ,'<=', $aferTomorow.' 23:59:00')
                                                            ->where( 'visible', 'Sim')
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
                                              $return[$i]['match'][$j]['date'] = $match->date;
                                              
                                              $count = Odd::where('match_id', $match->id)->count();
                                              $return[$i]['match'][$j]['count_odd'] = $count;
    
                                              if($count == 0) {
    
                                                for($q = 0; $q < 3; $q++) {
                                                    $return[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;
                                                    $return[$i]['match'][$j]['odds'][$q]['id'] = $match->event_id.$match->id.$q;
                                                    $return[$i]['match'][$j]['odds'][$q]['group_opp'] = 'Vencedor do Encontro';
                                                    $return[$i]['match'][$j]['odds'][$q]['odd'] = $q;
                                                    $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                                }
                    
                                            } else {
    
                                                $q=0;
                                                foreach($match->odds as $odd) {
                                                    $return[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;
                                                    if(in_array('Vencedor do Encontro', $block_merc)) {
                            
                                                        $return[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                                        $return[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->mercado_name;
                                                        $return[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                                                        $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0; 
                                                        
                                                    }else{
                    
                                                       
                                                        if (in_array($odd->id ,$arr_odd_b)) { 
                                                            $return[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                                            $return[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->mercado_name;
                                                            $return[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                                
                                                            $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                                        } else {
                    
                    
                                                        $return[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                                        $return[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->mercado_name;
                                                        $return[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                            
                                                           if($odd->cotacao <= $odd_z) {
                                                                    $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                                            }else{
                    
                                                                $m_porcents_geral = ConfigMercados::where('site_id', env('ID_SITE'))
                                                                                    ->where('name', $odd->mercado_name)
                                                                                    ->where('status', 1)  
                                                                                    ->where('user_id', env('ID_USER'))                     
                                                                                    ->first();
    
                                                                    if($m_porcents_geral) {
                                                                        $m_geral = $m_porcents_geral->porcentagem;
                                                                    } else {
                                                                        $m_geral = 0;    
                                                                    }
    
    
                    
                                                              
                    
                                                                 $o_porcents_geral = ConfigOdd::where('site_id', env('ID_SITE'))
                                                                                            ->where('status', 1)
                                                                                            ->where('mercado_full_name', $odd->odd)
                                                                                            ->where('user_id', env('ID_USER'))         
                                                                                            ->first();  
    
                                                                if($o_porcents_geral) {
                                                                    $o_geral = $o_porcents_geral->porcentagem;
                                                                } else {
                                                                    $o_geral = 0;
                                                                }
                    
                                                                    
                    
                    
                                                              
                                                 
                        
                                                                             
                        
                                                                                //Odds Alteradas
                                                                                if(in_array( $match->event_id.$odd->odd ,$arr_odd_alterada) ) {
                            
                                                                                    $odd_alt = BlockOddMatch::where('odd_id',  $match->event_id)->where('odd', $odd->odd)->get();
                            
                                                                                    foreach($odd_alt as $odd_at) {
                                                                                        $cotacao = round($odd_at->cotacao, 2); 
                                                                                    }
                            
                                                                                }else{
                                                                                    $cotacao = $odd->cotacao;
                                                                                }  
                            
                            
                                                                              
                                                                                $v_porcent_total_geral = ($m_geral+$o_geral)/100;
                                                                                
                                                                                $redu = $cotacao-1;
                        
                                                                                $odd_final = ($redu*$v_porcent_total_geral)+$redu+1;
                            
                                                                                
                                                                                
                                                                                $redu = $cotacao-1;
                                                                                $odd_final = ($redu*$v_porcent_total_geral)+$redu+1;
                        
                                                                                if($odd_final >= $odd_m) {
                                                                                    $return[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_m, 2);
                                                                                } else if($odd_final <= $odd_z) {
                                                                                   $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                                                                }
                                                                                else{
                                                                                   $return[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_final, 2);
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
    
        
        public function depoisAmanha()
        {
           return  array_merge($this->showDepoisAmanhaMain(), $this->showDepoisAmanha());
        
        }
    

       

        public function handle()
        {
            $matchs =  AferTomorowMatchFlash::where('site_id', env('ID_SITE'))->first();

            if($matchs) {
             $home = AferTomorowMatchFlash::find($matchs->id);
     
             $uptade =   $home->update([
                       'dados' => json_encode($this->depoisAmanha()),
             ]);
     
            echo "exite";
            } else {
             AferTomorowMatchFlash::create([
                 'dados'     => json_encode($this->depoisAmanha()),
                 'site_id'   => env('ID_SITE')
             ]);
                echo "nada";
            }
            //dd($this->depoisAmanha());
            broadcast(new LiveAfeterTomorowFutebol($this->depoisAmanha()));
        }
}
