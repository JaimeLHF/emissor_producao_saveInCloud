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
            
            // $request->validate([$request->all()]);

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

    public function gerarArray()
    {

        // Exemplo de uso
        $dados = [
            ["nome" => "ATHENAS", "acabamento_id" => 1],
            ["nome" => "ATHENAS", "acabamento_id" => 8],
            ["nome" => "BELICHE STAR PLUS", "acabamento_id" => 2],
            ["nome" => "BERLIM/MATHILDE", "acabamento_id" => 1],
            ["nome" => "BERLIM/MATHILDE", "acabamento_id" => 8],
            ["nome" => "BILA", "acabamento_id" => 2],
            ["nome" => "BILA", "acabamento_id" => 9],
            ["nome" => "BILA", "acabamento_id" => 1],
            ["nome" => "BILA", "acabamento_id" => 8],
            ["nome" => "CAMA DOCE SONINHO", "acabamento_id" => 3],
            ["nome" => "CWB", "acabamento_id" => 9],
            ["nome" => "CWB", "acabamento_id" => 8],
            ["nome" => "FLORENÇA", "acabamento_id" => 2],
            ["nome" => "FLORENÇA", "acabamento_id" => 1],
            ["nome" => "FLORIPA", "acabamento_id" => 2],
            ["nome" => "FLORIPA", "acabamento_id" => 8],
            ["nome" => "HANNOVER/CLEMENTINA", "acabamento_id" => 9],
            ["nome" => "HANNOVER/CLEMENTINA", "acabamento_id" => 1],
            ["nome" => "HANNOVER/CLEMENTINA", "acabamento_id" => 8],
            ["nome" => "LISBOA", "acabamento_id" => 9],
            ["nome" => "MALAGA", "acabamento_id" => 2],
            ["nome" => "MALAGA", "acabamento_id" => 9],
            ["nome" => "MALAGA", "acabamento_id" => 1],
            ["nome" => "MALAGA", "acabamento_id" => 8],
            ["nome" => "NAPOLES", "acabamento_id" => 2],
            ["nome" => "PAINEL 02", "acabamento_id" => 9],
            ["nome" => "PAINEL 02", "acabamento_id" => 8],
            ["nome" => "PAINEL MENDONZA", "acabamento_id" => 2],
            ["nome" => "PAINEL MENDONZA", "acabamento_id" => 9],
            ["nome" => "PAINEL MENDONZA", "acabamento_id" => 1],
            ["nome" => "PAINEL 02", "acabamento_id" => 8],
            ["nome" => "SP 02", "acabamento_id" => 1],
            ["nome" => "SP 02", "acabamento_id" => 8],
            ["nome" => "SP 03", "acabamento_id" => 2],
            ["nome" => "SP 03", "acabamento_id" => 9],
            ["nome" => "SP 03", "acabamento_id" => 1],
            ["nome" => "SP 03", "acabamento_id" => 8],
            ["nome" => "SP 04", "acabamento_id" => 9],
            ["nome" => "SP 04", "acabamento_id" => 1],
            ["nome" => "SP 04", "acabamento_id" => 8],
            ["nome" => "TRINITY", "acabamento_id" => 2],
            ["nome" => "TRINITY", "acabamento_id" => 1]
        ];


        $resultado = [];

        foreach ($dados as $item) {
            $resultado[] = [
                'nome' => $item['nome'],
                'acabamento_id' => $item['acabamento_id'],
                "valor" => 1,
                "cfop_interno" => "5910",
                "cfop_externo" => "6910",
                "ncm" => "44013900",
                "codigo_barras" => "SEM GTIN",
                "und_venda" => "UN",
                "cst_csosn" => "41",
                "cst_pis" => "01",
                "cst_cofins" => "01",
                "cst_ipi" => "50",
                "perc_icms" => 0,
                "perc_pis" => 1.65,
                "perc_cofins" => 7.60,
                "perc_ipi" => 3.25,
                "orig" => "0"
            ];
        }
        return $resultado;
    }
}
