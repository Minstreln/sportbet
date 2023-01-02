<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{

   protected $table = 'configuracaos';

   protected $fillable = [
    'valor_mini_aposta',
    'valor_max_aposta',	
    'menor_valor_loto',
    'max_valor_loto',
    'premio_max',	
    'cotacao_mini_bilhete',	
    'cotacao_max_bilhete',
    'bloquear_odd_abaixo',	
    'travar_odd_acima',
    'quantidade_jogos_mini_bilhete',
    'quantidade_jogos_max_bilhete',	
    'quantidade_times_visitantes_mesmo_camp',	
    'texto_rodape',		
    'email_alerta',	
    'alerta_aposta_acima',	
    'cambista_pode_cancelar',	
    'tempo_limite_camb_cancela_aposta',	
    'aposta_ativa',	
    'bloq_aposta_madrugada',
    'data_limite_jogos',
    'op_futebol',
    'op_ufcbox',
    'op_quininha',
    'op_seninha',
    'op_basquete',
    'op_tenis',
    'futebol_ao_vivo',
    'site_id',
    'time_live',
    'cotacao_live',
    'comissao_premio'
   ];

   public function dataLimite()
   {
      $data = Configuracao::get();

      return $data[0]->data_limite_jogos;

    
   }
}
