<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClienteController extends Controller
{
    //GET todos os acabamentos
    public function getAllClientes()
    {
        try {
            $clientes = Clientes::get();

            if (!$clientes) {
                return response()->json(['message' => 'Clientes não encontrado'], 404);
            }

            return response()->json($clientes, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function getBydId($id)
    {
        try {
            $cliente = Clientes::find($id);

            if (!$cliente) {
                return response()->json(['message' => 'Cliente não econtrado!'], 404);
            }

            return response()->json($cliente, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }

    //Novo cliente
    public function newCliente(Request $request)
    {

        try {

            $existingCliente = Clientes::where('cpf_cnpj', $request->input('cpf_cnpj'))->first();

            if ($existingCliente) {
                return response()->json(['message' => 'Cliente já existe'], 422);
            }

            $request->validate(Clientes::rules());

            $cliente = Clientes::create($request->all());

            return response()->json(['message' => 'Cliente criado com sucesso', $cliente], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor'], 500);
        }
    }


    public function updateById(Request $request, $id)
    {
        try {
            $cliente = Clientes::find($id);

            if (!$cliente) {
                return response()->json(['message' => 'Cliente não econtrado!'], 404);
            }
            
            $cliente->update($request->all());
            return response()->json($cliente, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }


    public function deleteById($id)
    {
        try {
            $cliente = Clientes::find($id);

            if (!$cliente) {
                return response()->json(['message' => 'Cliente não econtrado!'], 404);
            }

            $cliente->delete();

            return response()->json(['message' => 'Cliente deletado com sucesso!', 'cliente' => $cliente], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }
}
