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
        Schema::create('emitentes', function (Blueprint $table) {
            $table->id();
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('rua');
            $table->string('numero_endereco');
            $table->string('bairro');
            $table->string('cep');
            $table->string('municipio');
            $table->string('uf');
            $table->string('pais');
            $table->string('codigo_pais');
            $table->string('codigo_municipio');
            $table->string('complemento')->nullable();
            $table->integer('ultimo_numero_nfe');
            $table->integer('numero_serie_nfe');
            $table->integer('sequencia_evento');
            $table->string('cpf_cnpj');
            $table->string('ie_rg')->nullable();
            $table->string('im')->nullable();
            $table->string('fone')->nullable();
            $table->binary('certificado')->nullable();
            $table->string('senha');
            $table->integer('ambiente');
            $table->integer('situacao_tributaria');
            $table->string('logradouro')->nullable();
            $table->float('percentual_aliquota_icms')->nullable();
            $table->integer('codigo_uf');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emitentes');
    }
};
