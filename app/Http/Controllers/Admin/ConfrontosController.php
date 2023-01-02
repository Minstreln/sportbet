<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Match;
use App\Models\Mercado;
use App\Models\Odd;
use App\Models\BlockLeague;
use App\Models\BlockMatch;
use App\Models\BlockOddMatch;
use App\Models\MainLeague;
use App\Jobs\LoadMatchLive;
use App\Jobs\LoadEventHoje;
use App\Jobs\LoadEventAmanha;
use App\Jobs\LoadEventAfer;
use App\Jobs\LoadEventLive;

use Carbon\Carbon;

class ConfrontosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $hoje;
    private $amanha;
    private $agora;

    public function __construct()
    {

        $this->hoje     = $hoje     = Carbon::today();
        $this->amanha   = $amanha   = Carbon::tomorrow();
        $this->agora    = $agora    = Carbon::now();
    }
    public function indexView()
    {
         return view('admin.confrontos');

    }

    public function viewAovivo()
    {

        return view('admin.confrontos-aovivo');
    }

    public function indexAovivo()
    {



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

                    $return = array();

                    $leagues = Match::select('league')
                                    ->where( 'time_status', 1)
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
                            ->with('odds')
                            ->get();

                        $j=0;
                        foreach($matchs as $match) {
                            $return[$i]['match'][$j]['id'] = $match->id;
                            $return[$i]['match'][$j]['event_id'] = $match->event_id;
                            $return[$i]['match'][$j]['sport'] = $match->sport_name;
                            $return[$i]['match'][$j]['home'] = $match->home;
                            $return[$i]['match'][$j]['image_id_home'] = $match->image_id_home;
                            $return[$i]['match'][$j]['away'] = $match->away;
                            $return[$i]['match'][$j]['image_id_away'] = $match->image_id_away;
                            $return[$i]['match'][$j]['score'] = $match->score;
                            $return[$i]['match'][$j]['date'] = $match->date;
                            $return[$i]['match'][$j]['time'] = $match->time;
                            $return[$i]['match'][$j]['visible'] = $match->visible;

                        $j++;
                        }





                    $i++;
                    }

             return $return;
    }

    public function index()
    {
        $aferTomorow = $this->amanha->addDay()->format('Y-m-d');

        $arr = array();
        $bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();


        foreach($bloqueadas as $bloqueada) {

            $arr[] = $bloqueada->league;

        }

        $block_match = array();

        $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();



        foreach($matchs_bloqueadas as $match_bloqueada) {

            $block_match[] = $match_bloqueada->event_id;

        }




        $return = array();
        $data_hoje = date('Y-m-d', strtotime($this->hoje));
        $data_amanha= date('Y-m-d', strtotime($this->amanha));

        $leagues = Match::select('league')
                                ->where('date', '>=', $this->agora)
                                ->where('date' ,'<=',$aferTomorow.' 23:59:00')
                                ->where( 'visible', 'Sim')
                                ->whereNotIn('league', $arr)
                                ->groupBy('league')
                                ->orderBy('league', 'asc')
                                //->limit(30)
                                ->get();

                                $i=0;
                                foreach($leagues  as $league) {


                                            $return[$i]['league'] = $league->league;

                                            $matchs = Match::where('league', $league->league)
                                                            ->where('date', '>=', $this->agora)
                                                            ->where('date' ,'<=', $aferTomorow.' 23:59:00')
                                                            ->where( 'visible', 'Sim')
                                                            ->whereNotIn('event_id', $block_match)
                                                            ->get();

                                            $j=0;
                                            foreach($matchs as $match) {
                                                $return[$i]['match'][$j]['id'] = $match->id;
                                                $return[$i]['match'][$j]['sport'] = $match->sport_name;
                                                $return[$i]['match'][$j]['event_id'] = $match->event_id;
                                                $return[$i]['match'][$j]['home'] = $match->home;
                                                $return[$i]['match'][$j]['away'] = $match->away;
                                                $return[$i]['match'][$j]['date'] = $match->date;
                                                $return[$i]['match'][$j]['visible'] = $match->visible;
                                                //$return[$i]['match'][$j]['date'] = $match->date;




                                        $j++;
                                        }





                                    $i++;
                                }

                                return $return;
    }

    public function searchMatch(Request $request) {

      $data = date('Y-m-d', strtotime($request->date));



                        $block_league = array();
                        $leagues_bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();


                        foreach($leagues_bloqueadas as $league_bloqueada) {

                            $block_league[] = $league_bloqueada->league;

                        }

                        $block_match = array();

                        $matchs_bloqueadas = BlockMatch::where('site_id', env('ID_SITE'))->get();



                        foreach($matchs_bloqueadas as $match_bloqueada) {

                            $block_match[] = $match_bloqueada->event_id;

                        }

                        $return = array();
                        $data_hoje = date('Y-m-d', strtotime($this->hoje));
                        $data_amanha= date('Y-m-d', strtotime($this->amanha));

                        $leagues = Match::select('league')
                                                ->where('date', '>=', $this->agora)
                                                ->where('date', '>', $data.' 00:00:00')
                                                ->where('date', '<=', $data.' 23:59:59')
                                                ->where( 'visible', 'Sim')
                                                ->whereNotIn('league', $block_league)
                                                ->groupBy('league')
                                                ->orderBy('league', 'asc')
                                                //->limit(30)
                                                ->get();

                                                $i=0;
                                                foreach($leagues  as $league) {



                                                            $return[$i]['league'] = $league->league;

                                                            $matchs = Match::where('league', $league->league)
                                                                            ->where('date', '>=', $this->agora)
                                                                            //->where('date' ,'<=', $aferTomorow.' 23:59:00')
                                                                            ->whereNotIn('event_id', $block_match)
                                                                            ->where( 'visible', 'Sim')
                                                                            ->get();

                                                            $j=0;
                                                            foreach($matchs as $match) {
                                                                $return[$i]['match'][$j]['id'] = $match->id;
                                                                $return[$i]['match'][$j]['sport'] = $match->sport_name;
                                                                $return[$i]['match'][$j]['event_id'] = $match->event_id;
                                                                $return[$i]['match'][$j]['home'] = $match->home;
                                                                $return[$i]['match'][$j]['away'] = $match->away;
                                                                $return[$i]['match'][$j]['date'] = $match->date;
                                                                $return[$i]['match'][$j]['visible'] = $match->visible;
                                                                //$return[$i]['match'][$j]['date'] = $match->date;

                                                        $j++;
                                                        }



                                                    $i++;
                                                }

                                                return $return;

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
    //Mostra odds do confronto
    public function show($id)
    {

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
        $match = Match::find($id);
        $update = $match->update($request->all());
    }


    //Criação de odd bloqueada
    //Bloqueio de odd
    //Desbloqueio de odd
    //update odd ja cadastrada

    public function updateOdd(Request $request, $id)
    {


        if($request->tipo == 0) {

            //Verifica se já existe
            $odd =  BlockOddMatch::where('odd_id', $id)->where('odd', $request->odd)->get();

            if(count($odd)>0) {

                $odd_alt =  BlockOddMatch::where('odd_id', $id)->where('odd', $request->odd)->get();

                foreach($odd_alt as $od) {
                    $od->cotacao = $request->cotacao;
                    $od->odd     = $request->odd;
                    $od->save();
                }


            }else{

                $odd = BlockOddMatch::create([
                    'odd_id'    => $id,
                    'cotacao'   => $request->cotacao,
                    'odd'       => $request->odd,
                    'odd_uid'   => $request->odd_uid,
                    'status'    =>  1,
                    'site_id'   => env('ID_SITE'),
                ]);
            }


        }

        if($request->tipo == 1) {

             //Verifica se já existe
             $odd =  BlockOddMatch::where('odd_id', $id)->where('odd', $request->odd)->get();

             if(count($odd)>0) {
                $odd_alt =  BlockOddMatch::where('odd_id', $id)->where('odd', $request->odd)->get();

                foreach($odd_alt as $od) {
                 $od->status = 0;
                 $od->save();
                }

             }else{

                 $odd = BlockOddMatch::create([
                     'odd_id'    => $id,
                     'cotacao'   => $request->cotacao,
                     'odd'       => $request->odd,
                     'odd_uid'   => $request->odd_uid,
                     'status'    =>  0,
                     'site_id'   => env('ID_SITE'),
                 ]);
             }
        }

        if($request->tipo == 3) {

            //Verifica se já existe
            $odd =  BlockOddMatch::where('odd_id', $id)->where('odd', $request->odd)->get();

            if(count($odd)>0) {
               $odd_alt =  BlockOddMatch::where('odd_id', $id)->where('odd', $request->odd)->get();

               foreach($odd_alt as $od) {
                $od->status = 1;
                $od->save();
                }
            }


       }

       $match = Match::where('event_id', $id)->first();
    //    LoadMatchLive::dispatchNow($match, $match->id);

    LoadEventHoje::dispatchNow();
    //    LoadEventAmanha::dispatchNow();
    //    LoadEventAfer::dispatchNow();
    //    LoadEventLive::dispatchNow();




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

    public function deleteLeague($id) {

        $liga = BlockLeague::find($id);

        $liga->delete();
        // LoadEventHoje::dispatchNow();
        // LoadEventAmanha::dispatchNow();
        // LoadEventAfer::dispatchNow();
        // LoadEventLive::dispatchNow();

    }

    public function deleteMatch($id)
    {

        $match = BlockMatch::find($id);

        $match->delete();
        // LoadEventHoje::dispatchNow();
        // LoadEventAmanha::dispatchNow();
        // LoadEventAfer::dispatchNow();
        // LoadEventLive::dispatchNow();
    }


    public function indexViewLigas()
    {


    }

    public function indexLigasBlock()

    {

        return $bloqueadas = BlockLeague::where('site_id', env('ID_SITE'))->get();


        //$match = Match::where('league', $campeonato);

        //return $match;

    }

    public function indexMatchsBlock()
    {
       return  BlockMatch::where('site_id', env('ID_SITE'))
                            ->where('date','>=', $this->agora)
                            ->get();
    }

    public function blockLeague(Request $request)
    {
            BlockLeague::create([
                'league'    => $request->league,
                'site_id'   => env('ID_SITE'),
                ]);

    }

    public function blockMatch(Request $request)
    {
        BlockMatch::create([
            'event_id' => $request->event_id,
            'date' => $request->date ,
            'sport' => $request->sport,
            'confronto' => $request->confronto,
            'site_id'   => env('ID_SITE'),
            ]);

            // LoadEventHoje::dispatchNow();
            // LoadEventAmanha::dispatchNow();
            // LoadEventAfer::dispatchNow();
            // LoadEventLive::dispatchNow();
    }

    public function insertLeagueMain(Request $request)
    {

            if(count(MainLeague::where('sport', $request->sport)->where('league', $request->league_id)->get()) > 0) {


            }

            MainLeague::create([

                    'sport'     => $request->sport,
                    'league'    => $request->league,
                    'league_id' => $request->league_id,
                    'site_id' => env('ID_SITE')
            ]);

            // LoadEventHoje::dispatchNow();
            // LoadEventAmanha::dispatchNow();
            // LoadEventAfer::dispatchNow();
            // LoadEventLive::dispatchNow();
    }

    public function listLeagueMain()
    {
        return MainLeague::orderBy('league', 'ASC')->where('site_id', env('ID_SITE'))->get();
    }


}

