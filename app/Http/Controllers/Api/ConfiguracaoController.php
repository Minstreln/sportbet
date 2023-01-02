<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use App\Models\InfoBanca;

class ConfiguracaoController extends Controller
{
    public function listaLimites()
    {
       return $limites = Configuracao::where('site_id', env('ID_SITE'))->get();

  
        
    }

    public function regulamento() {

        return InfoBanca::select('id','regulamento')
                        ->where('site_id', env('ID_SITE'))                
                        ->get();
    }
} 