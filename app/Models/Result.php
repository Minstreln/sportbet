<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $fillable = [
        'match_id',
        'home',
        'away',
        'date',
        'socore_home_ful_time',
        'socore_away_ful_time',
        'socore_home_half_time',
        'socore_away_half_time',                    
        'score_global',
    ];
}
