<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lancamento extends Model
{
    protected $fillable = ['user_id','name', 'tipo', 'descricao', 'valor', 'site_id'];
}
