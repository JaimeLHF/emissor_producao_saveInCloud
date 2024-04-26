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
        Schema::create('xml_vendas', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('venda_id');                 
            $table->timestamps();

            $table->foreign('venda_id')->references('id')
                ->on('vendas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
