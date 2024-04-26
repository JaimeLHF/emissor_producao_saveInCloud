<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItensVenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'venda_id', 'produto_id', 'qtd', 'valor'
    ];


    public function produto()
    {
        return $this->belongsTo(Produtos::class);
    }

    public function venda()
    {
        return $this->belongsTo(Vendas::class, 'venda_id');
    }


    public static function rules()
    {
        return [
            'produto_id' => 'required|exists:produto,id',
            'qtd' => 'required|numeric',
            'valor' => 'required|numeric',
        ];
    }
}
