<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aposta extends Model
{
    protected $with = ['palpites', 'palpitesLoto'];

    protected $fillable = [
            'user_id',
            'adm_id',
            'gerente_id',
            'site_id',	
            'concurso',
            'tipo_aposta',
            'cupom',
            'modalidade',	
            'status',	
            'valor_apostado',	
            'retorno_possivel',	
            'retorno_cambista',
            'vendedor',	
            'cliente',	
            'tipo',
            'comicao',	
            'cotacao',	
            'total_palpites',
            'andamento_palpites',
            'acertos_palpites',
            'erros_palpites',
            'devolvidos_palpites',
            'controle',  
            'resultado_loto',    
            
        ];


        public function palpites() 
        {
            return $this->hasMany(Palpite::class);
        }

        public function palpitesLoto() 
        {
            return $this->hasMany(PalpiteLoto::class)->orderBy('dezena', 'asc');
        }
}
