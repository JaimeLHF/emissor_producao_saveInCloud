<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produtos;
use Illuminate\Validation\ValidationException;

class ProdutoController extends Controller
{

    public function getAllProducts()
    {
        try {
            header('Acess-Control-Allow-Origin: http://localhost:5173');
            header('Acess-Control-Allow-Methods: PUT, PATCH, POST, DELETE');
            $produtos = Produtos::with('acabamento')->get();

            if (!$produtos) {
                return response()->json(['message' => 'Produtos não encontrado'], 404);
            }

            return response()->json($produtos, 200);
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

    public function newProduto(Request $request)
    {
        try {

            // $request->validate([$request->all()]);
            header('Acess-Control-Allow-Origin: http://localhost:5173');
            header('Acess-Control-Allow-Methods: PUT, PATCH, POST, DELETE');
            $produtosCriados = [];

            foreach ($request->produtos as $produtoData) {
                $produto = Produtos::create($produtoData);
                $produtosCriados[] = $produto;
            }

            return response()->json(['message' => 'Produtos criados com sucesso', 'produtos' => $produtosCriados], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Campos obrigatórios não preenchidos. Verifique!', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'errors' => $e->getMessage()], 500);
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
