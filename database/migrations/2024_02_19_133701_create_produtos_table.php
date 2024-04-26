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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->unsignedBigInteger('acabamento_id');
            $table->float('valor');
            $table->char('cfop_interno');
            $table->char('cfop_externo');
            $table->char('ncm');
            $table->char('codigo_barras')->nullable();
            $table->char('und_venda');
            $table->char('cst_csosn');
            $table->char('cst_pis');
            $table->char('cst_cofins');
            $table->char('cst_ipi');
            $table->float('perc_icms');
            $table->float('perc_pis');
            $table->float('perc_cofins');
            $table->float('perc_ipi');
            $table->timestamps();

            // Chave estrangeira
            $table->foreign('acabamento_id')->references('id')->on('acabamentos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
