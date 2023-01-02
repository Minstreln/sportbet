<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuinaTaxasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quina_taxas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('dezena')->nullable();
            $table->string('taxa')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('quina_taxas');
    }
}
