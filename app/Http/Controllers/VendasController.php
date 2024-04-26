<?php

namespace App\Http\Controllers;

use App\Models\Fatura;
use App\Models\ItensVenda;
use App\Models\Produtos;
use App\Models\Vendas;
use Illuminate\Http\Request;

class VendasController extends Controller
{
    public function save(Request $request)
    {
        try {
            $venda = $request->venda;

            $valorTotal = 0;

            // Calcula a soma dos valores dos itens da venda
            foreach ($venda['itens'] as $i) {
                $valorTotal += floatval(str_replace(",", ".", $i['valor'])) * intval($i['qtd']);
            }

            // Cria a venda com o valorTotal calculado
            $result = Vendas::create([
                'valorTotal' => $valorTotal,
                'cliente_id' => $venda['cliente_id'],
                'sequencia_evento' => $venda['sequencia_evento'],
                'natOp' => $venda['natOp'],
                'finNFe' => $venda['finNFe'],
                'chave' => '',
                'numero_nfe' => 0,
                'modFrete' => $venda['modFrete'],
                'vFrete' => $venda['vFrete'],
                'status' => 'Novo'
            ]);

            //Itens da venda
            foreach ($venda['itens'] as $i) {

                ItensVenda::create([
                    'valor' => str_replace(",", ".", $i['valor']),
                    'qtd' => $i['qtd'],
                    'venda_id' => $result->id,
                    'produto_id' => $i['id']

                ]);
            }

            //Fatura/Duplicatas da venda
            foreach ($venda['fatura'] as $f) {
                $produto = Produtos::findOrFail($i['id']);
                $valorIPI = floatval(str_replace(",", ".", $f['valor'])) * ($produto->perc_ipi / 100);

                Fatura::create([
                    'valor' => str_replace(",", ".", $f['valor']),
                    'venda_id' => $result->id,
                    'vencimento' => \Carbon\Carbon::parse(str_replace("/", "-", $f['vencimento']))->format('Y-m-d'),
                    'forma_pagamento' => $f['forma_pagamento'],
                    'status' => $f['status'],
                    'valor_ipi' => $valorIPI + str_replace(",", ".", $f['valor'])
                ]);
            }

            return response()->json($venda, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 404);
        }
    }

    //GET todos os itens da venda - com os produtos
    public function getAllVendas()
    {
        try {

            $venda = Vendas::with('cliente', 'itens.produto', 'fatura')->get();

            if (!$venda) {
                return response()->json(['message' => 'venda não encontrada'], 404);
            }

            return response()->json(['vendas' => $venda], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateById(Request $request, $id)
    {
        try {
            $venda = Vendas::find($id);           


            if (!$venda) {
                return response()->json(['message' => 'venda não econtrado!'], 404);
            }

            if ($venda->status == "Aprovado" || $venda->chave != null) {
                return response()->json(['Venda com NFe já transmitida! Impossível alterar'], 400);
            }         

            $venda->update($request->all());

            return response()->json(['Venda atualizada' => $venda], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function getBydId($id)
    {
        try {

            $item = Vendas::with('cliente', 'itens.produto', 'fatura')->find($id);

            if (!$item) {
                return response()->json(['message' => 'Venda não econtrada!'], 404);
            }

            return response()->json(['venda' => $item], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }


    public function deleteById($id)
    {
        try {
            $venda = Vendas::find($id);

            if (!$venda) {
                return response()->json(['message' => 'Venda não econtrado!'], 404);
            }

            $venda->delete();

            return response()->json(['message' => 'Venda deletado com sucesso!', 'venda' => $venda], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }
}
