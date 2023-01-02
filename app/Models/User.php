<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Adm;
use App\Models\Gerente;
use App\Models\Cambista;
use App\Models\Aposta;
use App\Notifications\ResetPassword;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 
        'adm_id', 
        'gerente_id', 
        'email', 
        'username',
        'password',
        'nivel', 
        'situacao', 
        'contato', 
        'endereco',
        'comissao1',	
        'comissao2',	
        'comissao3',	
        'comissao4',	
        'comissao5',	
        'comissao6',	
        'comissao7',	
        'comissao8',	
        'comissao9',	
        'comissao10',
        'quantidade',	
        'comissao_gerente',
        'comissao_cambistas',
        'comissao_loto',
        'saldo_casadinha',	
        'saldo_loto',
        'saldo_simples',
        'saldo_gerente',
        'entradas',
        'entrada_loto',
        'entradas_abertas',
        'saidas',
        'lancamentos',
        'site_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    
    public function dadosAdm() {

        return $this->hasMany(Adm::class);
        
    }


    public function dadosGerente() {

        return $this->hasMany(Gerente::class);
        
    }

    public function dadosCambista() {

        return $this->hasMany(Cambista::class);
        
    }

  

     /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function sendPasswordResetNotification($token)
    {
        // Não esquece: use App\Notifications\ResetPassword;
        $this->notify(new ResetPassword($token));
    }



}
