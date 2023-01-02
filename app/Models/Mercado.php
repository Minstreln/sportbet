<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ConfigOdd;
class Mercado extends Model
{
    protected $fillable = ['match_id', 'updated', 'name', 'category', 'status'];



    public function odds() {

       // $odd_blocks = ConfigOdd::where('status', 0)

        return $this->hasMany(Odd::class)->orderBy('header', 'asc');
    }

  

}
