<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Acabamentos;
use Illuminate\Validation\ValidationException;

class AcabamentoController extends Controller
{
    //GET todos os acabamentos
    public function getAllAcabamentos()
    {
        try {
            $acabamento = Acabamentos::get();

            if (!$acabamento) {
                return response()->json(['message' => 'Acabamentos não encontrado'], 404);
            }

            return response()->json($acabamento, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function getBydId($id)
    {
        try {
            $acabamento = Acabamentos::find($id);

            if (!$acabamento) {
                return response()->json(['message' => 'Acabamento não econtrado!'], 404);
            }

            return response()->json($acabamento, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }

    //Novo acabamento
    public function newAcabamento(Request $request)
    {
     
        try {

            $existingAcabamento = Acabamentos::where('nome', $request->input('nome'))->first();

            if ($existingAcabamento) {
                return response()->json(['message' => 'Acabamento com o mesmo nome já existe'], 422);
            }

            $request->validate(Acabamentos::rules());

            $acabamento = Acabamentos::create($request->all());

            return response()->json(['message' => 'Acabamento criado com sucesso', 'acabamento' => $acabamento], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor'], 500);
        }
    }


    public function updateBydId(Request $request, $id)
    {
        try {
            $acabamento = Acabamentos::find($id);

            if (!$acabamento) {
                return response()->json(['message' => 'Acabamento não econtrado!'], 404);
            }

            $acabamento->update($request->all());
            return response()->json($acabamento, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }


    public function deleteBydId($id)
    {
        try {
            $acabamento = Acabamentos::find($id);

            if (!$acabamento) {
                return response()->json(['message' => 'Acabamento não econtrado!'], 404);
            }

            $acabamento->delete();

            return response()->json(['message' => 'Acabamento deletado com sucesso!', 'acabamento' => $acabamento], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }

}
