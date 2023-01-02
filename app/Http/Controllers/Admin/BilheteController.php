<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Aposta;
use App\Models\Palpite;
use App\Models\Configuracao;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Facades\DB;


class BilheteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexView()
    {
        return view('admin.bilhetes');
    }

    public function index() 
    {
        if(auth()->user()->nivel == 'adm')

            return Aposta::orderBy('id', 'desc')
                            ->where('site_id', env('ID_SITE'))
                            //->where('adm_id', auth()->user()->id)
                            ->limit(60)
                            ->where('tipo', '!=' ,'Bolão')
                            ->get();
        else 

            return Aposta::orderBy('id', 'desc')
                            ->where('site_id', env('ID_SITE'))
                            ->where('gerente_id', auth()->user()->id)
                            ->where('tipo', '!=' ,'Bolão')
                            ->limit(60)
                            ->get();

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

    public function search(Request $request)
    {
  
        if(auth()->user()->nivel == 'adm') {
            $consulta = Aposta::where('created_at' ,'>=', $request->date1.' 00:00:00')->where('tipo', '!=' ,'Bolão')->where('created_at', '<=', $request->date2.' 23:59:59')->orderBy('id', 'desc')->where('site_id', env('ID_SITE'));
            if( $request->cambista != 'Todos'){

                $consulta = $consulta->where('user_id', $request->cambista);
            }
            if($request->status != 'Todos'){
    
                $consulta = $consulta->where('status', $request->status);
            }
            if($request->tipo != 'Todos'){
    
                $consulta = $consulta->where('tipo', $request->tipo);
            }

            return $consulta->get();
            

        }else {
            
            $consulta = Aposta::where('created_at' ,'>=', $request->date1.' 00:00:00')->where('tipo', '!=' ,'Bolão')->where('created_at', '<=', $request->date2.' 23:59:59')->orderBy('id', 'desc')->where('site_id', env('ID_SITE'))->where('gerente_id', auth()->user()->id);
            
            if( $request->cambista != 'Todos'){

                $consulta = $consulta->where('user_id', $request->cambista);
            }
            if($request->status != 'Todos'){
    
                $consulta = $consulta->where('status', $request->status);
            }
            if($request->tipo != 'Todos'){
    
                $consulta = $consulta->where('tipo', $request->tipo);
            }

            return $consulta->get();

        }

       

       

       

        


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
            $nowDate = Carbon::now();
            $conf = Configuracao::where('site_id', env('ID_SITE'))->first();
            

            DB::beginTransaction();

            //Altera Bilhete
            $bilhete = Aposta::find($id);
            
            if(!$bilhete) // nao encontrou o bilhete
            {
                DB::rollBack();
                return response()->json(['message' => 'error'], 404);
            }

            // Gerente pode cancelar?
            if(auth()->user()->nivel == 'gerente')
            {
                $cancelamento = isset($conf->gerente_pode_cancelar) ? $conf->gerente_pode_cancelar : "Não";
                
                if ($cancelamento == "Não") {
                    DB::rollBack();
                    return response()->json(['message' => 'error'], 404);
                }

                // Cancela até x minutos permitidos depois que foi gerado o bilhete
                $data_agora     = Carbon::createFromFormat('Y-m-d H:i:s', $nowDate);
                $data_bilhete   = Carbon::createFromFormat('Y-m-d H:i:s', $bilhete->created_at);
                $diff           = $data_agora->diffInMinutes($data_bilhete);
                $tempo_limite   = $conf->tempo_limite_camb_cancela_aposta; // utiliza o mesmo tempo que cambista

                if ($diff > $tempo_limite) {
                    DB::rollBack();
                    return response()->json(['message' => 'error'], 404);
                }
            }
            // A partir daqui, rotina para cancelamento

            if($bilhete->modalidade == 'Loto') {

                $bilhete->status = 'Cancelado';
                $bilhete->save();
                //Caixa Cambista
                $user = User::find($request->user_id);
                $user->quantidade_aposta = $user->quantidade_aposta - 1;	
                $user->entrada_loto = $user->entrada_loto - $request->valor_apostado;
    
                //Caixa Gerente
                $gerente = User::find($bilhete->gerente_id);
                $gerente->quantidade_aposta = $gerente->quantidade_aposta - 1;	
                $gerente->entradas = $gerente->entradas - $request->valor_apostado;

                $user->comissoes = $user->comissoes - $bilhete->comicao;
                $gerente->comissoes = $gerente->comissoes - $bilhete->comicao;  
                $user->save();
                $gerente->save();
    
                if( $bilhete && $user  && $gerente) {
                    //Sucesso!
                    DB::commit();
                } else {
                    //Fail, desfaz as alterações no banco de dados
                    DB::rollBack();
                }

            } else {
                $bilhete->status = 'Cancelado';
                $bilhete->save();
    
                $palpites = Palpite::where('aposta_id', $id)->get();
    
                foreach($palpites as $palpite) {
                    $palp = Palpite::find($palpite->id);
                    $palp->status = 'Cancelado';
                    $palp->save();
                }
    
                //Caixa Cambista
                $user = User::find($request->user_id);
                $user->quantidade_aposta = $user->quantidade_aposta - 1;	
                $user->entradas = $user->entradas - $request->valor_apostado;
    
                //Caixa Gerente
                $gerente = User::find($bilhete->gerente_id);
                $gerente->quantidade_aposta = $gerente->quantidade_aposta - 1;	
                $gerente->entradas = $gerente->entradas - $request->valor_apostado;
    
                //Casadinhas
                if($request->total_palpites > 1) {
                    $user->entrada_casadinha = $user->entrada_casadinha - $request->valor_apostado;
                    //$user->saldo_casadinha = $user->saldo_casadinha -  $request->valor_apostado;
                }
                //Simples
                if($request->total_palpites == 1) {
                    $user->entrada_simples = $user->entrada_simples - $request->valor_apostado;
                    $gerente->entrada_simples = $gerente->entrada_simples - $request->valor_apostado;
                    //$user->saldo_simples = $user->saldo_simples -  $request->valor_apostado;
                }
                //Saídas
                if($request->bilhete_status == "Ganhou") {
                    $user->saidas = $user->saidas - $request->retorno_possivel;
                    $gerente->saidas = $gerente->saidas - $request->retorno_possivel;
                }
                //Comissão
                $user->comissoes = $user->comissoes - $bilhete->comicao;
                $gerente->comissoes = $gerente->comissoes - $bilhete->comicao;  
                $user->save();
                $gerente->save();
    
                if( $bilhete && $user  && $gerente) {
                    //Sucesso!
                    DB::commit();
                } else {
                    //Fail, desfaz as alterações no banco de dados
                    DB::rollBack();
                }
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
}
