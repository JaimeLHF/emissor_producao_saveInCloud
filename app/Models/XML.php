<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XML extends Model
{
    use HasFactory;

    protected $fillable = [
        'venda_id', 'xml', 'status'
    ];


    public function venda()
    {
        return $this->belongsTo(Vendas::class, 'venda_id');
    }
}