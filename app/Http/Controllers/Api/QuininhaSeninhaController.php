<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\QuinaTaxa;
use App\Models\SenaTaxa;
use Carbon\Carbon;
use App\Models\BlockDayLoto;

class QuininhaSeninhaController extends Controller
{
    private $arr = array();

    public function __construct() 
    {

    }

    public function geraQuina() 
    {          

            $q=0;
            for($i=01; $i<=80; $i++) {
                
                $this->arr[$q]['num'] = str_pad($i, 2, '0', STR_PAD_LEFT);
                $q++;
            }

            return $this->arr;

    }

    public function viewCotacaoQuina()
    {
        return QuinaTaxa::where('status', 1)->where('site_id', env('ID_SITE'))->get();
    }

    public function viewDiasSorteioQuina() 
    {

        $dateBloked = BlockDayLoto::all();
        
        $date = array();
        foreach($dateBloked as $dateBloke) {
            $date[] =$dateBloke->date;
        }   

        $q=0;
        for($i=0; $i < 20; $i++) {

            $data = date('D', strtotime('+'.$i.'day'));
            $mes = date('M');
            $dia = date('d/m/Y', strtotime('+'.$i.'day'));
            $ano = date('Y');
            $dataHoraSorteio = date('Y-m-d', strtotime('+'.$i.'day')).' 19:00:00';
            $agoradata =  Carbon::now()->format('Y-m-d H:i:s');
            
            $semana = array(
                'Sun' => 'Domingo', 
                'Mon' => 'Segunda-Feira',
                'Tue' => 'Terca-Feira',
                'Wed' => 'Quarta-Feira',
                'Thu' => 'Quinta-Feira',
                'Fri' => 'Sexta-Feira',
                'Sat' => 'Sábado'
            );

            if($semana["$data"] != 'Domingo' && $dataHoraSorteio > $agoradata) {

                if(in_array($dia, $date)) {

                }else {
                    $this->arr[$q]['day'] = $semana["$data"];
                    $this->arr[$q]['date'] = $dia;
                }
             
            } else {

            }

            $q++;
        }

        $this->arr = array_filter($this->arr);
        $this->arr = array_values($this->arr);

        return $this->arr;
      
    
    }

    public function geraSena() 
    {          

            $q=0;
            for($i=01; $i<=60; $i++) {
                
                $this->arr[$q]['num'] = str_pad($i, 2, '0', STR_PAD_LEFT);
                $q++;
            }

            return $this->arr;

    }

    public function viewCotacaoSena()
    {
        return SenaTaxa::where('status', 1)->where('site_id', env('ID_SITE'))->get();
    }


    public function viewDiasSorteioSena() 
    {

        $dateBloked = BlockDayLoto::all();
        
        $date = array();
        foreach($dateBloked as $dateBloke) {
            $date[] =$dateBloke->date;
        }   

        $q=0;
        for($i=0; $i < 20; $i++) {

            $data = date('D', strtotime('+'.$i.'day'));
            $mes = date('M');
            $dia = date('d/m/Y', strtotime('+'.$i.'day'));
            $ano = date('Y');
            $dataHoraSorteio = date('Y-m-d', strtotime('+'.$i.'day')).' 19:00:00';
            $agoradata =  Carbon::now()->format('Y-m-d H:i:s');
            
            $semana = array(
                'Sun' => 'Domingo', 
                'Mon' => 'Segunda-Feira',
                'Tue' => 'Terca-Feira',
                'Wed' => 'Quarta-Feira',
                'Thu' => 'Quinta-Feira',
                'Fri' => 'Sexta-Feira',
                'Sat' => 'Sábado'
            );

            if(
                $semana["$data"] != 'Domingo' && $semana["$data"] != 'Segunda-Feira' && $dataHoraSorteio > $agoradata
                && $semana["$data"] != 'Terca-Feira' && $semana["$data"] != 'Quinta-Feira' && $semana["$data"] != 'Sexta-Feira'
            
            ) {

                if(in_array($dia, $date)) {

                }else {
                    $this->arr[$q]['day'] = $semana["$data"];
                    $this->arr[$q]['date'] = $dia;
                }
             
            } else {

            }

            $q++;
        }

        $this->arr = array_filter($this->arr);
        $this->arr = array_values($this->arr);

        return $this->arr;
      
    
    }
}
