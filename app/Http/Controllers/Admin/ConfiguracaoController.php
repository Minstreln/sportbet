<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use App\Models\ListleaguesMain;
use App\Models\MainLeague;
use App\User;
use App\Events\LoadConfiguration;
use App\Jobs\LoadEventHoje;
use App\Jobs\LoadEventAmanha;
use App\Jobs\LoadEventAfer;


class ConfiguracaoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexView()
    {
       return  view('admin.configuracao');
    }

    public function index()
    {
        return Configuracao::where('site_id', env('ID_SITE'))->get();
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

        $configuracao =  Configuracao::find($id);

        $update = $configuracao->update($request->data[0]);

        $con = Configuracao::find($id);


        if($update) {
            broadcast(new LoadConfiguration($con));
            //  LoadEventHoje::dispatchNow(); 
            //  LoadEventAmanha::dispatchNow();
            //  LoadEventAfer::dispatchNow();
            $update;
        } else {
            $update;
        }

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

    public function gerenciarCotacoes() 
    {
        return view('admin.mercados-users');
    }


    public function gerenciarLigas()
    {
        return view('admin.bloqueio-ligas');
    }

    public function  gerenciarMatchs()
    {
        return view('admin.bloqueio-matchs');
    }


    public function bloquearUser(Request $request)
    {
        $user = User::find($request->id);
        $user->situacao = $request->situacao;
        $user->save();

        $cambistas = User::where('gerente_id', $user->id)->get();
        foreach($cambistas as $cambista) {
            $camb = User::find($cambista->id);
            $camb->situacao = $request->situacao;
            $camb->save();
        }
    }

    public function showLigas()
    {
        $mainleagues = MainLeague::where('site_id', env('ID_SITE'))->get();

        $liga = array();
        foreach($mainleagues as $league) {
            $liga[] =$league->league;
        }

        return ListleaguesMain::orderBy('league', 'ASC')
                                ->whereNotIn('league', $liga)
                                ->get();
    }

    public function deleteLeague($id) {

        $liga = MainLeague::find($id);

        $liga->delete();

    }

}
