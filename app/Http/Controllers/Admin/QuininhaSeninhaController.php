<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\QuinaTaxa;
use App\Models\SenaTaxa;

class QuininhaSeninhaController extends Controller
{
    public function __construct()
    {

    }


    public function listTaxasQuininha()
    {
        return QuinaTaxa::where('site_id', env('ID_SITE'))->get();
    }


    public function alteraCotacaoQuina(Request $request, $id)
    {

        $taxa = QuinaTaxa::find($id);
        $taxa->taxa = $request->taxa;
        $taxa->save();
    }

    public function bloqueiaCotacaoQuina(Request $request, $id)
    {
        $taxa = QuinaTaxa::find($id);
        $taxa->status = $request->status;
        $taxa->save();
    }

    

    public function listTaxasSeninha()
    {
        return SenaTaxa::where('site_id', env('ID_SITE'))->get();
    }

    public function alteraCotacaoSena(Request $request, $id)
    {

        $taxa = SenaTaxa::find($id);
        $taxa->taxa = $request->taxa;
        $taxa->save();
    }

    public function bloqueiaCotacaoSena(Request $request, $id)
    {
        $taxa = SenaTaxa::find($id);
        $taxa->status = $request->status;
        $taxa->save();
    }

    
}