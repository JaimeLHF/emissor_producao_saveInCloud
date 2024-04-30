<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendas;
use App\Models\Emitente;
use App\Models\XML;
use App\Services\NFeService;
use InvalidArgumentException;
use NFePHP\DA\NFe\Danfe;

class NFeController extends Controller
{

    public function gerarXml($id)
    {
        try {
            $venda = Vendas::with('cliente', 'itens.produto', 'fatura')->find($id);
            $emitente = Emitente::first();

            if ($emitente == null) {
                return response()->json(['message' => 'Emitente não encontrado'], 404);
            }

            $cnpj = str_replace(".", "", $emitente->cpf_cnpj);
            $cnpj = str_replace("/", "", $cnpj);
            $cnpj = str_replace("-", "", $cnpj);
            $cnpj = str_replace(" ", "", $cnpj);

            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$emitente->ambiente,
                "razaosocial" => $emitente->razao_social,
                "siglaUF" => $emitente->uf,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => "AAAAAAA",
                "CSCid" => "000001"
            ], $emitente);

            $result = $nfe_service->gerarXml($venda, $emitente);

            if (!isset($result['erros_xml'])) {
                $xml = $result['xml'];
                return response($xml)->header('Content-Type', 'application/xml');
            } else {
                return response()->json(['message' => $result['erros_xml']], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function transmitir(Request $request)
    {
        try {

            $venda = Vendas::with('xml')->find($request->venda_id);
            $emitente = Emitente::first();

            if ($emitente == null) {
                return response()->json('Configure o emitente', 404);
            }

            $cnpj = str_replace(".", "", $emitente->cpf_cnpj);
            $cnpj = str_replace("/", "", $cnpj);
            $cnpj = str_replace("-", "", $cnpj);
            $cnpj = str_replace(" ", "", $cnpj);

            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$emitente->ambiente,
                "razaosocial" => $emitente->razao_social,
                "siglaUF" => $emitente->uf,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => "AAAAAAA",
                "CSCid" => "000001"
            ], $emitente);

            if ($venda->status == 'Rejeitado' || $venda->status == 'Novo') {
                $result = $nfe_service->gerarXml($venda, $emitente);

                if (!isset($result['erros_xml'])) {
                    $signed = $nfe_service->sign($result['xml']);
                    $resultado = $nfe_service->transmitir($signed, $result['chave']);
                    if (isset($resultado['sucesso'])) {
                        $venda->chave = $result['chave'];
                        $venda->status = 'Aprovado';
                        $venda->numero_nfe = $result['nNf'];

                        XML::create(
                            [

                                'venda_id' => $venda->id,
                                'status' => $venda->status,
                                'xml' => $resultado['sucesso']

                            ]
                        );

                        $venda->save();

                        return response()->json('NFe emitida com sucesso! Nota nº : ' . $result['nNf'] . ' Chave de acesso: ' . $result['chave'], 200);
                    } else {
                        $venda->status = 'Rejeitado';
                        $venda->motivo_rejeitado = $resultado['erro'];
                        $venda->save();
                        return response()->json($resultado['erro'], 401);
                    }
                } else {
                    return response()->json($result['erros_xml'], 404);
                }
            } else {
                return response()->json("Error", 404);
            }

            return response()->json($venda, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 404);
        }
    }

    public function cancenlarNFe(Request $request)
    {
        try {

            $venda = Vendas::with('xml')->find($request->venda_id);
            $emitente = Emitente::first();
            $xml_venda = XML::where('venda_id', $venda->id)->first();

            if ($emitente == null) {
                return response()->json('Configure o emitente', 404);
            }

            $cnpj = str_replace(".", "", $emitente->cpf_cnpj);
            $cnpj = str_replace("/", "", $cnpj);
            $cnpj = str_replace("-", "", $cnpj);
            $cnpj = str_replace(" ", "", $cnpj);

            $nfe_service = new NFeService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$emitente->ambiente,
                "razaosocial" => $emitente->razao_social,
                "siglaUF" => $emitente->uf,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => "AAAAAAA",
                "CSCid" => "000001"
            ], $emitente);

            $nfe = $nfe_service->cancelar($venda, $request->justificativa, $xml_venda->xml);

            if ($venda->status == 'Novo') {
                return response()->json('Status venda: ' . $venda->status, 404);
            }

            if (!isset($nfe['erro'])) {

                $venda->status = 'Cancelado';
                $venda->valorTotal = 0;
                $venda->save();

                $xml_venda->status = 'Cancelado';
                $xml_venda->xml = $nfe['sucesso'];
                $xml_venda->save();

                return response()->json($nfe, 200);
            } else {
                return response()->json($nfe['data'], 404);
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 404);
        }
    }

    public function consultaNFe($id)
    {
        $venda = Vendas::with('cliente', 'itens.produto', 'fatura')->find($id);
        $emitente = Emitente::first();

        $cnpj = str_replace(".", "", $emitente->cpf_cnpj);
        $cnpj = str_replace("/", "", $cnpj);
        $cnpj = str_replace("-", "", $cnpj);
        $cnpj = str_replace(" ", "", $cnpj);

        $nfe_service = new NFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$emitente->ambiente,
            "razaosocial" => $emitente->razao_social,
            "siglaUF" => $emitente->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => "AAAAAAA",
            "CSCid" => "000001"
        ], $emitente);

        $result = $nfe_service->consultaNFe($venda);

        return response()->json($result, 200);
    }

    public function vincular(Request $request)
    {
        $venda = Vendas::with('cliente', 'itens.produto', 'fatura')->find($request->venda_id);
        $emitente = Emitente::first();
        $xml_venda = XML::where('venda_id', $venda->id)->first();
 
        $cnpj = str_replace(".", "", $emitente->cpf_cnpj);
        $cnpj = str_replace("/", "", $cnpj);
        $cnpj = str_replace("-", "", $cnpj);
        $cnpj = str_replace(" ", "", $cnpj);

        $nfe_service = new NFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$emitente->ambiente,
            "razaosocial" => $emitente->razao_social,
            "siglaUF" => $emitente->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => "AAAAAAA",
            "CSCid" => "000001"
        ], $emitente);
        
        $result = $nfe_service->consultaNFe($venda);

        $nfe = $nfe_service->vincularCancelamento($venda, $xml_venda->xml);

        $xml_venda->xml = $nfe['sucesso'];
        $xml_venda->save();

        return response()->json($result, 200);
    }

    public function download($id)
    {
        try {
            try {

                $venda = Vendas::with('xml')->find($id);
                $xmlContent  = $venda->xml->first()->xml;
            } catch (\Exception $e) {
                return response()->json('XML da venda ' . $venda->id . ' não encontrado',  404);
            }
            $fileName = 'venda_' . $venda->id . '.xml';

            $tempFilePath = storage_path('app/' . $fileName);
            file_put_contents($tempFilePath, $xmlContent);
            return response()->download($tempFilePath, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function imprimir($id)
    {
        date_default_timezone_set('America/Sao_Paulo');
        $venda = Vendas::with('xml')->find($id);
        $xmlContent  = $venda->xml->first()->xml;

        $xml = $xmlContent;
        $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents(realpath('../public/drd_logo.jpg')));

        try {

            $danfe = new Danfe($xml);
            $danfe->exibirTextoFatura = false;
            $danfe->exibirPIS = false;
            $danfe->exibirIcmsInterestadual = false;
            $danfe->exibirValorTributos = false;
            $danfe->descProdInfoComplemento = false;
            $danfe->exibirNumeroItemPedido = false;
            $danfe->setOcultarUnidadeTributavel(true);
            $danfe->obsContShow(false);
            $danfe->printParameters(
                $orientacao = 'P',
                $papel = 'A4',
                $margSup = 2,
                $margEsq = 2
            );
            // $danfe->logoParameters($logo, $logoAlign = 'C', $mode_bw = false);
            $danfe->setDefaultFont($font = 'times');
            $danfe->setDefaultDecimalPlaces(4);
            $danfe->debugMode(false);
            $danfe->creditsIntegratorFooter('by FuckingSystem');
            //$danfe->epec('891180004131899', '14/08/2018 11:24:45'); //marca como autorizada por EPEC

            // Caso queira mudar a configuracao padrao de impressao
            /*  $this->printParameters( $orientacao = '', $papel = 'A4', $margSup = 2, $margEsq = 2 ); */
            // Caso queira sempre ocultar a unidade tributável
            /*  $this->setOcultarUnidadeTributavel(true); */
            //Informe o numero DPEC
            /*  $danfe->depecNumber('123456789'); */
            //Configura a posicao da logo
            $danfe->logoParameters($logo, 'C', false);
            //Gera o PDF
            $pdf = $danfe->render($logo);
            header('Content-Type: application/pdf');
            echo $pdf;
        } catch (InvalidArgumentException $e) {
            echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
        }
    }

    public function imprimirNota($numero_nfe)
    {
        date_default_timezone_set('America/Sao_Paulo');
        $venda = Vendas::with('xml')->where('numero_nfe', $numero_nfe)->first();

        $xmlContent  = $venda->xml->first()->xml;

        $xml = $xmlContent;
        $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents(realpath('../public/drd_logo.jpg')));

        try {

            $danfe = new Danfe($xml);
            $danfe->exibirTextoFatura = false;
            $danfe->exibirPIS = false;
            $danfe->exibirIcmsInterestadual = false;
            $danfe->exibirValorTributos = false;
            $danfe->descProdInfoComplemento = false;
            $danfe->exibirNumeroItemPedido = false;
            $danfe->setOcultarUnidadeTributavel(true);
            $danfe->obsContShow(false);
            $danfe->printParameters(
                $orientacao = 'P',
                $papel = 'A4',
                $margSup = 2,
                $margEsq = 2
            );
            $danfe->logoParameters($logo, $logoAlign = 'C', $mode_bw = false);
            $danfe->setDefaultFont($font = 'times');
            $danfe->setDefaultDecimalPlaces(4);
            $danfe->debugMode(false);
            $danfe->creditsIntegratorFooter('by FuckingSystem');
            //$danfe->epec('891180004131899', '14/08/2018 11:24:45'); //marca como autorizada por EPEC

            // Caso queira mudar a configuracao padrao de impressao
            /*  $this->printParameters( $orientacao = '', $papel = 'A4', $margSup = 2, $margEsq = 2 ); */
            // Caso queira sempre ocultar a unidade tributável
            /*  $this->setOcultarUnidadeTributavel(true); */
            //Informe o numero DPEC
            /*  $danfe->depecNumber('123456789'); */
            //Configura a posicao da logo
            // $danfe->logoParameters($logo, 'C', false);
            //Gera o PDF
            $pdf = $danfe->render($logo);
            header('Content-Type: application/pdf');
            echo $pdf;
        } catch (InvalidArgumentException $e) {
            echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
        }
    }
}
