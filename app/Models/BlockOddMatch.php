<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockOddMatch extends Model
{
    protected $fillable = ['odd_id', 'odd_uid', 'odd', 'cotacao', 'status', 'site_id'];
}
