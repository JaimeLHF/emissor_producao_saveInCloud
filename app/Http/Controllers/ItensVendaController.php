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
    
                return response()->json(['itens' => $item], 200);
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
    
                return response()->json(['item' => $item], 200);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
            }
        }
    
        //Novo item - vai ser criado no controller Venda
        // public function newProduto(Request $request)
        // {  
    
        //     try {
    
        //         $existingProduto = ItensVenda::where('nome', $request->input('nome'))->first();
    
        //         if ($existingProduto) {
        //             return response()->json(['message' => 'Produto com o mesmo nome já existe'], 422);
        //         }
    
        //         $request->validate(ItensVenda::rules());
             
        //         $produto = ItensVenda::create($request->all());
    
        //         return response()->json(['message' => 'Produto criado com sucesso', 'produto' => $produto], 201);
        //     } catch (ValidationValidationException $e) {
        //         return response()->json(['message' => 'Campos obrigatórios não preenchidos. Verifique!', 'errors' => $e->errors()], 422);
        //     } catch (\Exception $e) {
        //         return response()->json(['message' => 'Erro interno no servidor', 'errors' => $e], 500);
        //     }
        // }
    
    
        //Update Item - vai ser atualizado no controller Venda 
        // public function updateBydId(Request $request, $id)
        // {
        //     try {
        //         $produto = ItensVenda::find($id);
    
        //         if (!$produto) {
        //             return response()->json(['message' => 'Produto não econtrado!'], 404);
        //         }
    
        //         $produto->update($request->all());
        //         return response()->json(['produto' => $produto], 200);
        //     } catch (\Exception $e) {
        //         return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        //     }
        // }
    
        //Deletar Item - Vai ser deletado pelo controller Venda 
        // public function deleteBydId($id)
        // {
        //     try {
        //         $produto = ItensVenda::find($id);
    
        //         if (!$produto) {
        //             return response()->json(['message' => 'Produto não econtrado!'], 404);
        //         }
    
        //         $produto->delete();
    
        //         return response()->json(['message' => 'Produto deletado com sucesso!', 'acabamento' => $produto], 200);
        //     } catch (\Exception $e) {
        //         return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        //     }
        // }
    
}
