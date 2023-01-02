<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Match;
use App\Models\Mercado;
use App\Models\Odd;
use App\Models\MapaBet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MapaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * 
     */

    private $agora;
    private $return = array();
    public function __construct()
    {
       $this->agora    = $agora    = Carbon::now();
    }


    public function index()
    {
        return view('admin.mapa-apostas');
    }

    public function mapAposta() 
    {       
          
          $matchs = MapaBet::select('confronto', 'date_event','sport', 
                                    DB::raw('sum( apostado ) as total_apostado'),
                                    DB::raw('count( confronto ) as quantidade')
                                    )
                                    ->where('date_event' ,'>=', $this->agora->subHour(3)->format('Y-m-d H:i:s'))
                                    //->where('date_event' ,'<=', $this->agora->addHour(2)->format('Y-m-d H:i:s'))
                                    ->where('site_id', env('ID_SITE'))
                                    ->groupBy('confronto')
                                    ->groupBy('date_event')
                                    ->groupBy('sport')
                                    ->orderBy('total_apostado', 'desc')
                                    ->get();
                 
                $i=0;
                foreach($matchs as $match) {
                    
                        $count = 1;
                        $this->return[$i]['confronto'] = $match->confronto;
                        $this->return[$i]['date'] = $match->date_event;
                        $this->return[$i]['total_apostado'] = $match->total_apostado;
                        $this->return[$i]['sport'] = $match->sport;
                        $this->return[$i]['quantidade'] = $match->quantidade;
                    
                        $opps = MapaBet::select('opcao', 'group_opp', 'sport', 
                                                    DB::raw('sum( apostado ) as total_apostado'),
                                                    DB::raw('count( opcao ) as quantidade')
                                                )
                                                ->where('confronto', $match->confronto)
                                                ->groupBy('opcao')
                                                ->groupBy('group_opp')
                                                ->groupBy('sport')
                                                ->orderBy('total_apostado', 'desc')
                                                ->where('site_id', env('ID_SITE'))
                                                ->get();

                            $q=0;
                            $opp_count = 0;
                            foreach($opps as $opp) {
                                    //$this->return[$i]['quantidade'] = $count++;
                                    $this->return[$i]['opps'][$q]['group_opp'] = $opp->group_opp;
                                    $this->return[$i]['opps'][$q]['opcao'] = $opp->opcao;
                                    $this->return[$i]['opps'][$q]['total_apostado'] = $opp->total_apostado;
                                    $this->return[$i]['opps'][$q]['quantidade'] = $opp->quantidade;

                             

                            $q++;
                            }
                $i++;
                }

                return $this->return;

             






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
        //
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
        //
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
