<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigOdd extends Model
{
        protected $fillable = ['mercado_name','user_id', 'site_id', 'porcentagem', 'header', 'mercado_full_name',  'name', 'status'];
}
