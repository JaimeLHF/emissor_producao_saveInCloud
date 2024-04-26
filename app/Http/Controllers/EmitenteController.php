<?php

namespace App\Http\Controllers;

use App\Models\Emitente;
use Illuminate\Http\Request;

class EmitenteController extends Controller
{
    public function getEmitente()
    {
        try {
            $emitente = Emitente::get();

            if (!$emitente) {
                return response()->json(['message' => 'Emitente não encontrado'], 404);
            }

            $emitente->makeHidden(['certificado']);

            return response()->json(['emitente:' => $emitente], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function getBydId($id)
    {
        try {
            $emitente = Emitente::find($id);

            if (!$emitente) {
                return response()->json(['message' => 'Emitente não econtrado!'], 404);
            }

            $emitente->makeHidden(['certificado']);

            return response()->json(['emitente' => $emitente], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }


    public function updateBydId(Request $request, $id)
    {
        try {
            $emitente = Emitente::find($id);

            if (!$emitente) {
                return response()->json(['message' => 'Emitente não econtrado!'], 404);
            }

            $emitente->update($request->all());
            
            return response()->json(['emitente' => $emitente], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function newEmitente(Request $request)
    {

        try {

            $request->validate(Emitente::rules());

            if ($request->hasFile('certificado')) {
                $file = $request->file('certificado');
                $ctx = file_get_contents($file);
                $request->merge(['certificado' => $ctx]);
            }

            $emitente = Emitente::create($request->all());

            return response()->json(['emitente:' => $emitente], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function deleteBydId($id)
    {
        try {
            $emitente = Emitente::find($id);

            if (!$emitente) {
                return response()->json(['message' => 'Emitente não econtrado!'], 404);
            }

            $emitente->delete();

            return response()->json(['message' => 'Emitente deletado com sucesso!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno no servidor', 'error' => $e->getMessage()], 500);
        }
    }
}
