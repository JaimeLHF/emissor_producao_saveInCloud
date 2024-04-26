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
        Schema::create('faturas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('venda_id');
            $table->foreign('venda_id')->references('id')->on('vendas')->onDelete('cascade');
            $table->string('status');
            $table->float('valor_ipi');
            $table->decimal('valor', 10, 2);        
            $table->string('forma_pagamento');
            $table->date('vencimento');
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faturas');
    }
};
