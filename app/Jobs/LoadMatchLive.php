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
use App\Events\LoadMatch;

class LoadMatchLive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $id;
    private $match;
    private $arr = array();

    public function __construct(Match $match, $id)
    {
        $this->id       = $id;
        $this->match    = $match;
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */

    public function showOdds()
    {    
           
              
    }

    public function handle()
    {
        $id = $this->id;
        $dados_live = Configuracao::where('site_id', env('ID_SITE'))
                                    ->get();

            foreach($dados_live as $live) {
                $cotacao_live =  $live->cotacao_live;
                $time_live = $live->time_live;
            }


            //Mercado Bloqueados
            $merc_blocks = ConfigMercados::where('status', 0)
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

            $block_odd = array();

            foreach($odd_blocks as $odd_block) {

            $block_odd[] = $odd_block->name;

            }

            //Bloqueio de odd limite
            $odd_zerada = Configuracao::where('site_id', env('ID_SITE'))
                            ->get();


            foreach($odd_zerada as $zerada) {
                $odd_z =  $zerada->bloquear_odd_abaixo;
                $odd_m = $zerada->travar_odd_acima;
            }





            //Odds alteradas
            $odds_alteradas = BlockOddMatch::where('site_id', env('ID_SITE'))
                                            ->where('status', 1)
                                            ->get();

            $arr_odd_alterada = array();
            $arr_odd_alt      = array();
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

            $mercados = Odd::select('mercado_name')
                            ->where('match_id', $id)
                            ->groupBy('order')
                            ->groupBy('mercado_name')
                            ->where('state', 'ACTIVE')
                            // ->whereNotIn('mercado_name', $block_merc)
                            // ->whereNotIn('id', $arr_odd_b)
                            ->orderBy('order')  
                            ->get();


            $i = 0;
            foreach($mercados as $mercado) {
            $this->arr[$i]['match_id'] = $id;
            $this->arr[$i]['name'] = $mercado->mercado_name;


            $odds = Odd::where('match_id', $id)
                            ->where('mercado_name', $mercado->mercado_name)
                            ->orderBy('header', 'asc')  
                            ->orderBy('goals', 'asc')
                            ->get();

                    
                                                                        
            $j=0;                      
            foreach($odds as $odd) {      
                $redu = $odd->cotacao-1;
                $odd_final = ($redu*$cotacao_live/100)+$redu+1;
                if ($odd_final <= $odd_z) { 
                    $cota =  0;
                } 
                else {
                    $cota =  $odd_final;
                }
                $this->arr[$i]['odds'][$j]['id']                = $odd->id;
                $this->arr[$i]['odds'][$j]['group_opp']         = $odd->mercado_name;
                $this->arr[$i]['odds'][$j]['odd']               = $odd->odd;
                $this->arr[$i]['odds'][$j]['type']              = $odd->type;
                $this->arr[$i]['odds'][$j]['cotacaoOriginal']   = $odd->cotacao;
                $this->arr[$i]['odds'][$j]['cotacao']           = round($cota, 2);

            $j++;
            }

            $i++;
            }

            $this->arr = array_filter($this->arr);
            $this->arr = array_values($this->arr);

            //return $this->arr;

         //Atualiza broadcast
         broadcast(new LoadMatch($this->arr));
    }
}
