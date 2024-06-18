<?php

namespace App\Http\Controllers;

use App\Models\Transportadora;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransportadoraController extends Controller
{
    //GET todos os acabamentos
    public function getAllTransportadoras()
    {
        try {
            $transportadora = Transportadora::get();

            if (!$transportadora) {
                return response()->json(['message' => 'Transportadoras não encontrado'], 404);
            }

            return response()->json($transportadora, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }


    public function getBydId($id)
    {
        try {
            $transportadora = Transportadora::find($id);

            if (!$transportadora) {
                return response()->json(['message' => 'Transportadora não econtrado!'], 404);
            }

            return response()->json($transportadora, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }

        //Nova transportadora
        public function newTrans(Request $request)
        {
    
            try {
    
                $existingTrans = Transportadora::where('cpf_cnpj', $request->input('cpf_cnpj'))->first();
    
                if ($existingTrans) {
                    return response()->json(['message' => 'Transportadora já existe'], 422);
                }
    
                $request->validate(Transportadora::rules());
    
                $trans = Transportadora::create($request->all());
    
                return response()->json(['message' => 'Transportadora criado com sucesso', $trans], 201);
            } catch (ValidationException $e) {
                return response()->json(['message' => 'Erro de validação', 'errors' => $e->errors()], 422);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Erro interno no servidor'], 500);
            }
        }

        public function updateById(Request $request, $id)
        {
            try {
                $trans = Transportadora::find($id);
    
                if (!$trans) {
                    return response()->json(['message' => 'Transportadora não econtrado!'], 404);
                }
                
                $trans->update($request->all());
                return response()->json($trans, 200);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
            }
        }
    
    
        public function deleteById($id)
        {
            try {
                $trans = Transportadora::find($id);
    
                if (!$trans) {
                    return response()->json(['message' => 'Transportadora não econtrado!'], 404);
                }
    
                $trans->delete();
    
                return response()->json(['message' => 'Transportadora deletado com sucesso!', 'transportadora' => $trans], 200);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
            }
        }
}
