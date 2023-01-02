<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockMatch extends Model
{
    protected $fillable = ['event_id', 'site_id', 'date', 'sport', 'confronto'];
}
