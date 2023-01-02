<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('adm_id')->nullable(); 
            $table->string('gerente_id')->nullable(); 
            $table->string('name')->nullable(); 
            $table->string('username')->nullable(); 
            $table->string('email')->unique(); 
            $table->string('password');  
            $table->string('nivel')->nullable(); 
            $table->string('site_id')->nullable(); 
            $table->string('situacao')->nullable();
            $table->string('contato')->nullable(); 
            $table->string('endereco')->nullable();
            $table->string('comissao1')->nullable(); 
            $table->string('comissao2')->nullable(); 
            $table->string('comissao3')->nullable(); 
            $table->string('comissao4')->nullable(); 
            $table->string('comissao5')->nullable(); 
            $table->string('comissao6')->nullable(); 
            $table->string('comissao7')->nullable(); 
            $table->string('comissao8')->nullable(); 
            $table->string('comissao9')->nullable(); 
            $table->string('comissao10')->nullable(); 
            $table->string('saldo_casadinha')->nullable(); 
            $table->string('saldo_simples')->nullable(); 
            $table->string('saldo_gerente')->nullable(); 
            $table->string('comissao_gerente')->nullable(); 
            $table->string('entradas')->nullable(); 
            $table->string('entradas_abertas')->nullable(); 
            $table->string('saidas')->nullable(); 
            $table->string('lancamentos')->nullable(); 
            $table->string('comissoes')->nullable(); 
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
        Schema::dropIfExists('users');
    }
}
