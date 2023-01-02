<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ConfigMercados;
use App\Models\Mercado;
use App\Models\Odd;
use App\Models\BlockOddMatch;
use App\Jobs\LoadEventHoje;
use App\Jobs\LoadEventAmanha;
use App\Jobs\LoadEventAfer;
use App\Jobs\LoadEventLive;

class MercadosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function indexView()
    {
        return view('admin.bloqueio-mercado');
    }
 
    public function index()
    {
            return ConfigMercados::where('site_id', env('ID_SITE'))
                                    ->where('user_id', auth()->user()->id)
                                    ->get();
    }

    public function mercadoUser($id) 
    {
        return ConfigMercados::where('user_id', $id)->get();    
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {     
         //Odds Alteradas
         $odds_alteradas = BlockOddMatch::where('site_id', env('ID_SITE'))->get();
         
         $arr_odd_alterada = array();
         $arr_odd_alt      = array(); 
         foreach($odds_alteradas as $odd_alterada) {
            $arr_odd_alterada[] = $odd_alterada->odd_id.$odd_alterada->odd;
            
         }

        $return = array();

        $mercados = Odd::select('mercado_name')
                        ->where('match_id', $id)
                        ->groupBy('order')
                        ->groupBy('mercado_name')
                        // ->whereNotIn('mercado_name', $block_merc)
                        // ->whereNotIn('id', $arr_odd_b)
                        ->orderBy('order')  
                        ->get();


        $i=0;
        foreach($mercados as $mercado) {

            $return[$i]['name'] = $mercado->mercado_name;

            $odds = Odd::where('match_id', $id)
                        ->where('mercado_name', $mercado->mercado_name)
                        ->orderBy('header', 'asc')  
                        ->orderBy('goals', 'asc')->get();

            $j=0;
            foreach($odds as $odd) {


                  if(in_array($odd->event_id.$odd->odd ,$arr_odd_alterada)) {
                    
                    $return[$i]['odds'][$j]['id'] = $odd->id;
                    $return[$i]['odds'][$j]['odd'] = $odd->odd;
                    $odd_alt = BlockOddMatch::where('odd_id', $odd->event_id)->where('odd', $odd->odd)->get();

                    foreach($odd_alt as $odd_at) {
                            $return[$i]['odds'][$j]['cotacao'] = round($odd_at->cotacao, 2);
                            $return[$i]['odds'][$j]['status'] = $odd_at->status; 
                            $return[$i]['odds'][$j]['alterada'] = 1;        
                    }
                      
 
                 }else{
                   
                    $return[$i]['odds'][$j]['id'] = $odd->id;
                    $return[$i]['odds'][$j]['odd'] = $odd->odd;
                    $return[$i]['odds'][$j]['cotacao'] =  round($odd->cotacao, 2);
                    $return[$i]['odds'][$j]['status'] = $odd->status; 
                    $return[$i]['odds'][$j]['alterada'] = 0;   
                 }
                
               
               
                   
         
               
            
               
            $j++;    
            }
        $i++;
        }

        return $return;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $mercado = ConfigMercados::find($id);

        $mercado->update($request->all());

             LoadEventHoje::dispatchNow(); 
            // LoadEventAmanha::dispatchNow();
            // LoadEventAfer::dispatchNow();
            // LoadEventLive::dispatchNow();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
