<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produtos;
use Illuminate\Validation\ValidationException;

class ProdutoController extends Controller
{
    //GET todos os produtos - com o array do acabamento
    public function getAllProducts()
    {
        try {

            $produtos = Produtos::with('acabamento')->get();

            if (!$produtos) {
                return response()->json(['message' => 'Produtos não encontrado'], 404);
            }

            return response()->json(['produtos:' => $produtos], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function getBydId($id)
    {
        try {

            $produto = Produtos::with('acabamento')->find($id);

            if (!$produto) {
                return response()->json(['message' => 'Produto não econtrado!'], 404);
            }            

            return response()->json(['produto' => $produto], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }

    //Novo produto
    public function newProduto(Request $request)
    {  

        try {

            $existingProduto = Produtos::where('nome', $request->input('nome'))->first();

            if ($existingProduto) {
                return response()->json(['message' => 'Produto com o mesmo nome já existe'], 422);
            }

            $request->validate(Produtos::rules());
         
            $produto = Produtos::create($request->all());

            return response()->json(['message' => 'Produto criado com sucesso', 'produto' => $produto], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Campos obrigatórios não preenchidos. Verifique!', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'errors' => $e], 500);
        }
    }


    public function updateBydId(Request $request, $id)
    {
        try {
            $produto = Produtos::find($id);

            if (!$produto) {
                return response()->json(['message' => 'Produto não econtrado!'], 404);
            }

            $produto->update($request->all());
            return response()->json(['produto' => $produto], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function deleteBydId($id)
    {
        try {
            $produto = Produtos::find($id);

            if (!$produto) {
                return response()->json(['message' => 'Produto não econtrado!'], 404);
            }

            $produto->delete();

            return response()->json(['message' => 'Produto deletado com sucesso!', 'acabamento' => $produto], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }

}
