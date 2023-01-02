<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigMercados extends Model
{
    protected $fillable = ['user_id', 'site_id', 'name', 'porcentagem', 'status'];
}
