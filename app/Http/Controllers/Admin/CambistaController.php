<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Models\Cambista;
use App\Models\Lancamento;
use App\Models\ConfigMercados; 
use App\Models\ConfigOdd;
use DB;
class CambistaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $cambista;

    public function __construct(User $user) 
    {
            $this->user = $user;
    }

    public function indexView() 
    {
        return view('admin.cambistas');
    }

    public function index()
    {
        if(auth()->user()->nivel == 'adm')

        return $this->user->where('nivel', 'cambista')
                            ->where('site_id', env('ID_SITE'))
                            ->orderBy('name', 'asc')
                            ->get();
        else
        return $this->user->where('nivel', 'cambista')
                            ->where('site_id', env('ID_SITE'))
                            ->where('gerente_id', auth()->user()->id)
                            ->orderBy('name', 'asc')
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

    public function lancamento()
    {
        return view('admin.store-lancamento');
    }

    public function storeLancamento(Request $request) 
    {
            if($request->tipo == "Crédito") {

                $user = User::find($request->user_id);

                $user->name;

                $user->lancamentos = $user->lancamentos+$request->valor;
                                        
                $user->save();

                $lancamento = Lancamento::create([
                    'user_id' => $user->id,
                    'name' =>  $user->name,
                    'tipo' => $request->tipo,
                    'descricao' => $request->descricao,
                    'valor'   => $request->valor,
                    'site_id' =>  env('ID_SITE'),
                ]); 

            }


            if($request->tipo == "Débito") {

                $user = User::find($request->user_id);


                $user->lancamentos = $user->lancamentos-$request->valor;
                                        
                $user->save();

                $lancamento = Lancamento::create([
                    'user_id' => $user->id,
                    'name' =>  $user->name,
                    'tipo' => $request->tipo,
                    'descricao' => $request->descricao,
                    'valor'   => $request->valor,
                    'site_id' =>  env('ID_SITE'),
                ]);

               // $lancamento->save();
            }   
    }


    public function lancamentos() 
    {
        return Lancamento::orderBy('id', 'desc')
                            ->where('site_id', env('ID_SITE'))
                            ->get();
    }



    




    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeView()
    {
        return view('admin.cadastrar-cambista');
    }
    public function store(Request $request)
    {
       
        if(auth()->user()->nivel == 'gerente' && auth()->user()->saldo_gerente < ($request->saldo_simples+$request->saldo_casadinha) ) {

             return response()->json(['message'=> 'error'], 404);
        }
     

       if ($request->gerente_id == 0) {

            $gerente_id = auth()->user()->id;

            }else{

                    $gerente_id = $request->gerente_id;
            }
   
        $user =  User::create([
             'name'             => $request->name, 
             'adm_id'           => auth()->user()->id,
             'gerente_id'       => $gerente_id,
             'username'         => $request->username, 
             'password'         => bcrypt($request->password), 
             'nivel'            => 'cambista', 
             'situacao'         => 'ativo', 
             'site_id'          => env('ID_SITE'),
             'contato'          => $request->contato, 
             'endereco'         => $request->endereco,
             'comissao1'         => $request->comissao1,
             'comissao2'         => $request->comissao2,
             'comissao3'         => $request->comissao3,
             'comissao4'         => $request->comissao4,
             'comissao5'         => $request->comissao5,
             'comissao6'         => $request->comissao6,
             'comissao7'         => $request->comissao7,
             'comissao8'         => $request->comissao8,
             'comissao9'         => $request->comissao9,
             'comissao10'        => $request->comissao10,
             'comissao_loto'     => $request->comissao_loto,
             'saldo_simples'     => $request->saldo_simples,
             'saldo_casadinha'   => $request->saldo_casadinha,
             'saldo_loto'        => $request->saldo_loto,
         ]);

        if(auth()->user()->nivel == 'gerente' && $user) {

                $gerente = User::find(auth()->user()->id);
                $gerente->saldo_gerente  = $gerente->saldo_gerente - ($request->saldo_simples+$request->saldo_casadinha);
                $gerente->save();

        }

        $mercados = ConfigMercados::where('site_id', env('ID_SITE'))->where('user_id', env('ID_USER'))->get();
       
        $i=0;
        foreach($mercados as $mercado) {
            ConfigMercados::create([
                'name' => $mercado->name,
                'porcentagem' => $mercado->porcentagem,
                'status' => $mercado->status, 
                'site_id' => $user->site_id,
                'user_id' => $user->id,      
            ]);
         $i++;   
        }

        $odds = ConfigOdd::where('site_id', env('ID_SITE'))->where('user_id', env('ID_USER'))->get();
       
        $j=0;
        foreach($odds as $odd) {
            ConfigOdd::create([
                'mercado_name' => $odd->mercado_name ,
                'name' => $odd->name, 
                'user_id' => $user->id,
                'site_id' => $user->site_id,
                'mercado_full_name' => $odd->mercado_full_name,
                'status' => $odd->status, 
                'porcentagem' => $odd->porcentagem,
            ]);
         $j++;   
        }

      
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

    public function edtView()
    {
        return view('admin.editar-cambista');
    }
    public function edit($id)
    {
        //
    }

    public function searchUser(Request $request) {

        return $this->user->where('name','LIKE',"%{$request->name}%")
                            ->where('nivel', 'cambista')
                            ->where('gerente_id', auth()->user()->id)
                            ->where('site_id', env('ID_SITE'))
                            ->get(); 
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
       
        $data = $request->all();

     
        if($request->password != null) 

        $data['password'] = bcrypt($data['password']);
        
        else
            unset($data['password']);

        $cambista = User::find($id);

        $update = $cambista->update($data);   

       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $gerente = User::find($id);

        $gerente->delete();
    }
}