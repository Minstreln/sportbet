<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuinaTaxa extends Model
{
    protected $fillable = ['dezena', 'taxa', 'status', 'site_id'];
}
