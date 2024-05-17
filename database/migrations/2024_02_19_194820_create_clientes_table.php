<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('razao_social')->nullable();
            $table->char('cpf_cnpj');
            $table->char('ie_rg')->nullable();
            $table->char('im')->nullable();
            $table->boolean('contribuinte');
            $table->string('cep');
            $table->string('rua');
            $table->string('numero');
            $table->string('bairro');
            $table->string('municipio');
            $table->string('uf');
            $table->string('complemento')->nullable();
            $table->string('logradouro')->nullable();
            $table->string('codigo_municipio');
            $table->string('codigo_pais');
            $table->string('pais');
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
