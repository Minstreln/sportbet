<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\InfoBanca;
use App\Models\Gerente;
use App\Models\Aposta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\User;

class HomeController extends Controller
{
    private $adm;
    private $data = 'adm';
    private $gerente;

    public function __construct( User $user)
    {

        $this->user = $user;
        $this->hoje     = $hoje  = Carbon::today();
        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

   

    public function index()
    {
        return view('admin.home');
    }

    public function registerpage()
    {
        return view('adminlte.register');
    }

    public function relatorioHome() 
    {    
            
        if(auth()->user()->nivel == 'adm') {
  
        $users = User::where('nivel', 'cambista')
                            //->where('adm_id', auth()->user()->id)
                            ->where('site_id', env('ID_SITE'))
                            ->get();  


                        $bets = Aposta::select('status', 'site_id', DB::raw('sum( valor_apostado ) as total_apostado'))
                                        ->groupBy('status')
                                        ->groupBy('site_id')
                                        ->where('status', 'Aberto')
                                        ->where('tipo', '!=' ,'Bolão')
                                        //->where('adm_id', auth()->user()->id)
                                        ->where('site_id', env('ID_SITE'))
                                        ->get();

                $this->arr['quantidade'] = $users->sum('quantidade_aposta');
                $this->arr['entradas'] = $users->sum('entradas') + $users->sum('entrada_loto');
                $this->arr['entradas_abertas'] = $bets->sum('total_apostado');
                $this->arr['saidas'] =  $users->sum('saidas');
                $this->arr['comissoes'] =  $users->sum('comissoes');
                $this->arr['lancamentos'] =  $users->sum('lancamentos');
                $this->arr['total'] =  $users->sum('entradas') +   $users->sum('entrada_loto') + $users->sum('lancamentos') - ($users->sum('saidas')+  $users->sum('comissoes'));
    
        
     }

     if(auth()->user()->nivel == 'gerente') {
        $gerente = User::find(auth()->user()->id);
        $comissao = $gerente->comissao_gerente/100;
        $users = User::where('gerente_id', auth()->user()->id)
                            ->get();  

                        $bets = Aposta::select('status', 'gerente_id', DB::raw('sum( valor_apostado ) as total_apostado'))
                                        ->groupBy('status')
                                        ->groupBy('gerente_id')
                                        ->where('status', 'Aberto')
                                        ->where('tipo', '!=' ,'Bolão')
                                        ->where('gerente_id', auth()->user()->id)
                                        ->get();

                $this->arr['quantidade'] = $users->sum('quantidade_aposta');
                $this->arr['entradas'] = $users->sum('entradas');
                $this->arr['entradas_abertas'] =  $bets->sum('total_apostado');
                $this->arr['saidas'] =  $users->sum('saidas');
                $total = $users->sum('entradas') - (($users->sum('saidas') + $users->sum('comissoes')));
                $this->arr['comissoes'] =  $total * $comissao;
                $this->arr['lancamentos'] =  $users->sum('lancamentos');
                $this->arr['total'] = $total;
    
        
     }
            return $this->arr;

    }

    public function editBanca()
    {
        return view('admin.edit-banca');
    }

  
  


    public function viewRegulamento() 
    {
        return view('admin.regulamento');
    }

    
    public function indexRegulamento() {

        return InfoBanca::select('id','regulamento')
                            ->where('site_id', env('ID_SITE'))
                            ->get();
    }
    

    public function regulamentoUpdate(Request $request, $id) 
    {
   
      $data = InfoBanca::find($id);

      $data->regulamento = $request->regulamento;
  
      $data->save();

       
       
    }

    public function userLogado() 
    {
        return User::find(auth()->user()->id);
    }
}
