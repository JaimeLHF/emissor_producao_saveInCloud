<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emitente extends Model
{
    use HasFactory;

    protected $fillable = [
        'razao_social', 'nome_fantasia', 'situacao_tributaria', 'rua', 'numero_endereco', 'bairro', 'cep',
        'municipio', 'uf', 'codigo_uf', 'pais', 'codigo_pais', 'codigo_municipio', 'complemento', 'logradouro', 'ultimo_numero_nfe',
        'numero_serie_nfe', 'sequencia_evento', 'cpf_cnpj', 'ie_rg', 'fone', 'certificado', 'senha', 'percentual_aliquota_icms',
        'ambiente',
    ];

    public static function rules()
    {
        return [
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'required|string|max:255',
            'situacao_tributaria' => 'required|integer',
            'rua' => 'required|string|max:255',
            'numero_endereco' => 'required|string|max:50',
            'bairro' => 'required|string|max:255',
            'cep' => 'required|string|max:15',
            'municipio' => 'required|string|max:255',
            'codigo_uf' => 'required|string|max:2',
            'uf' => 'required|string|max:2',
            'pais' => 'required|string|max:255',
            'codigo_pais' => 'required|string|max:10',
            'codigo_municipio' => 'required|string|max:10',
            'complemento' => 'nullable|string|max:255',
            'logradouro' => 'nullable|string|max:255',
            'ultimo_numero_nfe' => 'required|integer',
            'numero_serie_nfe' => 'required|integer',
            'sequencia_evento' => 'required|integer',
            'cpf_cnpj' => 'required|string|max:20',
            'ie_rg' => 'string|string|max:20',
            'fone' => 'string|string|max:20',
            'certificado' => 'required|file',
            'senha' => 'required|string',
            'percentual_aliquota_icms' => 'required|numeric',
            'ambiente' => 'required|integer',
        ];
    }
}
