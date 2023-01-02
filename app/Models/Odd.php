<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Odd extends Model

{   
    protected $fillable = [
                            'match_id', 
                            'order', 
                            'selectionId', 
                            'state',
                            'stateMarc', 
                            'event_id', 
                            'mercado_name', 
                            'header', 
                            'odd',
                            'goals', 
                            'cotacao', 
                            'mercado_full_name', 
                            'status', 
                            'valor_apostado',
                            'type'
                           ];
}
