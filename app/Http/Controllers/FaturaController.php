<?php

namespace App\Http\Controllers;

use App\Models\Fatura;
use Illuminate\Http\Request;

class FaturaController extends Controller
{
    //GET todos as faturas da venda - com os produtos
    public function getAllFaturas()
    {
        try {

            $fatura = Fatura::get();

            if (!$fatura) {
                return response()->json(['message' => 'fatura não encontrada'], 404);
            }

            return response()->json($fatura, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function getBydId($id)
    {
        try {

            $fatura = Fatura::find($id);

            if (!$fatura) {
                return response()->json(['message' => 'Fatura não econtrado!'], 404);
            }

            return response()->json($fatura, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }
}
