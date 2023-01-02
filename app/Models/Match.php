<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
 
   protected $fillable = [ 
      'event_id',
      'sport_id',
      'sport_name',
      'confuso',
      'order',
      'visible',
      'time_status',
      'schedule',
      'date',
      'league',
      'time_status',
      'league_id',
      'confronto',
      'home',
      'image_id_home',
      'away',
      'image_id_away',
      'score',
      'time',
      'live_status',
      'halfTimeScoreHome',
      'halfTimeScoreAway',
      'fullTimeScoreHome',
      'fullTimeScoreAway',
      'numberOfCornersHome',
      'numberOfCornersAway',
      'numberOfYellowCardsHome',
      'numberOfYellowCardsAway',
      'numberOfRedCardsHome',
      'numberOfRedCardsAway',
   ];

public function odds() {

    return $this->hasMany(Odd::class)
               ->where('mercado_name', 'Vencedor do Encontro')
                ->orderBy('id', 'asc');
}

public function fullOdds() {

   return $this->hasMany(Odd::class)
               ->orderBy('order', 'asc');

}



}
