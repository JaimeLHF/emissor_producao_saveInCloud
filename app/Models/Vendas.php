<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendas extends Model
{
    use HasFactory;


    protected $fillable = [
        'valorTotal', 'cliente_id', 'chave', 'numero_nfe', 'status', 'sequencia_evento', 'natOp', 'finNFe', 'motivo_rejeitado', 'vFrete', 'modFrete'
    ];


    public static function rules()
    {
        return [
            'valorTotal' => 'required|numeric',
            'cliente_id' => 'required|exists:clientes,id',
            'chave' => 'required|string',
            'numero_nfe' => 'required|numeric',
            'status' => 'required|string',
            'sequencia_evento' => 'required|numeric',
            'finNFe' => 'required|numeric',
            'vFrete' => 'required|numeric',
            'modFrete' => 'required|string', 
            'natOp' => 'required|string',
            'infCpl' => 'string'
        ];
    }

    public static function ultimoNumeroNFe()
    {
        $venda = Vendas::orderBy('numero_nfe', 'desc')->first();

        $emitente = Emitente::first();

        if ($emitente == null) {
            return $venda->numero_nfe;
        }

        if ($emitente->ultimo_numero_nfe > $venda->numero_nfe) {
            return $emitente->ultimo_numero_nfe;
        }
        return $venda->numero_nfe;
    }

    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'cliente_id');
    }

    public function itens()
    {
        return $this->hasMany(ItensVenda::class, 'venda_id');
    }

    public function fatura()
    {
        return $this->hasMany(Fatura::class, 'venda_id', 'id');
    }

    public function xml()
    {
        return $this->hasMany(XML::class, 'venda_id');
    }
}
