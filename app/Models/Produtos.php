<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Acabamentos;

class Produtos extends Model
{
    use HasFactory;

	protected $fillable = [
		'nome', 'acabamento_id', 'valor', 'cfop_interno', 'cfop_externo', 'ncm', 'codigo_barras', 
		'und_venda', 'cst_csosn', 'cst_pis', 'cst_cofins', 'cst_ipi', 'perc_icms', 'perc_pis',
		'perc_cofins', 'perc_ipi', 'orig'
	];


	public static function rules()
    {
        return [
			'nome' => 'required|string|max:50',
			'acabamento_id' => 'required|exists:acabamentos,id',
			'valor' => 'required|numeric',
			'cfop_interno' => 'required|string',
			'cfop_externo' => 'required|string',
			'ncm' => 'required|string',
			'und_venda' => 'required|string',
			'cst_csosn' => 'required|string',
			'cst_pis' => 'required|string',
			'cst_cofins' => 'required|string',
			'cst_ipi' => 'required|string',
			'perc_icms' => 'required|numeric',
			'perc_pis' => 'required|numeric',
			'perc_cofins' => 'required|numeric',
			'perc_ipi' => 'required|numeric',
			'orig' => 'required|numeric',
        ];
    }


	public function acabamento()
	{
		return $this->belongsTo(Acabamentos::class, 'acabamento_id');
	}
}
