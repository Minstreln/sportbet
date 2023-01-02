<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Models\Aposta;
use Illuminate\Support\Facades\DB;
class RelatorioController extends Controller
{
    
    public function relatorioGerente(Request $request) 
    {     
        $arr = array();
        $i=0;

        if( $request->gerente != 'Todos'){

            $gerente = User::find($request->gerente);
              
          
            
                $bets = Aposta::select('gerente_id',  
                                DB::raw('sum(valor_apostado) as total_apostado'),
                                DB::raw('sum(comicao) as comissoes' ),
                                DB::raw('count(id) as quantidade' ) )
                                ->groupBy('gerente_id')
                                ->where('created_at' ,'>=', $request->date1.' 00:00:00')
                                ->where('created_at', '<=', $request->date2.' 23:59:59')
                                ->where('gerente_id', $gerente->id)
                                ->where('status', '!=', 'Cancelado')
                                ->where('status', '!=', 'Devolvido')
                                ->where('site_id', env('ID_SITE'))->get();

                                //Pegando as premiadas
                                $bets_ganhas = Aposta::select('gerente_id',  
                                                DB::raw('sum(retorno_possivel) as ganho' ))
                                                ->groupBy('gerente_id')
                                                ->where('created_at' ,'>=', $request->date1.' 00:00:00')
                                                ->where('created_at', '<=', $request->date2.' 23:59:59')
                                                ->where('gerente_id', $gerente->id)
                                                ->where('site_id', env('ID_SITE'))
                                                ->where('status', 'Ganhou')->get();
                                                
                                foreach($bets as $bet) {
                                        $total = $bet->total_apostado-($bet->comissoes + $bets_ganhas->sum('ganho'));
                                        $comissao_gerente = 0;
                                        if($total > 0) {
                                            $comissao_gerente = ($total*$gerente->comissao_gerente)/100;
                                        }
                                        $arr[$i]['saidas'] = $bets_ganhas->sum('ganho');
                                        $arr[$i]['name'] = $gerente->name;
                                        $arr[$i]['comissao_gerente'] = $comissao_gerente;
                                        $arr[$i]['entradas'] = $bet->total_apostado; 

                                        $arr[$i]['quantidade'] = $bet->quantidade;
                                        $arr[$i]['comissaocambista'] = $bet->comissoes;
                                        $arr[$i]['saldo'] = $total_geral = $total - $comissao_gerente;
                                        $i++;  
                                }
                              
    
            
        }else {

                $gerentes = User::where('site_id', env('ID_SITE'))->where('nivel', 'gerente')->get();
              
                foreach($gerentes as $gerente) {    
                
                    $bets = Aposta::select('gerente_id',  
                                    DB::raw('sum(valor_apostado) as total_apostado'),
                                    DB::raw('sum(comicao) as comissoes' ),
                                    DB::raw('count(id) as quantidade' ) )
                                    ->groupBy('gerente_id')
                                    ->where('created_at' ,'>=', $request->date1.' 00:00:00')
                                    ->where('created_at', '<=', $request->date2.' 23:59:59')
                                    ->where('gerente_id', $gerente->id)
                                    ->where('status', '!=', 'Cancelado')
                                     ->where('status', '!=', 'Devolvido')
                                    ->where('site_id', env('ID_SITE'))->get();

                                    //Pegando as premiadas
                                    $bets_ganhas = Aposta::select('gerente_id',  
                                                    DB::raw('sum(retorno_possivel) as ganho' ))
                                                    ->groupBy('gerente_id')
                                                    ->where('created_at' ,'>=', $request->date1.' 00:00:00')
                                                    ->where('created_at', '<=', $request->date2.' 23:59:59')
                                                    ->where('gerente_id', $gerente->id)
                                                    ->where('site_id', env('ID_SITE'))
                                                    ->where('status', 'Ganhou')->get();
                                                    
                                    foreach($bets as $bet) {
                                            $total = $bet->total_apostado-($bet->comissoes + $bets_ganhas->sum('ganho'));
                                            $comissao_gerente = 0;
                                            if($total > 0) {
                                                $comissao_gerente = ($total*$gerente->comissao_gerente)/100;
                                            }
                                            $arr[$i]['saidas'] = $bets_ganhas->sum('ganho');
                                            $arr[$i]['name'] = $gerente->name;
                                            $arr[$i]['comissao_gerente'] = $comissao_gerente;
                                            $arr[$i]['entradas'] = $bet->total_apostado; 

                                            $arr[$i]['quantidade'] = $bet->quantidade;
                                            $arr[$i]['comissaocambista'] = $bet->comissoes;
                                            $arr[$i]['saldo'] = $total_geral = $total - $comissao_gerente; 
                                            $i++;     
                                    }

                                    
                                    
        }
        

                               


                                

                           
        }

        return $arr;

        
      
  
      

      
    }


    public function relatorioCambista(Request $request) 
    {     
        $arr = array();
        $i=0;

        if( $request->cambista != 'Todos'){

            $cambista = User::find($request->cambista);
              
          
            
                $bets = Aposta::select('user_id',  
                                DB::raw('sum(valor_apostado) as total_apostado'),
                                DB::raw('sum(comicao) as comissoes' ),
                                DB::raw('count(id) as quantidade' ) )
                                ->groupBy('user_id')
                                ->where('created_at' ,'>=', $request->date1.' 00:00:00')
                                ->where('created_at', '<=', $request->date2.' 23:59:59')
                                ->where('user_id', $cambista->id)
                                ->where('status', '!=', 'Cancelado')
                                 ->where('status', '!=', 'Devolvido')
                                ->where('site_id', env('ID_SITE'))->get();

                                //Pegando as premiadas
                                $bets_ganhas = Aposta::select('user_id',  
                                                DB::raw('sum(retorno_possivel) as ganho' ))
                                                ->groupBy('user_id')
                                                ->where('created_at' ,'>=', $request->date1.' 00:00:00')
                                                ->where('created_at', '<=', $request->date2.' 23:59:59')
                                                ->where('user_id', $cambista->id)
                                                ->where('site_id', env('ID_SITE'))
                                                ->where('status', 'Ganhou')->get();
                                                
                                foreach($bets as $bet) {
                                        $total = $bet->total_apostado-($bet->comissoes+$bets_ganhas->sum('ganho'));
                                        $arr[$i]['saidas'] = $bets_ganhas->sum('ganho');
                                        $arr[$i]['name'] = $cambista->name;
                                        $arr[$i]['entradas'] = $bet->total_apostado; 
                                        $arr[$i]['quantidade'] = $bet->quantidade;
                                        $arr[$i]['comissaocambista'] = $bet->comissoes;
                                        $arr[$i]['saldo'] = $total;

                                        $i++; 
                                         
                                }
                              
    
            
        }else {

                $cambistas = User::where('site_id', env('ID_SITE'))
                                        ->where('nivel', 'cambista')
                                        ->where('gerente_id', auth()->user()->id)
                                        ->get();
                if(auth()->user()->nivel == 'adm') {
                    $cambistas = User::where('site_id', env('ID_SITE'))
                                            ->where('nivel', 'cambista')
                                        // ->where('adm_id', auth()->user()->id)
                                            ->get();
                }
               
                
              
                foreach($cambistas as $cambista) {    
                
                    $bets = Aposta::select('user_id',  
                                    DB::raw('sum(valor_apostado) as total_apostado'),
                                    DB::raw('sum(comicao) as comissoes' ),
                                    DB::raw('count(id) as quantidade' ) )
                                    ->groupBy('user_id')
                                    ->where('created_at' ,'>=', $request->date1.' 00:00:00')
                                    ->where('created_at', '<=', $request->date2.' 23:59:59')
                                    ->where('user_id', $cambista->id)
                                    ->where('status', '!=', 'Cancelado')
                                     ->where('status', '!=', 'Devolvido')
                                    ->where('site_id', env('ID_SITE'))->get();

                                    //Pegando as premiadas
                                    $bets_ganhas = Aposta::select('user_id',  
                                                    DB::raw('sum(retorno_possivel) as ganho' ))
                                                    ->groupBy('user_id')
                                                    ->where('created_at' ,'>=', $request->date1.' 00:00:00')
                                                    ->where('created_at', '<=', $request->date2.' 23:59:59')
                                                    ->where('user_id', $cambista->id)
                                                    ->where('site_id', env('ID_SITE'))
                                                    ->where('status', 'Ganhou')->get();
                                                    
                                    foreach($bets as $bet) {
                                            $total = $bet->total_apostado-($bet->comissoes + $bets_ganhas->sum('ganho'));                                           
                                            $arr[$i]['saidas'] = $bets_ganhas->sum('ganho');
                                            $arr[$i]['name'] = $cambista->name;
                                            $arr[$i]['entradas'] = $bet->total_apostado; 
                                            $arr[$i]['quantidade'] = $bet->quantidade;
                                            $arr[$i]['comissaocambista'] = $bet->comissoes;
                                            $arr[$i]['saldo'] =  $total;  
                                            
                                    }

                                    $i++; 
                                   
        }
        

                               


                                

                           
        }

        return $arr;

        
      
  
      

      
    }
}
