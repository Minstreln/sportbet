<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfiguracaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configuracaos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('valor_mini_aposta')->nullable();
            $table->string('valor_max_aposta')->nullable();	
            $table->string('premio_max')->nullable();	
            $table->string('cotacao_mini_bilhete')->nullable();
            $table->string('cotacao_max_bilhete')->nullable();	
            $table->string('quantidade_jogos_mini_bilhete')->nullable();
            $table->string('quantidade_jogos_max_bilhete')->nullable();
            $table->string('bloquear_odd_abaixo')->nullable();
            $table->string('quantidade_times_visitantes_mesmo_camp')->nullable();
            $table->string('texto_rodape')->nullable();		
            $table->string('email_alerta')->nullable();
            $table->string('alerta_aposta_acima')->nullable();
            $table->string('cambista_pode_cancelar')->nullable();	
            $table->string('tempo_limite_camb_cancela_aposta')->nullable();	
            $table->string('aposta_ativa')->nullable();
            $table->string('bloq_aposta_madrugada')->nullable();
            $table->string('travar_odd_acima')->nullable();
            $table->string('data_limite_jogos')->nullable();
            $table->string('op_futebol')->nullable();
            $table->string('op_ufcbox')->nullable();
            $table->string('op_basquete')->nullable();
            $table->string('op_tenis')->nullable();
            $table->string('op_quininha')->nullable();
            $table->string('op_seninha')->nullable();
            $table->string('futebol_ao_vivo')->nullable();
            $table->string('site_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configuracaos');
    }
}
