<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'cpf_cnpj', 'ie_rg', 'contribuinte', 'cep', 'rua', 'numero', 'codigo_municipio', 
        'codigo_pais', 'pais', 'bairro', 'logradouro', 'municipio', 'uf', 'complemento', 'telefone', 'email'
    ];

    public static function rules()
    {
        return [
            'nome' => 'required|string',
            'cpf_cnpj' => 'required|string',
            'ie_rg' => 'required|string',
            'contribuinte' => 'required|boolean',
            'cep' => 'required|string',
            'rua' => 'required|string',
            'numero' => 'required|string',
            'codigo_municipio' => 'required|numeric',
            'codigo_pais' => 'required|numeric',
            'pais' => 'required|string',
            'bairro' => 'required|string',
            'logradouro' => 'string',
            'municipio' => 'required|string',
            'uf' => 'required|string',
            'complemento' => 'required|string',
            'telefone' => 'required|string',
            'email' => 'required|string',
        ];
    }
}
