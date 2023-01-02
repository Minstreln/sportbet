<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Models\Aposta;
use App\Models\Lancamento;
use Illuminate\Support\Facades\DB;

class FinanceiroController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $arr = array();

    public function __contruct() {
        
    }
    public function index()
    {
        //
    }

    public function indexViewAdmGerente()
    {
        return view('admin.caixa-adm-gerente');
    }

    public function indexViewAdmCambista() 
    {
        return view('admin.caixa-adm-cambista');
    }


    public function caixaGerente()
    {
        $users = User::where('nivel', 'gerente')
                    ->where('site_id', env('ID_SITE'))
                    ->orderBy('quantidade_aposta', 'DESC')
                    ->get();

         

            $i=0;
            foreach($users as $user) {

                $bets = Aposta::select('status', DB::raw('sum( valor_apostado ) as total_apostado'))
                                ->groupBy('status')
                                ->where('status', 'Aberto')
                                ->where('site_id', env('ID_SITE'))
                                ->where('gerente_id', $user->id)
                                ->get();

             
                $total = $user->entradas  - ($user->saidas + $user->comissoes);
                $this->arr[$i]['id'] = $user->id;
                $this->arr[$i]['colaborador'] = $user->name;
                $this->arr[$i]['quantidade'] = $user->quantidade_aposta;
                $this->arr[$i]['entradas'] = $user->entradas;



                    

                $this->arr[$i]['entradas_abertas'] = $bets->sum('total_apostado');

                $this->arr[$i]['saidas'] = $user->saidas;
                $this->arr[$i]['comissoes'] = $user->comissoes;
                $this->arr[$i]['lancamentos'] = $user->lancamentos;
                $this->arr[$i]['total'] =    $total;
                if($total > 0) {
                    $this->arr[$i]['comissao_gerente'] = $total*$user->comissao_gerente/100;
                }else {
                    $this->arr[$i]['comissao_gerente'] = 0; 
                }

               

            $i++;
            }

                 return $this->arr;
    }


    public function caixaCambista()
    {
         
        
        if(auth()->user()->nivel == 'adm') {
            $users = User::where('nivel', 'cambista')
                       ->where('site_id', env('ID_SITE'))
                       ->orderBy('quantidade_aposta', 'DESC')
                       ->get();
           
           $i=0;
           foreach($users as $user) {

            $bets = Aposta::select('status', DB::raw('sum( valor_apostado ) as total_apostado'))
                            ->groupBy('status')
                            ->where('status', 'Aberto')
                            ->where('site_id', env('ID_SITE'))
                            ->where('user_id', $user->id)
                            ->get();
           
                    $this->arr[$i]['id'] = $user->id;
                    $this->arr[$i]['colaborador'] = $user->name;
                    $this->arr[$i]['quantidade'] = $user->quantidade_aposta;
                    $this->arr[$i]['entradas'] = $user->entradas +$user->entrada_loto;
                    $this->arr[$i]['entradas_abertas'] =  $bets->sum('total_apostado');
                    $this->arr[$i]['saidas'] = $user->saidas;
                    $this->arr[$i]['comissoes'] = $user->comissoes;
                    $this->arr[$i]['lancamentos'] = $user->lancamentos;
                    $this->arr[$i]['total'] =  $user->entradas+ $user->entrada_loto +  $user->lancamentos - ($user->saidas + $user->comissoes);
                    
   

           $i++;
           }
        }

        if(auth()->user()->nivel == 'gerente') {
            $users = User::where('nivel', 'cambista')
                            ->where('site_id', env('ID_SITE'))
                            ->where('gerente_id',auth()->user()->id)
                            ->orderBy('quantidade_aposta', 'DESC')
                            ->get();
           
           $i=0;
           foreach($users as $user) {

            $bets = Aposta::select('status', DB::raw('sum( valor_apostado ) as total_apostado'))
                            ->groupBy('status')
                            ->where('status', 'Aberto')
                            ->where('site_id', env('ID_SITE'))
                            ->where('user_id', $user->id)
                            ->get();
           
                    $this->arr[$i]['id'] = $user->id;
                    $this->arr[$i]['colaborador'] = $user->name;
                    $this->arr[$i]['quantidade'] = $user->quantidade_aposta;
                    $this->arr[$i]['entradas'] = $user->entradas + $user->entrada_loto;
                    $this->arr[$i]['entradas_abertas'] =  $bets->sum('total_apostado');
                    $this->arr[$i]['saidas'] = $user->saidas;
                    $this->arr[$i]['comissoes'] = $user->comissoes;
                    $this->arr[$i]['lancamentos'] = $user->lancamentos;
                    $this->arr[$i]['total'] =  $user->entradas + $user->entrada_loto + $user->lancamentos - ($user->saidas + $user->comissoes);
                    
   

           $i++;
           }
        }
        

           return $this->arr;
    }


    public function viewCaixaGerente($id) 
    {
        $users = User::where('id', $id)
                        ->get();

                        $bets = Aposta::select('status', DB::raw('sum( valor_apostado ) as total_apostado'))
                                        ->groupBy('status')
                                        ->where('status', 'Aberto')
                                        ->where('site_id', env('ID_SITE'))
                                        ->where('gerente_id', $id)
                                        ->get();       
                                  

                                                          
                            $i=0;
                            foreach($users as $user) {
                            

                                        $total = $user->entradas + $user->entrada_loto  - ($user->saidas + $user->comissoes);
                                        $this->arr[$i]['id'] = $user->id;
                                        $this->arr[$i]['colaborador'] = $user->name;
                                        $this->arr[$i]['quantidade'] = $user->quantidade_aposta;
                                        $this->arr[$i]['entradas'] = $user->entradas +$user->entrada_loto;

                                        $this->arr[$i]['entradas_abertas'] =  $bets->sum('total_apostado');
                    
                                        $this->arr[$i]['saidas'] = $user->saidas;
                                        $this->arr[$i]['comissoes'] = $user->comissoes;
                                        $this->arr[$i]['lancamentos'] = $user->lancamentos;
                                        $this->arr[$i]['total'] =  $total;
                                        $this->arr[$i]['comissao_gerente'] = $total*($user->comissao_gerente/100);

                            $i++;
                            }

                            return $this->arr;
    }               

    public function caixaUser($id)
    {
         $users = User::where('gerente_id', $id)
                        ->where('site_id', env('ID_SITE'))
                        ->get();

                        $bets = Aposta::select('status', DB::raw('sum( valor_apostado ) as total_apostado'))
                                        ->groupBy('status')
                                        ->where('status', 'Aberto')
                                        ->where('site_id', env('ID_SITE'))
                                        ->where('gerente_id', $id)
                                        ->get();
                                        
           $i=0;
           foreach($users as $user) {
           

           
                    $this->arr[$i]['id'] = $user->id;
                    $this->arr[$i]['colaborador'] = $user->name;
                    $this->arr[$i]['quantidade'] = $user->quantidade_aposta;
                    $this->arr[$i]['entradas'] = $user->entradas + $user->entrada_loto;

                    $this->arr[$i]['entradas_abertas'] =  $bets->sum('total_apostado');
 
                    $this->arr[$i]['saidas'] = $user->saidas;
                    $this->arr[$i]['comissoes'] = $user->comissoes;
                    $this->arr[$i]['lancamentos'] = $user->lancamentos;
                    $this->arr[$i]['total'] =  $user->entradas + $user->entrada_loto + $user->lancamentos - ($user->saidas + $user->comissoes);
                    $this->arr[$i]['comissao_gerente'] = $user->entradas  + $user->entrada_loto + $user->lancamentos - ($user->saidas + $user->comissoes)*($user->comissao_gerente/100);

           $i++;
           }

           return $this->arr;
    }

    public function caixaUserCambista($id) 
    {
        $users = User::where('id', $id)
        ->where('site_id', env('ID_SITE'))
        ->get();

        $bets = Aposta::select('status', DB::raw('sum( valor_apostado ) as total_apostado'))
                        ->groupBy('status')
                        ->where('status', 'Aberto')
                        ->where('site_id', env('ID_SITE'))
                        ->where('user_id', $id)
                        ->get();
                        
$i=0;
foreach($users as $user) {



    $this->arr[$i]['id'] = $user->id;
    $this->arr[$i]['colaborador'] = $user->name;
    $this->arr[$i]['quantidade'] = $user->quantidade_aposta;
    $this->arr[$i]['entradas'] = $user->entradas + $user->entrada_loto;

    $this->arr[$i]['entradas_abertas'] =  $bets->sum('total_apostado');

    $this->arr[$i]['saidas'] = $user->saidas;
    $this->arr[$i]['comissoes'] = $user->comissoes;
    $this->arr[$i]['lancamentos'] = $user->lancamentos;
    $this->arr[$i]['total'] =  $user->entradas  + $user->entrada_loto +  $user->lancamentos - ($user->saidas + $user->comissoes);
    $this->arr[$i]['comissao_gerente'] = $user->entradas  + $user->entrada_loto + $user->lancamentos - ($user->saidas + $user->comissoes)*($user->comissao_gerente/100);

$i++;
}

return $this->arr;

    }

    public function encerraCaixa($id)
    {
       
        

        $user = User::find($id);

        $user->entradas = 0;
        $user->entradas_abertas = 0;
        $user->saidas = 0;
        $user->comissoes = 0;
        $user->lancamentos = 0;
        $user->quantidade_aposta = 0;
        $user->entrada_casadinha = 0;
        $user->entrada_simples = 0;
        $user->entrada_loto = 0;

        $user->save();

        $lancamentos =  Lancamento::where('user_id', $id)->delete();
        
    
        
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
