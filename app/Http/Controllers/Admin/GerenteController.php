<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use DB;
class GerenteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * 
     * 
     */
    private $user;

     public function __construct(User $user) 
     {
        $this->user = $user;
     }

    public function indexView() 
    {
        return view('admin.gerentes');
    }
    
    public function index()
    {
        if(auth()->user()->nivel == 'adm')
        return $this->user->where('nivel', 'gerente')
                            //->orwhere('nivel', 'adm')
                            ->orderBy('name', 'asc')
                            ->where('site_id', env('ID_SITE'))
                            ->get();
        else 
        return '';
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

    public function storeView() 
    {
        return view('admin.cadastrar-gerente');
    } 
    public function store(Request $request)
    {

      
       $user =  User::create([
            'name'          => $request->name, 
            'adm_id'        => auth()->user()->id,
            'gerente_id'    => auth()->user()->id,
            'username'         => $request->username, 
            'password'      => bcrypt($request->password), 
            'nivel'         => 'gerente', 
            'situacao'      => 'ativo', 
            'site_id'          => env('ID_SITE'),
            'contato'       => $request->contato, 
            'endereco'      => $request->endereco,
            'comissao_gerente'      => $request->comissao_gerente,
            'saldo_gerente' => $request->saldo_gerente,
        ]);

 
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      
    }

    public function searchUser(Request $request) {

        return $this->user->where('name','LIKE',"%{$request->name}%")
                            ->where('nivel', 'gerente')
                            ->where('site_id', env('ID_SITE'))
                            ->get(); 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edtView()
    {
        return view('admin.editar-gerente');
    }
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

      
        $data = $request->all();

     
        if($request->password != null) 

        $data['password'] = bcrypt($data['password']);
        
        else
            unset($data['password']);

        $gerente = User::find($id);

        $update = $gerente->update($data);   
        
  
        

    }
        /*
        
     
 
      
        
    
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
