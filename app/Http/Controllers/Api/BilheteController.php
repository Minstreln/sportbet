<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Aposta;
use App\Models\Palpite;
use App\Models\PalpiteLoto;
use App\Models\PalpiteBolao;
use App\Models\Match;
use App\Models\Odd;
use App\Models\MapaBet;
use App\Models\BlockOddMatch;
use App\Models\ConfigOdd;
use App\Models\Rodada;
use Carbon\Carbon;
use App\User;
use DB;
use App\Models\Configuracao;
use App\Jobs\sendAlertaBet;



class BilheteController extends Controller
{

    private $arr = array();
    private $agora;
    private $hoje;


    public function __construct()
    {
        $this->agora    = $agora = Carbon::now()->format('Y-m-d H:i:s');
        $this->hoje     = $hoje     = Carbon::today();
    }

    public function dadosLogado()
    {

        $user = User::find(auth()->user()->id);


        $this->arr['saldo_casadinha'] = $user->saldo_casadinha - $user->entrada_casadinha;
        $this->arr['saldo_simples']   = $user->saldo_simples - $user->entrada_simples;
        $this->arr['saldo_loto']      = $user->saldo_loto - $user->entrada_loto;
        $this->arr['saldo_bolao']     = $user->saldo_bolao - $user->entradas;
        $this->arr['quantidade'] = $user->quantidade_aposta;
        $this->arr['entradas'] = $user->entradas +  $user->entrada_loto;

        //Verifica quantas apostas estão em abertas desse cambista
        $bets = Aposta::select('status', 'user_id', DB::raw('sum( valor_apostado ) as total_apostado'))
            ->groupBy('status')
            ->groupBy('user_id')
            ->where('status', 'Aberto')
            ->where('user_id', auth()->user()->id)
            ->get();

        $this->arr['entradas_abertas'] =  $bets->sum('total_apostado');
        $this->arr['saidas'] = $user->saidas;
        $this->arr['comissoes'] = $user->comissoes;
        $this->arr['lancamentos'] = $user->lancamentos;
        $this->arr['total'] = $user->entradas  + $user->entrada_loto +  $user->lancamentos - ($user->saidas + $user->comissoes);


        return $this->arr;
    }

    public function relatorio(Request $request)
    {
        $arr = array();
        $bets = Aposta::select(
            'user_id',
            DB::raw('sum(valor_apostado) as total_apostado'),
            DB::raw('sum(comicao) as comissoes'),
            DB::raw('count(id) as quantidade')
        )
            ->groupBy('user_id')
            ->where('created_at', '>=', $request->date1 . ' 00:00:00')
            ->where('tipo', '!=', 'Bolão')
            ->where('created_at', '<=', $request->date2 . ' 23:59:59')
            ->where('user_id', auth()->user()->id)
            ->where('status', '!=', 'Cancelado')->get();


        //Pegando as premiadas
        $bets_ganhas = Aposta::select(
            'user_id',
            DB::raw('sum(retorno_possivel) as ganho')
        )
            ->groupBy('user_id')
            ->where('created_at', '>=', $request->date1 . ' 00:00:00')
            ->where('tipo', '!=', 'Bolão')
            ->where('created_at', '<=', $request->date2 . ' 23:59:59')
            ->where('user_id', auth()->user()->id)
            ->where('status', 'Ganhou')->get();
        $i = 0;
        foreach ($bets as $bet) {
            $total = $bet->total_apostado - ($bet->comissoes + $bets_ganhas->sum('ganho'));
            $arr[$i]['saidas'] = $bets_ganhas->sum('ganho');
            $arr[$i]['entradas'] = $bet->total_apostado;
            $arr[$i]['quantidade'] = $bet->quantidade;
            $arr[$i]['comissaocambista'] = $bet->comissoes;
            $arr[$i]['saldo'] =  $total;
            $i++;
        }

        return $arr;
    }

    public function printBilheteId($id)
    {


        $b = Aposta::find($id);

        if ($b->modalidade == 'Loto') {
            return $bilhete = Aposta::where('id', $id)
                ->where('site_id', env('ID_SITE'))
                ->where('tipo', '!=', 'Bolão')
                ->with('palpitesLoto')
                ->get();
        } else {
            return  $bilhete = Aposta::where('id', $id)
                ->where('site_id', env('ID_SITE'))
                ->where('tipo', '!=', 'Bolão')
                ->with('palpites')
                ->get();
        }
    }

    public function printBilheteCod(Request $request)
    {

        $b = Aposta::where('cupom', $request->cupom)
            ->where('site_id', env('ID_SITE'))
            //->with('palpites')
            ->where('tipo', '!=', 'Bolão')
            ->first();

        if ($b) {
            if ($b->modalidade == 'Loto') {
                return Aposta::where('cupom', $request->cupom)
                    ->where('site_id', env('ID_SITE'))
                    ->with('palpitesLoto')
                    ->where('tipo', '!=', 'Bolão')
                    ->get();
            } else if ($b->tipo == 'Bolão') {
                return Aposta::where('cupom', $request->cupom)->where('tipo', '!=', 'Bolão')->get();
            } else {
                return Aposta::where('cupom', $request->cupom)
                    ->where('site_id', env('ID_SITE'))
                    ->where('tipo', '!=', 'Bolão')
                    ->with('palpites')
                    ->get();
            }
        } else {
            return response()->json(['message' => 'error'], 404);
        }
    }

    public function printBilheteGetCod(Request $request)
    {
        $cod = Aposta::where('cupom', $request->cupom)
            ->where('tipo', '!=', 'Bolão')
            ->first();
        if ($cod->modalidade == 'Loto') {
            return Aposta::where('cupom', $request->cupom)
                ->where('tipo', '!=', 'Bolão')
                ->with('palpitesLoto')
                ->get();
        } else if ($cod->tipo == 'Bolão') {
            return Aposta::where('cupom', $request->cupom)->where('tipo', '!=', 'Bolão')->first();
        } else {

            $cupom = Aposta::where('cupom', $request->cupom)
                ->where('tipo', '!=', 'Bolão')
                ->get();


            if (count($cupom) < 1) {
                return response()->json(['message' => 'error'], 404);
            }


            $cont = 0;
            $i = 0;
            foreach ($cupom as $bilhete) {

                $this->arr[$i]['id']        = $bilhete->id;
                $this->arr[$i]['user_id']   = $bilhete->user_id;
                $this->arr[$i]['cupom']     = $bilhete->cupom;
                $dt = Carbon::now();
                //$dt->timestamp($dt);
                $date = $dt->format('Y-m-d H:i:s');
                $this->arr[$i]['created_at']      = $date;
                $this->arr[$i]['status']    = $bilhete->status;
                $this->arr[$i]['valor_apostado'] = $bilhete->valor_apostado;
                $this->arr[$i]['retorno_possivel'] = $bilhete->retorno_possivel;
                $this->arr[$i]['vendedor']     = auth()->user()->name;
                $this->arr[$i]['cliente'] = $bilhete->cliente;
                $this->arr[$i]['tipo'] = $bilhete->tipo;
                $this->arr[$i]['comicao'] = $bilhete->comicao;
                $this->arr[$i]['total_palpites'] = $bilhete->total_palpites;

                $j = 0;

                $palpites = Palpite::where('aposta_id', $bilhete->id)
                    ->where('match_temp', '>', $date)
                    ->get();

                foreach ($palpites as $palpite) {

                    // $m_porcents_user = ConfigMercados::where('site_id', env('ID_SITE'))
                    //                                     ->where('name', $palpite->group_opp)
                    //                                     ->where('status', 1)  
                    //                                     //->where('user_id', auth()->user()->id)                     
                    //                                     ->first();


                    // if($m_porcents_user) {
                    //     $m_user = $m_porcents_user->porcentagem;

                    // } else {
                    //     $m_user = 0;
                    // }

                    // $o_porcents_user = ConfigOdd::where('site_id', env('ID_SITE'))
                    //                                 ->where('status', 1)
                    //                                 ->where('mercado_full_name', $palpite->palpite)
                    //                                 //->where('user_id', auth()->user()->id)         
                    //                                 ->first();  

                    //                                 if($o_porcents_user) {
                    //                                     $o_user = $o_porcents_user->porcentagem;
                    //                                 }else {
                    //                                     $o_user = 0;
                    //                                 }

                    $m_user = 0;
                    $o_user = 0;
                    $v_porcent_total_geral = ($m_user + $m_user + $o_user) / 100;
                    $redu = $palpite->cotacao - 1;
                    $odd_final = ($redu * $v_porcent_total_geral) + $redu + 1;

                    $this->arr[$i]['palpites'][$j]['idOdd']       = $palpite->idOdd;
                    $this->arr[$i]['palpites'][$j]['idEvent']     = $palpite->match_id;
                    $this->arr[$i]['palpites'][$j]['sport']       = $palpite->sport;
                    $this->arr[$i]['palpites'][$j]['match_temp']  = $palpite->match_temp;
                    $this->arr[$i]['palpites'][$j]['league']      = $palpite->league;
                    $this->arr[$i]['palpites'][$j]['home']        = $palpite->home;
                    $this->arr[$i]['palpites'][$j]['away']        = $palpite->away;
                    $this->arr[$i]['palpites'][$j]['group_opp']   = $palpite->group_opp;
                    $this->arr[$i]['palpites'][$j]['palpite']     = $palpite->palpite;
                    $this->arr[$i]['palpites'][$j]['odds']        = json_decode($palpite->odds);
                    $this->arr[$i]['palpites'][$j]['cotacao']     = round($odd_final, 2);
                    $j++;
                }






                $i++;
            }


            return $this->arr;
        }
    }

    public function printBilheteGetCodSite(Request $request)
    {
        $cod = Aposta::where('cupom', $request->cupom)
            ->where('tipo', '!=', 'Bolão')
            ->first();
        if ($cod->modalidade == 'Loto') {
            return Aposta::where('cupom', $request->cupom)
                ->where('tipo', '!=', 'Bolão')
                ->with('palpitesLoto')
                ->get();
        } else if ($cod->tipo == 'Bolão') {
            return Aposta::where('cupom', $request->cupom)->where('tipo', '!=', 'Bolão')->first();
        } else {

            $cupom = Aposta::where('cupom', $request->cupom)
                ->where('tipo', '!=', 'Bolão')
                ->get();


            if (count($cupom) < 1) {
                return response()->json(['message' => 'error'], 404);
            }


            $cont = 0;
            $i = 0;
            foreach ($cupom as $bilhete) {


                $this->arr[$i]['id']        = $bilhete->id;
                $this->arr[$i]['user_id']   = $bilhete->user_id;
                $this->arr[$i]['cupom']     = $bilhete->cupom;
                $dt = Carbon::now();
                //$dt->timestamp($dt);
                $date = $dt->format('Y-m-d H:i:s');
                $this->arr[$i]['created_at']      = $date;
                $this->arr[$i]['status']    = $bilhete->status;
                $this->arr[$i]['valor_apostado'] = $bilhete->valor_apostado;
                $this->arr[$i]['retorno_possivel'] = $bilhete->retorno_possivel;
                $this->arr[$i]['vendedor']     = auth()->user()->name;
                $this->arr[$i]['cliente'] = $bilhete->cliente;
                $this->arr[$i]['tipo'] = $bilhete->tipo;
                $this->arr[$i]['comicao'] = $bilhete->comicao;
                $this->arr[$i]['total_palpites'] = $bilhete->total_palpites;

                $j = 0;

                $palpites = Palpite::where('aposta_id', $bilhete->id)
                    ->where('match_temp', '>', $date)
                    ->get();

                foreach ($palpites as $palpite) {

                    // $m_porcents_user = ConfigMercados::where('site_id', env('ID_SITE'))
                    //                                     ->where('name', $palpite->group_opp)
                    //                                     ->where('status', 1)  
                    //                                     //->where('user_id', auth()->user()->id)                     
                    //                                     ->first();
                    // $m_user = $m_porcents_user->porcentagem;

                    // $o_porcents_user = ConfigOdd::where('site_id', env('ID_SITE'))
                    //                                 ->where('status', 1)
                    //                                 ->where('mercado_full_name', $palpite->palpite)
                    //                                 //->where('user_id', auth()->user()->id)         
                    //                                 ->first();  

                    //                                 if($o_porcents_user) {
                    //                                     $o_user = $o_porcents_user->porcentagem;
                    //                                 }else {
                    //                                     $o_user = 0;
                    //                                 }

                    $m_user = 0;
                    $o_user = 0;
                    $v_porcent_total_geral = ($m_user + $m_user + $o_user) / 100;
                    $redu = $palpite->cotacao - 1;
                    $odd_final = ($redu * $v_porcent_total_geral) + $redu + 1;

                    $this->arr[$i]['palpites'][$j]['idOdd']       = $palpite->idOdd;
                    $this->arr[$i]['palpites'][$j]['partida']     = $palpite->match_id;
                    $this->arr[$i]['palpites'][$j]['sport']       = $palpite->sport;
                    $this->arr[$i]['palpites'][$j]['date']        = $palpite->match_temp;
                    $this->arr[$i]['palpites'][$j]['league']      = $palpite->league;
                    $this->arr[$i]['palpites'][$j]['home']        = $palpite->home;
                    $this->arr[$i]['palpites'][$j]['away']        = $palpite->away;
                    $this->arr[$i]['palpites'][$j]['group_opp']   = $palpite->group_opp;
                    $this->arr[$i]['palpites'][$j]['odd']         = $palpite->palpite;
                    $this->arr[$i]['palpites'][$j]['odds']        = json_decode($palpite->odds);
                    $this->arr[$i]['palpites'][$j]['cotacao']     = round($odd_final, 2);
                    $j++;
                }



                $i++;
            }


            return $this->arr;
        }
    }

    public function validaCod(Request $request)
    {


        $cont = 0;

        $palpites = Palpite::where('aposta_id', $request->aposta_id)->where('tipo', '!=', 'Bolão')->get();

        foreach ($palpites as $palp) {
            if ($palp->match_temp < $this->agora) {
                $cont++;
            }
        }

        if ($cont > 0) {

            return response()->json(['message' => 'error'], 404);
        }



        $ap = Aposta::find($request->aposta_id);

        $ap->user_id = auth()->user()->id;
        $ap->vendedor = auth()->user()->name;
        $ap->adm_id   = auth()->user()->adm_id;
        $ap->gerente_id = auth()->user()->gerente_id;



        if ($ap->total_palpites ==  1) {

            $comissao = $ap->valor_apostado * auth()->user()->comissao1 / 100;
        }
        //2
        if ($ap->total_palpites == 2) {

            $comissao = $ap->valor_apostado * auth()->user()->comissao2 / 100;
        }
        //3
        if ($ap->total_palpites == 3) {

            $comissao = $ap->valor_apostado * auth()->user()->comissao3 / 100;
        }
        //4
        if ($ap->total_palpites == 4) {

            $comissao = $ap->valor_apostado * auth()->user()->comissao4 / 100;
        }
        //5
        if ($ap->total_palpites == 5) {

            $comissao = $ap->valor_apostado * auth()->user()->comissao5 / 100;
        }
        //6
        if ($ap->total_palpites == 6) {

            $comissao = $ap->valor_apostado * auth()->user()->comissao6 / 100;
        }
        //7
        if ($ap->total_palpites == 7) {

            $comissao = $ap->valor_apostado * auth()->user()->comissao7 / 100;
        }
        //8
        if ($ap->total_palpites == 8) {

            $comissao = $ap->valor_apostado * auth()->user()->comissao8 / 100;
        }
        //9
        if ($ap->total_palpites == 9) {

            $comissao = $ap->valor_apostado * auth()->user()->comissao9 / 100;
        }

        //10
        if ($ap->total_palpites >= 10) {

            $comissao = $ap->valor_apostado * auth()->user()->comissao10 / 100;
        }

        if ($ap->total_palpites == 1) {

            $entrada_s = $ap->valor_apostado;
            $entrada_c = 0;
        }
        if ($ap->total_palpites > 1) {

            $entrada_c = $ap->valor_apostado;
            $entrada_s = 0;
        }

        $ap->site_id = env('ID_SITE');
        $ap->comicao = $comissao;
        $ap->save();


        //Atualizações Usuários
        $user = User::find(auth()->user()->id);
        $user->quantidade_aposta = $user->quantidade_aposta + 1;
        $user->entradas = $user->entradas + $ap->valor_apostado;
        $user->entrada_casadinha = $user->entrada_casadinha + $entrada_c;
        $user->entrada_simples = $user->entrada_simples + $entrada_s;
        $user->comissoes = $user->comissoes + $comissao;
        $user->save();



        //Atualizar dados do gerente
        $gerente = User::find(auth()->user()->gerente_id);
        // dd($gerente);
        $gerente->quantidade_aposta = $gerente->quantidade_aposta + 1;
        $gerente->entradas = $gerente->entradas + $ap->valor_apostado;
        $gerente->save();

        //Alimenta mapa de apostas
        foreach ($palpites as $palpite) {

            MapaBet::create([
                'event_id'      => $palpite->match_id,
                'sport'         => $palpite->sport,
                'confronto'     => $palpite->home . " X " . $palpite->away,
                'date_event'    => $palpite->match_temp,
                'apostado'      => $ap->valor_apostado,
                'group_opp'     => $palpite->group_opp,
                'opcao'         => $palpite->palpite,
                'tipo_aposta'   => 'pre',
                'site_id'       => env('ID_SITE'),
            ]);
        }

        return response()->json(['message' => 'sucesso'], 200);
    }

    public function sendApostaLiveApp(Request $request)
    {

        $conf = Configuracao::where('site_id', env('ID_SITE'))->first();
        $cotacao_live =  $conf->cotacao_live;
        //Pré Validações
        $cot = 1;
        $p = array();
        $q = 0;
        $this->loadOddVivo($request->palpites);
        foreach ($request->palpites as $palpite) {


            //$match = Match::where('event_id', $palpite['idEvent'])->first();

            // LoadMatchLive::dispatchNow($match, $match->id);
            // LoadEventLive::dispatchNow();
            $od = Odd::where('event_id', $palpite['idEvent'])->where('odd', $palpite['palpite'])->where('mercado_name', $palpite['group_opp'])->first();
            if ($od) {
                $redu = $od->cotacao - 1;
                $odd_final = ($redu * $cotacao_live / 100) + $redu + 1;
                $cot = $odd_final * $cot;
                $p[$q]['idOdd']     = $palpite['idOdd'];
                $p[$q]['cotacao'] = round($odd_final, 2);
                $p[$q]['sport']     = $palpite['sport'];
                $p[$q]['idEvent']   = $palpite['idEvent'];
                $p[$q]['match_temp'] = $palpite['match_temp'];
                $p[$q]['league']    = $palpite['league'];
                $p[$q]['palpite']   = $palpite['palpite'];
                $p[$q]['home']      = $palpite['home'];
                $p[$q]['away']      = $palpite['away'];
                $p[$q]['group_opp'] = $palpite['group_opp'];
                $p[$q]['type']      = $palpite['type'];
            } else {
            }

            $q++;
        }

        //LoadEventLive::dispatchNow();
        return $p;
    }

    public function sendAposta(Request $request)
    {

        $user = User::find(auth()->user()->id);
        if ($user && $user->situacao == 'ativo') {
            $data_hoje = date('Y-m-d', strtotime($this->hoje));
            $data_atual = date('Y-m-d H:i:s');
            $hora = date('Hi');

            $confs = Configuracao::where('site_id', env('ID_SITE'))->get();
            foreach ($confs as $conf) {
                $aposta_ativa = $conf->aposta_ativa;
                $bloq_aposta_madrugada = $conf->bloq_aposta_madrugada;
                $email_alerta = $conf->email_alerta;
                $alerta_aposta_acima = $conf->alerta_aposta_acima;
            }


            if ($bloq_aposta_madrugada == 'Sim' && $hora >= '0100' &&  $hora <= '0559') {
                return response()->json(['message' => 'error'], 404);
            }

            //$codigo
            //Função que faz o armazenamento e concatenação das letras e números
            $valorMaximo = 7;
            $varcaracteres = $letras = 'ABCDEFGHIJLMNOPRSTUVXZYW';
            $varcaracteres .= $numeros = '0123456789ABCDGHSMHHHK';

            $i = 0;
            //variável que armazena o código gerado
            $cupom = "";

            while ($i < $valorMaximo) {

                $numrad = rand(0, strlen($varcaracteres) - 1);
                $cupom .= substr($varcaracteres, $numrad, 1);
                $i++;
            }

            //     //Comissões
            //1
            if ($request->total_palpites ==  1) {

                $comissao = $request->valor_apostado * auth()->user()->comissao1 / 100;
            }
            //2
            if ($request->total_palpites == 2) {

                $comissao = $request->valor_apostado * auth()->user()->comissao2 / 100;
            }
            //3
            if ($request->total_palpites == 3) {

                $comissao = $request->valor_apostado * auth()->user()->comissao3 / 100;
            }
            //4
            if ($request->total_palpites == 4) {

                $comissao = $request->valor_apostado * auth()->user()->comissao4 / 100;
            }
            //5
            if ($request->total_palpites == 5) {

                $comissao = $request->valor_apostado * auth()->user()->comissao5 / 100;
            }
            //6
            if ($request->total_palpites == 6) {

                $comissao = $request->valor_apostado * auth()->user()->comissao6 / 100;
            }
            //7
            if ($request->total_palpites == 7) {

                $comissao = $request->valor_apostado * auth()->user()->comissao7 / 100;
            }
            //8
            if ($request->total_palpites == 8) {

                $comissao = $request->valor_apostado * auth()->user()->comissao8 / 100;
            }
            //9
            if ($request->total_palpites == 9) {

                $comissao = $request->valor_apostado * auth()->user()->comissao9 / 100;
            }

            //10
            if ($request->total_palpites >= 10) {

                $comissao = $request->valor_apostado * auth()->user()->comissao10 / 100;
            }
            //Tipo de aposta
            if ($request->total_palpites == 1) {
                $tipo_aposta = 'Simples';
                $valor_aposta = $request->valor_apostado;
            } else {
                $tipo_aposta = 'Multipla';
                $valor_aposta = $request->valor_apostado;
            }

            if ($aposta_ativa == 'Não') {

                return response()->json(['message' => 'error'], 404);
            }
            //Pré Validações
            foreach ($request->palpites as $palpite) {

                if ($palpite['match_temp'] < $this->agora) {

                    return response()->json(['message' => 'error'], 404);
                }
            }


            DB::beginTransaction();
            $aposta = Aposta::create([
                'user_id'               => auth()->user()->id,
                'adm_id'                => auth()->user()->adm_id,
                'gerente_id'            => auth()->user()->gerente_id,
                'site_id'               => env('ID_SITE'),
                'modalidade'            => 'Esporte',
                'cupom'                 => $cupom,
                'status'                => 'Aberto',
                'valor_apostado'        => $request->valor_apostado,
                'retorno_possivel'      => $request->retorno_possivel,
                'retorno_cambista'      => $request->retorno_cambista,
                'vendedor'              => auth()->user()->name,
                'cliente'               => $request->cliente,
                'tipo'                  => $tipo_aposta,
                'comicao'               => $comissao,
                'cotacao'               => $request->cotacao,
                'total_palpites'        => $request->total_palpites,
                'andamento_palpites'    => $request->total_palpites,
                'acertos_palpites'      => 0,
                'erros_palpites'        => 0,
                'devolvidos_palpites'   => 0,
            ]);

            foreach ($request->palpites as $palpite) {

                $palp =  Palpite::create([
                    'aposta_id'     => $aposta->id,
                    'sport'         => $palpite['sport'],
                    'match_id'      => $palpite['idEvent'],
                    'match_temp'    => $palpite['match_temp'],
                    'league'        => $palpite['league'],
                    'home'          => $palpite['home'],
                    'away'          => $palpite['away'],
                    'group_opp'     => $palpite['group_opp'],
                    'palpite'       => $palpite['palpite'],
                    'cotacao'       => $palpite['cotacao'],
                    'ativo'         => 1,
                    'apostado'      => $request->valor_apostado,
                    'status'        => 'Aberto',
                    //'score'         => '--',

                ]);



                $mapa =  MapaBet::create([
                    'event_id'      => $palpite['idEvent'],
                    'sport'         => $palpite['sport'],
                    'confronto'     => $palpite['home'] . " X " . $palpite['away'],
                    'date_event'    => $palpite['match_temp'],
                    'apostado'      => $request->valor_apostado,
                    'group_opp'     => $palpite['group_opp'],
                    'opcao'         => $palpite['palpite'],
                    'tipo_aposta'   => 'pre',
                    'site_id'       => env('ID_SITE'),
                ]);
            }

            if ($request->total_palpites == 1) {

                $entrada_s = $request->valor_apostado;
                $entrada_c = 0;
            }
            if ($request->total_palpites > 1) {

                $entrada_c = $request->valor_apostado;
                $entrada_s = 0;
            }



            //Atualizações Usuários
            $user = User::find(auth()->user()->id);
            $user->quantidade_aposta = $user->quantidade_aposta + 1;
            $user->entradas = $user->entradas + $request->valor_apostado;
            $user->entrada_casadinha = $user->entrada_casadinha + $entrada_c;
            $user->entrada_simples = $user->entrada_simples + $entrada_s;
            $user->comissoes = $user->comissoes + $comissao;
            $user->save();



            //Atualizar dados do gerente
            $gerente = User::find(auth()->user()->gerente_id);
            // dd($gerente);
            $gerente->quantidade_aposta = $gerente->quantidade_aposta + 1;
            $gerente->entradas = $gerente->entradas + $request->valor_apostado;
            $gerente->comissoes = $gerente->comissoes + $comissao;
            $gerente->save();

            //Envia email de alerta acima de
            if ($request->valor_apostado >= $alerta_aposta_acima) {
                sendAlertaBet::dispatchNow($aposta, $email_alerta);
            }

            if ($aposta && $palp && $user && $gerente) {
                //Sucesso!
                DB::commit();
                return Aposta::find($aposta->id);
            } else {
                //Fail, desfaz as alterações no banco de dados
                DB::rollBack();
                return response()->json(['message' => 'error'], 404);
            }
        } else {
            return response()->json(['message' => 'error'], 404);
        }
    }

    public function sendApostaLive(Request $request)
    {


        $user = User::find(auth()->user()->id);
        $this->loadOddVivoSite($request->palpites);

        if ($user && $user->situacao == 'ativo') {
            $data_hoje = date('Y-m-d', strtotime($this->hoje));
            $data_atual = date('Y-m-d H:i:s');
            $hora = date('Hi');



            $confs = Configuracao::where('site_id', env('ID_SITE'))->get();

            foreach ($confs as $conf) {
                $aposta_ativa = $conf->aposta_ativa;
                $bloq_aposta_madrugada = $conf->bloq_aposta_madrugada;
                $email_alerta = $conf->email_alerta;
                $alerta_aposta_acima = $conf->alerta_aposta_acima;
                $cotacao_live =  $conf->cotacao_live;
            }


            if ($bloq_aposta_madrugada == 'Sim' && $hora >= '0100' &&  $hora <= '0559') {
                return response()->json(['message' => 'error0'], 404);
            }

            //$codigo
            //Função que faz o armazenamento e concatenação das letras e números
            $valorMaximo = 7;
            $varcaracteres = $letras = 'ABCDEFGHIJLMNOPRSTUVXZYW';
            $varcaracteres .= $numeros = '0123456789ABCDGHSMHHHK';

            $i = 0;
            //variável que armazena o código gerado
            $cupom = "";

            while ($i < $valorMaximo) {
                $numrad = rand(0, strlen($varcaracteres) - 1);
                $cupom .= substr($varcaracteres, $numrad, 1);
                $i++;
            }

            //     //Comissões
            //1
            if ($request->total_palpites ==  1) {

                $comissao = $request->valor_apostado * auth()->user()->comissao1 / 100;
            }
            //2
            if ($request->total_palpites == 2) {

                $comissao = $request->valor_apostado * auth()->user()->comissao2 / 100;
            }
            //3
            if ($request->total_palpites == 3) {

                $comissao = $request->valor_apostado * auth()->user()->comissao3 / 100;
            }
            //4
            if ($request->total_palpites == 4) {

                $comissao = $request->valor_apostado * auth()->user()->comissao4 / 100;
            }
            //5
            if ($request->total_palpites == 5) {

                $comissao = $request->valor_apostado * auth()->user()->comissao5 / 100;
            }
            //6
            if ($request->total_palpites == 6) {

                $comissao = $request->valor_apostado * auth()->user()->comissao6 / 100;
            }
            //7
            if ($request->total_palpites == 7) {

                $comissao = $request->valor_apostado * auth()->user()->comissao7 / 100;
            }
            //8
            if ($request->total_palpites == 8) {

                $comissao = $request->valor_apostado * auth()->user()->comissao8 / 100;
            }
            //9
            if ($request->total_palpites == 9) {

                $comissao = $request->valor_apostado * auth()->user()->comissao9 / 100;
            }

            //10
            if ($request->total_palpites >= 10) {

                $comissao = $request->valor_apostado * auth()->user()->comissao10 / 100;
            }


            //Tipo de aposta
            if ($request->total_palpites == 1) {
                $tipo_aposta = 'Simples';
                $valor_aposta = $request->valor_apostado;
            } else {
                $tipo_aposta = 'Multipla';
                $valor_aposta = $request->valor_apostado;
            }

            if ($aposta_ativa == 'Não') {

                return response()->json(['message' => 'error1'], 404);
            }

            //Pré Validações  
            $cot = 1;
            $p = array();
            $q = 0;
            foreach ($request->palpites as $palpite) {

                // $this->loadOddVivo($palpite['partida']);
                // $match = Match::where('event_id', $palpite['partida'])->first();
                // LoadMatchLive::dispatchNow($match, $match->id);
                // LoadEventLive::dispatchNow();


                $od = Odd::where('event_id', $palpite['partida'])->where('odd', $palpite['odd'])->where('mercado_name', $palpite['group_opp'])->first();
                if ($od) {
                    $redu = $od->cotacao - 1;
                    $odd_final = ($redu * $cotacao_live / 100) + $redu + 1;
                    $cot = $odd_final * $cot;
                    $p[$q]['idOdd']     = $palpite['idOdd'];
                    $p[$q]['cotacao'] = round($odd_final, 2);
                    $p[$q]['sport']     = $palpite['sport'];
                    $p[$q]['partida']   = $palpite['partida'];
                    $p[$q]['date']      = $palpite['date'];
                    $p[$q]['league']    = $palpite['league'];
                    $p[$q]['odd']       = $palpite['odd'];
                    $p[$q]['home']      = $palpite['home'];
                    $p[$q]['away']      = $palpite['away'];
                    $p[$q]['group_opp'] = $palpite['group_opp'];
                    $p[$q]['type']      = $palpite['type'];
                } else {
                }

                $q++;
            }



            //LoadEventLive::dispatchNow();
            return $p;



            //   if($request->valor_apostado*$cot != $request->retorno_possivel) {
            //     return $p;
            //   } else {
            //       return 'Apostou';
            //   }

        } else {
            return response()->json(['message' => 'error2'], 404);
        }
    }

    public function validLive(Request $request)
    {

        $user = User::find(auth()->user()->id);

        if ($user && $user->situacao == 'ativo') {
            $data_hoje = date('Y-m-d', strtotime($this->hoje));
            $data_atual = date('Y-m-d H:i:s');
            $hora = date('Hi');



            $confs = Configuracao::where('site_id', env('ID_SITE'))
                ->get();

            foreach ($confs as $conf) {
                $aposta_ativa = $conf->aposta_ativa;
                $bloq_aposta_madrugada = $conf->bloq_aposta_madrugada;
                $email_alerta = $conf->email_alerta;
                $alerta_aposta_acima = $conf->alerta_aposta_acima;
                $cotacao_live =  $conf->cotacao_live;
            }


            if ($bloq_aposta_madrugada == 'Sim' && $hora >= '0100' &&  $hora <= '0559') {
                return response()->json(['message' => 'error'], 404);
            }



            //$codigo
            //Função que faz o armazenamento e concatenação das letras e números
            $valorMaximo = 7;
            $varcaracteres = $letras = 'ABCDEFGHIJLMNOPRSTUVXZYW';
            $varcaracteres .= $numeros = '0123456789ABCDGHSMHHHK';

            $i = 0;
            //variável que armazena o código gerado
            $cupom = "";

            while ($i < $valorMaximo) {

                $numrad = rand(0, strlen($varcaracteres) - 1);
                $cupom .= substr($varcaracteres, $numrad, 1);
                $i++;
            }

            //     //Comissões
            //1
            if ($request->total_palpites ==  1) {

                $comissao = $request->valor_apostado * auth()->user()->comissao1 / 100;
            }
            //2
            if ($request->total_palpites == 2) {

                $comissao = $request->valor_apostado * auth()->user()->comissao2 / 100;
            }
            //3
            if ($request->total_palpites == 3) {

                $comissao = $request->valor_apostado * auth()->user()->comissao3 / 100;
            }
            //4
            if ($request->total_palpites == 4) {

                $comissao = $request->valor_apostado * auth()->user()->comissao4 / 100;
            }
            //5
            if ($request->total_palpites == 5) {

                $comissao = $request->valor_apostado * auth()->user()->comissao5 / 100;
            }
            //6
            if ($request->total_palpites == 6) {

                $comissao = $request->valor_apostado * auth()->user()->comissao6 / 100;
            }
            //7
            if ($request->total_palpites == 7) {

                $comissao = $request->valor_apostado * auth()->user()->comissao7 / 100;
            }
            //8
            if ($request->total_palpites == 8) {

                $comissao = $request->valor_apostado * auth()->user()->comissao8 / 100;
            }
            //9
            if ($request->total_palpites == 9) {

                $comissao = $request->valor_apostado * auth()->user()->comissao9 / 100;
            }

            //10
            if ($request->total_palpites >= 10) {

                $comissao = $request->valor_apostado * auth()->user()->comissao10 / 100;
            }


            //Tipo de aposta
            if ($request->total_palpites == 1) {
                $tipo_aposta = 'Simples';
                $valor_aposta = $request->valor_apostado;
            } else {
                $tipo_aposta = 'Multipla';
                $valor_aposta = $request->valor_apostado;
            }

            if ($aposta_ativa == 'Não') {

                return response()->json(['message' => 'error'], 404);
            }




            DB::beginTransaction();
            $aposta = Aposta::create([
                'user_id'               => auth()->user()->id,
                'adm_id'                => auth()->user()->adm_id,
                'gerente_id'            => auth()->user()->gerente_id,
                'site_id'               => env('ID_SITE'),
                'cupom'                 => $cupom,
                'tipo_aposta'           => $request->tipoAposta,
                'modalidade'            => 'Esporte',
                'status'                => 'Aberto',
                'valor_apostado'        => $request->valor_apostado,
                'retorno_possivel'      => $request->retorno_possivel,
                'retorno_cambista'      => $request->retorno_cambista,
                'vendedor'              => auth()->user()->name,
                'cliente'               => $request->cliente,
                'tipo'                  => $tipo_aposta,
                'comicao'               => $comissao,
                'cotacao'               => $request->cotacao,
                'total_palpites'        => $request->total_palpites,
                'andamento_palpites'    => $request->total_palpites,
                'acertos_palpites'      => 0,
                'erros_palpites'        => 0,
                'devolvidos_palpites'   => 0,
            ]);

            foreach ($request->palpites as $palpite) {


                $palp =  Palpite::create([
                    'aposta_id'     => $aposta->id,
                    'idOdd'         => $palpite['idOdd'],
                    'sport'         => $palpite['sport'],
                    'match_id'      => $palpite['partida'],
                    'match_temp'    => $palpite['date'],
                    'league'        => $palpite['league'],
                    'home'          => $palpite['home'],
                    'away'          => $palpite['away'],
                    'group_opp'     => $palpite['group_opp'],
                    'palpite'       => $palpite['odd'],
                    'type'          => $palpite['type'],
                    'cotacao'       => $palpite['cotacao'],
                    'apostado'      => $request->valor_apostado,
                    'status'        => 'Aberto',
                    'ativo'         => 1,

                ]);



                $mapa =  MapaBet::create([
                    'event_id'      => $palpite['partida'],
                    'sport'         => $palpite['sport'],
                    'confronto'     => $palpite['home'] . " X " . $palpite['away'],
                    'date_event'    => $palpite['date'],
                    'apostado'      => $request->valor_apostado,
                    'group_opp'     => $palpite['group_opp'],
                    'opcao'         => $palpite['odd'],
                    'tipo_aposta'   => 'pre',
                    'site_id'       => env('ID_SITE'),
                ]);

                //$match = Match::where('event_id', $palpite['partida'])->first();
                //LoadMatchLive::dispatchNow($match, $match->id);

            }

            if ($request->total_palpites == 1) {

                $entrada_s = $request->valor_apostado;
                $entrada_c = 0;
            }
            if ($request->total_palpites > 1) {

                $entrada_c = $request->valor_apostado;
                $entrada_s = 0;
            }



            //Atualizações Usuários
            $user = User::find(auth()->user()->id);
            $user->quantidade_aposta = $user->quantidade_aposta + 1;
            $user->entradas = $user->entradas + $request->valor_apostado;
            $user->entrada_casadinha = $user->entrada_casadinha + $entrada_c;
            $user->entrada_simples = $user->entrada_simples + $entrada_s;
            $user->comissoes = $user->comissoes + $comissao;
            $user->save();



            //Atualizar dados do gerente
            $gerente = User::find(auth()->user()->gerente_id);
            // dd($gerente);
            $gerente->quantidade_aposta = $gerente->quantidade_aposta + 1;
            $gerente->entradas = $gerente->entradas + $request->valor_apostado;
            $gerente->comissoes = $gerente->comissoes + $comissao;
            $gerente->save();

            //Envia email de alerta acima de
            if ($request->valor_apostado >= $alerta_aposta_acima) {
                sendAlertaBet::dispatchNow($aposta, $email_alerta);
            }

            if ($aposta && $palp && $user && $gerente) {
                //Sucesso!
                DB::commit();
                return Aposta::find($aposta->id);
                //  return response()->json(['aposta' => Aposta::find($aposta->id)]);
                return $p;
                //return response()->json(Aposta::find($aposta->id));
            } else {
                //Fail, desfaz as alterações no banco de dados
                DB::rollBack();
                return response()->json(['message' => 'error'], 404);
            }
            //LoadEventLive::dispatchNow();



            //   if($request->valor_apostado*$cot != $request->retorno_possivel) {
            //     return $p;
            //   } else {
            //       return 'Apostou';
            //   }

        } else {
            return response()->json(['message' => 'error'], 404);
        }
    }

    public function validLiveApp(Request $request)
    {

        $user = User::find(auth()->user()->id);

        if ($user && $user->situacao == 'ativo') {
            $data_hoje = date('Y-m-d', strtotime($this->hoje));
            $data_atual = date('Y-m-d H:i:s');
            $hora = date('Hi');

            $confs = Configuracao::where('site_id', env('ID_SITE'))
                ->get();

            foreach ($confs as $conf) {
                $aposta_ativa = $conf->aposta_ativa;
                $bloq_aposta_madrugada = $conf->bloq_aposta_madrugada;
                $email_alerta = $conf->email_alerta;
                $alerta_aposta_acima = $conf->alerta_aposta_acima;
                $cotacao_live =  $conf->cotacao_live;
            }


            if ($bloq_aposta_madrugada == 'Sim' && $hora >= '0100' &&  $hora <= '0559') {
                return response()->json(['message' => 'error'], 404);
            }

            //$codigo
            //Função que faz o armazenamento e concatenação das letras e números
            $valorMaximo = 7;
            $varcaracteres = $letras = 'ABCDEFGHIJLMNOPRSTUVXZYW';
            $varcaracteres .= $numeros = '0123456789ABCDGHSMHHHK';

            $i = 0;
            //variável que armazena o código gerado
            $cupom = "";

            while ($i < $valorMaximo) {

                $numrad = rand(0, strlen($varcaracteres) - 1);
                $cupom .= substr($varcaracteres, $numrad, 1);
                $i++;
            }

            //     //Comissões
            //1
            if ($request->total_palpites ==  1) {

                $comissao = $request->valor_apostado * auth()->user()->comissao1 / 100;
            }
            //2
            if ($request->total_palpites == 2) {

                $comissao = $request->valor_apostado * auth()->user()->comissao2 / 100;
            }
            //3
            if ($request->total_palpites == 3) {

                $comissao = $request->valor_apostado * auth()->user()->comissao3 / 100;
            }
            //4
            if ($request->total_palpites == 4) {

                $comissao = $request->valor_apostado * auth()->user()->comissao4 / 100;
            }
            //5
            if ($request->total_palpites == 5) {

                $comissao = $request->valor_apostado * auth()->user()->comissao5 / 100;
            }
            //6
            if ($request->total_palpites == 6) {

                $comissao = $request->valor_apostado * auth()->user()->comissao6 / 100;
            }
            //7
            if ($request->total_palpites == 7) {

                $comissao = $request->valor_apostado * auth()->user()->comissao7 / 100;
            }
            //8
            if ($request->total_palpites == 8) {

                $comissao = $request->valor_apostado * auth()->user()->comissao8 / 100;
            }
            //9
            if ($request->total_palpites == 9) {

                $comissao = $request->valor_apostado * auth()->user()->comissao9 / 100;
            }

            //10
            if ($request->total_palpites >= 10) {

                $comissao = $request->valor_apostado * auth()->user()->comissao10 / 100;
            }


            //Tipo de aposta
            if ($request->total_palpites == 1) {
                $tipo_aposta = 'Simples';
                $valor_aposta = $request->valor_apostado;
            } else {
                $tipo_aposta = 'Multipla';
                $valor_aposta = $request->valor_apostado;
            }

            if ($aposta_ativa == 'Não') {

                return response()->json(['message' => 'error'], 404);
            }

            DB::beginTransaction();
            $aposta = Aposta::create([
                'user_id'               => auth()->user()->id,
                'adm_id'                => auth()->user()->adm_id,
                'gerente_id'            => auth()->user()->gerente_id,
                'site_id'               => env('ID_SITE'),
                'cupom'                 => $cupom,
                'tipo_aposta'           => 'live',
                'modalidade'            => 'Esporte',
                'status'                => 'Aberto',
                'valor_apostado'        => $request->valor_apostado,
                'retorno_possivel'      => $request->retorno_possivel,
                'retorno_cambista'      => $request->retorno_cambista,
                'vendedor'              => auth()->user()->name,
                'cliente'               => $request->cliente,
                'tipo'                  => $tipo_aposta,
                'comicao'               => $comissao,
                'cotacao'               => $request->cotacao,
                'total_palpites'        => $request->total_palpites,
                'andamento_palpites'    => $request->total_palpites,
                'acertos_palpites'      => 0,
                'erros_palpites'        => 0,
                'devolvidos_palpites'   => 0,
            ]);

            foreach ($request->palpites as $palpite) {

                $palp =  Palpite::create([
                    'aposta_id'     => $aposta->id,
                    'idOdd'         => $palpite['idOdd'],
                    'sport'         => $palpite['sport'],
                    'match_id'      => $palpite['idEvent'],
                    'match_temp'    => $palpite['match_temp'],
                    'league'        => $palpite['league'],
                    'home'          => $palpite['home'],
                    'away'          => $palpite['away'],
                    'group_opp'     => $palpite['group_opp'],
                    'palpite'       => $palpite['palpite'],
                    'type'          => $palpite['type'],
                    'cotacao'       => $palpite['cotacao'],
                    'apostado'      => $request->valor_apostado,
                    'status'        => 'Aberto',
                    'ativo'         => 1,

                ]);


                $mapa =  MapaBet::create([
                    'event_id'      => $palpite['idEvent'],
                    'sport'         => $palpite['sport'],
                    'confronto'     => $palpite['home'] . " X " . $palpite['away'],
                    'date_event'    => $palpite['match_temp'],
                    'apostado'      => $request->valor_apostado,
                    'group_opp'     => $palpite['group_opp'],
                    'opcao'         => $palpite['palpite'],
                    'tipo_aposta'   => 'ao-vivo',
                    'site_id'       => env('ID_SITE'),
                ]);

                //  $match = Match::where('event_id', $palpite['idEvent'])->first();
                //  LoadMatchLive::dispatchNow($match, $match->id);

            }

            if ($request->total_palpites == 1) {

                $entrada_s = $request->valor_apostado;
                $entrada_c = 0;
            }
            if ($request->total_palpites > 1) {

                $entrada_c = $request->valor_apostado;
                $entrada_s = 0;
            }



            //Atualizações Usuários
            $user = User::find(auth()->user()->id);
            $user->quantidade_aposta = $user->quantidade_aposta + 1;
            $user->entradas = $user->entradas + $request->valor_apostado;
            $user->entrada_casadinha = $user->entrada_casadinha + $entrada_c;
            $user->entrada_simples = $user->entrada_simples + $entrada_s;
            $user->comissoes = $user->comissoes + $comissao;
            $user->save();



            //Atualizar dados do gerente
            $gerente = User::find(auth()->user()->gerente_id);
            // dd($gerente);
            $gerente->quantidade_aposta = $gerente->quantidade_aposta + 1;
            $gerente->entradas = $gerente->entradas + $request->valor_apostado;
            $gerente->comissoes = $gerente->comissoes + $comissao;
            $gerente->save();

            //Envia email de alerta acima de
            if ($request->valor_apostado >= $alerta_aposta_acima) {
                sendAlertaBet::dispatchNow($aposta, $email_alerta);
            }

            if ($aposta && $palp && $user && $gerente) {
                //Sucesso!
                DB::commit();
                return Aposta::find($aposta->id);
                //  return response()->json(['aposta' => Aposta::find($aposta->id)]);
                // return $p;
                //return response()->json(Aposta::find($aposta->id));
            } else {
                //Fail, desfaz as alterações no banco de dados
                DB::rollBack();
                return response()->json(['message' => 'error'], 404);
            }
            //LoadEventLive::dispatchNow();



            //   if($request->valor_apostado*$cot != $request->retorno_possivel) {
            //     return $p;
            //   } else {
            //       return 'Apostou';
            //   }

        } else {
            return response()->json(['message' => 'error'], 404);
        }
    }

    public function sendApostaSite(Request $request)
    {


        $user = User::find(auth()->user()->id);

        if ($user && $user->situacao == 'ativo') {
            $data_hoje = date('Y-m-d', strtotime($this->hoje));
            $data_atual = date('Y-m-d H:i:s');
            $hora = date('Hi');



            $confs = Configuracao::where('site_id', env('ID_SITE'))->get();
            foreach ($confs as $conf) {
                $aposta_ativa = $conf->aposta_ativa;
                $bloq_aposta_madrugada = $conf->bloq_aposta_madrugada;
                $email_alerta = $conf->email_alerta;
                $alerta_aposta_acima = $conf->alerta_aposta_acima;
            }


            if ($bloq_aposta_madrugada == 'Sim' && $hora >= '0100' &&  $hora <= '0559') {
                return response()->json(['message' => 'error'], 404);
            }



            //$codigo
            //Função que faz o armazenamento e concatenação das letras e números
            $valorMaximo = 7;
            $varcaracteres = $letras = 'ABCDEFGHIJLMNOPRSTUVXZYW';
            $varcaracteres .= $numeros = '0123456789ABCDGHSMHHHK';

            $i = 0;
            //variável que armazena o código gerado
            $cupom = "";

            while ($i < $valorMaximo) {

                $numrad = rand(0, strlen($varcaracteres) - 1);
                $cupom .= substr($varcaracteres, $numrad, 1);
                $i++;
            }

            //     //Comissões
            //1
            if ($request->total_palpites ==  1) {

                $comissao = $request->valor_apostado * auth()->user()->comissao1 / 100;
            }
            //2
            if ($request->total_palpites == 2) {

                $comissao = $request->valor_apostado * auth()->user()->comissao2 / 100;
            }
            //3
            if ($request->total_palpites == 3) {

                $comissao = $request->valor_apostado * auth()->user()->comissao3 / 100;
            }
            //4
            if ($request->total_palpites == 4) {

                $comissao = $request->valor_apostado * auth()->user()->comissao4 / 100;
            }
            //5
            if ($request->total_palpites == 5) {

                $comissao = $request->valor_apostado * auth()->user()->comissao5 / 100;
            }
            //6
            if ($request->total_palpites == 6) {

                $comissao = $request->valor_apostado * auth()->user()->comissao6 / 100;
            }
            //7
            if ($request->total_palpites == 7) {

                $comissao = $request->valor_apostado * auth()->user()->comissao7 / 100;
            }
            //8
            if ($request->total_palpites == 8) {

                $comissao = $request->valor_apostado * auth()->user()->comissao8 / 100;
            }
            //9
            if ($request->total_palpites == 9) {

                $comissao = $request->valor_apostado * auth()->user()->comissao9 / 100;
            }

            //10
            if ($request->total_palpites >= 10) {

                $comissao = $request->valor_apostado * auth()->user()->comissao10 / 100;
            }


            //Tipo de aposta
            if ($request->total_palpites == 1) {
                $tipo_aposta = 'Simples';
                $valor_aposta = $request->valor_apostado;
            } else {
                $tipo_aposta = 'Multipla';
                $valor_aposta = $request->valor_apostado;
            }

            if ($aposta_ativa == 'Não') {

                return response()->json(['message' => 'error'], 404);
            }

            //Pré Validações  
            foreach ($request->palpites as $palpite) {
                //if($palpite['type'] == 'pre') {

                if ($palpite['date'] < $this->agora) {

                    return response()->json(['message' => 'error'], 404);
                }
                //}
                // if($palpite['type'] == 'ao-vivo') {

                //         $this->loadOddVivo($palpite['partida']);
                //         $match = Match::where('event_id', $palpite['partida'])->first();
                //         LoadMatchLive::dispatchNow($match, $match->id);
                //         LoadEventLive::dispatchNow();


                //         $od = Odd::find($palpite['idOdd']);
                //         if($od) {
                //             if($palpite['cotacaoOriginal'] != $od->cotacao) {

                //                 return response()->json(['message' => 'erro odd'], 404);

                //             }

                //         } else {
                //             return response()->json(['message' => 'error odd n existe'], 404);
                //         }

                // }          

            }


            DB::beginTransaction();
            $aposta = Aposta::create([
                'user_id'               => auth()->user()->id,
                'adm_id'                => auth()->user()->adm_id,
                'gerente_id'            => auth()->user()->gerente_id,
                'site_id'               => env('ID_SITE'),
                'cupom'                 => $cupom,
                'tipo_aposta'           => $request->tipoAposta,
                'modalidade'            => 'Esporte',
                'status'                => 'Aberto',
                'valor_apostado'        => $request->valor_apostado,
                'retorno_possivel'      => $request->retorno_possivel,
                'retorno_cambista'      => $request->retorno_cambista,
                'vendedor'              => auth()->user()->name,
                'cliente'               => $request->cliente,
                'tipo'                  => $tipo_aposta,
                'comicao'               => $comissao,
                'cotacao'               => $request->cotacao,
                'total_palpites'        => $request->total_palpites,
                'andamento_palpites'    => $request->total_palpites,
                'acertos_palpites'      => 0,
                'erros_palpites'        => 0,
                'devolvidos_palpites'   => 0,
            ]);

            foreach ($request->palpites as $palpite) {



                $palp =  Palpite::create([
                    'aposta_id'     => $aposta->id,
                    'idOdd'         => $palpite['idOdd'],
                    'sport'         => $palpite['sport'],
                    'match_id'      => $palpite['partida'],
                    'match_temp'    => $palpite['date'],
                    'league'        => $palpite['league'],
                    'home'          => $palpite['home'],
                    'away'          => $palpite['away'],
                    'group_opp'     => $palpite['group_opp'],
                    'palpite'       => $palpite['odd'],
                    'type'          => 'pre',
                    'cotacao'       => $palpite['cotacao'],
                    'apostado'      => $request->valor_apostado,
                    'status'        => 'Aberto',
                    'ativo'         => 1,

                ]);



                $mapa =  MapaBet::create([
                    'event_id'      => $palpite['partida'],
                    'sport'         => $palpite['sport'],
                    'confronto'     => $palpite['home'] . " X " . $palpite['away'],
                    'date_event'    => $palpite['date'],
                    'apostado'      => $request->valor_apostado,
                    'group_opp'     => $palpite['group_opp'],
                    'opcao'         => $palpite['odd'],
                    'tipo_aposta'   => 'pre',
                    'site_id'       => env('ID_SITE'),
                ]);

                // $match = Match::where('event_id', $palpite['partida'])->first();
                //LoadMatchLive::dispatchNow($match, $match->id);

            }

            if ($request->total_palpites == 1) {

                $entrada_s = $request->valor_apostado;
                $entrada_c = 0;
            }
            if ($request->total_palpites > 1) {

                $entrada_c = $request->valor_apostado;
                $entrada_s = 0;
            }



            //Atualizações Usuários
            $user = User::find(auth()->user()->id);
            $user->quantidade_aposta = $user->quantidade_aposta + 1;
            $user->entradas = $user->entradas + $request->valor_apostado;
            $user->entrada_casadinha = $user->entrada_casadinha + $entrada_c;
            $user->entrada_simples = $user->entrada_simples + $entrada_s;
            $user->comissoes = $user->comissoes + $comissao;
            $user->save();



            //Atualizar dados do gerente
            $gerente = User::find(auth()->user()->gerente_id);
            // dd($gerente);
            $gerente->quantidade_aposta = $gerente->quantidade_aposta + 1;
            $gerente->entradas = $gerente->entradas + $request->valor_apostado;
            $gerente->comissoes = $gerente->comissoes + $comissao;
            $gerente->save();

            //Envia email de alerta acima de
            if ($request->valor_apostado >= $alerta_aposta_acima) {
                sendAlertaBet::dispatchNow($aposta, $email_alerta);
            }

            if ($aposta && $palp && $user && $gerente) {
                //Sucesso!
                DB::commit();
                return Aposta::find($aposta->id);
            } else {
                //Fail, desfaz as alterações no banco de dados
                DB::rollBack();
                return response()->json(['message' => 'error'], 404);
            }
        } else {
            return response()->json(['message' => 'error'], 404);
        }
    }


    public function sendPreAposta(Request $request)
    {

        $confs = Configuracao::where('site_id', env('ID_SITE'))->get();
        foreach ($confs as $conf) {
            $aposta_ativa = $conf->aposta_ativa;
            $bloq_aposta_madrugada = $conf->bloq_aposta_madrugada;
            $email_alerta = $conf->email_alerta;
            $alerta_aposta_acima = $conf->alerta_aposta_acima;
        }

        if ($aposta_ativa == 'Não') {

            return response()->json(['message' => 'error'], 404);
        }

        //$codigo
        //Função que faz o armazenamento e concatenação das letras e números
        $valorMaximo = 7;
        $varcaracteres = $letras = 'ABCDEFGHIJLMNOPRSTUVXZYW';
        $varcaracteres .= $numeros = '0123456789ABCDGHSMHHHK';

        $i = 0;
        //variável que armazena o código gerado
        $cupom = "";

        while ($i < $valorMaximo) {

            $numrad = rand(0, strlen($varcaracteres) - 1);
            $cupom .= substr($varcaracteres, $numrad, 1);
            $i++;
        }






        if ($request->total_palpites == 1) {
            $tipo_aposta = 'Simples';
        } else {
            $tipo_aposta = 'Multipla';
        }


        $aposta = Aposta::create([
            'user_id'               => env('ID_USER'),
            'adm_id'                => 0,
            'gerente_id'            => 0,
            'site_id'               => ' ',
            'cupom'                 => $cupom,
            'status'                => 'Aberto',
            'valor_apostado'        => $request->valor_apostado,
            'retorno_possivel'      => $request->retorno_possivel,
            'vendedor'              => ' ',
            'cliente'               => $request->cliente,
            'tipo'                  => $tipo_aposta,
            'comicao'               => 0,
            'cotacao'               => $request->cotacao,
            'total_palpites'        => $request->total_palpites,
            'andamento_palpites'    => $request->total_palpites,
            'acertos_palpites'      => 0,
            'erros_palpites'        => 0,
            'devolvidos_palpites'   => 0,
        ]);



        foreach ($request->palpites as $palpite) {

            $this->arr['objeto']['id'] = $palpite['idOdd'];
            $this->arr['objeto']['cotacao'] = $palpite['cotacao'];
            $this->arr['objeto']['group_odd'] = $palpite['group_opp'];
            $this->arr['objeto']['odd'] = $palpite['odd'];
            $this->arr['objeto']['selected'] = true;

            $palp =  Palpite::create([
                'aposta_id'     => $aposta->id,
                'idOdd'         => $palpite['idOdd'],
                'sport'         => $palpite['sport'],
                'match_id'      => $palpite['partida'],
                'match_temp'    => $palpite['date'],
                'league'        => $palpite['league'],
                'home'          => $palpite['home'],
                'away'          => $palpite['away'],
                'group_opp'     => $palpite['group_opp'],
                'palpite'       => $palpite['odd'],
                'cotacao'       => $palpite['cotacao'],
                'apostado'      => $request->valor_apostado,
                'status'        => 'Aberto',
                'ativo'         => 0,
                'odds'          => json_encode($this->arr),

            ]);

            // $match = Match::where('event_id', $palpite['partida'])->first();
            // broadcast(new LoadMatchLiveScore($match));
            // LoadMatchLive::dispatchNow($match, $match->id);



        }

        if ($aposta && $palp) {
            //Sucesso!
            DB::commit();
            return Aposta::find($aposta->id);
        } else {
            //Fail, desfaz as alterações no banco de dados
            DB::rollBack();
            return response()->json(['message' => 'error'], 404);
        }
    }


    public function sendApostaLoto(Request $request)
    {


        $user = User::find(auth()->user()->id);

        if ($user && $user->situacao == 'ativo') {
            $data_hoje = date('Y-m-d', strtotime($this->hoje));
            $data_atual = date('Y-m-d H:i:s');
            $hora = date('Hi');



            $confs = Configuracao::where('site_id', env('ID_SITE'))->get();
            foreach ($confs as $conf) {
                $aposta_ativa = $conf->aposta_ativa;
                $bloq_aposta_madrugada = $conf->bloq_aposta_madrugada;
                $email_alerta = $conf->email_alerta;
                $alerta_aposta_acima = $conf->alerta_aposta_acima;
            }


            if ($bloq_aposta_madrugada == 'Sim' && $hora >= '0100' &&  $hora <= '0559') {
                return response()->json(['message' => 'error'], 404);
            }

            //$codigo
            //Função que faz o armazenamento e concatenação das letras e números
            $valorMaximo = 7;
            $varcaracteres = $letras = 'ABCDEFGHIJLMNOPRSTUVXZYW';
            $varcaracteres .= $numeros = '0123456789ABCDGHSMHHHK';

            $i = 0;
            //variável que armazena o código gerado
            $cupom = "";

            while ($i < $valorMaximo) {

                $numrad = rand(0, strlen($varcaracteres) - 1);
                $cupom .= substr($varcaracteres, $numrad, 1);
                $i++;
            }

            //Comissão
            $comissao = $request->valor_apostado * auth()->user()->comissao_loto / 100;

            DB::beginTransaction();
            $aposta = Aposta::create([
                'user_id'               => auth()->user()->id,
                'adm_id'                => auth()->user()->adm_id,
                'gerente_id'            => auth()->user()->gerente_id,
                'site_id'               => env('ID_SITE'),
                'modalidade'            => 'Loto',
                'cupom'                 => $cupom,
                'status'                => 'Aberto',
                'concurso'              => $request->concurso,
                'valor_apostado'        => $request->valor_apostado,
                'retorno_possivel'      => $request->retorno_possivel,
                'vendedor'              => auth()->user()->name,
                'cliente'               => $request->cliente,
                'tipo'                  => $request->tipo,
                'comicao'               => $comissao,
                'cotacao'               => $request->cotacao,
                'total_palpites'        => $request->total_palpites,
                'andamento_palpites'    => $request->total_palpites,
                'acertos_palpites'      => 0,
                'erros_palpites'        => 0,
                'devolvidos_palpites'   => 0,
            ]);

            foreach ($request->palpites as $palpite) {

                $palp =  PalpiteLoto::create([
                    'aposta_id'     =>  $aposta->id,
                    'tipo'          =>  $request->tipo,
                    'dezena'        =>  $palpite['num'],
                    'status'        => 'Aberto',
                    'concurso'      => $request->concurso,

                ]);
            }

            //Atualizações Usuários
            $user = User::find(auth()->user()->id);
            $user->quantidade_aposta = $user->quantidade_aposta + 1;
            $user->entrada_loto = $user->entrada_loto + $request->valor_apostado;
            $user->comissoes = $user->comissoes + $comissao;
            $user->save();



            //Atualizar dados do gerente
            $gerente = User::find(auth()->user()->gerente_id);
            // dd($gerente);
            $gerente->quantidade_aposta = $gerente->quantidade_aposta + 1;
            $gerente->entradas = $gerente->entradas + $request->valor_apostado;
            $gerente->comissoes = $gerente->comissoes + $comissao;
            $gerente->save();



            if ($aposta && $palp && $user && $gerente) {
                //Sucesso!
                DB::commit();
                return $aposta->id;
            } else {
                //Fail, desfaz as alterações no banco de dados
                DB::rollBack();
                return response()->json(['message' => 'error'], 404);
            }
        } else {
            return response()->json(['message' => 'error'], 404);
        }
    }

    //Apostas bolão
    public function sendPreApostaBolao(Request $request)
    {
        //$codigo
        //Função que faz o armazenamento e concatenação das letras e números
        $valorMaximo = 7;
        $varcaracteres = $letras = 'ABCDEFGHIJLMNOPRSTUVXZYW';
        $varcaracteres .= $numeros = '0123456789ABCDGHSMHHHK';

        $i = 0;
        //variável que armazena o código gerado
        $cupom = "";

        while ($i < $valorMaximo) {

            $numrad = rand(0, strlen($varcaracteres) - 1);
            $cupom .= substr($varcaracteres, $numrad, 1);
            $i++;
        }


        //Pré Validações    
        foreach ($request->palpites as $palpite) {

            if ($palpite['date'] < $this->agora) {

                return response()->json(['message' => 'error'], 404);
            }
        }

        DB::beginTransaction();
        $aposta = Aposta::create([
            'user_id'               => env('ID_USER'),
            'adm_id'                => 0,
            'gerente_id'            => 0,
            'site_id'               => ' ',
            'bolao_id'              => $request->bolao_id,
            'rodada_id'             => $request->rodada_id,
            'premio_max'            => $request->premio_max,
            'premio_primeiro'       => $request->premio_primeiro,
            'cupom'                 => $cupom,
            'status'                => 'Aberto',
            'valor_apostado'        => $request->valor_apostado,
            'retorno_possivel'      => 0,
            'vendedor'              => ' ',
            'cliente'               => $request->cliente,
            'tipo'                  => $request->tipo,
            'comicao'               => 0,
            'cotacao'               => 0,
            'total_palpites'        => $request->total_palpites,
            'andamento_palpites'    => $request->total_palpites,
            'acertos_palpites'      => 0,
            'erros_palpites'        => 0,
            'devolvidos_palpites'   => 0,
        ]);

        foreach ($request->palpites as $palpite) {

            $palp =  PalpiteBolao::create([
                'aposta_id'         => $aposta->id,
                'confronto_id'      => $palpite['confronto_id'],
                'bolao'             => $palpite['bolao'],
                'rodada'            => $palpite['rodada'],
                'date'              => $palpite['date'],
                'home'              => $palpite['home'],
                'away'              => $palpite['away'],
                'mercado'           => $palpite['mercado'],
                'palpite'           => $palpite['palpite'],
                'status'            => 'Aberto',

            ]);
        }



        if ($aposta && $palp) {
            //Sucesso!
            DB::commit();
            //return $aposta->with('palpitesBolao')->get();
            return  Aposta::find($aposta->id);
        } else {
            //Fail, desfaz as alterações no banco de dados
            DB::rollBack();
            return response()->json(['message' => 'error'], 404);
        }
    }


    public function sendBolao(Request $request)
    {


        $userAll = User::find(auth()->user()->id);
        //$codigo
        //Função que faz o armazenamento e concatenação das letras e números
        $valorMaximo = 7;
        $varcaracteres = $letras = 'ABCDEFGHIJLMNOPRSTUVXZYW';
        $varcaracteres .= $numeros = '0123456789ABCDGHSMHHHK';

        $i = 0;
        //variável que armazena o código gerado
        $cupom = "";

        while ($i < $valorMaximo) {

            $numrad = rand(0, strlen($varcaracteres) - 1);
            $cupom .= substr($varcaracteres, $numrad, 1);
            $i++;
        }


        //Pré Validações    
        foreach ($request->palpites as $palpite) {

            if ($palpite['date'] < $this->agora) {

                return response()->json(['message' => 'error'], 404);
            }
        }

        DB::beginTransaction();
        $aposta = Aposta::create([
            'user_id'               => auth()->user()->id,
            'adm_id'                => auth()->user()->adm_id,
            'gerente_id'            => auth()->user()->gerente_id,
            'site_id'               => env('ID_SITE'),
            'bolao_id'              => $request->bolao_id,
            'rodada_id'             => $request->rodada_id,
            'premio_max'            => $request->premio_max,
            'premio_primeiro'       => $request->premio_primeiro,
            'cupom'                 => $cupom,
            'status'                => 'Aberto',
            'valor_apostado'        => $request->valor_apostado,
            'retorno_possivel'      => 0,
            'vendedor'              => auth()->user()->name,
            'cliente'               => $request->cliente,
            'tipo'                  => $request->tipo,
            'comicao'               => $request->valor_apostado * $userAll->comissao_bolao / 100,
            'cotacao'               => 0,
            'total_palpites'        => $request->total_palpites,
            'andamento_palpites'    => $request->total_palpites,
            'acertos_palpites'      => 0,
            'erros_palpites'        => 0,
            'devolvidos_palpites'   => 0,
        ]);

        foreach ($request->palpites as $palpite) {

            $palp =  PalpiteBolao::create([
                'aposta_id'         => $aposta->id,
                'confronto_id'      => $palpite['confronto_id'],
                'bolao'             => $palpite['bolao'],
                'rodada'            => $palpite['rodada'],
                'home'              => $palpite['home'],
                'date'              => $palpite['date'],
                'away'              => $palpite['away'],
                'mercado'           => $palpite['mercado'],
                'palpite'           => $palpite['palpite'],
                'status'            => 'Aberto',


            ]);
        }


        //Atualizar rodada
        $rodada = Rodada::find($request->rodada_id);
        $rodada->quantidade = $rodada->quantidade + 1;
        $rodada->arrecadado = $rodada->arrecadado + $request->valor_apostado;
        $rodada->save();

        //Atualizações Usuários
        $user = User::find(auth()->user()->id);
        $user->quantidade_aposta = $user->quantidade_aposta + 1;
        $user->entradas = $user->entradas + $request->valor_apostado;
        $user->comissoes = $user->comissoes + ($request->valor_apostado * $user->comissao_bolao) / 100;
        $user->save();



        //Atualizar dados do gerente
        $gerente = User::find(auth()->user()->gerente_id);
        $gerente->quantidade_aposta = $gerente->quantidade_aposta + 1;
        $gerente->entradas = $gerente->entradas + $request->valor_apostado;
        $gerente->comissoes = $gerente->comissoes + ($request->valor_apostado * $gerente->comissoes) / 100;
        $gerente->save();



        $saldo =  $user->saldo_bolao - $user->entradas;


        if ($aposta && $palp && $rodada && $userAll && $userAll->situacao == 'ativo' && $user && $gerente && $request->valor_apostado <= $saldo) {
            //Sucesso!
            DB::commit();
            //return $aposta->with('palpitesBolao')->get();
            return  Aposta::find($aposta->id);
        } else {
            //Fail, desfaz as alterações no banco de dados
            DB::rollBack();
            return response()->json(['message' => 'error'], 404);
        }
    }


    public function bilhetes()
    {

        return Aposta::where('user_id', auth()->user()->id)
            ->orderBy('id', 'desc')
            ->limit(30)
            ->where('tipo', '!=', 'Bolão')
            ->get();
    }

    public function searchBilhete(Request $request)
    {
        return  $consulta = Aposta::where('created_at', '>=', $request->date . ' 00:00:00')
            ->where('created_at', '<=', $request->date . ' 23:59:59')
            ->where('user_id', auth()->user()->id)
            ->where('tipo', '!=', 'Bolão')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function cancelaBilhete($id)
    {
        //return response()->json(['message' => 'error'], 404);
        $nowDate = Carbon::now();
        $confs = Configuracao::where('site_id', env('ID_SITE'))->get();
        foreach ($confs as $conf) {
            $tempo_linite = $conf->tempo_limite_camb_cancela_aposta;
            $cancelamento = $conf->cambista_pode_cancelar;
        }

        if ($cancelamento == "Não") {
            return response()->json(['message' => 'error'], 404);
        }

        $bilhete = Aposta::find($id);

        if ($bilhete->tipo_aposta === 'live') {
            return response()->json(['message' => 'error'], 404);
        }
        $bilhete->created_at;

        $data_agora = Carbon::createFromFormat('Y-m-d H:i:s', $nowDate);
        $data_bilhete = Carbon::createFromFormat('Y-m-d H:i:s', $bilhete->created_at);
        $diff = $data_agora->diffInMinutes($data_bilhete);



        if ($diff < $tempo_linite) {


            if ($bilhete->tipo == 'Bolão') {

                DB::beginTransaction();
                //Altera Bilhete
                $bilhete->status = 'Cancelado';
                $bilhete->save();


                $palpites = PalpiteBolao::where('aposta_id', $bilhete->id)->get();

                foreach ($palpites as $palpite) {
                    $palp = PalpiteBolao::find($palpite->id);
                    $palp->status = 'Cancelado';
                    $palp->save();
                }

                //Caixa Cambista
                $user = User::find($bilhete->user_id);
                $user->quantidade_aposta = $user->quantidade_aposta - 1;
                $user->entradas = $user->entradas - $bilhete->valor_apostado;

                //Caixa Gerente
                $gerente = User::find($bilhete->gerente_id);
                $gerente->quantidade_aposta = $gerente->quantidade_aposta - 1;
                $gerente->entradas = $gerente->entradas - $bilhete->valor_apostado;


                //Comissão
                $user->comissoes = $user->comissoes - $bilhete->comicao;
                $gerente->comissoes = $gerente->comissoes - $bilhete->comicao;
                $user->save();
                $gerente->save();

                //Atualizar rodada
                $rodada = Rodada::find($bilhete->rodada_id);
                $rodada->quantidade = $rodada->quantidade - 1;
                $rodada->arrecadado = $rodada->arrecadado - $bilhete->valor_apostado;
                $rodada->save();

                if ($bilhete && $user  && $gerente && $rodada) {
                    //Sucesso!
                    DB::commit();
                    return response()->json(['message' => 'sucesso'], 200);
                } else {
                    //Fail, desfaz as alterações no banco de dados
                    DB::rollBack();
                    return response()->json(['message' => 'error'], 404);
                }
            } else {
                DB::beginTransaction();
                //Altera Bilhete
                $bilhete->status = 'Cancelado';
                $bilhete->save();


                $palpites = Palpite::where('aposta_id', $id)->get();

                foreach ($palpites as $palpite) {
                    $palp = Palpite::find($palpite->id);
                    $palp->status = 'Cancelado';
                    $palp->save();
                }

                //Caixa Cambista
                $user = User::find($bilhete->user_id);
                $user->quantidade_aposta = $user->quantidade_aposta - 1;
                $user->entradas = $user->entradas - $bilhete->valor_apostado;

                //Caixa Gerente
                $gerente = User::find($bilhete->gerente_id);
                $gerente->quantidade_aposta = $gerente->quantidade_aposta - 1;
                $gerente->entradas = $gerente->entradas - $bilhete->valor_apostado;

                //Casadinhas
                if ($bilhete->total_palpites > 1) {
                    $user->entrada_casadinha = $user->entrada_casadinha - $bilhete->valor_apostado;
                    //$user->saldo_casadinha = $user->saldo_casadinha -  $bilhete->valor_apostado;
                }
                //Simples
                if ($bilhete->total_palpites == 1) {
                    $user->entrada_simples = $user->entrada_simples - $bilhete->valor_apostado;
                    //$user->saldo_simples = $user->saldo_simples -  $bilhete->valor_apostado;
                }
                //Saídas
                if ($bilhete->status == "Ganhou") {
                    $user->saidas = $user->saidas - $bilhete->retorno_possivel;
                    $gerente->saidas = $gerente->saidas - $bilhete->retorno_possivel;
                }
                //Comissão
                $user->comissoes = $user->comissoes - $bilhete->comicao;
                $gerente->comissoes = $gerente->comissoes - $bilhete->comicao;
                $user->save();
                $gerente->save();
                if ($bilhete && $user  && $gerente) {
                    //Sucesso!
                    DB::commit();
                    return response()->json(['message' => 'sucesso'], 200);
                } else {
                    //Fail, desfaz as alterações no banco de dados
                    DB::rollBack();
                    return response()->json(['message' => 'error'], 404);
                }
            }
        } else {
            return response()->json(['message' => 'error'], 404);
        }
    }

    public function loadOddVivoSite($idPartidas)
    {
        //while(1) {
        $trnaslate = array(
            'HOME'              => 'Casa',
            'The Draw'          => 'Empate',
            'DRAW'              => 'Empate',
            'AWAY'              => 'Fora',
            'Over '             => 'Mais de ',
            ' Goals'            => '',
            'Under '            => 'Menos de ',
            '1st Half Over '    => 'Mais de ',
            '1st Half Under '   => 'Menos de ',
            'No Goalscorer'     => 'Sem Gols',
            '1'                 => 'Casa',
            '2'                 => 'Empate',
            '3'                 => 'Fora',
        );

        $arrGolsMaisMenosPartida = [
            'OVER_UNDER_05',
            'OVER_UNDER_15',
            'OVER_UNDER_25',
            'OVER_UNDER_35',
            'OVER_UNDER_45',
            'OVER_UNDER_55',
            'OVER_UNDER_65',
            'OVER_UNDER_75',
            'OVER_UNDER_85',
            'OVER_UNDER_95',
        ];

        $arrGolsMaisMenosPrimeiroTempo = [
            'OVER_UNDER_HALF_TIME_05',
            'OVER_UNDER_HALF_TIME_15',
            'OVER_UNDER_HALF_TIME_25',
            'OVER_UNDER_HALF_TIME_35',
            'OVER_UNDER_HALF_TIME_45',
            'OVER_UNDER_HALF_TIME_55',
            'OVER_UNDER_HALF_TIME_65',
            'OVER_UNDER_HALF_TIME_75',
            'OVER_UNDER_HALF_TIME_85',
            'OVER_UNDER_HALF_TIME_95',
        ];


        // $matchs = Match::select('id', 'event_id', 'date')
        //             ->where('event_id' , $idPartida)
        //             ->get();

        $num = 0;
        $arrays = 0;
        $arrMatchs = array();
        $arrMatchs[0] = [];
        foreach ($idPartidas as $match) {
            if ($num > 0) {
                $arrays++;
                $arrMatchs[$arrays] = array();
                $num = 0;
            }
            array_push($arrMatchs[$arrays], $match['partida']);

            $num++;
        }
        foreach ($arrMatchs as $matchArray) {
            $url =  'https://api.betsapi.com/v1/betfair/sb/event?token=' . env('TOKEN') . '&event_id=';

            $size = count($matchArray);
            $tmp = 0;
            foreach ($matchArray as $match) {
                if ($tmp + 1 == $size) {
                    $url = $url . $match;
                } else {
                    $url = $url . $match . ",";
                }
                $tmp++;
            }



            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($ch);
            if ($data === false) {
                $info = curl_getinfo($ch);
                curl_close($ch);
                die('error occured during curl exec. Additioanl info: ' . var_export($info));
            }
            curl_close($ch);
            $data = json_decode($data);

            if (isset($data->success) && isset($data->results)) {
                foreach ($data->results as $results) {
                    // dd($results->updated_at);
                    ////echo $results->event->name."\n";
                    $event     = explode(" v ", $results->event->name);
                    $home      = $event[0];
                    $away      = $event[1];
                    $partida   = Match::where('event_id', $results->event->eventId)->first();

                    $match_id  = $partida->id;
                    $event_id  = $partida->event_id;
                    //excluir o mercaddo
                    $mercados = Odd::where('match_id', $partida->id)->delete();
                    $alters =  BlockOddMatch::where('odd_id', $event_id)->get();
                    if (count($alters) > 0) {
                        foreach ($alters as $alter) {
                            $alt = BlockOddMatch::find($alter->id);
                            $alt->delete();
                        }
                    }

                    if (isset($results->timeline)) {
                        ////echo "Tudo de Boa\n";


                        if (isset($results->timeline->matchStatus) && $results->timeline->matchStatus ==  "Finished") {
                            $event = Match::where('event_id', $results->timeline->eventId)->first();
                            $event->time_status = 3;
                            $event->visible = 'Não';
                            $event->score = $results->timeline->score->home->score . " - " . $results->timeline->score->away->score;
                            $event->time = $results->timeline->elapsedRegularTime;
                            //Score primeito tempo
                            if ($results->timeline->score->home->halfTimeScore != "") {
                                $event->halfTimeScoreHome = $results->timeline->score->home->halfTimeScore;
                            }
                            if ($results->timeline->score->away->halfTimeScore != "") {
                                $event->halfTimeScoreAway = $results->timeline->score->away->halfTimeScore;
                            }
                            //Score segundo tempo
                            if ($results->timeline->score->home->halfTimeScore != "") {
                                $event->fullTimeScoreHome = $results->timeline->score->home->score - $results->timeline->score->home->halfTimeScore;
                            }
                            if ($results->timeline->score->away->halfTimeScore != "") {
                                $event->fullTimeScoreAway = $results->timeline->score->away->score - $results->timeline->score->away->halfTimeScore;
                            }
                            //Quantidade de escanteios
                            //Casa
                            $event->numberOfCornersHome = $results->timeline->score->home->numberOfCorners;
                            //Fora
                            $event->numberOfCornersAway = $results->timeline->score->away->numberOfCorners;
                            $event->numberOfYellowCardsHome = $results->timeline->score->home->numberOfYellowCards;
                            $event->numberOfYellowCardsAway = $results->timeline->score->away->numberOfYellowCards;
                            $event->numberOfRedCardsHome    = $results->timeline->score->home->numberOfRedCards;
                            $event->numberOfRedCardsAway    = $results->timeline->score->away->numberOfRedCards;
                            $event->save();
                        } else {
                            $event = Match::where('event_id', $results->timeline->eventId)->first();
                            $event->score = $results->timeline->score->home->score . " - " . $results->timeline->score->away->score;
                            $event->time = $results->timeline->elapsedRegularTime;

                            if (isset($results->timeline->matchStatus) == "KickOff") {
                                $event->halfTimeScoreHome = $results->timeline->score->home->score;
                                $event->halfTimeScoreAway = $results->timeline->score->away->score;
                                $event->fullTimeScoreHome = 0;
                                $event->fullTimeScoreAway = 0;
                            }
                            if (isset($results->timeline->matchStatus) == "SecondHalfKickOff") {
                                // $event->fullTimeScoreHome = $results->timeline->score->home->score - $results->timeline->score->home->halfTimeScore;
                                // $event->fullTimeScoreAway = $results->timeline->score->away->score - $results->timeline->score->away->halfTimeScore;
                            }
                            //Score primeito tempo
                            if ($results->timeline->score->home->halfTimeScore != "") {
                                $event->halfTimeScoreHome = $results->timeline->score->home->halfTimeScore;
                            }
                            if ($results->timeline->score->away->halfTimeScore != "") {
                                $event->halfTimeScoreAway = $results->timeline->score->away->halfTimeScore;
                            }
                            //Score segundo tempo
                            if ($results->timeline->score->home->halfTimeScore != "") {
                                $event->fullTimeScoreHome = $results->timeline->score->home->score - $results->timeline->score->home->halfTimeScore;
                            }
                            if ($results->timeline->score->away->halfTimeScore != "") {
                                $event->fullTimeScoreAway = $results->timeline->score->away->score - $results->timeline->score->away->halfTimeScore;
                            }

                            //Quantidade de escanteios
                            //Casa
                            $event->numberOfCornersHome = $results->timeline->score->home->numberOfCorners;
                            //Fora
                            $event->numberOfCornersAway = $results->timeline->score->away->numberOfCorners;
                            //Cartões
                            $event->numberOfYellowCardsHome = $results->timeline->score->home->numberOfYellowCards;
                            $event->numberOfYellowCardsAway = $results->timeline->score->away->numberOfYellowCards;
                            $event->numberOfRedCardsHome    = $results->timeline->score->home->numberOfRedCards;
                            $event->numberOfRedCardsAway    = $results->timeline->score->away->numberOfRedCards;

                            $event->save();
                            //$mercados = Odd::where('match_id', $match_id )->get();

                            if (count($results->markets) == 0) {
                                $mercados = Odd::where('match_id', $match_id)->delete();
                            }
                            $mercados = Odd::where('match_id', $partida->id)->delete();
                            //Markets
                            foreach ($results->markets as $markets) {
                                $event     = explode(" v ", $results->event->name);
                                $home      = $event[0];
                                $away      = $event[1];

                                //Vencedor do Encontro
                                if ($markets->market->marketType == "MATCH_ODDS") {

                                    foreach ($markets->market->runners as $key => $runners) {

                                        if ($matchOdd = Odd::where('selectionId', $event_id . $runners->result->type . $markets->market->marketType)->where('match_id', $match_id)->first()) {
                                            //echo "Atualiza\n";
                                            if (isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                                $odd = Odd::find($matchOdd->id);
                                                $odd->cotacao   = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                                $odd->state     = $runners->runnerStatus;
                                                $odd->type      = "ao-vivo";
                                                $odd->header    = $key;
                                                $odd->save();
                                            } else {
                                                $odd = Odd::find($matchOdd->id);
                                                $odd->cotacao   = 1;
                                                $odd->state     = $runners->runnerStatus;
                                                $odd->type      = "ao-vivo";
                                                $odd->header    = $key;
                                                $odd->save();
                                            }
                                        } else {
                                            if (isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                                //echo "Cadastra\n";
                                                $odd = Odd::create([
                                                    'match_id'                  => $match_id, //$match_id,
                                                    'event_id'                  => $event_id,
                                                    'mercado_name'              => 'Vencedor do Encontro',
                                                    'odd'                       => strtr($runners->result->type, $trnaslate),
                                                    'mercado_full_name'         => strtr($runners->result->type, $trnaslate),
                                                    'cotacao'                   => $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                                    'status'                    => 1,
                                                    'selectionId'               => $event_id . $runners->result->type . $markets->market->marketType,
                                                    'state'                     => $runners->runnerStatus,
                                                    'stateMarc'                 => $markets->market->marketStatus,
                                                    'order'                     => 1,
                                                    'header'                    => $key,
                                                    'type'                      => "ao-vivo"
                                                ]);
                                            } else {
                                                $odd = Odd::create([
                                                    'match_id'                  => $match_id, //$match_id,
                                                    'event_id'                  => $event_id,
                                                    'mercado_name'              => 'Vencedor do Encontro',
                                                    'odd'                       => strtr($runners->result->type, $trnaslate),
                                                    'mercado_full_name'         => strtr($runners->result->type, $trnaslate),
                                                    'cotacao'                   => 1,
                                                    'status'                    => 1,
                                                    'selectionId'               => $event_id . $runners->result->type . $markets->market->marketType,
                                                    'state'                     => $runners->runnerStatus,
                                                    'stateMarc'                 => $markets->market->marketStatus,
                                                    'order'                     => 1,
                                                    'header'                    => $key,
                                                    'type'                      => "ao-vivo"
                                                ]);
                                            }
                                        }
                                    }
                                } //End Vencedor do Encontro


                                //Ambas Equipes marcam 
                                if ($markets->market->marketType == "BOTH_TEAMS_TO_SCORE") {
                                    for ($i = 0; $i < count($markets->market->runners); $i++) {
                                        $odd = Odd::create([
                                            'match_id'                  =>  $match_id,
                                            'event_id'                  =>  $event_id,
                                            'mercado_name'              =>  'Ambas as equipes marcarão na partida',
                                            'odd'                       =>  str_replace('Yes', 'Ambas - Sim', str_replace('No', 'Ambas - Não', $markets->market->runners[$i]->runnerName)),
                                            'mercado_full_name'         =>  str_replace('Yes', 'Ambas - Sim', str_replace('No', 'Ambas - Não', $markets->market->runners[$i]->runnerName)),
                                            'cotacao'                   =>  0,
                                            'status'                    =>  0,
                                            'selectionId'               =>  $markets->market->runners[$i]->selectionId,
                                            'state'                     =>  $markets->market->runners[$i]->runnerStatus,
                                            'stateMarc'                 =>  $markets->market->marketStatus,
                                            'order'                     =>  2,
                                            'header'                    =>  $markets->market->runners[$i]->sortPriority,
                                            'type'                      =>  'ao-vivo'
                                        ]);
                                    }
                                    //Cotação
                                    for ($i = 0; $i < count($markets->runnerDetails); $i++) {

                                        $odd = Odd::where('event_id', $event_id)->where('mercado_name', 'Ambas as equipes marcarão na partida')->where('selectionId', $markets->runnerDetails[$i]->selectionId)->first();
                                        if ($odd) {
                                            $odd->cotacao = $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                            $odd->status = 1;
                                            $odd->save();
                                        }
                                        ////echo $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds." - ".$markets->runnerDetails[$i]->selectionId." - ".$markets->runnerDetails[$i]->runnerStatus."\n";
                                    }
                                } //Ambas Equipes marcam

                                //Total de gols na partida
                                // if(in_array($markets->market->marketType, $arrGolsMaisMenosPartida) ) {
                                //     // for($i = 0; $i < count($markets->market->runners); $i++) {
                                //     //     $odd = Odd::create([
                                //     //         'match_id'                  =>  $match_id,
                                //     //         'event_id'                  =>  $event_id,
                                //     //         'mercado_name'              =>  'Total de gols na partida',
                                //     //         'odd'                       =>  strtr($markets->market->runners[$i]->runnerName, $trnaslate),
                                //     //         'mercado_full_name'         => strtr($markets->market->runners[$i]->runnerName, $trnaslate),
                                //     //         'cotacao'                   =>  0,
                                //     //         'status'                    =>  0,
                                //     //         'selectionId'               =>  $markets->market->runners[$i]->selectionId,
                                //     //         'state'                     =>  $markets->market->runners[$i]->runnerStatus,
                                //     //         'stateMarc'                 =>  $markets->market->marketStatus,
                                //     //         'order'                     =>  3,
                                //     //         'goals'                     =>  null,
                                //     //         'header'                    =>  $markets->market->runners[$i]->sortPriority,
                                //     //         'type'                      =>  'ao-vivo'
                                //     //     ]);
                                //     // }
                                //         foreach($markets->market->runners as $key => $runners) {

                                //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //             // //echo "Atualiza\n";
                                //             if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                 // $odd = Odd::find($matchOdd->id);
                                //                 // $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                 // $odd->state = $runners->runnerStatus;
                                //                 // $odd->type = "ao-vivo";
                                //                 // $odd->save();
                                //             }else {
                                //                 // $odd = Odd::find($matchOdd->id);
                                //                 // $odd->cotacao = 1;
                                //                 // $odd->state = $runners->runnerStatus;
                                //                 // $odd->type = "ao-vivo";
                                //                 // $odd->save();
                                //             }
                                //             } else {
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     // //echo "Cadastra\n";
                                //                         $odd = Odd::create([
                                //                             'match_id'                  => $match_id,//$match_id,
                                //                             'event_id'                  => $event_id, 
                                //                             'mercado_name'              => 'Total de gols na partida',
                                //                             'odd'                       => strtr($runners->runnerName, $trnaslate),
                                //                             'mercado_full_name'         => strtr($runners->runnerName, $trnaslate),
                                //                             'cotacao'                   => 0,
                                //                             'status'                    => 1,
                                //                             'selectionId'               => $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                             'state'                     => $runners->runnerStatus,
                                //                             'stateMarc'                 => $markets->market->marketStatus,
                                //                             'order'                     => 3,
                                //                             'type'                      => "ao-vivo",
                                //                             'goals'                     => null,
                                //                             'status'                    => 1,
                                //                             'header'                    => $runners->runnerName,
                                //                             ]);
                                //                     }
                                //                 }


                                //         }                        

                                // }//Total de gols na partida







                                //Chance Dupla
                                if ($markets->market->marketType == "DOUBLE_CHANCE") {
                                    $translate_dupla = [
                                        '1' => "Casa ou Empate",
                                        '2' => "Empate ou Fora",
                                        '3' => "Casa ou Fora"
                                    ];
                                    for ($i = 0; $i < count($markets->market->runners); $i++) {
                                        $odd = Odd::create([
                                            'match_id'                  =>  $match_id,
                                            'event_id'                  =>  $event_id,
                                            'mercado_name'              =>  'Chance Dupla',
                                            'odd'                       =>  strtr($markets->market->runners[$i]->sortPriority, $translate_dupla),
                                            'mercado_full_name'         =>  strtr($markets->market->runners[$i]->sortPriority, $translate_dupla),
                                            'cotacao'                   =>  0,
                                            'status'                    =>  0,
                                            'selectionId'               =>  $markets->market->runners[$i]->selectionId,
                                            'state'                     =>  $markets->market->runners[$i]->runnerStatus,
                                            'stateMarc'                 =>  $markets->market->marketStatus,
                                            'order'                     =>  3,
                                            'header'                    =>  $markets->market->runners[$i]->sortPriority,
                                            'type'                      =>  'ao-vivo'
                                        ]);
                                    }
                                    //Cotação
                                    for ($i = 0; $i < count($markets->runnerDetails); $i++) {

                                        $odd = Odd::where('event_id', $event_id)->where('mercado_name', 'Chance Dupla')->where('selectionId', $markets->runnerDetails[$i]->selectionId)->first();
                                        if ($odd) {
                                            $odd->cotacao = $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                            $odd->status = 1;
                                            $odd->save();
                                        }
                                        ////echo $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds." - ".$markets->runnerDetails[$i]->selectionId." - ".$markets->runnerDetails[$i]->runnerStatus."\n";
                                    }
                                } //end Chance Dupla

                                //     //Vencedo do Encontro e Ambas marcam
                                //     if($markets->market->marketType == "MATCH_ODDS_AND_BOTH_TEAMS_TO_SCORE") {

                                //         $event     = explode(" v ", $results->event->name);
                                //         $home      = $event[0];
                                //         $away      = $event[1]; 
                                //         $arrFirstTime = 
                                //         [
                                //             $home       => 'Casa',
                                //             'The Draw'  => 'Empate',
                                //             'Draw'      => 'Empate',
                                //             $away       => 'Fora',
                                //         ];
                                //         //echo "Resultado Final e Ambas as Equipas Marcam\n";

                                //             foreach($markets->market->runners as $key => $runners) {

                                //                 if($runners->sortPriority == 1) {
                                //                     $name = "Casa - (Ambas - Sim)";
                                //                 }
                                //                 if($runners->sortPriority == 2) {
                                //                     $name = "Empate - (Ambas - Sim)";
                                //                 }
                                //                 if($runners->sortPriority == 3) {
                                //                     $name = "Fora - (Ambas - Sim)";
                                //                 }



                                //                 if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                     ////echo "Atualiza Handicap Tempo Completo\n";
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                         $odd->odd = $name;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }else {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = 1;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }
                                //                 } else {
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     // //echo "Cadastra Handicap Tempo Completo\n";
                                //                             $odd = Odd::create([
                                //                                 'match_id'                  =>  $match_id,
                                //                                 'event_id'                  =>  $event_id,
                                //                                 'mercado_name'              =>  'Resultado Final e Ambas as Equipas Marcam',
                                //                                 'odd'                       =>  $name,
                                //                                 'mercado_full_name'         =>  $name,
                                //                                 'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                 'status'                    =>  1,
                                //                                 'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                 'state'                     =>  $runners->runnerStatus,
                                //                                 'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                 'order'                     =>  5,
                                //                                 'type'                      =>  "ao-vivo"
                                //                             ]);
                                //                     }
                                //                 }


                                //             }                        

                                //     }//Ambas Equipes marcam 




                                //     $arrFirstTime = 
                                //     [
                                //         $home       => 'Casa',
                                //         'The Draw'  => 'Empate',
                                //         'Draw'      => 'Empate',
                                //         $away       => 'Fora',
                                //     ];
                                if ($markets->market->marketType == "HALF_TIME") {
                                    // //echo "Vencedor do Encontro (1T)\n";

                                    //             foreach($markets->market->runners as $key => $runners) {


                                    //                 if($runners->sortPriority == 1) {
                                    //                     $name = "Casa (1T)";
                                    //                 }
                                    //                 if($runners->sortPriority == 2) {
                                    //                     $name = "Empate (1T)";
                                    //                 }
                                    //                 if($runners->sortPriority == 3) {
                                    //                     $name = "Fora (1T)";
                                    //                 }



                                    //                 if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                    //                     ////echo "Atualiza\n";
                                    //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                    //                         $odd = Odd::find($matchOdd->id);
                                    //                         $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                    //                         $odd->state = $runners->runnerStatus;
                                    //                         $odd->type = "ao-vivo";
                                    //                         $odd->save();
                                    //                     }else {
                                    //                         $odd = Odd::find($matchOdd->id);
                                    //                         $odd->cotacao = 1;
                                    //                         $odd->state = $runners->runnerStatus;
                                    //                         $odd->type = "ao-vivo";
                                    //                         $odd->save();
                                    //                     }
                                    //                 } else {
                                    //                     ////echo "Cadastra\n";
                                    //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                    //                             $odd = Odd::create([
                                    //                                 'match_id'                  =>  $match_id,
                                    //                                 'event_id'                  =>  $event_id,
                                    //                                 'mercado_name'              =>  'Vencedor do Encontro (1T)',
                                    //                                 'odd'                       =>  $name,
                                    //                                 'mercado_full_name'         =>  $name,
                                    //                                 'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                    //                                 'status'                    =>  1,
                                    //                                 'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                    //                                 'state'                     =>  $runners->runnerStatus,
                                    //                                 'stateMarc'                 =>  $markets->market->marketStatus,
                                    //                                 'order'                     =>  6,
                                    //                                 'type'                      =>  "ao-vivo"
                                    //                             ]);
                                    //                     }
                                    //                 }

                                    //             }                        

                                } //End Vencedor do Encontro (1T)

                                //     //Total de gols na partida 1º tempo
                                //     if(in_array($markets->market->marketType, $arrGolsMaisMenosPrimeiroTempo) ) {

                                //             //echo "Total de Gols (1T)\n";

                                //         foreach($markets->market->runners as $key => $runners) {


                                //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                 ////echo "Atualiza\n";
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }else {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = 1;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }
                                //             } else {
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     ////echo "Cadastra\n";
                                //                     $odd = Odd::create([
                                //                         'match_id'              => $match_id,
                                //                         'event_id'              => $event_id,
                                //                         'mercado_name'          => 'Total de Gols (1T)',
                                //                         'odd'                   => strtr($runners->runnerName, $trnaslate)." (1T)",
                                //                         'mercado_full_name'     => strtr($runners->runnerName, $trnaslate)." (1T)",
                                //                         'cotacao'               => $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                         'status'                => 1,
                                //                         'header'                => $runners->runnerName,
                                //                         'selectionId'           => $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                         'state'                 => $runners->runnerStatus,
                                //                         'stateMarc'             => $markets->market->marketStatus,
                                //                         'order'                 => 7,
                                //                         'type'                  => "ao-vivo"
                                //                     ]);  
                                //                 }
                                //             }

                                //         }                        

                                //     }//Total de gols na partida 1º tempo


                                //     //Par ou Ímpar?
                                //     if($markets->market->marketType == "ODD_OR_EVEN") {
                                //         //echo "Par ou Ímpar?\n";


                                //         foreach($markets->market->runners as $key => $runners) {

                                //                 //echo str_replace('Odd', 'Ímpar', str_replace('Even', 'Par', $runners->runnerName))."\n";

                                //                 if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                     ////echo "Atualiza\n";
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }else {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = 1;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }
                                //                 } else {
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         ////echo "Cadastra\n";
                                //                         $odd = Odd::create([
                                //                             'match_id'              => $match_id,
                                //                             'event_id'              => $event_id,
                                //                             'mercado_name'          => 'Par ou Ímpar?',
                                //                             'odd'                   => str_replace('Odd', 'Ímpar', str_replace('Even', 'Par', $runners->runnerName)),
                                //                             'mercado_full_name'     => str_replace('Odd', 'Ímpar', str_replace('Even', 'Par', $runners->runnerName)),
                                //                             'cotacao'               => $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                             'status'                => 1,
                                //                             'selectionId'           => $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                             'state'                 => $runners->runnerStatus,
                                //                             'stateMarc'             => $markets->market->marketStatus,
                                //                             'order'                 => 8,
                                //                             'type'                  => "ao-vivo"
                                //                         ]);
                                //                     }

                                //                 }
                                //             }                        

                                //     }//End Par ou Ímpar?

                                //     //Array Vencedor Intervalo Vencedor Final
                                //     $arrVencedorHalfFull = 
                                //     [
                                //         0   => 'Casa - Casa',
                                //         1   => 'Casa - Empate',
                                //         2   => 'Casa - Fora',
                                //         3   => 'Empate - Casa',
                                //         4   => 'Empate - Empate',
                                //         5   => 'Empate - Fora',
                                //         6   => 'Fora - Casa',
                                //         7   => 'Fora - Empate',
                                //         8   => 'Fora - Fora',

                                //     ];

                                //     //Vencedor ao Intervalo | Vencedor Final
                                //     if($markets->market->marketType == "HALF_TIME_FULL_TIME") {
                                //         //echo "Vencedor ao Intervalo | Vencedor Final\n";

                                //         foreach($markets->market->runners as $key => $runners) {                       
                                //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                 ////echo "Atualiza\n";
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }else {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = 1;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }
                                //             } else {
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                 ////echo "Cadastra\n";
                                //                         $odd = Odd::create([
                                //                             'match_id'              => $match_id,
                                //                             'event_id'              => $event_id,
                                //                             'mercado_name'          => 'Vencedor ao Intervalo | Vencedor Final',
                                //                             'odd'                   => strtr($key, $arrVencedorHalfFull),
                                //                             'mercado_full_name'     => strtr($key, $arrVencedorHalfFull),
                                //                             'cotacao'               => $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                             'status'                => 1,
                                //                             'selectionId'           => $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                             'state'                 => $runners->runnerStatus,
                                //                             'stateMarc'             => $markets->market->marketStatus,
                                //                             'order'                 => 9,
                                //                             'type'                  => "ao-vivo"
                                //                         ]);
                                //                 }
                                //             }



                                //             }                        

                                //     }//End Vencedor ao Intervalo | Vencedor Final
                                //     $event     = explode(" v ", $results->event->name);
                                //     $home      = $event[0];
                                //     $away      = $event[1]; 

                                //     //Vencedor do Encontro (2T)
                                //     $arrSecondTime = 
                                //     [
                                //         $home       => 'Casa',
                                //         'The Draw'  => 'Empate',
                                //         $away       => 'Fora',
                                //         'Draw'      => 'Empate',
                                //         'No Goals'  => 'Sem Gols'
                                //     ];


                                //     if($markets->market->marketName == "Second-Half Result") {
                                //         //echo "Vencedor do Encontro (2T)"."\n";

                                //             foreach($markets->market->runners as $key => $runners) {

                                //                 if($runners->sortPriority == 1) {
                                //                     $name = "Casa (2T)";
                                //                 }
                                //                 if($runners->sortPriority == 2) {
                                //                     $name = "Empate (2T)";
                                //                 }
                                //                 if($runners->sortPriority == 3) {
                                //                     $name = "Fora (2T)";
                                //                 }

                                //                 if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                     ////echo "Atualiza\n";
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }else {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = 1;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }
                                //                 } else {
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     ////echo "Cadastra\n";
                                //                             $odd = Odd::create([
                                //                                 'match_id'                  =>  $match_id,
                                //                                 'event_id'                  =>  $event_id,
                                //                                 'mercado_name'              =>  'Vencedor do Encontro (2T)',
                                //                                 'odd'                       =>  $name,
                                //                                 'mercado_full_name'         =>  $name,
                                //                                 'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                 'status'                    =>  1,
                                //                                 'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                 'state'                     =>  $runners->runnerStatus,
                                //                                 'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                 'order'                     =>  10,
                                //                                 'type'                      =>  "ao-vivo"
                                //                             ]);
                                //                     }
                                //                 }


                                //             }                        

                                //     }//End Vencedor do Encontro (2T)

                                //             //Placar Exato Tempo Completo
                                if ($markets->market->marketType == "CORRECT_SCORE") {
                                    // //echo "Placar Exato Tempo Completo\n";


                                    //Odd   
                                    for ($i = 0; $i < count($markets->market->runners); $i++) {
                                        ////echo $i."\n";
                                        ////echo $markets->market->runners[$i]->runnerName." - ".$markets->market->runners[$i]->selectionId." - ".$markets->market->runners[$i]->runnerStatus."\n";
                                        $odd = Odd::create([
                                            'match_id'                  =>  $match_id,
                                            'event_id'                  =>  $event_id,
                                            'mercado_name'              =>  'Placar Exato Tempo Completo',
                                            'odd'                       =>  str_replace(' - ', '-', $markets->market->runners[$i]->runnerName),
                                            'mercado_full_name'         =>  str_replace(' - ', '-', $markets->market->runners[$i]->runnerName),
                                            'cotacao'                   =>  0,
                                            'status'                    =>  0,
                                            'selectionId'               =>  $markets->market->runners[$i]->selectionId,
                                            'state'                     =>  $markets->market->runners[$i]->runnerStatus,
                                            'stateMarc'                 =>  $markets->market->marketStatus,
                                            'order'                     =>  11,
                                            'type'                      =>  'ao-vivo'
                                        ]);
                                    }

                                    //Cotação
                                    for ($i = 0; $i < count($markets->runnerDetails); $i++) {
                                        ////echo $i."\n";

                                        $odd = Odd::where('event_id', $event_id)->where('mercado_name', 'Placar Exato Tempo Completo')->where('selectionId', $markets->runnerDetails[$i]->selectionId)->first();
                                        if ($odd) {
                                            $odd->cotacao = $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                            $odd->status = 1;
                                            $odd->save();
                                        }

                                        ////echo $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds." - ".$markets->runnerDetails[$i]->selectionId." - ".$markets->runnerDetails[$i]->runnerStatus."\n";
                                    }

                                    $delete = Odd::where('event_id', $event_id)->where('mercado_name', 'Placar Exato Tempo Completo')->where('status', 0)->delete();
                                } //End Placar Exato Tempo Completo

                                //Placar Exato (1T)
                                if ($markets->market->marketType == "HALF_TIME_SCORE") {
                                    ////echo "Placar Exato (1T)\n";
                                    //Odd   
                                    for ($i = 0; $i < count($markets->market->runners); $i++) {
                                        ////echo $i."\n";
                                        // //echo $markets->market->runners[$i]->runnerName." - ".$markets->market->runners[$i]->selectionId." - ".$markets->market->runners[$i]->runnerStatus."\n";
                                        $odd = Odd::create([
                                            'match_id'                  =>  $match_id,
                                            'event_id'                  =>  $event_id,
                                            'mercado_name'              =>  'Placar Exato (1T)',
                                            'odd'                       =>  str_replace(' - ', '-', $markets->market->runners[$i]->runnerName) . " (1T)",
                                            'mercado_full_name'         =>  str_replace(' - ', '-', $markets->market->runners[$i]->runnerName) . " (1T)",
                                            'cotacao'                   =>  0,
                                            'status'                    =>  0,
                                            'selectionId'               =>  $markets->market->runners[$i]->selectionId,
                                            'state'                     =>  $markets->market->runners[$i]->runnerStatus,
                                            'stateMarc'                 =>  $markets->market->marketStatus,
                                            'order'                     =>  12,
                                            'type'                      =>  'ao-vivo'
                                        ]);
                                    }

                                    //Cotação
                                    for ($i = 0; $i < count($markets->runnerDetails); $i++) {
                                        ////echo $i."\n";

                                        $odd = Odd::where('event_id', $event_id)->where('mercado_name', 'Placar Exato (1T)')->where('selectionId', $markets->runnerDetails[$i]->selectionId)->first();
                                        if ($odd) {
                                            $odd->cotacao = $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                            $odd->status = 1;
                                            $odd->save();
                                        }

                                        ////echo $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds." - ".$markets->runnerDetails[$i]->selectionId." - ".$markets->runnerDetails[$i]->runnerStatus."\n";
                                    }

                                    $delete = Odd::where('event_id', $event_id)->where('mercado_name', 'Placar Exato (1T)')->where('status', 0)->delete();
                                } //Placar Exato (1T)

                                //Placar Exato (2T)
                                if ($markets->market->marketType == "2ND_HALF_CORRECT_SCORE") {
                                    // //echo "Placar Exato (2T)\n";

                                    //Odd   
                                    for ($i = 0; $i < count($markets->market->runners); $i++) {
                                        ////echo $i."\n";
                                        ////echo $markets->market->runners[$i]->runnerName." - ".$markets->market->runners[$i]->selectionId." - ".$markets->market->runners[$i]->runnerStatus."\n";
                                        $odd = Odd::create([
                                            'match_id'                  =>  $match_id,
                                            'event_id'                  =>  $event_id,
                                            'mercado_name'              =>  'Placar Exato (2T)',
                                            'odd'                       =>  str_replace(' - ', '-', $markets->market->runners[$i]->runnerName) . " (2T)",
                                            'mercado_full_name'         =>  str_replace(' - ', '-', $markets->market->runners[$i]->runnerName) . " (2T)",
                                            'cotacao'                   =>  0,
                                            'status'                    =>  0,
                                            'selectionId'               =>  $markets->market->runners[$i]->selectionId,
                                            'state'                     =>  $markets->market->runners[$i]->runnerStatus,
                                            'stateMarc'                 =>  $markets->market->marketStatus,
                                            'order'                     =>  13,
                                            'type'                      =>  'ao-vivo'
                                        ]);
                                    }

                                    //Cotação
                                    for ($i = 0; $i < count($markets->runnerDetails); $i++) {
                                        ////echo $i."\n";

                                        $odd = Odd::where('event_id', $event_id)->where('mercado_name', 'Placar Exato (2T)')->where('selectionId', $markets->runnerDetails[$i]->selectionId)->first();
                                        if ($odd) {
                                            $odd->cotacao = $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                            $odd->status = 1;
                                            $odd->save();
                                        }

                                        ////echo $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds." - ".$markets->runnerDetails[$i]->selectionId." - ".$markets->runnerDetails[$i]->runnerStatus."\n";
                                    }

                                    $delete = Odd::where('event_id', $event_id)->where('mercado_name', 'Placar Exato (2T)')->where('status', 0)->delete();
                                } //End Placar Exato (2T)



                                //     //Empate Anula Aposta
                                //     if($markets->market->marketType == "DRAW_NO_BET") {
                                //         //echo "Empate Anula Aposta\n";


                                //         foreach($markets->market->runners as $key => $runners) {

                                //             if($runners->sortPriority == 1) {
                                //                 $name = "Casa - (Empate Anula Aposta)";
                                //             }

                                //             if($runners->sortPriority == 2) {
                                //                 $name = "Fora - (Empate Anula Aposta)";
                                //             }


                                //                 if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                     ////echo "Atualiza\n";
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }else {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = 1;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }
                                //                 } else {
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     ////echo "Cadastra\n";
                                //                             $odd = Odd::create([
                                //                                 'match_id'                  =>  $match_id,
                                //                                 'event_id'                  =>  $event_id,
                                //                                 'mercado_name'              =>  'Empate Anula Aposta',
                                //                                 'odd'                       =>  strtr($runners->runnerName, $arrFirstTime)." - (Empate Anula Aposta)",
                                //                                 'mercado_full_name'         =>  strtr($runners->runnerName, $arrFirstTime)." - (Empate Anula Aposta)",
                                //                                 'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                 'status'                    =>  1,
                                //                                 'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                 'state'                     =>  $runners->runnerStatus,
                                //                                 'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                 'order'                     =>  14,
                                //                                 'type'                      =>  "ao-vivo"
                                //                             ]);
                                //                     }
                                //                 }



                                //             }                        

                                //     }//End Empate Anula Aposta

                                //     // //Handicap Tempo Completo    
                                //     // $arrHandicapFulltime = 
                                //     // [
                                //     //     'Handicap Draw'   => 'Empate',
                                //     //     $home             => 'Casa',
                                //     //     $away             => 'Fora'    
                                //     // ];

                                //     // if($markets->market->marketType == "MATCH_HANDICAP_WITH_TIE") {
                                //     //     //echo "Handicap Tempo Completo\n";


                                //     //     foreach($markets->market->runners as $key => $runners) {

                                //     //             ////echo $runners->selectionId."\n";
                                //     //             if($runners->handicap > 0) {
                                //     //                 $name =  strtr($runners->runnerName, $arrHandicapFulltime).' +'.$runners->handicap."\n";
                                //     //             } else {
                                //     //                 $name =  strtr($runners->runnerName, $arrHandicapFulltime).' '.$runners->handicap."\n";
                                //     //             }


                                //     //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //     //                 //echo "Atualiza Handicap Tempo Completo\n";
                                //     //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //     //                     $odd = Odd::find($matchOdd->id);
                                //     //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //     //                     $odd->state = $runners->runnerStatus;
                                //     //                     $odd->type = "ao-vivo";
                                //     //                     $odd->save();
                                //     //                 }else {
                                //     //                     $odd = Odd::find($matchOdd->id);
                                //     //                     $odd->cotacao = 1;
                                //     //                     $odd->state = $runners->runnerStatus;
                                //     //                     $odd->type = "ao-vivo";
                                //     //                     $odd->save();
                                //     //                 }
                                //     //             } else {
                                //     //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //     //                 //echo "Cadastra Handicap Tempo Completo\n";
                                //     //                         $odd = Odd::create([
                                //     //                             'match_id'                  =>  $match_id,
                                //     //                             'event_id'                  =>  $event_id,
                                //     //                             'mercado_name'              =>  'Handicap Tempo Completo',
                                //     //                             'odd'                       =>  $name,
                                //     //                             'mercado_full_name'         =>  $name,
                                //     //                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //     //                             'status'                    =>  1,
                                //     //                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //     //                             'state'                     =>  $runners->runnerStatus,
                                //     //                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //     //                             'order'                     =>  15,
                                //     //                             'type'                      =>  "ao-vivo"
                                //     //                         ]);
                                //     //                 }
                                //     //             }

                                //     //     }

                                //     // }//End Handicap Tempo Completo



                                //     //Margem da vitória
                                //     if($markets->market->marketType == "WINNING_MARGIN") {
                                //         $arrMargin = 
                                //         [
                                //             $home." to win by exactly 1 goal"       => "Casa : 1",
                                //             $home." to win by exactly 2 goals"      => "Casa : 2",
                                //             $home." to win by exactly 3 goals"      => "Casa : 3",
                                //             $home." to win by 4 or more goals"      => "Casa : 4+",
                                //             "0-0 draw"                              => "Empate Sem Gols",
                                //             "Score Draw"                            => "Empate com Gols",
                                //             $away." to win by exactly 1 goal"       => "Fora : 1",
                                //             $away." to win by exactly 2 goals"      => "Fora : 2",
                                //             $away." to win by exactly 3 goals"      => "Fora : 3",
                                //             $away." to win by 4 or more goals"      => "Fora : 4+",

                                //         ];
                                //         //echo "Margem da vitória"."\n";

                                //             foreach($markets->market->runners as $key => $runners) {

                                //                 if($runners->sortPriority == 1) {
                                //                     $name = "Casa : 1";
                                //                 }
                                //                 if($runners->sortPriority == 2) {
                                //                     $name = "Casa : 2";
                                //                 }
                                //                 if($runners->sortPriority == 3) {
                                //                     $name = "Casa : 3";
                                //                 }
                                //                 if($runners->sortPriority == 4) {
                                //                     $name = "Casa : 4+";
                                //                 }
                                //                 if($runners->sortPriority == 5) {
                                //                     $name = "Empate Sem Gols";
                                //                 }
                                //                 if($runners->sortPriority == 6) {
                                //                     $name = "Empate com Gols";
                                //                 }
                                //                 if($runners->sortPriority == 7) {
                                //                     $name = "Fora : 1";
                                //                 }
                                //                 if($runners->sortPriority == 8) {
                                //                     $name = "Fora : 2";
                                //                 }
                                //                 if($runners->sortPriority == 9) {
                                //                     $name = "Fora : 3";
                                //                 }
                                //                 if($runners->sortPriority == 10) {
                                //                     $name = "Fora : 4+";
                                //                 }



                                //                 if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                     ////echo "Atualiza Handicap Tempo Completo\n";
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }else {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = 1;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }
                                //                 } else {
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     // //echo "Cadastra Handicap Tempo Completo\n";
                                //                             $odd = Odd::create([
                                //                                 'match_id'                  =>  $match_id,
                                //                                 'event_id'                  =>  $event_id,
                                //                                 'mercado_name'              =>  'Margem da vitória',
                                //                                 'odd'                       =>  $name,
                                //                                 'mercado_full_name'         =>  $name,
                                //                                 'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                 'status'                    =>  1,
                                //                                 'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                 'state'                     =>  $runners->runnerStatus,
                                //                                 'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                 'order'                     =>  16,
                                //                                 'type'                      =>  "ao-vivo"
                                //                             ]);
                                //                     }
                                //                 }

                                //             }                        

                                //     }//Margem da vitória

                                //     //Total Exato de Gols (Casa & Fora)
                                //     if($markets->market->marketType == "NUMBER_OF_TEAM_GOALS") {

                                //         //echo "Total Exato de Gols (Casa & Fora)"."\n";

                                //             foreach($markets->market->runners as $key => $runners) {

                                //                 if($runners->sortPriority == 1) {
                                //                     $name =  "Casa : (0)";
                                //                 }
                                //                 if($runners->sortPriority == 2) {
                                //                     $name = "Casa : (1)";
                                //                 }
                                //                 if($runners->sortPriority == 3) {
                                //                     $name = "Casa : (2)";
                                //                 }
                                //                 if($runners->sortPriority == 4) {
                                //                     $name = "Casa : (3)";
                                //                 }
                                //                 if($runners->sortPriority == 5) {
                                //                     $name = "Casa : (4+)";
                                //                 }
                                //                 if($runners->sortPriority == 6) {
                                //                     $name =  "Fora : (0)";
                                //                 }
                                //                 if($runners->sortPriority == 7) {
                                //                     $name =  "Fora : (1)";
                                //                 }
                                //                 if($runners->sortPriority == 8) {
                                //                     $name = "Fora : (2)";
                                //                 }
                                //                 if($runners->sortPriority == 9) {
                                //                     $name = "Fora : (3)";
                                //                 }
                                //                 if($runners->sortPriority == 10) {
                                //                     $name =  "Fora : (4+)";
                                //                 }

                                //                 if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                     ////echo "Atualiza Handicap Tempo Completo\n";
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }else {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = 1;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }
                                //                 } else {
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     // //echo "Cadastra Handicap Tempo Completo\n";
                                //                             $odd = Odd::create([
                                //                                 'match_id'                  =>  $match_id,
                                //                                 'event_id'                  =>  $event_id,
                                //                                 'mercado_name'              =>  'Total Exato de Gols (Casa & Fora)',
                                //                                 'odd'                       =>  $name,
                                //                                 'mercado_full_name'         =>  $name,
                                //                                 'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                 'status'                    =>  1,
                                //                                 'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                 'state'                     =>  $runners->runnerStatus,
                                //                                 'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                 'order'                     =>  17,
                                //                                 'type'                      =>  "ao-vivo"
                                //                             ]);
                                //                     }
                                //                 }

                                //             }                        

                                //     }//End Total Exato de Gols (Casa & Fora)

                                //     //Primeira Equipe a Marcar
                                //     if($markets->market->marketType == "GOAL_01") {
                                //         $arrFirstTime = 
                                //         [
                                //             $home       => 'Casa',
                                //             'The Draw'  => 'Empate',
                                //             $away       => 'Fora',
                                //             'Draw'      => 'Empate',
                                //             'No Goals'  => 'Sem Gols'
                                //         ];
                                //         //echo "Primeira Equipe a Marcar\n";

                                //         foreach($markets->market->runners as $key => $runners) {

                                //             if($runners->sortPriority == 1) {
                                //                 $name =  "Casa - (1º Gol)";
                                //             }
                                //             if($runners->sortPriority == 2) {
                                //                 $name =  "Fora - (1º Gol)";
                                //             }
                                //             if($runners->sortPriority == 3) {
                                //                 $name =  "Sem Gols";
                                //             }


                                //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                 ////echo "Atualiza Handicap Tempo Completo\n";
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }else {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = 1;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }
                                //             } else {
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                 // //echo "Cadastra Handicap Tempo Completo\n";
                                //                         $odd = Odd::create([
                                //                             'match_id'                  =>  $match_id,
                                //                             'event_id'                  =>  $event_id,
                                //                             'mercado_name'              =>  'Primeira Equipe a Marcar',
                                //                             'odd'                       =>  $name,
                                //                             'mercado_full_name'         =>  $name,
                                //                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                             'status'                    =>  1,
                                //                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                             'state'                     =>  $runners->runnerStatus,
                                //                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //                             'order'                     =>  18,
                                //                             'type'                      =>  "ao-vivo"
                                //                         ]);
                                //                 }
                                //             }

                                //         }                        

                                //     }//Primeira Equipe a Marcar


                                //     //Ambas Equipes Marcam (1T)
                                //     if($markets->market->marketType == "BOTH_TEAMS_TO_SCORE_FIRST_HALF") {

                                //         //echo "Ambas Equipes Marcam (1T)\n";

                                //             foreach($markets->market->runners as $key => $runners) {

                                //                 $name =  str_replace('Yes', 'Ambas - Sim (1T)', str_replace('No', 'Ambas - Não (1T)', $runners->runnerName));


                                //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                 ////echo "Atualiza Handicap Tempo Completo\n";
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }else {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = 1;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }
                                //             } else {
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                 // //echo "Cadastra Handicap Tempo Completo\n";
                                //                         $odd = Odd::create([
                                //                             'match_id'                  =>  $match_id,
                                //                             'event_id'                  =>  $event_id,
                                //                             'mercado_name'              =>  'Ambas Equipes Marcam (1T)',
                                //                             'odd'                       =>  $name,
                                //                             'mercado_full_name'         =>  $name,
                                //                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                             'status'                    =>  1,
                                //                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                             'state'                     =>  $runners->runnerStatus,
                                //                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //                             'order'                     =>  19,
                                //                             'type'                      =>  "ao-vivo"
                                //                         ]);
                                //                 }
                                //             }

                                //             }                        

                                //     }//End Ambas Equipes Marcam (1T)


                                //     //Ambas Equipes Marcam (2T)
                                //     if($markets->market->marketType == "BOTH_TEAMS_TO_SCORE_2ND_HALF") {

                                //         //echo "Ambas Equipes Marcam (2T)\n";

                                //             foreach($markets->market->runners as $key => $runners) {

                                //                 $name =  str_replace('Yes', 'Ambas - Sim (2T)', str_replace('No', 'Ambas - Não (2T)', $runners->runnerName));


                                //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                 ////echo "Atualiza Handicap Tempo Completo\n";
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }else {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = 1;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }
                                //             } else {
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                 // //echo "Cadastra Handicap Tempo Completo\n";
                                //                         $odd = Odd::create([
                                //                             'match_id'                  =>  $match_id,
                                //                             'event_id'                  =>  $event_id,
                                //                             'mercado_name'              =>  'Ambas Equipes Marcam (2T)',
                                //                             'odd'                       =>  $name,
                                //                             'mercado_full_name'         =>  $name,
                                //                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                             'status'                    =>  1,
                                //                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                             'state'                     =>  $runners->runnerStatus,
                                //                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //                             'order'                     =>  20,
                                //                             'type'                      =>  "ao-vivo"
                                //                         ]);
                                //                 }
                                //             }

                                //             }                        

                                //     }//End Ambas Equipes Marcam (2T)


                                //     //Ambas Equipes Marcam (2T)
                                //     if($markets->market->marketType == "BOTH_TEAMS_TO_SCORE_BOTH_HALVES") {

                                //         //echo "Ambas Equipes Marcam (1T & 2T)\n";

                                //             foreach($markets->market->runners as $key => $runners) {

                                //                 $name =  str_replace('Yes', 'Ambas - Sim (1T & 2T)', str_replace('No', 'Ambas - Não (1T & 2T)', $runners->runnerName));


                                //                 if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                     ////echo "Atualiza Handicap Tempo Completo\n";
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }else {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = 1;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }
                                //                 } else {
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     // //echo "Cadastra Handicap Tempo Completo\n";
                                //                             $odd = Odd::create([
                                //                                 'match_id'                  =>  $match_id,
                                //                                 'event_id'                  =>  $event_id,
                                //                                 'mercado_name'              =>  'Ambas Equipes Marcam (1T & 2T)',
                                //                                 'odd'                       =>  $name,
                                //                                 'mercado_full_name'         =>  $name,
                                //                                 'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                 'status'                    =>  1,
                                //                                 'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                 'state'                     =>  $runners->runnerStatus,
                                //                                 'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                 'order'                     =>  21,
                                //                                 'type'                      =>  "ao-vivo"
                                //                             ]);
                                //                     }
                                //                 }

                                //             }                        

                                //     }//End Ambas Equipes Marcam (2T)


                                //     //Total Exato de Gols (1T)
                                //     if($markets->market->marketType == "TEAM_FIRST_HALF_GOALS") {
                                //         //echo "Total Exato de Gols (1T)\n";


                                //                 foreach($markets->market->runners as $key => $runners) {

                                //                     if($runners->sortPriority == 1) {
                                //                         $name =  "Casa (1T) : (0)";
                                //                     }
                                //                     if($runners->sortPriority == 2) {
                                //                         $name =  "Casa (1T) : (1)";
                                //                     }
                                //                     if($runners->sortPriority == 3) {
                                //                         $name =  "Casa (1T) : (2+)";
                                //                     }
                                //                     if($runners->sortPriority == 4) {
                                //                         $name =  "Fora (1T) : (0)";
                                //                     }
                                //                     if($runners->sortPriority == 5) {
                                //                         $name =  "Fora (1T) : (1)";
                                //                     }
                                //                     if($runners->sortPriority == 6) {
                                //                         $name =  "Fora (1T) : (2+)";
                                //                     }



                                //                     if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                         ////echo "Atualiza Handicap Tempo Completo\n";
                                //                         if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                             $odd = Odd::find($matchOdd->id);
                                //                             $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                             $odd->state = $runners->runnerStatus;
                                //                             $odd->type = "ao-vivo";
                                //                             $odd->save();
                                //                         }else {
                                //                             $odd = Odd::find($matchOdd->id);
                                //                             $odd->cotacao = 1;
                                //                             $odd->state = $runners->runnerStatus;
                                //                             $odd->type = "ao-vivo";
                                //                             $odd->save();
                                //                         }
                                //                     } else {
                                //                         if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         // //echo "Cadastra Handicap Tempo Completo\n";
                                //                                 $odd = Odd::create([
                                //                                     'match_id'                  =>  $match_id,
                                //                                     'event_id'                  =>  $event_id,
                                //                                     'mercado_name'              =>  'Total Exato de Gols (1T)',
                                //                                     'odd'                       =>  $name,
                                //                                     'mercado_full_name'         =>  $name,
                                //                                     'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                     'status'                    =>  1,
                                //                                     'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                     'state'                     =>  $runners->runnerStatus,
                                //                                     'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                     'order'                     =>  22,
                                //                                     'type'                      =>  "ao-vivo"
                                //                                 ]);
                                //                         }
                                //                     }

                                //                 }                        

                                //         }//End Total Exato de Gols (1T)


                                //         //Total Exato de Gols (2T)
                                //         if($markets->market->marketType == "TEAM_SECOND_HALF_GOALS") {
                                //         //echo "Total Exato de Gols (2T)\n";


                                //                 foreach($markets->market->runners as $key => $runners) {


                                //                     if($runners->sortPriority == 1) {
                                //                         $name =  "Casa (2T) : (0)";
                                //                     }
                                //                     if($runners->sortPriority == 2) {
                                //                         $name =  "Casa (2T) : (1)";
                                //                     }
                                //                     if($runners->sortPriority == 3) {
                                //                         $name =  "Casa (2T) : (2+)";
                                //                     }
                                //                     if($runners->sortPriority == 4) {
                                //                         $name =  "Fora (2T) : (0)";
                                //                     }
                                //                     if($runners->sortPriority == 5) {
                                //                         $name =  "Fora (2T) : (1)";
                                //                     }
                                //                     if($runners->sortPriority == 6) {
                                //                         $name =  "Fora (2T) : (2+)";
                                //                     }



                                //                     if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                         ////echo "Atualiza Handicap Tempo Completo\n";
                                //                         if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                             $odd = Odd::find($matchOdd->id);
                                //                             $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                             $odd->state = $runners->runnerStatus;
                                //                             $odd->type = "ao-vivo";
                                //                             $odd->save();
                                //                         }else {
                                //                             $odd = Odd::find($matchOdd->id);
                                //                             $odd->cotacao = 1;
                                //                             $odd->state = $runners->runnerStatus;
                                //                             $odd->type = "ao-vivo";
                                //                             $odd->save();
                                //                         }
                                //                     } else {
                                //                         if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         // //echo "Cadastra Handicap Tempo Completo\n";
                                //                                 $odd = Odd::create([
                                //                                     'match_id'                  =>  $match_id,
                                //                                     'event_id'                  =>  $event_id,
                                //                                     'mercado_name'              =>  'Total Exato de Gols (2T)',
                                //                                     'odd'                       =>  $name,
                                //                                     'mercado_full_name'         =>  $name,
                                //                                     'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                     'status'                    =>  1,
                                //                                     'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                     'state'                     =>  $runners->runnerStatus,
                                //                                     'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                     'order'                     =>  23,
                                //                                     'type'                      =>  "ao-vivo"
                                //                                 ]);
                                //                         }
                                //                     }


                                //                 }                        

                                //         }//End Total Exato de Gols (2T)

                                //         //Chance Dupla (1T)
                                //         if($markets->market->marketType == "FIRST_HALF_DOUBLE_CHANCE") {
                                //             //echo "Chance Dupla (1T)\n";

                                //                 foreach($markets->market->runners as $key => $runners) {

                                //                     if($runners->sortPriority == 1) {
                                //                         $name = "Casa ou Empate (1T)";
                                //                     }
                                //                     if($runners->sortPriority == 2) {
                                //                         $name = "Empate ou Fora (1T)";
                                //                     }
                                //                     if($runners->sortPriority == 3) {
                                //                         $name = "Casa ou Fora (1T)";
                                //                     }


                                //                     if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                         ////echo "Atualiza Handicap Tempo Completo\n";
                                //                         if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                             $odd = Odd::find($matchOdd->id);
                                //                             $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                             $odd->state = $runners->runnerStatus;
                                //                             $odd->type = "ao-vivo";
                                //                             $odd->save();
                                //                         }else {
                                //                             $odd = Odd::find($matchOdd->id);
                                //                             $odd->cotacao = 1;
                                //                             $odd->state = $runners->runnerStatus;
                                //                             $odd->type = "ao-vivo";
                                //                             $odd->save();
                                //                         }
                                //                     } else {
                                //                         if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         // //echo "Cadastra Handicap Tempo Completo\n";
                                //                                 $odd = Odd::create([
                                //                                     'match_id'                  =>  $match_id,
                                //                                     'event_id'                  =>  $event_id,
                                //                                     'mercado_name'              =>  'Chance Dupla (1T)',
                                //                                     'odd'                       =>  $name,
                                //                                     'mercado_full_name'         =>  $name,
                                //                                     'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                     'status'                    =>  1,
                                //                                     'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                     'state'                     =>  $runners->runnerStatus,
                                //                                     'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                     'order'                     =>  24,
                                //                                     'type'                      =>  "ao-vivo"
                                //                                 ]);
                                //                         }
                                //                     }

                                //                 }                        

                                //             }//end Chance Dupla (1T)


                                //             // //Handicap (1T)
                                //             // $arrHandicapFulltime = 
                                //             // [
                                //             //     'Handicap Draw'   => 'Empate',
                                //             //     $home             => 'Casa',
                                //             //     $away             => 'Fora'    
                                //             // ];

                                //             // if($markets->market->marketType == "FIRST_HALF_HANDICAP_WITH_TIE") {
                                //             //     //echo "Handicap (1T)\n";


                                //             //     foreach($markets->market->runners as $key => $runners) {

                                //             //             if($runners->handicap > 0) {
                                //             //                 $name =  strtr($runners->runnerName, $arrHandicapFulltime).' +'.$runners->handicap." (1T)";
                                //             //             } else {
                                //             //                 $name =  strtr($runners->runnerName, $arrHandicapFulltime).' '.$runners->handicap." (1T)";
                                //             //             }


                                //             //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //             //                 ////echo "Atualiza Handicap Tempo Completo\n";
                                //             //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //             //                     $odd = Odd::find($matchOdd->id);
                                //             //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //             //                     $odd->state = $runners->runnerStatus;
                                //             //                     $odd->type = "ao-vivo";
                                //             //                     $odd->save();
                                //             //                 }else {
                                //             //                     $odd = Odd::find($matchOdd->id);
                                //             //                     $odd->cotacao = 1;
                                //             //                     $odd->state = $runners->runnerStatus;
                                //             //                     $odd->type = "ao-vivo";
                                //             //                     $odd->save();
                                //             //                 }
                                //             //             } else {
                                //             //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //             //                 // //echo "Cadastra Handicap Tempo Completo\n";
                                //             //                         $odd = Odd::create([
                                //             //                             'match_id'                  =>  $match_id,
                                //             //                             'event_id'                  =>  $event_id,
                                //             //                             'mercado_name'              =>  'Handicap (1T)',
                                //             //                             'odd'                       =>  $name,
                                //             //                             'mercado_full_name'         =>  $name,
                                //             //                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //             //                             'status'                    =>  1,
                                //             //                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //             //                             'state'                     =>  $runners->runnerStatus,
                                //             //                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //             //                             'order'                     =>  25,
                                //             //                             'type'                      =>  "ao-vivo"
                                //             //                         ]);
                                //             //                 }
                                //             //             }


                                //             //     }

                                //             // }//End Handicap Tempo Completo


                                //             // //Handicap (2T)
                                //             // $arrHandicapFulltime = 
                                //             // [
                                //             //     'Handicap Draw'   => 'Empate',
                                //             //     $home             => 'Casa',
                                //             //     $away             => 'Fora'    
                                //             // ];

                                //             // if($markets->market->marketName == "Handicap Second Half") {
                                //             //     //echo "Handicap (2T)\n";


                                //             //     foreach($markets->market->runners as $key => $runners) {

                                //             //             if($runners->handicap > 0) {
                                //             //                 $name =  strtr($runners->runnerName, $arrHandicapFulltime).' +'.$runners->handicap." (2T)";
                                //             //             } else {
                                //             //                 $name =  strtr($runners->runnerName, $arrHandicapFulltime).' '.$runners->handicap." (2T)";
                                //             //             }


                                //             //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //             //                 ////echo "Atualiza Handicap Tempo Completo\n";
                                //             //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //             //                     $odd = Odd::find($matchOdd->id);
                                //             //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //             //                     $odd->state = $runners->runnerStatus;
                                //             //                     $odd->type = "ao-vivo";
                                //             //                     $odd->save();
                                //             //                 }else {
                                //             //                     $odd = Odd::find($matchOdd->id);
                                //             //                     $odd->cotacao = 1;
                                //             //                     $odd->state = $runners->runnerStatus;
                                //             //                     $odd->type = "ao-vivo";
                                //             //                     $odd->save();
                                //             //                 }
                                //             //             } else {
                                //             //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //             //                 // //echo "Cadastra Handicap Tempo Completo\n";
                                //             //                         $odd = Odd::create([
                                //             //                             'match_id'                  =>  $match_id,
                                //             //                             'event_id'                  =>  $event_id,
                                //             //                             'mercado_name'              =>  'Handicap (2T)',
                                //             //                             'odd'                       =>  $name,
                                //             //                             'mercado_full_name'         =>  $name,
                                //             //                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //             //                             'status'                    =>  1,
                                //             //                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //             //                             'state'                     =>  $runners->runnerStatus,
                                //             //                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //             //                             'order'                     =>  26,
                                //             //                             'type'                      =>  "ao-vivo"
                                //             //                         ]);
                                //             //                 }
                                //             //             }


                                //             //     }

                                //             // }//End Handicap Tempo Completo


                                //                 //Primeiro Jogador a Marcar?
                                //                 if($markets->market->marketType == "FIRST_GOAL_SCORER") {
                                //                     //echo "Marca 1º Gol\n";


                                //                     foreach($markets->market->runners as $key => $runners) {
                                //                             ////echo str_replace('Odd', 'Ímpar', str_replace('Even', 'Par', $runners->runnerName))."\n";
                                //                             ////echo $runners->selectionId."\n";
                                //                             if($runners->runnerName == "No Goalscorer") {
                                //                                 $name = strtr($runners->runnerName, $trnaslate)."\n";
                                //                             } else {
                                //                                 $name = strtr($runners->runnerName, $trnaslate)." - Marca 1º Gol";
                                //                             }


                                //                             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                                 ////echo "Atualiza Handicap Tempo Completo\n";
                                //                                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                                     $odd = Odd::find($matchOdd->id);
                                //                                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                                     $odd->state = $runners->runnerStatus;
                                //                                     $odd->type = "ao-vivo";
                                //                                     $odd->save();
                                //                                 }else {
                                //                                     $odd = Odd::find($matchOdd->id);
                                //                                     $odd->cotacao = 1;
                                //                                     $odd->state = $runners->runnerStatus;
                                //                                     $odd->type = "ao-vivo";
                                //                                     $odd->save();
                                //                                 }
                                //                             } else {
                                //                                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                                 // //echo "Cadastra Handicap Tempo Completo\n";
                                //                                         $odd = Odd::create([
                                //                                             'match_id'                  =>  $match_id,
                                //                                             'event_id'                  =>  $event_id,
                                //                                             'mercado_name'              =>  'Marca 1º Gol',
                                //                                             'odd'                       =>  $name,
                                //                                             'mercado_full_name'         =>  $name,
                                //                                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                             'status'                    =>  1,
                                //                                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                             'state'                     =>  $runners->runnerStatus,
                                //                                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                             'order'                     =>  27,
                                //                                             'type'                      =>  "ao-vivo"
                                //                                         ]);
                                //                                 }
                                //                             }


                                //                         }                        

                                //                 }//End Primeiro Jogador a Marcar?

                                //                 // //Último Jogador a Marcar
                                //                 // if($markets->market->marketType == "LAST_GOALSCORER") {
                                //                 //     //echo "Marca Último Gol\n";

                                //                 //     foreach($markets->market->runners as $key => $runners) {

                                //                 //             if($runners->runnerName == "No Goalscorer") {
                                //                 //             $name =   strtr($runners->runnerName, $trnaslate)."\n";
                                //                 //             } else {
                                //                 //             $name =   strtr($runners->runnerName, $trnaslate)." - Marca Último Gol\n";
                                //                 //             }

                                //                 //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                 //                 ////echo "Atualiza Handicap Tempo Completo\n";
                                //                 //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                 //                     $odd = Odd::find($matchOdd->id);
                                //                 //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                 //                     $odd->state = $runners->runnerStatus;
                                //                 //                     $odd->type = "ao-vivo";
                                //                 //                     $odd->save();
                                //                 //                 }else {
                                //                 //                     $odd = Odd::find($matchOdd->id);
                                //                 //                     $odd->cotacao = 1;
                                //                 //                     $odd->state = $runners->runnerStatus;
                                //                 //                     $odd->type = "ao-vivo";
                                //                 //                     $odd->save();
                                //                 //                 }
                                //                 //             } else {
                                //                 //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                 //                 // //echo "Cadastra Handicap Tempo Completo\n";
                                //                 //                         $odd = Odd::create([
                                //                 //                             'match_id'                  =>  $match_id,
                                //                 //                             'event_id'                  =>  $event_id,
                                //                 //                             'mercado_name'              =>  'Marca Último Gol',
                                //                 //                             'odd'                       =>  $name,
                                //                 //                             'mercado_full_name'         =>  $name,
                                //                 //                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                 //                             'status'                    =>  1,
                                //                 //                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                 //                             'state'                     =>  $runners->runnerStatus,
                                //                 //                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //                 //                             'order'                     =>  28,
                                //                 //                             'type'                      =>  "ao-vivo"
                                //                 //                         ]);
                                //                 //                 }
                                //                 //             }
                                //                 //         }                        

                                //                 // }//End Último Jogador a Marcar?

                                //                 //Marca Gol na Partida
                                //                 if($markets->market->marketType == "TO_SCORE") {
                                //                     //echo "Marca Gol na Partida\n";


                                //                     foreach($markets->market->runners as $key => $runners) {

                                //                             if($runners->runnerName == "No Goalscorer") {
                                //                             $name =  strtr($runners->runnerName, $trnaslate);
                                //                             } else {
                                //                             $name =  strtr($runners->runnerName, $trnaslate)." - Marca Gol na Partida\n";
                                //                             }

                                //                             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                                 ////echo "Atualiza Handicap Tempo Completo\n";
                                //                                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                                     $odd = Odd::find($matchOdd->id);
                                //                                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                                     $odd->state = $runners->runnerStatus;
                                //                                     $odd->type = "ao-vivo";
                                //                                     $odd->save();
                                //                                 }else {
                                //                                     $odd = Odd::find($matchOdd->id);
                                //                                     $odd->cotacao = 1;
                                //                                     $odd->state = $runners->runnerStatus;
                                //                                     $odd->type = "ao-vivo";
                                //                                     $odd->save();
                                //                                 }
                                //                             } else {
                                //                                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                                 // //echo "Cadastra Handicap Tempo Completo\n";
                                //                                         $odd = Odd::create([
                                //                                             'match_id'                  =>  $match_id,
                                //                                             'event_id'                  =>  $event_id,
                                //                                             'mercado_name'              =>  'Marca Gol na Partida',
                                //                                             'odd'                       =>  $name,
                                //                                             'mercado_full_name'         =>  $name,
                                //                                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                             'status'                    =>  1,
                                //                                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                             'state'                     =>  $runners->runnerStatus,
                                //                                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                             'order'                     =>  29,
                                //                                             'type'                      =>  "ao-vivo"
                                //                                         ]);
                                //                                 }
                                //                             }

                                //                         }

                                //                 }//End Marca Gol na Partida

                            } //End Markets


                        }
                    } else {
                        $event = Match::find($partida->id);
                        $event->delete();
                        //echo "Deleteda\n";
                    }
                } //End Foreach Global

            } //End If Global

        }

        // }
        // sleep(50);

    }

    public function loadOddVivo($idPartidas)
    {
        //while(1) {
        $trnaslate = array(
            'HOME'              => 'Casa',
            'The Draw'          => 'Empate',
            'DRAW'              => 'Empate',
            'AWAY'              => 'Fora',
            'Over '             => 'Mais de ',
            ' Goals'            => '',
            'Under '            => 'Menos de ',
            '1st Half Over '    => 'Mais de ',
            '1st Half Under '   => 'Menos de ',
            'No Goalscorer'     => 'Sem Gols',
            '1'                 => 'Casa',
            '2'                 => 'Empate',
            '3'                 => 'Fora',
        );

        $arrGolsMaisMenosPartida = [
            'OVER_UNDER_05',
            'OVER_UNDER_15',
            'OVER_UNDER_25',
            'OVER_UNDER_35',
            'OVER_UNDER_45',
            'OVER_UNDER_55',
            'OVER_UNDER_65',
            'OVER_UNDER_75',
            'OVER_UNDER_85',
            'OVER_UNDER_95',
        ];

        $arrGolsMaisMenosPrimeiroTempo = [
            'OVER_UNDER_HALF_TIME_05',
            'OVER_UNDER_HALF_TIME_15',
            'OVER_UNDER_HALF_TIME_25',
            'OVER_UNDER_HALF_TIME_35',
            'OVER_UNDER_HALF_TIME_45',
            'OVER_UNDER_HALF_TIME_55',
            'OVER_UNDER_HALF_TIME_65',
            'OVER_UNDER_HALF_TIME_75',
            'OVER_UNDER_HALF_TIME_85',
            'OVER_UNDER_HALF_TIME_95',
        ];


        // $matchs = Match::select('id', 'event_id', 'date')
        //             ->where('event_id' , $idPartida)
        //             ->get();

        $num = 0;
        $arrays = 0;
        $arrMatchs = array();
        $arrMatchs[0] = [];
        foreach ($idPartidas as $match) {
            if ($num > 0) {
                $arrays++;
                $arrMatchs[$arrays] = array();
                $num = 0;
            }
            array_push($arrMatchs[$arrays], $match['idEvent']);

            $num++;
        }
        foreach ($arrMatchs as $matchArray) {
            $url =  'https://api.betsapi.com/v1/betfair/sb/event?token=' . env('TOKEN') . '&event_id=';

            $size = count($matchArray);
            $tmp = 0;
            foreach ($matchArray as $match) {
                if ($tmp + 1 == $size) {
                    $url = $url . $match;
                } else {
                    $url = $url . $match . ",";
                }
                $tmp++;
            }



            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($ch);
            if ($data === false) {
                $info = curl_getinfo($ch);
                curl_close($ch);
                die('error occured during curl exec. Additioanl info: ' . var_export($info));
            }
            curl_close($ch);
            $data = json_decode($data);

            if (isset($data->success) && isset($data->results)) {
                foreach ($data->results as $results) {
                    // dd($results->updated_at);
                    ////echo $results->event->name."\n";
                    $event     = explode(" v ", $results->event->name);
                    $home      = $event[0];
                    $away      = $event[1];
                    $partida   = Match::where('event_id', $results->event->eventId)->first();

                    $match_id  = $partida->id;
                    $event_id  = $partida->event_id;
                    //excluir o mercaddo
                    $mercados = Odd::where('match_id', $partida->id)->delete();
                    $alters =  BlockOddMatch::where('odd_id', $event_id)->get();
                    if (count($alters) > 0) {
                        foreach ($alters as $alter) {
                            $alt = BlockOddMatch::find($alter->id);
                            $alt->delete();
                        }
                    }

                    if (isset($results->timeline)) {
                        ////echo "Tudo de Boa\n";


                        if (isset($results->timeline->matchStatus) && $results->timeline->matchStatus ==  "Finished") {
                            $event = Match::where('event_id', $results->timeline->eventId)->first();
                            $event->time_status = 3;
                            $event->visible = 'Não';
                            $event->score = $results->timeline->score->home->score . " - " . $results->timeline->score->away->score;
                            $event->time = $results->timeline->elapsedRegularTime;
                            //Score primeito tempo
                            if ($results->timeline->score->home->halfTimeScore != "") {
                                $event->halfTimeScoreHome = $results->timeline->score->home->halfTimeScore;
                            }
                            if ($results->timeline->score->away->halfTimeScore != "") {
                                $event->halfTimeScoreAway = $results->timeline->score->away->halfTimeScore;
                            }
                            //Score segundo tempo
                            if ($results->timeline->score->home->halfTimeScore != "") {
                                $event->fullTimeScoreHome = $results->timeline->score->home->score - $results->timeline->score->home->halfTimeScore;
                            }
                            if ($results->timeline->score->away->halfTimeScore != "") {
                                $event->fullTimeScoreAway = $results->timeline->score->away->score - $results->timeline->score->away->halfTimeScore;
                            }
                            //Quantidade de escanteios
                            //Casa
                            $event->numberOfCornersHome = $results->timeline->score->home->numberOfCorners;
                            //Fora
                            $event->numberOfCornersAway = $results->timeline->score->away->numberOfCorners;
                            $event->numberOfYellowCardsHome = $results->timeline->score->home->numberOfYellowCards;
                            $event->numberOfYellowCardsAway = $results->timeline->score->away->numberOfYellowCards;
                            $event->numberOfRedCardsHome    = $results->timeline->score->home->numberOfRedCards;
                            $event->numberOfRedCardsAway    = $results->timeline->score->away->numberOfRedCards;
                            $event->save();
                        } else {
                            $event = Match::where('event_id', $results->timeline->eventId)->first();
                            $event->score = $results->timeline->score->home->score . " - " . $results->timeline->score->away->score;
                            $event->time = $results->timeline->elapsedRegularTime;

                            if (isset($results->timeline->matchStatus) == "KickOff") {
                                $event->halfTimeScoreHome = $results->timeline->score->home->score;
                                $event->halfTimeScoreAway = $results->timeline->score->away->score;
                                $event->fullTimeScoreHome = 0;
                                $event->fullTimeScoreAway = 0;
                            }
                            if (isset($results->timeline->matchStatus) == "SecondHalfKickOff") {
                                // $event->fullTimeScoreHome = $results->timeline->score->home->score - $results->timeline->score->home->halfTimeScore;
                                // $event->fullTimeScoreAway = $results->timeline->score->away->score - $results->timeline->score->away->halfTimeScore;
                            }
                            //Score primeito tempo
                            if ($results->timeline->score->home->halfTimeScore != "") {
                                $event->halfTimeScoreHome = $results->timeline->score->home->halfTimeScore;
                            }
                            if ($results->timeline->score->away->halfTimeScore != "") {
                                $event->halfTimeScoreAway = $results->timeline->score->away->halfTimeScore;
                            }
                            //Score segundo tempo
                            if ($results->timeline->score->home->halfTimeScore != "") {
                                $event->fullTimeScoreHome = $results->timeline->score->home->score - $results->timeline->score->home->halfTimeScore;
                            }
                            if ($results->timeline->score->away->halfTimeScore != "") {
                                $event->fullTimeScoreAway = $results->timeline->score->away->score - $results->timeline->score->away->halfTimeScore;
                            }

                            //Quantidade de escanteios
                            //Casa
                            $event->numberOfCornersHome = $results->timeline->score->home->numberOfCorners;
                            //Fora
                            $event->numberOfCornersAway = $results->timeline->score->away->numberOfCorners;
                            //Cartões
                            $event->numberOfYellowCardsHome = $results->timeline->score->home->numberOfYellowCards;
                            $event->numberOfYellowCardsAway = $results->timeline->score->away->numberOfYellowCards;
                            $event->numberOfRedCardsHome    = $results->timeline->score->home->numberOfRedCards;
                            $event->numberOfRedCardsAway    = $results->timeline->score->away->numberOfRedCards;

                            $event->save();
                            //$mercados = Odd::where('match_id', $match_id )->get();

                            if (count($results->markets) == 0) {
                                $mercados = Odd::where('match_id', $match_id)->delete();
                            }
                            $mercados = Odd::where('match_id', $partida->id)->delete();
                            //Markets
                            foreach ($results->markets as $markets) {
                                $event     = explode(" v ", $results->event->name);
                                $home      = $event[0];
                                $away      = $event[1];

                                //Vencedor do Encontro
                                if ($markets->market->marketType == "MATCH_ODDS") {

                                    foreach ($markets->market->runners as $key => $runners) {

                                        if ($matchOdd = Odd::where('selectionId', $event_id . $runners->result->type . $markets->market->marketType)->where('match_id', $match_id)->first()) {
                                            //echo "Atualiza\n";
                                            if (isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                                $odd = Odd::find($matchOdd->id);
                                                $odd->cotacao   = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                                $odd->state     = $runners->runnerStatus;
                                                $odd->type      = "ao-vivo";
                                                $odd->header    = $key;
                                                $odd->save();
                                            } else {
                                                $odd = Odd::find($matchOdd->id);
                                                $odd->cotacao   = 1;
                                                $odd->state     = $runners->runnerStatus;
                                                $odd->type      = "ao-vivo";
                                                $odd->header    = $key;
                                                $odd->save();
                                            }
                                        } else {
                                            if (isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                                //echo "Cadastra\n";
                                                $odd = Odd::create([
                                                    'match_id'                  => $match_id, //$match_id,
                                                    'event_id'                  => $event_id,
                                                    'mercado_name'              => 'Vencedor do Encontro',
                                                    'odd'                       => strtr($runners->result->type, $trnaslate),
                                                    'mercado_full_name'         => strtr($runners->result->type, $trnaslate),
                                                    'cotacao'                   => $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                                    'status'                    => 1,
                                                    'selectionId'               => $event_id . $runners->result->type . $markets->market->marketType,
                                                    'state'                     => $runners->runnerStatus,
                                                    'stateMarc'                 => $markets->market->marketStatus,
                                                    'order'                     => 1,
                                                    'header'                    => $key,
                                                    'type'                      => "ao-vivo"
                                                ]);
                                            } else {
                                                $odd = Odd::create([
                                                    'match_id'                  => $match_id, //$match_id,
                                                    'event_id'                  => $event_id,
                                                    'mercado_name'              => 'Vencedor do Encontro',
                                                    'odd'                       => strtr($runners->result->type, $trnaslate),
                                                    'mercado_full_name'         => strtr($runners->result->type, $trnaslate),
                                                    'cotacao'                   => 1,
                                                    'status'                    => 1,
                                                    'selectionId'               => $event_id . $runners->result->type . $markets->market->marketType,
                                                    'state'                     => $runners->runnerStatus,
                                                    'stateMarc'                 => $markets->market->marketStatus,
                                                    'order'                     => 1,
                                                    'header'                    => $key,
                                                    'type'                      => "ao-vivo"
                                                ]);
                                            }
                                        }
                                    }
                                } //End Vencedor do Encontro


                                //Ambas Equipes marcam 
                                if ($markets->market->marketType == "BOTH_TEAMS_TO_SCORE") {
                                    for ($i = 0; $i < count($markets->market->runners); $i++) {
                                        $odd = Odd::create([
                                            'match_id'                  =>  $match_id,
                                            'event_id'                  =>  $event_id,
                                            'mercado_name'              =>  'Ambas as equipes marcarão na partida',
                                            'odd'                       =>  str_replace('Yes', 'Ambas - Sim', str_replace('No', 'Ambas - Não', $markets->market->runners[$i]->runnerName)),
                                            'mercado_full_name'         =>  str_replace('Yes', 'Ambas - Sim', str_replace('No', 'Ambas - Não', $markets->market->runners[$i]->runnerName)),
                                            'cotacao'                   =>  0,
                                            'status'                    =>  0,
                                            'selectionId'               =>  $markets->market->runners[$i]->selectionId,
                                            'state'                     =>  $markets->market->runners[$i]->runnerStatus,
                                            'stateMarc'                 =>  $markets->market->marketStatus,
                                            'order'                     =>  2,
                                            'header'                    =>  $markets->market->runners[$i]->sortPriority,
                                            'type'                      =>  'ao-vivo'
                                        ]);
                                    }
                                    //Cotação
                                    for ($i = 0; $i < count($markets->runnerDetails); $i++) {

                                        $odd = Odd::where('event_id', $event_id)->where('mercado_name', 'Ambas as equipes marcarão na partida')->where('selectionId', $markets->runnerDetails[$i]->selectionId)->first();
                                        if ($odd) {
                                            $odd->cotacao = $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                            $odd->status = 1;
                                            $odd->save();
                                        }
                                        ////echo $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds." - ".$markets->runnerDetails[$i]->selectionId." - ".$markets->runnerDetails[$i]->runnerStatus."\n";
                                    }
                                } //Ambas Equipes marcam

                                //Total de gols na partida
                                // if(in_array($markets->market->marketType, $arrGolsMaisMenosPartida) ) {
                                //     // for($i = 0; $i < count($markets->market->runners); $i++) {
                                //     //     $odd = Odd::create([
                                //     //         'match_id'                  =>  $match_id,
                                //     //         'event_id'                  =>  $event_id,
                                //     //         'mercado_name'              =>  'Total de gols na partida',
                                //     //         'odd'                       =>  strtr($markets->market->runners[$i]->runnerName, $trnaslate),
                                //     //         'mercado_full_name'         => strtr($markets->market->runners[$i]->runnerName, $trnaslate),
                                //     //         'cotacao'                   =>  0,
                                //     //         'status'                    =>  0,
                                //     //         'selectionId'               =>  $markets->market->runners[$i]->selectionId,
                                //     //         'state'                     =>  $markets->market->runners[$i]->runnerStatus,
                                //     //         'stateMarc'                 =>  $markets->market->marketStatus,
                                //     //         'order'                     =>  3,
                                //     //         'goals'                     =>  null,
                                //     //         'header'                    =>  $markets->market->runners[$i]->sortPriority,
                                //     //         'type'                      =>  'ao-vivo'
                                //     //     ]);
                                //     // }
                                //         foreach($markets->market->runners as $key => $runners) {

                                //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //             // //echo "Atualiza\n";
                                //             if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                 // $odd = Odd::find($matchOdd->id);
                                //                 // $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                 // $odd->state = $runners->runnerStatus;
                                //                 // $odd->type = "ao-vivo";
                                //                 // $odd->save();
                                //             }else {
                                //                 // $odd = Odd::find($matchOdd->id);
                                //                 // $odd->cotacao = 1;
                                //                 // $odd->state = $runners->runnerStatus;
                                //                 // $odd->type = "ao-vivo";
                                //                 // $odd->save();
                                //             }
                                //             } else {
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     // //echo "Cadastra\n";
                                //                         $odd = Odd::create([
                                //                             'match_id'                  => $match_id,//$match_id,
                                //                             'event_id'                  => $event_id, 
                                //                             'mercado_name'              => 'Total de gols na partida',
                                //                             'odd'                       => strtr($runners->runnerName, $trnaslate),
                                //                             'mercado_full_name'         => strtr($runners->runnerName, $trnaslate),
                                //                             'cotacao'                   => 0,
                                //                             'status'                    => 1,
                                //                             'selectionId'               => $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                             'state'                     => $runners->runnerStatus,
                                //                             'stateMarc'                 => $markets->market->marketStatus,
                                //                             'order'                     => 3,
                                //                             'type'                      => "ao-vivo",
                                //                             'goals'                     => null,
                                //                             'status'                    => 1,
                                //                             'header'                    => $runners->runnerName,
                                //                             ]);
                                //                     }
                                //                 }


                                //         }                        

                                // }//Total de gols na partida







                                //Chance Dupla
                                if ($markets->market->marketType == "DOUBLE_CHANCE") {
                                    $translate_dupla = [
                                        '1' => "Casa ou Empate",
                                        '2' => "Empate ou Fora",
                                        '3' => "Casa ou Fora"
                                    ];
                                    for ($i = 0; $i < count($markets->market->runners); $i++) {
                                        $odd = Odd::create([
                                            'match_id'                  =>  $match_id,
                                            'event_id'                  =>  $event_id,
                                            'mercado_name'              =>  'Chance Dupla',
                                            'odd'                       =>  strtr($markets->market->runners[$i]->sortPriority, $translate_dupla),
                                            'mercado_full_name'         =>  strtr($markets->market->runners[$i]->sortPriority, $translate_dupla),
                                            'cotacao'                   =>  0,
                                            'status'                    =>  0,
                                            'selectionId'               =>  $markets->market->runners[$i]->selectionId,
                                            'state'                     =>  $markets->market->runners[$i]->runnerStatus,
                                            'stateMarc'                 =>  $markets->market->marketStatus,
                                            'order'                     =>  3,
                                            'header'                    =>  $markets->market->runners[$i]->sortPriority,
                                            'type'                      =>  'ao-vivo'
                                        ]);
                                    }
                                    //Cotação
                                    for ($i = 0; $i < count($markets->runnerDetails); $i++) {

                                        $odd = Odd::where('event_id', $event_id)->where('mercado_name', 'Chance Dupla')->where('selectionId', $markets->runnerDetails[$i]->selectionId)->first();
                                        if ($odd) {
                                            $odd->cotacao = $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                            $odd->status = 1;
                                            $odd->save();
                                        }
                                        ////echo $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds." - ".$markets->runnerDetails[$i]->selectionId." - ".$markets->runnerDetails[$i]->runnerStatus."\n";
                                    }
                                } //end Chance Dupla

                                //     //Vencedo do Encontro e Ambas marcam
                                //     if($markets->market->marketType == "MATCH_ODDS_AND_BOTH_TEAMS_TO_SCORE") {

                                //         $event     = explode(" v ", $results->event->name);
                                //         $home      = $event[0];
                                //         $away      = $event[1]; 
                                //         $arrFirstTime = 
                                //         [
                                //             $home       => 'Casa',
                                //             'The Draw'  => 'Empate',
                                //             'Draw'      => 'Empate',
                                //             $away       => 'Fora',
                                //         ];
                                //         //echo "Resultado Final e Ambas as Equipas Marcam\n";

                                //             foreach($markets->market->runners as $key => $runners) {

                                //                 if($runners->sortPriority == 1) {
                                //                     $name = "Casa - (Ambas - Sim)";
                                //                 }
                                //                 if($runners->sortPriority == 2) {
                                //                     $name = "Empate - (Ambas - Sim)";
                                //                 }
                                //                 if($runners->sortPriority == 3) {
                                //                     $name = "Fora - (Ambas - Sim)";
                                //                 }



                                //                 if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                     ////echo "Atualiza Handicap Tempo Completo\n";
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                         $odd->odd = $name;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }else {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = 1;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }
                                //                 } else {
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     // //echo "Cadastra Handicap Tempo Completo\n";
                                //                             $odd = Odd::create([
                                //                                 'match_id'                  =>  $match_id,
                                //                                 'event_id'                  =>  $event_id,
                                //                                 'mercado_name'              =>  'Resultado Final e Ambas as Equipas Marcam',
                                //                                 'odd'                       =>  $name,
                                //                                 'mercado_full_name'         =>  $name,
                                //                                 'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                 'status'                    =>  1,
                                //                                 'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                 'state'                     =>  $runners->runnerStatus,
                                //                                 'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                 'order'                     =>  5,
                                //                                 'type'                      =>  "ao-vivo"
                                //                             ]);
                                //                     }
                                //                 }


                                //             }                        

                                //     }//Ambas Equipes marcam 




                                //     $arrFirstTime = 
                                //     [
                                //         $home       => 'Casa',
                                //         'The Draw'  => 'Empate',
                                //         'Draw'      => 'Empate',
                                //         $away       => 'Fora',
                                //     ];
                                if ($markets->market->marketType == "HALF_TIME") {
                                    // //echo "Vencedor do Encontro (1T)\n";

                                    //             foreach($markets->market->runners as $key => $runners) {


                                    //                 if($runners->sortPriority == 1) {
                                    //                     $name = "Casa (1T)";
                                    //                 }
                                    //                 if($runners->sortPriority == 2) {
                                    //                     $name = "Empate (1T)";
                                    //                 }
                                    //                 if($runners->sortPriority == 3) {
                                    //                     $name = "Fora (1T)";
                                    //                 }



                                    //                 if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                    //                     ////echo "Atualiza\n";
                                    //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                    //                         $odd = Odd::find($matchOdd->id);
                                    //                         $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                    //                         $odd->state = $runners->runnerStatus;
                                    //                         $odd->type = "ao-vivo";
                                    //                         $odd->save();
                                    //                     }else {
                                    //                         $odd = Odd::find($matchOdd->id);
                                    //                         $odd->cotacao = 1;
                                    //                         $odd->state = $runners->runnerStatus;
                                    //                         $odd->type = "ao-vivo";
                                    //                         $odd->save();
                                    //                     }
                                    //                 } else {
                                    //                     ////echo "Cadastra\n";
                                    //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                    //                             $odd = Odd::create([
                                    //                                 'match_id'                  =>  $match_id,
                                    //                                 'event_id'                  =>  $event_id,
                                    //                                 'mercado_name'              =>  'Vencedor do Encontro (1T)',
                                    //                                 'odd'                       =>  $name,
                                    //                                 'mercado_full_name'         =>  $name,
                                    //                                 'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                    //                                 'status'                    =>  1,
                                    //                                 'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                    //                                 'state'                     =>  $runners->runnerStatus,
                                    //                                 'stateMarc'                 =>  $markets->market->marketStatus,
                                    //                                 'order'                     =>  6,
                                    //                                 'type'                      =>  "ao-vivo"
                                    //                             ]);
                                    //                     }
                                    //                 }

                                    //             }                        

                                } //End Vencedor do Encontro (1T)

                                //     //Total de gols na partida 1º tempo
                                //     if(in_array($markets->market->marketType, $arrGolsMaisMenosPrimeiroTempo) ) {

                                //             //echo "Total de Gols (1T)\n";

                                //         foreach($markets->market->runners as $key => $runners) {


                                //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                 ////echo "Atualiza\n";
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }else {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = 1;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }
                                //             } else {
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     ////echo "Cadastra\n";
                                //                     $odd = Odd::create([
                                //                         'match_id'              => $match_id,
                                //                         'event_id'              => $event_id,
                                //                         'mercado_name'          => 'Total de Gols (1T)',
                                //                         'odd'                   => strtr($runners->runnerName, $trnaslate)." (1T)",
                                //                         'mercado_full_name'     => strtr($runners->runnerName, $trnaslate)." (1T)",
                                //                         'cotacao'               => $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                         'status'                => 1,
                                //                         'header'                => $runners->runnerName,
                                //                         'selectionId'           => $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                         'state'                 => $runners->runnerStatus,
                                //                         'stateMarc'             => $markets->market->marketStatus,
                                //                         'order'                 => 7,
                                //                         'type'                  => "ao-vivo"
                                //                     ]);  
                                //                 }
                                //             }

                                //         }                        

                                //     }//Total de gols na partida 1º tempo


                                //     //Par ou Ímpar?
                                //     if($markets->market->marketType == "ODD_OR_EVEN") {
                                //         //echo "Par ou Ímpar?\n";


                                //         foreach($markets->market->runners as $key => $runners) {

                                //                 //echo str_replace('Odd', 'Ímpar', str_replace('Even', 'Par', $runners->runnerName))."\n";

                                //                 if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                     ////echo "Atualiza\n";
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }else {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = 1;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }
                                //                 } else {
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         ////echo "Cadastra\n";
                                //                         $odd = Odd::create([
                                //                             'match_id'              => $match_id,
                                //                             'event_id'              => $event_id,
                                //                             'mercado_name'          => 'Par ou Ímpar?',
                                //                             'odd'                   => str_replace('Odd', 'Ímpar', str_replace('Even', 'Par', $runners->runnerName)),
                                //                             'mercado_full_name'     => str_replace('Odd', 'Ímpar', str_replace('Even', 'Par', $runners->runnerName)),
                                //                             'cotacao'               => $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                             'status'                => 1,
                                //                             'selectionId'           => $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                             'state'                 => $runners->runnerStatus,
                                //                             'stateMarc'             => $markets->market->marketStatus,
                                //                             'order'                 => 8,
                                //                             'type'                  => "ao-vivo"
                                //                         ]);
                                //                     }

                                //                 }
                                //             }                        

                                //     }//End Par ou Ímpar?

                                //     //Array Vencedor Intervalo Vencedor Final
                                //     $arrVencedorHalfFull = 
                                //     [
                                //         0   => 'Casa - Casa',
                                //         1   => 'Casa - Empate',
                                //         2   => 'Casa - Fora',
                                //         3   => 'Empate - Casa',
                                //         4   => 'Empate - Empate',
                                //         5   => 'Empate - Fora',
                                //         6   => 'Fora - Casa',
                                //         7   => 'Fora - Empate',
                                //         8   => 'Fora - Fora',

                                //     ];

                                //     //Vencedor ao Intervalo | Vencedor Final
                                //     if($markets->market->marketType == "HALF_TIME_FULL_TIME") {
                                //         //echo "Vencedor ao Intervalo | Vencedor Final\n";

                                //         foreach($markets->market->runners as $key => $runners) {                       
                                //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                 ////echo "Atualiza\n";
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }else {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = 1;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }
                                //             } else {
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                 ////echo "Cadastra\n";
                                //                         $odd = Odd::create([
                                //                             'match_id'              => $match_id,
                                //                             'event_id'              => $event_id,
                                //                             'mercado_name'          => 'Vencedor ao Intervalo | Vencedor Final',
                                //                             'odd'                   => strtr($key, $arrVencedorHalfFull),
                                //                             'mercado_full_name'     => strtr($key, $arrVencedorHalfFull),
                                //                             'cotacao'               => $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                             'status'                => 1,
                                //                             'selectionId'           => $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                             'state'                 => $runners->runnerStatus,
                                //                             'stateMarc'             => $markets->market->marketStatus,
                                //                             'order'                 => 9,
                                //                             'type'                  => "ao-vivo"
                                //                         ]);
                                //                 }
                                //             }



                                //             }                        

                                //     }//End Vencedor ao Intervalo | Vencedor Final
                                //     $event     = explode(" v ", $results->event->name);
                                //     $home      = $event[0];
                                //     $away      = $event[1]; 

                                //     //Vencedor do Encontro (2T)
                                //     $arrSecondTime = 
                                //     [
                                //         $home       => 'Casa',
                                //         'The Draw'  => 'Empate',
                                //         $away       => 'Fora',
                                //         'Draw'      => 'Empate',
                                //         'No Goals'  => 'Sem Gols'
                                //     ];


                                //     if($markets->market->marketName == "Second-Half Result") {
                                //         //echo "Vencedor do Encontro (2T)"."\n";

                                //             foreach($markets->market->runners as $key => $runners) {

                                //                 if($runners->sortPriority == 1) {
                                //                     $name = "Casa (2T)";
                                //                 }
                                //                 if($runners->sortPriority == 2) {
                                //                     $name = "Empate (2T)";
                                //                 }
                                //                 if($runners->sortPriority == 3) {
                                //                     $name = "Fora (2T)";
                                //                 }

                                //                 if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                     ////echo "Atualiza\n";
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }else {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = 1;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }
                                //                 } else {
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     ////echo "Cadastra\n";
                                //                             $odd = Odd::create([
                                //                                 'match_id'                  =>  $match_id,
                                //                                 'event_id'                  =>  $event_id,
                                //                                 'mercado_name'              =>  'Vencedor do Encontro (2T)',
                                //                                 'odd'                       =>  $name,
                                //                                 'mercado_full_name'         =>  $name,
                                //                                 'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                 'status'                    =>  1,
                                //                                 'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                 'state'                     =>  $runners->runnerStatus,
                                //                                 'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                 'order'                     =>  10,
                                //                                 'type'                      =>  "ao-vivo"
                                //                             ]);
                                //                     }
                                //                 }


                                //             }                        

                                //     }//End Vencedor do Encontro (2T)

                                //             //Placar Exato Tempo Completo
                                if ($markets->market->marketType == "CORRECT_SCORE") {
                                    // //echo "Placar Exato Tempo Completo\n";


                                    //Odd   
                                    for ($i = 0; $i < count($markets->market->runners); $i++) {
                                        ////echo $i."\n";
                                        ////echo $markets->market->runners[$i]->runnerName." - ".$markets->market->runners[$i]->selectionId." - ".$markets->market->runners[$i]->runnerStatus."\n";
                                        $odd = Odd::create([
                                            'match_id'                  =>  $match_id,
                                            'event_id'                  =>  $event_id,
                                            'mercado_name'              =>  'Placar Exato Tempo Completo',
                                            'odd'                       =>  str_replace(' - ', '-', $markets->market->runners[$i]->runnerName),
                                            'mercado_full_name'         =>  str_replace(' - ', '-', $markets->market->runners[$i]->runnerName),
                                            'cotacao'                   =>  0,
                                            'status'                    =>  0,
                                            'selectionId'               =>  $markets->market->runners[$i]->selectionId,
                                            'state'                     =>  $markets->market->runners[$i]->runnerStatus,
                                            'stateMarc'                 =>  $markets->market->marketStatus,
                                            'order'                     =>  11,
                                            'type'                      =>  'ao-vivo'
                                        ]);
                                    }

                                    //Cotação
                                    for ($i = 0; $i < count($markets->runnerDetails); $i++) {
                                        ////echo $i."\n";

                                        $odd = Odd::where('event_id', $event_id)->where('mercado_name', 'Placar Exato Tempo Completo')->where('selectionId', $markets->runnerDetails[$i]->selectionId)->first();
                                        if ($odd) {
                                            $odd->cotacao = $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                            $odd->status = 1;
                                            $odd->save();
                                        }

                                        ////echo $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds." - ".$markets->runnerDetails[$i]->selectionId." - ".$markets->runnerDetails[$i]->runnerStatus."\n";
                                    }

                                    $delete = Odd::where('event_id', $event_id)->where('mercado_name', 'Placar Exato Tempo Completo')->where('status', 0)->delete();
                                } //End Placar Exato Tempo Completo

                                //Placar Exato (1T)
                                if ($markets->market->marketType == "HALF_TIME_SCORE") {
                                    ////echo "Placar Exato (1T)\n";
                                    //Odd   
                                    for ($i = 0; $i < count($markets->market->runners); $i++) {
                                        ////echo $i."\n";
                                        // //echo $markets->market->runners[$i]->runnerName." - ".$markets->market->runners[$i]->selectionId." - ".$markets->market->runners[$i]->runnerStatus."\n";
                                        $odd = Odd::create([
                                            'match_id'                  =>  $match_id,
                                            'event_id'                  =>  $event_id,
                                            'mercado_name'              =>  'Placar Exato (1T)',
                                            'odd'                       =>  str_replace(' - ', '-', $markets->market->runners[$i]->runnerName) . " (1T)",
                                            'mercado_full_name'         =>  str_replace(' - ', '-', $markets->market->runners[$i]->runnerName) . " (1T)",
                                            'cotacao'                   =>  0,
                                            'status'                    =>  0,
                                            'selectionId'               =>  $markets->market->runners[$i]->selectionId,
                                            'state'                     =>  $markets->market->runners[$i]->runnerStatus,
                                            'stateMarc'                 =>  $markets->market->marketStatus,
                                            'order'                     =>  12,
                                            'type'                      =>  'ao-vivo'
                                        ]);
                                    }

                                    //Cotação
                                    for ($i = 0; $i < count($markets->runnerDetails); $i++) {
                                        ////echo $i."\n";

                                        $odd = Odd::where('event_id', $event_id)->where('mercado_name', 'Placar Exato (1T)')->where('selectionId', $markets->runnerDetails[$i]->selectionId)->first();
                                        if ($odd) {
                                            $odd->cotacao = $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                            $odd->status = 1;
                                            $odd->save();
                                        }

                                        ////echo $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds." - ".$markets->runnerDetails[$i]->selectionId." - ".$markets->runnerDetails[$i]->runnerStatus."\n";
                                    }

                                    $delete = Odd::where('event_id', $event_id)->where('mercado_name', 'Placar Exato (1T)')->where('status', 0)->delete();
                                } //Placar Exato (1T)

                                //Placar Exato (2T)
                                if ($markets->market->marketType == "2ND_HALF_CORRECT_SCORE") {
                                    // //echo "Placar Exato (2T)\n";

                                    //Odd   
                                    for ($i = 0; $i < count($markets->market->runners); $i++) {
                                        ////echo $i."\n";
                                        ////echo $markets->market->runners[$i]->runnerName." - ".$markets->market->runners[$i]->selectionId." - ".$markets->market->runners[$i]->runnerStatus."\n";
                                        $odd = Odd::create([
                                            'match_id'                  =>  $match_id,
                                            'event_id'                  =>  $event_id,
                                            'mercado_name'              =>  'Placar Exato (2T)',
                                            'odd'                       =>  str_replace(' - ', '-', $markets->market->runners[$i]->runnerName) . " (2T)",
                                            'mercado_full_name'         =>  str_replace(' - ', '-', $markets->market->runners[$i]->runnerName) . " (2T)",
                                            'cotacao'                   =>  0,
                                            'status'                    =>  0,
                                            'selectionId'               =>  $markets->market->runners[$i]->selectionId,
                                            'state'                     =>  $markets->market->runners[$i]->runnerStatus,
                                            'stateMarc'                 =>  $markets->market->marketStatus,
                                            'order'                     =>  13,
                                            'type'                      =>  'ao-vivo'
                                        ]);
                                    }

                                    //Cotação
                                    for ($i = 0; $i < count($markets->runnerDetails); $i++) {
                                        ////echo $i."\n";

                                        $odd = Odd::where('event_id', $event_id)->where('mercado_name', 'Placar Exato (2T)')->where('selectionId', $markets->runnerDetails[$i]->selectionId)->first();
                                        if ($odd) {
                                            $odd->cotacao = $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                            $odd->status = 1;
                                            $odd->save();
                                        }

                                        ////echo $markets->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds." - ".$markets->runnerDetails[$i]->selectionId." - ".$markets->runnerDetails[$i]->runnerStatus."\n";
                                    }

                                    $delete = Odd::where('event_id', $event_id)->where('mercado_name', 'Placar Exato (2T)')->where('status', 0)->delete();
                                } //End Placar Exato (2T)



                                //     //Empate Anula Aposta
                                //     if($markets->market->marketType == "DRAW_NO_BET") {
                                //         //echo "Empate Anula Aposta\n";


                                //         foreach($markets->market->runners as $key => $runners) {

                                //             if($runners->sortPriority == 1) {
                                //                 $name = "Casa - (Empate Anula Aposta)";
                                //             }

                                //             if($runners->sortPriority == 2) {
                                //                 $name = "Fora - (Empate Anula Aposta)";
                                //             }


                                //                 if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                     ////echo "Atualiza\n";
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }else {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = 1;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }
                                //                 } else {
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     ////echo "Cadastra\n";
                                //                             $odd = Odd::create([
                                //                                 'match_id'                  =>  $match_id,
                                //                                 'event_id'                  =>  $event_id,
                                //                                 'mercado_name'              =>  'Empate Anula Aposta',
                                //                                 'odd'                       =>  strtr($runners->runnerName, $arrFirstTime)." - (Empate Anula Aposta)",
                                //                                 'mercado_full_name'         =>  strtr($runners->runnerName, $arrFirstTime)." - (Empate Anula Aposta)",
                                //                                 'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                 'status'                    =>  1,
                                //                                 'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                 'state'                     =>  $runners->runnerStatus,
                                //                                 'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                 'order'                     =>  14,
                                //                                 'type'                      =>  "ao-vivo"
                                //                             ]);
                                //                     }
                                //                 }



                                //             }                        

                                //     }//End Empate Anula Aposta

                                //     // //Handicap Tempo Completo    
                                //     // $arrHandicapFulltime = 
                                //     // [
                                //     //     'Handicap Draw'   => 'Empate',
                                //     //     $home             => 'Casa',
                                //     //     $away             => 'Fora'    
                                //     // ];

                                //     // if($markets->market->marketType == "MATCH_HANDICAP_WITH_TIE") {
                                //     //     //echo "Handicap Tempo Completo\n";


                                //     //     foreach($markets->market->runners as $key => $runners) {

                                //     //             ////echo $runners->selectionId."\n";
                                //     //             if($runners->handicap > 0) {
                                //     //                 $name =  strtr($runners->runnerName, $arrHandicapFulltime).' +'.$runners->handicap."\n";
                                //     //             } else {
                                //     //                 $name =  strtr($runners->runnerName, $arrHandicapFulltime).' '.$runners->handicap."\n";
                                //     //             }


                                //     //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //     //                 //echo "Atualiza Handicap Tempo Completo\n";
                                //     //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //     //                     $odd = Odd::find($matchOdd->id);
                                //     //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //     //                     $odd->state = $runners->runnerStatus;
                                //     //                     $odd->type = "ao-vivo";
                                //     //                     $odd->save();
                                //     //                 }else {
                                //     //                     $odd = Odd::find($matchOdd->id);
                                //     //                     $odd->cotacao = 1;
                                //     //                     $odd->state = $runners->runnerStatus;
                                //     //                     $odd->type = "ao-vivo";
                                //     //                     $odd->save();
                                //     //                 }
                                //     //             } else {
                                //     //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //     //                 //echo "Cadastra Handicap Tempo Completo\n";
                                //     //                         $odd = Odd::create([
                                //     //                             'match_id'                  =>  $match_id,
                                //     //                             'event_id'                  =>  $event_id,
                                //     //                             'mercado_name'              =>  'Handicap Tempo Completo',
                                //     //                             'odd'                       =>  $name,
                                //     //                             'mercado_full_name'         =>  $name,
                                //     //                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //     //                             'status'                    =>  1,
                                //     //                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //     //                             'state'                     =>  $runners->runnerStatus,
                                //     //                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //     //                             'order'                     =>  15,
                                //     //                             'type'                      =>  "ao-vivo"
                                //     //                         ]);
                                //     //                 }
                                //     //             }

                                //     //     }

                                //     // }//End Handicap Tempo Completo



                                //     //Margem da vitória
                                //     if($markets->market->marketType == "WINNING_MARGIN") {
                                //         $arrMargin = 
                                //         [
                                //             $home." to win by exactly 1 goal"       => "Casa : 1",
                                //             $home." to win by exactly 2 goals"      => "Casa : 2",
                                //             $home." to win by exactly 3 goals"      => "Casa : 3",
                                //             $home." to win by 4 or more goals"      => "Casa : 4+",
                                //             "0-0 draw"                              => "Empate Sem Gols",
                                //             "Score Draw"                            => "Empate com Gols",
                                //             $away." to win by exactly 1 goal"       => "Fora : 1",
                                //             $away." to win by exactly 2 goals"      => "Fora : 2",
                                //             $away." to win by exactly 3 goals"      => "Fora : 3",
                                //             $away." to win by 4 or more goals"      => "Fora : 4+",

                                //         ];
                                //         //echo "Margem da vitória"."\n";

                                //             foreach($markets->market->runners as $key => $runners) {

                                //                 if($runners->sortPriority == 1) {
                                //                     $name = "Casa : 1";
                                //                 }
                                //                 if($runners->sortPriority == 2) {
                                //                     $name = "Casa : 2";
                                //                 }
                                //                 if($runners->sortPriority == 3) {
                                //                     $name = "Casa : 3";
                                //                 }
                                //                 if($runners->sortPriority == 4) {
                                //                     $name = "Casa : 4+";
                                //                 }
                                //                 if($runners->sortPriority == 5) {
                                //                     $name = "Empate Sem Gols";
                                //                 }
                                //                 if($runners->sortPriority == 6) {
                                //                     $name = "Empate com Gols";
                                //                 }
                                //                 if($runners->sortPriority == 7) {
                                //                     $name = "Fora : 1";
                                //                 }
                                //                 if($runners->sortPriority == 8) {
                                //                     $name = "Fora : 2";
                                //                 }
                                //                 if($runners->sortPriority == 9) {
                                //                     $name = "Fora : 3";
                                //                 }
                                //                 if($runners->sortPriority == 10) {
                                //                     $name = "Fora : 4+";
                                //                 }



                                //                 if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                     ////echo "Atualiza Handicap Tempo Completo\n";
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }else {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = 1;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }
                                //                 } else {
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     // //echo "Cadastra Handicap Tempo Completo\n";
                                //                             $odd = Odd::create([
                                //                                 'match_id'                  =>  $match_id,
                                //                                 'event_id'                  =>  $event_id,
                                //                                 'mercado_name'              =>  'Margem da vitória',
                                //                                 'odd'                       =>  $name,
                                //                                 'mercado_full_name'         =>  $name,
                                //                                 'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                 'status'                    =>  1,
                                //                                 'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                 'state'                     =>  $runners->runnerStatus,
                                //                                 'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                 'order'                     =>  16,
                                //                                 'type'                      =>  "ao-vivo"
                                //                             ]);
                                //                     }
                                //                 }

                                //             }                        

                                //     }//Margem da vitória

                                //     //Total Exato de Gols (Casa & Fora)
                                //     if($markets->market->marketType == "NUMBER_OF_TEAM_GOALS") {

                                //         //echo "Total Exato de Gols (Casa & Fora)"."\n";

                                //             foreach($markets->market->runners as $key => $runners) {

                                //                 if($runners->sortPriority == 1) {
                                //                     $name =  "Casa : (0)";
                                //                 }
                                //                 if($runners->sortPriority == 2) {
                                //                     $name = "Casa : (1)";
                                //                 }
                                //                 if($runners->sortPriority == 3) {
                                //                     $name = "Casa : (2)";
                                //                 }
                                //                 if($runners->sortPriority == 4) {
                                //                     $name = "Casa : (3)";
                                //                 }
                                //                 if($runners->sortPriority == 5) {
                                //                     $name = "Casa : (4+)";
                                //                 }
                                //                 if($runners->sortPriority == 6) {
                                //                     $name =  "Fora : (0)";
                                //                 }
                                //                 if($runners->sortPriority == 7) {
                                //                     $name =  "Fora : (1)";
                                //                 }
                                //                 if($runners->sortPriority == 8) {
                                //                     $name = "Fora : (2)";
                                //                 }
                                //                 if($runners->sortPriority == 9) {
                                //                     $name = "Fora : (3)";
                                //                 }
                                //                 if($runners->sortPriority == 10) {
                                //                     $name =  "Fora : (4+)";
                                //                 }

                                //                 if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                     ////echo "Atualiza Handicap Tempo Completo\n";
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }else {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = 1;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }
                                //                 } else {
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     // //echo "Cadastra Handicap Tempo Completo\n";
                                //                             $odd = Odd::create([
                                //                                 'match_id'                  =>  $match_id,
                                //                                 'event_id'                  =>  $event_id,
                                //                                 'mercado_name'              =>  'Total Exato de Gols (Casa & Fora)',
                                //                                 'odd'                       =>  $name,
                                //                                 'mercado_full_name'         =>  $name,
                                //                                 'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                 'status'                    =>  1,
                                //                                 'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                 'state'                     =>  $runners->runnerStatus,
                                //                                 'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                 'order'                     =>  17,
                                //                                 'type'                      =>  "ao-vivo"
                                //                             ]);
                                //                     }
                                //                 }

                                //             }                        

                                //     }//End Total Exato de Gols (Casa & Fora)

                                //     //Primeira Equipe a Marcar
                                //     if($markets->market->marketType == "GOAL_01") {
                                //         $arrFirstTime = 
                                //         [
                                //             $home       => 'Casa',
                                //             'The Draw'  => 'Empate',
                                //             $away       => 'Fora',
                                //             'Draw'      => 'Empate',
                                //             'No Goals'  => 'Sem Gols'
                                //         ];
                                //         //echo "Primeira Equipe a Marcar\n";

                                //         foreach($markets->market->runners as $key => $runners) {

                                //             if($runners->sortPriority == 1) {
                                //                 $name =  "Casa - (1º Gol)";
                                //             }
                                //             if($runners->sortPriority == 2) {
                                //                 $name =  "Fora - (1º Gol)";
                                //             }
                                //             if($runners->sortPriority == 3) {
                                //                 $name =  "Sem Gols";
                                //             }


                                //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                 ////echo "Atualiza Handicap Tempo Completo\n";
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }else {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = 1;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }
                                //             } else {
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                 // //echo "Cadastra Handicap Tempo Completo\n";
                                //                         $odd = Odd::create([
                                //                             'match_id'                  =>  $match_id,
                                //                             'event_id'                  =>  $event_id,
                                //                             'mercado_name'              =>  'Primeira Equipe a Marcar',
                                //                             'odd'                       =>  $name,
                                //                             'mercado_full_name'         =>  $name,
                                //                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                             'status'                    =>  1,
                                //                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                             'state'                     =>  $runners->runnerStatus,
                                //                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //                             'order'                     =>  18,
                                //                             'type'                      =>  "ao-vivo"
                                //                         ]);
                                //                 }
                                //             }

                                //         }                        

                                //     }//Primeira Equipe a Marcar


                                //     //Ambas Equipes Marcam (1T)
                                //     if($markets->market->marketType == "BOTH_TEAMS_TO_SCORE_FIRST_HALF") {

                                //         //echo "Ambas Equipes Marcam (1T)\n";

                                //             foreach($markets->market->runners as $key => $runners) {

                                //                 $name =  str_replace('Yes', 'Ambas - Sim (1T)', str_replace('No', 'Ambas - Não (1T)', $runners->runnerName));


                                //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                 ////echo "Atualiza Handicap Tempo Completo\n";
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }else {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = 1;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }
                                //             } else {
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                 // //echo "Cadastra Handicap Tempo Completo\n";
                                //                         $odd = Odd::create([
                                //                             'match_id'                  =>  $match_id,
                                //                             'event_id'                  =>  $event_id,
                                //                             'mercado_name'              =>  'Ambas Equipes Marcam (1T)',
                                //                             'odd'                       =>  $name,
                                //                             'mercado_full_name'         =>  $name,
                                //                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                             'status'                    =>  1,
                                //                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                             'state'                     =>  $runners->runnerStatus,
                                //                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //                             'order'                     =>  19,
                                //                             'type'                      =>  "ao-vivo"
                                //                         ]);
                                //                 }
                                //             }

                                //             }                        

                                //     }//End Ambas Equipes Marcam (1T)


                                //     //Ambas Equipes Marcam (2T)
                                //     if($markets->market->marketType == "BOTH_TEAMS_TO_SCORE_2ND_HALF") {

                                //         //echo "Ambas Equipes Marcam (2T)\n";

                                //             foreach($markets->market->runners as $key => $runners) {

                                //                 $name =  str_replace('Yes', 'Ambas - Sim (2T)', str_replace('No', 'Ambas - Não (2T)', $runners->runnerName));


                                //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                 ////echo "Atualiza Handicap Tempo Completo\n";
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }else {
                                //                     $odd = Odd::find($matchOdd->id);
                                //                     $odd->cotacao = 1;
                                //                     $odd->state = $runners->runnerStatus;
                                //                     $odd->type = "ao-vivo";
                                //                     $odd->save();
                                //                 }
                                //             } else {
                                //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                 // //echo "Cadastra Handicap Tempo Completo\n";
                                //                         $odd = Odd::create([
                                //                             'match_id'                  =>  $match_id,
                                //                             'event_id'                  =>  $event_id,
                                //                             'mercado_name'              =>  'Ambas Equipes Marcam (2T)',
                                //                             'odd'                       =>  $name,
                                //                             'mercado_full_name'         =>  $name,
                                //                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                             'status'                    =>  1,
                                //                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                             'state'                     =>  $runners->runnerStatus,
                                //                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //                             'order'                     =>  20,
                                //                             'type'                      =>  "ao-vivo"
                                //                         ]);
                                //                 }
                                //             }

                                //             }                        

                                //     }//End Ambas Equipes Marcam (2T)


                                //     //Ambas Equipes Marcam (2T)
                                //     if($markets->market->marketType == "BOTH_TEAMS_TO_SCORE_BOTH_HALVES") {

                                //         //echo "Ambas Equipes Marcam (1T & 2T)\n";

                                //             foreach($markets->market->runners as $key => $runners) {

                                //                 $name =  str_replace('Yes', 'Ambas - Sim (1T & 2T)', str_replace('No', 'Ambas - Não (1T & 2T)', $runners->runnerName));


                                //                 if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                     ////echo "Atualiza Handicap Tempo Completo\n";
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }else {
                                //                         $odd = Odd::find($matchOdd->id);
                                //                         $odd->cotacao = 1;
                                //                         $odd->state = $runners->runnerStatus;
                                //                         $odd->type = "ao-vivo";
                                //                         $odd->save();
                                //                     }
                                //                 } else {
                                //                     if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                     // //echo "Cadastra Handicap Tempo Completo\n";
                                //                             $odd = Odd::create([
                                //                                 'match_id'                  =>  $match_id,
                                //                                 'event_id'                  =>  $event_id,
                                //                                 'mercado_name'              =>  'Ambas Equipes Marcam (1T & 2T)',
                                //                                 'odd'                       =>  $name,
                                //                                 'mercado_full_name'         =>  $name,
                                //                                 'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                 'status'                    =>  1,
                                //                                 'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                 'state'                     =>  $runners->runnerStatus,
                                //                                 'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                 'order'                     =>  21,
                                //                                 'type'                      =>  "ao-vivo"
                                //                             ]);
                                //                     }
                                //                 }

                                //             }                        

                                //     }//End Ambas Equipes Marcam (2T)


                                //     //Total Exato de Gols (1T)
                                //     if($markets->market->marketType == "TEAM_FIRST_HALF_GOALS") {
                                //         //echo "Total Exato de Gols (1T)\n";


                                //                 foreach($markets->market->runners as $key => $runners) {

                                //                     if($runners->sortPriority == 1) {
                                //                         $name =  "Casa (1T) : (0)";
                                //                     }
                                //                     if($runners->sortPriority == 2) {
                                //                         $name =  "Casa (1T) : (1)";
                                //                     }
                                //                     if($runners->sortPriority == 3) {
                                //                         $name =  "Casa (1T) : (2+)";
                                //                     }
                                //                     if($runners->sortPriority == 4) {
                                //                         $name =  "Fora (1T) : (0)";
                                //                     }
                                //                     if($runners->sortPriority == 5) {
                                //                         $name =  "Fora (1T) : (1)";
                                //                     }
                                //                     if($runners->sortPriority == 6) {
                                //                         $name =  "Fora (1T) : (2+)";
                                //                     }



                                //                     if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                         ////echo "Atualiza Handicap Tempo Completo\n";
                                //                         if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                             $odd = Odd::find($matchOdd->id);
                                //                             $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                             $odd->state = $runners->runnerStatus;
                                //                             $odd->type = "ao-vivo";
                                //                             $odd->save();
                                //                         }else {
                                //                             $odd = Odd::find($matchOdd->id);
                                //                             $odd->cotacao = 1;
                                //                             $odd->state = $runners->runnerStatus;
                                //                             $odd->type = "ao-vivo";
                                //                             $odd->save();
                                //                         }
                                //                     } else {
                                //                         if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         // //echo "Cadastra Handicap Tempo Completo\n";
                                //                                 $odd = Odd::create([
                                //                                     'match_id'                  =>  $match_id,
                                //                                     'event_id'                  =>  $event_id,
                                //                                     'mercado_name'              =>  'Total Exato de Gols (1T)',
                                //                                     'odd'                       =>  $name,
                                //                                     'mercado_full_name'         =>  $name,
                                //                                     'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                     'status'                    =>  1,
                                //                                     'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                     'state'                     =>  $runners->runnerStatus,
                                //                                     'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                     'order'                     =>  22,
                                //                                     'type'                      =>  "ao-vivo"
                                //                                 ]);
                                //                         }
                                //                     }

                                //                 }                        

                                //         }//End Total Exato de Gols (1T)


                                //         //Total Exato de Gols (2T)
                                //         if($markets->market->marketType == "TEAM_SECOND_HALF_GOALS") {
                                //         //echo "Total Exato de Gols (2T)\n";


                                //                 foreach($markets->market->runners as $key => $runners) {


                                //                     if($runners->sortPriority == 1) {
                                //                         $name =  "Casa (2T) : (0)";
                                //                     }
                                //                     if($runners->sortPriority == 2) {
                                //                         $name =  "Casa (2T) : (1)";
                                //                     }
                                //                     if($runners->sortPriority == 3) {
                                //                         $name =  "Casa (2T) : (2+)";
                                //                     }
                                //                     if($runners->sortPriority == 4) {
                                //                         $name =  "Fora (2T) : (0)";
                                //                     }
                                //                     if($runners->sortPriority == 5) {
                                //                         $name =  "Fora (2T) : (1)";
                                //                     }
                                //                     if($runners->sortPriority == 6) {
                                //                         $name =  "Fora (2T) : (2+)";
                                //                     }



                                //                     if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                         ////echo "Atualiza Handicap Tempo Completo\n";
                                //                         if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                             $odd = Odd::find($matchOdd->id);
                                //                             $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                             $odd->state = $runners->runnerStatus;
                                //                             $odd->type = "ao-vivo";
                                //                             $odd->save();
                                //                         }else {
                                //                             $odd = Odd::find($matchOdd->id);
                                //                             $odd->cotacao = 1;
                                //                             $odd->state = $runners->runnerStatus;
                                //                             $odd->type = "ao-vivo";
                                //                             $odd->save();
                                //                         }
                                //                     } else {
                                //                         if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         // //echo "Cadastra Handicap Tempo Completo\n";
                                //                                 $odd = Odd::create([
                                //                                     'match_id'                  =>  $match_id,
                                //                                     'event_id'                  =>  $event_id,
                                //                                     'mercado_name'              =>  'Total Exato de Gols (2T)',
                                //                                     'odd'                       =>  $name,
                                //                                     'mercado_full_name'         =>  $name,
                                //                                     'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                     'status'                    =>  1,
                                //                                     'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                     'state'                     =>  $runners->runnerStatus,
                                //                                     'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                     'order'                     =>  23,
                                //                                     'type'                      =>  "ao-vivo"
                                //                                 ]);
                                //                         }
                                //                     }


                                //                 }                        

                                //         }//End Total Exato de Gols (2T)

                                //         //Chance Dupla (1T)
                                //         if($markets->market->marketType == "FIRST_HALF_DOUBLE_CHANCE") {
                                //             //echo "Chance Dupla (1T)\n";

                                //                 foreach($markets->market->runners as $key => $runners) {

                                //                     if($runners->sortPriority == 1) {
                                //                         $name = "Casa ou Empate (1T)";
                                //                     }
                                //                     if($runners->sortPriority == 2) {
                                //                         $name = "Empate ou Fora (1T)";
                                //                     }
                                //                     if($runners->sortPriority == 3) {
                                //                         $name = "Casa ou Fora (1T)";
                                //                     }


                                //                     if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                         ////echo "Atualiza Handicap Tempo Completo\n";
                                //                         if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                             $odd = Odd::find($matchOdd->id);
                                //                             $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                             $odd->state = $runners->runnerStatus;
                                //                             $odd->type = "ao-vivo";
                                //                             $odd->save();
                                //                         }else {
                                //                             $odd = Odd::find($matchOdd->id);
                                //                             $odd->cotacao = 1;
                                //                             $odd->state = $runners->runnerStatus;
                                //                             $odd->type = "ao-vivo";
                                //                             $odd->save();
                                //                         }
                                //                     } else {
                                //                         if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                         // //echo "Cadastra Handicap Tempo Completo\n";
                                //                                 $odd = Odd::create([
                                //                                     'match_id'                  =>  $match_id,
                                //                                     'event_id'                  =>  $event_id,
                                //                                     'mercado_name'              =>  'Chance Dupla (1T)',
                                //                                     'odd'                       =>  $name,
                                //                                     'mercado_full_name'         =>  $name,
                                //                                     'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                     'status'                    =>  1,
                                //                                     'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                     'state'                     =>  $runners->runnerStatus,
                                //                                     'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                     'order'                     =>  24,
                                //                                     'type'                      =>  "ao-vivo"
                                //                                 ]);
                                //                         }
                                //                     }

                                //                 }                        

                                //             }//end Chance Dupla (1T)


                                //             // //Handicap (1T)
                                //             // $arrHandicapFulltime = 
                                //             // [
                                //             //     'Handicap Draw'   => 'Empate',
                                //             //     $home             => 'Casa',
                                //             //     $away             => 'Fora'    
                                //             // ];

                                //             // if($markets->market->marketType == "FIRST_HALF_HANDICAP_WITH_TIE") {
                                //             //     //echo "Handicap (1T)\n";


                                //             //     foreach($markets->market->runners as $key => $runners) {

                                //             //             if($runners->handicap > 0) {
                                //             //                 $name =  strtr($runners->runnerName, $arrHandicapFulltime).' +'.$runners->handicap." (1T)";
                                //             //             } else {
                                //             //                 $name =  strtr($runners->runnerName, $arrHandicapFulltime).' '.$runners->handicap." (1T)";
                                //             //             }


                                //             //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //             //                 ////echo "Atualiza Handicap Tempo Completo\n";
                                //             //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //             //                     $odd = Odd::find($matchOdd->id);
                                //             //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //             //                     $odd->state = $runners->runnerStatus;
                                //             //                     $odd->type = "ao-vivo";
                                //             //                     $odd->save();
                                //             //                 }else {
                                //             //                     $odd = Odd::find($matchOdd->id);
                                //             //                     $odd->cotacao = 1;
                                //             //                     $odd->state = $runners->runnerStatus;
                                //             //                     $odd->type = "ao-vivo";
                                //             //                     $odd->save();
                                //             //                 }
                                //             //             } else {
                                //             //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //             //                 // //echo "Cadastra Handicap Tempo Completo\n";
                                //             //                         $odd = Odd::create([
                                //             //                             'match_id'                  =>  $match_id,
                                //             //                             'event_id'                  =>  $event_id,
                                //             //                             'mercado_name'              =>  'Handicap (1T)',
                                //             //                             'odd'                       =>  $name,
                                //             //                             'mercado_full_name'         =>  $name,
                                //             //                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //             //                             'status'                    =>  1,
                                //             //                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //             //                             'state'                     =>  $runners->runnerStatus,
                                //             //                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //             //                             'order'                     =>  25,
                                //             //                             'type'                      =>  "ao-vivo"
                                //             //                         ]);
                                //             //                 }
                                //             //             }


                                //             //     }

                                //             // }//End Handicap Tempo Completo


                                //             // //Handicap (2T)
                                //             // $arrHandicapFulltime = 
                                //             // [
                                //             //     'Handicap Draw'   => 'Empate',
                                //             //     $home             => 'Casa',
                                //             //     $away             => 'Fora'    
                                //             // ];

                                //             // if($markets->market->marketName == "Handicap Second Half") {
                                //             //     //echo "Handicap (2T)\n";


                                //             //     foreach($markets->market->runners as $key => $runners) {

                                //             //             if($runners->handicap > 0) {
                                //             //                 $name =  strtr($runners->runnerName, $arrHandicapFulltime).' +'.$runners->handicap." (2T)";
                                //             //             } else {
                                //             //                 $name =  strtr($runners->runnerName, $arrHandicapFulltime).' '.$runners->handicap." (2T)";
                                //             //             }


                                //             //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //             //                 ////echo "Atualiza Handicap Tempo Completo\n";
                                //             //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //             //                     $odd = Odd::find($matchOdd->id);
                                //             //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //             //                     $odd->state = $runners->runnerStatus;
                                //             //                     $odd->type = "ao-vivo";
                                //             //                     $odd->save();
                                //             //                 }else {
                                //             //                     $odd = Odd::find($matchOdd->id);
                                //             //                     $odd->cotacao = 1;
                                //             //                     $odd->state = $runners->runnerStatus;
                                //             //                     $odd->type = "ao-vivo";
                                //             //                     $odd->save();
                                //             //                 }
                                //             //             } else {
                                //             //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //             //                 // //echo "Cadastra Handicap Tempo Completo\n";
                                //             //                         $odd = Odd::create([
                                //             //                             'match_id'                  =>  $match_id,
                                //             //                             'event_id'                  =>  $event_id,
                                //             //                             'mercado_name'              =>  'Handicap (2T)',
                                //             //                             'odd'                       =>  $name,
                                //             //                             'mercado_full_name'         =>  $name,
                                //             //                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //             //                             'status'                    =>  1,
                                //             //                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //             //                             'state'                     =>  $runners->runnerStatus,
                                //             //                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //             //                             'order'                     =>  26,
                                //             //                             'type'                      =>  "ao-vivo"
                                //             //                         ]);
                                //             //                 }
                                //             //             }


                                //             //     }

                                //             // }//End Handicap Tempo Completo


                                //                 //Primeiro Jogador a Marcar?
                                //                 if($markets->market->marketType == "FIRST_GOAL_SCORER") {
                                //                     //echo "Marca 1º Gol\n";


                                //                     foreach($markets->market->runners as $key => $runners) {
                                //                             ////echo str_replace('Odd', 'Ímpar', str_replace('Even', 'Par', $runners->runnerName))."\n";
                                //                             ////echo $runners->selectionId."\n";
                                //                             if($runners->runnerName == "No Goalscorer") {
                                //                                 $name = strtr($runners->runnerName, $trnaslate)."\n";
                                //                             } else {
                                //                                 $name = strtr($runners->runnerName, $trnaslate)." - Marca 1º Gol";
                                //                             }


                                //                             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                                 ////echo "Atualiza Handicap Tempo Completo\n";
                                //                                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                                     $odd = Odd::find($matchOdd->id);
                                //                                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                                     $odd->state = $runners->runnerStatus;
                                //                                     $odd->type = "ao-vivo";
                                //                                     $odd->save();
                                //                                 }else {
                                //                                     $odd = Odd::find($matchOdd->id);
                                //                                     $odd->cotacao = 1;
                                //                                     $odd->state = $runners->runnerStatus;
                                //                                     $odd->type = "ao-vivo";
                                //                                     $odd->save();
                                //                                 }
                                //                             } else {
                                //                                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                                 // //echo "Cadastra Handicap Tempo Completo\n";
                                //                                         $odd = Odd::create([
                                //                                             'match_id'                  =>  $match_id,
                                //                                             'event_id'                  =>  $event_id,
                                //                                             'mercado_name'              =>  'Marca 1º Gol',
                                //                                             'odd'                       =>  $name,
                                //                                             'mercado_full_name'         =>  $name,
                                //                                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                             'status'                    =>  1,
                                //                                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                             'state'                     =>  $runners->runnerStatus,
                                //                                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                             'order'                     =>  27,
                                //                                             'type'                      =>  "ao-vivo"
                                //                                         ]);
                                //                                 }
                                //                             }


                                //                         }                        

                                //                 }//End Primeiro Jogador a Marcar?

                                //                 // //Último Jogador a Marcar
                                //                 // if($markets->market->marketType == "LAST_GOALSCORER") {
                                //                 //     //echo "Marca Último Gol\n";

                                //                 //     foreach($markets->market->runners as $key => $runners) {

                                //                 //             if($runners->runnerName == "No Goalscorer") {
                                //                 //             $name =   strtr($runners->runnerName, $trnaslate)."\n";
                                //                 //             } else {
                                //                 //             $name =   strtr($runners->runnerName, $trnaslate)." - Marca Último Gol\n";
                                //                 //             }

                                //                 //             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                 //                 ////echo "Atualiza Handicap Tempo Completo\n";
                                //                 //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                 //                     $odd = Odd::find($matchOdd->id);
                                //                 //                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                 //                     $odd->state = $runners->runnerStatus;
                                //                 //                     $odd->type = "ao-vivo";
                                //                 //                     $odd->save();
                                //                 //                 }else {
                                //                 //                     $odd = Odd::find($matchOdd->id);
                                //                 //                     $odd->cotacao = 1;
                                //                 //                     $odd->state = $runners->runnerStatus;
                                //                 //                     $odd->type = "ao-vivo";
                                //                 //                     $odd->save();
                                //                 //                 }
                                //                 //             } else {
                                //                 //                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                 //                 // //echo "Cadastra Handicap Tempo Completo\n";
                                //                 //                         $odd = Odd::create([
                                //                 //                             'match_id'                  =>  $match_id,
                                //                 //                             'event_id'                  =>  $event_id,
                                //                 //                             'mercado_name'              =>  'Marca Último Gol',
                                //                 //                             'odd'                       =>  $name,
                                //                 //                             'mercado_full_name'         =>  $name,
                                //                 //                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                 //                             'status'                    =>  1,
                                //                 //                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                 //                             'state'                     =>  $runners->runnerStatus,
                                //                 //                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //                 //                             'order'                     =>  28,
                                //                 //                             'type'                      =>  "ao-vivo"
                                //                 //                         ]);
                                //                 //                 }
                                //                 //             }
                                //                 //         }                        

                                //                 // }//End Último Jogador a Marcar?

                                //                 //Marca Gol na Partida
                                //                 if($markets->market->marketType == "TO_SCORE") {
                                //                     //echo "Marca Gol na Partida\n";


                                //                     foreach($markets->market->runners as $key => $runners) {

                                //                             if($runners->runnerName == "No Goalscorer") {
                                //                             $name =  strtr($runners->runnerName, $trnaslate);
                                //                             } else {
                                //                             $name =  strtr($runners->runnerName, $trnaslate)." - Marca Gol na Partida\n";
                                //                             }

                                //                             if($matchOdd = Odd::where('selectionId', $event_id.$runners->runnerName.$markets->market->marketType)->where('match_id', $match_id)->first()) {
                                //                                 ////echo "Atualiza Handicap Tempo Completo\n";
                                //                                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                                     $odd = Odd::find($matchOdd->id);
                                //                                     $odd->cotacao = $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                //                                     $odd->state = $runners->runnerStatus;
                                //                                     $odd->type = "ao-vivo";
                                //                                     $odd->save();
                                //                                 }else {
                                //                                     $odd = Odd::find($matchOdd->id);
                                //                                     $odd->cotacao = 1;
                                //                                     $odd->state = $runners->runnerStatus;
                                //                                     $odd->type = "ao-vivo";
                                //                                     $odd->save();
                                //                                 }
                                //                             } else {
                                //                                 if(isset($markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                //                                 // //echo "Cadastra Handicap Tempo Completo\n";
                                //                                         $odd = Odd::create([
                                //                                             'match_id'                  =>  $match_id,
                                //                                             'event_id'                  =>  $event_id,
                                //                                             'mercado_name'              =>  'Marca Gol na Partida',
                                //                                             'odd'                       =>  $name,
                                //                                             'mercado_full_name'         =>  $name,
                                //                                             'cotacao'                   =>  $markets->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                //                                             'status'                    =>  1,
                                //                                             'selectionId'               =>  $event_id.$runners->runnerName.$markets->market->marketType, 
                                //                                             'state'                     =>  $runners->runnerStatus,
                                //                                             'stateMarc'                 =>  $markets->market->marketStatus,
                                //                                             'order'                     =>  29,
                                //                                             'type'                      =>  "ao-vivo"
                                //                                         ]);
                                //                                 }
                                //                             }

                                //                         }

                                //                 }//End Marca Gol na Partida

                            } //End Markets


                        }
                    } else {
                        $event = Match::find($partida->id);
                        $event->delete();
                        //echo "Deleteda\n";
                    }
                } //End Foreach Global

            } //End If Global

        }

        // }
        // sleep(50);

    }
}
