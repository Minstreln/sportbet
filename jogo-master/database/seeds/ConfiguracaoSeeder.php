<?php

use Illuminate\Database\Seeder;
use App\Models\Configuracao;

class ConfiguracaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Configuracao::create([
            'valor_mini_aposta' => 5,
            'valor_max_aposta' => 1000,	
            'premio_max' => 1500,	
            'cotacao_mini_bilhete' => 1.20,	
            'cotacao_max_bilhete' => 150,	
            'quantidade_jogos_mini_bilhete' => 1,
            'quantidade_jogos_max_bilhete' => 10,
            'bloquear_odd_abaixo' => 1,	
            'quantidade_times_visitantes_mesmo_camp' => 5,	
            'texto_rodape' => 'Texto para o seu rodapé',		
            'email_alerta' => 'email@gmail.com',	
            'alerta_aposta_acima' => 50,	
            'cambista_pode_cancelar' => 'Sim',	
            'tempo_limite_camb_cancela_aposta' => 10,	
            'aposta_ativa' => 'Sim',	
            'bloq_aposta_madrugada' => 'Sim',
            'travar_odd_acima' => 200,
            'data_limite_jogos' => '2020-07-26',
            'op_futebol' => 'Sim',
            'op_ufcbox' => 'Sim',
            'op_basquete' => 'Sim',
            'op_tenis' => 'Sim',
            'op_quininha' => 'Sim',
            'op_seninha' => 'Sim',
            'futebol_ao_vivo' => 'Sim',
            'site_id' =>  env('ID_SITE'),
        ]);
    }
}
