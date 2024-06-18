<?php

namespace App\Http\Controllers;

use App\Models\ItensVenda;

class ItensVendaController extends Controller
{
        //GET todos os itens da venda - com os produtos
        public function getAllItens()
        {
            try {
    
                $item = ItensVenda::with('produto')->get();
    
                if (!$item) {
                    return response()->json(['message' => 'item não encontrado'], 404);
                }
    
                return response()->json($item, 200);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
            }
        }
    
        public function getBydId($id)
        {
            try {
    
                $item = ItensVenda::with('produto')->find($id);
    
                if (!$item) {
                    return response()->json(['message' => 'Item não econtrado!'], 404);
                }            
    
                return response()->json($item, 200);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
            }
        }  
}
