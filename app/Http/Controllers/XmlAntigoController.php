<?php

namespace App\Http\Controllers;

use App\Models\XmlAntigo;
use App\Services\NFeService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use NFePHP\DA\NFe\Danfe;

class XmlAntigoController extends Controller
{
    public function index()
    {
        // Obtém todos os registros da tabela xml_antigos
        $xmlAntigos = XmlAntigo::all();

        // Retorna os registros em formato JSON
        return response()->json($xmlAntigos);
    }

    public function vincular(Request $request)
    {
       
        // $emitente = Emitente::first();
        $xml_venda = XmlAntigo::where('numero_nota', $request->numero_nfe)->first();

    

        $nfe_service = new NFeService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => 1,
            "razaosocial" => 'DRD INDUSTRIA E COMERCIO DE MOVEIS LTDA',
            "siglaUF" => 'SC',
            "cnpj" => '24287808000160',
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => "AAAAAAA",
            "CSCid" => "000001"
        ]);

        $result = $nfe_service->consultaNFe($request->chave);

        $nfe = $nfe_service->vincularCancelamento($request->chave, $xml_venda->xml);

        $xml_venda->xml = $nfe['sucesso'];
        $xml_venda->save();

        return response()->json($result, 200);
    }


    public function imprimir($numero_nfe)
    {

        date_default_timezone_set('America/Sao_Paulo');

        $venda = XmlAntigo::where('numero_nota', $numero_nfe)->first();

        $xmlContent  = $venda->xml;
       
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
