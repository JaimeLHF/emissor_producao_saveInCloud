<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fatura extends Model
{
    use HasFactory;

    protected $fillable = [
        'venda_id', 'forma_pagamento', 'valor', 'vencimento', 'status','valor_ipi'
    ];

    public static function rules()
    {
        return [
            'venda_id' => 'required|exists:venda,id',
            'forma_pagamento' => 'required|string',
            'vencimento' => 'required|date',
            'valor' => 'required|numeric',
            'status' => 'required|string',
            'valor_ipi' => 'required|numeric',
        ];
    }

    public function venda()
    {
        return $this->belongsTo(Vendas::class, 'venda_id');
    }

    public static function formasPagamento()
    {
        return [
            '01' => 'Dinheiro',
            '02' => 'Cheque',
            '03' => 'Cartão de Crédito',
            '04' => 'Cartão de Débito',
            '05' => 'Crédito Loja',
            '10' => 'Vale Alimentação',
            '11' => 'Vale Refeição',
            '12' => 'Vale Presente',
            '13' => 'Vale Combustível',
            '14' => 'Duplicata Mercantil',
            '15' => 'Boleto Bancário',
            '16' => 'Depósito Bancário',
            '17' => 'Pagamento Instantâneo (PIX)',
            '90' => 'Sem pagamento',
            '99' => 'Outros',
        ];
    }

    public static function bandeiraCartao()
    {
        return [
            '01' => 'Visa',
            '02' => 'Mastercard',
            '03' => 'American Express',
            '04' => 'Sorocred',
            '05' => 'Diners Club',
            '06' => 'Elo',
            '07' => 'Aura',
            '08' => 'Cabal',
            '09' => 'Cabal',
            '99' => 'Outros'
        ];
    }
}
