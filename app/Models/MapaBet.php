<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapaBet extends Model
{
    protected $fillable = ['event_id', 'confronto', 'date_event','sport', 'group_opp', 'apostado', 'opcao', 'tipo_aposta', 'site_id'];
}
