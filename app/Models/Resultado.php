<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resultado extends Model
{
   protected $fillable = ['match_id','scores', 'resultado'];
}
