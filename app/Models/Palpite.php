<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Palpite extends Model
{
    protected $fillable = 
    [
        'aposta_id',
        'idOdd',
        'sport',	
        'match_id',	
        'match_temp',	
        'league',	
        'home',	
        'away',	
        'group_opp',	
        'palpite',	
        'cotacao',
        'apostado',	
        'status',
        'concurso',
        'odds',
        'ativo',
        'score',
        'type'
      
    ];
}
