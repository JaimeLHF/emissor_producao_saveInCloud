<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->float('valorTotal');
            $table->float('vFrete')->nullable();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('chave', 44);
            $table->integer('numero_nfe');
            $table->enum('status', ['Novo', 'Rejeitado', 'Cancelado', 'Aprovado']);
            $table->integer('sequencia_evento');
            $table->integer('modFrete')->nullable();
            $table->string('natOp');
            $table->string('motivo_rejeitado');
            $table->integer('finNFe');
            $table->timestamps();            
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendas');
    }
};
