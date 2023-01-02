<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ConfigOdd;
use App\Models\ConfigMercados;
use App\Jobs\LoadEventHoje;
use App\Jobs\LoadEventAmanha;
use App\Jobs\LoadEventAfer;
use App\Jobs\LoadEventLive;

class OddsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $arr ;

    public function __construct() {
        $this->arr = $arr = array();
    }
    public function indexView()
    {
        return view('admin.bloqueio-odd');
    }

    public function index(Request $request)
    {
            return ConfigOdd::where('mercado_name', $request->mercado_name)
                                ->where('site_id', env('ID_SITE'))
                                ->where('user_id', auth()->user()->id)
                                ->orderBy('header', 'asc')
                                ->get();

    }

    public function indexViewCambista()
    {
        return view('admin.bloqueio-odd-cambista');
    }

    public function oddsUser($id) 
    {
         $mercados = ConfigMercados::select('name')
                                ->where('site_id', env('ID_SITE'))
                                ->where('user_id', $id)
                                ->get();
                    $i=0;
                    foreach($mercados as $mercado) {
                          $odds = ConfigOdd::where('mercado_name', $mercado->name)
                                            ->where('user_id', $id) 
                                            ->where('site_id', env('ID_SITE'))
                                            ->get();
                                            
                          $this->arr[$i]['mercado'] = $mercado->name;
                          $j=0;
                          foreach($odds as $odd) {
                                $this->arr[$i]['odds'][$j]['id'] = $odd->id;
                                $this->arr[$i]['odds'][$j]['name'] = $odd->name;
                                $this->arr[$i]['odds'][$j]['porcentagem'] = $odd->porcentagem;
                           $j++;
                          }
                    $i++;
                    }

                    return $this->arr;


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
        $mercado = ConfigOdd::find($id);

        $mercado->update($request->all());

            LoadEventHoje::dispatchNow(); 
            //LoadEventAmanha::dispatchNow();
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
