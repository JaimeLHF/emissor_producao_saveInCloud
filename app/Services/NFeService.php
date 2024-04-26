<?php

namespace App\Services;

use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use App\Models\Vendas;
use NFePHP\NFe\Complements;


error_reporting(E_ALL);
ini_set('display_errors', 'On');

class NFeService
{

    protected $tools;

    public function __construct($config, $emitente)
    {
        $certificadoDigital = file_get_contents('../public/DRD INDUSTRIA E COMERCIO DE MOVEIS LTDA24287808000160.pfx');
        $this->tools = new Tools(json_encode($config), Certificate::readPfx($certificadoDigital, $emitente->senha));
        $this->tools->model(55);
    }

    public function consultaNFe($venda)
    {
        try {

            $chave = $venda->chave;
            $response = $this->tools->sefazConsultaChave($chave);

            $stdCl = new Standardize($response);
            $arr = $stdCl->toArray();
            return $arr;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function gerarXML($venda, $emitente)
    {

        $nfe = new Make();

        date_default_timezone_set('America/Sao_Paulo');

        //Informações suplementares da Nota Fiscal
        $stdInNFe = new \stdClass();
        $stdInNFe->versao = '4.00';
        $stdInNFe->Id = null;
        $stdInNFe->pk_nItem = '';
        $nfe->taginfNFe($stdInNFe);

        ////////////////////////////////////////////////////////////////////

        //Identificação da NFe

        $numeroNFe = Vendas::ultimoNumeroNFe(); //pegar ultimo número da NFe

        $stdIde = new \stdClass();
        $stdIde->cUF = $emitente->codigo_uf; //
        $stdIde->cNF = rand(11111, 99999); //
        $stdIde->natOp = $venda->natOp; //
        $stdIde->mod = 55; //
        $stdIde->serie = $emitente->numero_serie_nfe; //
        $stdIde->nNF = 710; //
        $stdIde->dhEmi = date("Y-m-d\TH:i:sP"); //
        $stdIde->dhSaiEnt = date("Y-m-d\TH:i:sP"); //
        $stdIde->tpNF = 1; //
        $stdIde->idDest = $emitente->uf != $venda->cliente->uf ? 2 : 1; //
        $stdIde->cMunFG = $emitente->codigo_municipio; //
        $stdIde->tpImp = 1; //
        $stdIde->tpEmis = 1; //
        $stdIde->tpAmb = $emitente->ambiente; //
        $stdIde->finNFe = $venda->finNFe; //
        $stdIde->indFinal = 1; //
        $stdIde->indPres = 0; //
        $stdIde->procEmi = '0'; //
        $stdIde->verProc = '0.00'; //
        $nfe->tagide($stdIde);

        ////////////////////////////////////////////////////////////////////

        //Identificação do emitente da NF-e
        $stdEmit = new \stdClass();
        $stdEmit->xNome = $emitente->razao_social;
        $stdEmit->xFant = $emitente->nome_fantasia;

        $ie = str_replace(".", "", $emitente->ie_rg);
        $ie = str_replace("/", "", $ie);
        $ie = str_replace("-", "", $ie);
        $stdEmit->IE = $ie;
        $stdEmit->IM = 3153;

        $stdEmit->CRT = $emitente->situacao_tributaria; // Ajsutar situação tributaria conforme cliente

        $cnpj_cpf = str_replace(".", "", $emitente->cpf_cnpj);
        $cnpj_cpf = str_replace("/", "", $cnpj_cpf);
        $cnpj_cpf = str_replace("-", "", $cnpj_cpf);
        $cnpj_cpf = str_replace(" ", "", $cnpj_cpf);

        if (strlen($cnpj_cpf) == 14) {
            $stdEmit->CNPJ = $cnpj_cpf;
        } else {
            $stdEmit->CPF = $cnpj_cpf;
        }
        $nfe->tagemit($stdEmit);

        ////////////////////////////////////////////////////////////////////

        // Endereço do emitente
        $stdEnderEmit = new \stdClass();
        $stdEnderEmit->xLgr = $this->retiraAcentos($emitente->rua);
        $stdEnderEmit->nro = $emitente->numero_endereco;
        $stdEnderEmit->xCpl = $this->retiraAcentos($emitente->complemento);
        $stdEnderEmit->xBairro = $this->retiraAcentos($emitente->bairro);
        $stdEnderEmit->cMun = $emitente->codigo_municipio; //Código de município precisa ser válido e igual o cMunFG 
        $stdEnderEmit->xMun = $this->retiraAcentos($emitente->municipio);
        $stdEnderEmit->UF = $emitente->uf;
        $telefone = $emitente->fone;
        $telefone = str_replace("(", "", $telefone);
        $telefone = str_replace(")", "", $telefone);
        $telefone = str_replace("-", "", $telefone);
        $telefone = str_replace(" ", "", $telefone);
        $stdEnderEmit->fone = $telefone;
        $cep = str_replace("-", "", $emitente->cep);
        $cep = str_replace(".", "", $cep);
        $stdEnderEmit->CEP = $cep;
        $stdEnderEmit->cPais = $emitente->codigo_pais;
        $stdEnderEmit->xPais = $emitente->pais;
        $nfe->tagenderEmit($stdEnderEmit);

        ////////////////////////////////////////////////////////////////////

        //Identificação do Destinatário da NF-e
        $stdDest = new \stdClass();
        $stdDest->xNome = $this->retiraAcentos($venda->cliente->nome);
        $stdDest->email = $venda->cliente->email;
        if ($venda->cliente->contribuinte) {
            if ($venda->cliente->ie_rg == 'ISENTO') {
                $stdDest->indIEDest = "2"; //Contribuinte isento de Inscrição no cadastro de Contribuintes do ICMS
            } else {
                $stdDest->indIEDest = "1"; //Contribuinte ICMS
            }
        } else {
            $stdDest->indIEDest = "9"; //Não Contribuinte
        }

        $cnpj_cpf = str_replace(".", "", $venda->cliente->cpf_cnpj);
        $cnpj_cpf = str_replace("/", "", $cnpj_cpf);
        $cnpj_cpf = str_replace("-", "", $cnpj_cpf);

        if (strlen($cnpj_cpf) == 14) {
            $stdDest->CNPJ = $cnpj_cpf;
            $ie = str_replace(".", "", $venda->cliente->ie_rg);
            $ie = str_replace("/", "", $ie);
            $ie = str_replace("-", "", $ie);
            $stdDest->IE = $ie;
        } else {
            $stdDest->CPF = $cnpj_cpf;
            $ie = str_replace(".", "", $venda->cliente->ie_rg);
            $ie = str_replace("/", "", $ie);
            $ie = str_replace("-", "", $ie);
            if (strtoupper($ie) != 'ISENTO' && $venda->cliente->contribuinte)
                $stdDest->IE = $ie;
        }

        $nfe->tagdest($stdDest);

        ////////////////////////////////////////////////////////////////////

        //ENDEREÇO DESTINATÁRIO

        $stdEnderDest = new \stdClass();
        $stdEnderDest->xLgr = $this->retiraAcentos($venda->cliente->rua);
        $stdEnderDest->nro = $this->retiraAcentos($venda->cliente->numero);
        $stdEnderDest->xCpl = $this->retiraAcentos($venda->cliente->complemento);
        $stdEnderDest->xBairro = $this->retiraAcentos($venda->cliente->bairro);

        $telefone = $venda->cliente->telefone;
        $telefone = str_replace("(", "", $telefone);
        $telefone = str_replace(")", "", $telefone);
        $telefone = str_replace("-", "", $telefone);
        $telefone = str_replace(" ", "", $telefone);
        $stdEnderDest->fone = $telefone;

        $stdEnderDest->cMun = $venda->cliente->codigo_municipio;
        $stdEnderDest->xMun = $this->retiraAcentos($venda->cliente->municipio);
        $stdEnderDest->UF = $venda->cliente->uf;

        $cep = str_replace("-", "", $venda->cliente->cep);
        $cep = str_replace(".", "", $cep);
        $stdEnderDest->CEP = $cep;
        $stdEnderDest->cPais = $venda->cliente->codigo_pais;
        $stdEnderDest->xPais = $venda->cliente->pais;
        $nfe->tagenderDest($stdEnderDest);

        ////////////////////////////////////////////////////////////////////

        //ITENS DA NFE
        foreach ($venda->itens as $key => $i) {

            //TAG DE PRODUTO
            $stdProd = new \stdClass();
            $stdProd->item = $key + 1;

            $cod = $this->validate_EAN13Barcode($i->produto->codigo_barras);

            $stdProd->cEAN = $cod ? $i->produto->codigo_barras : 'SEM GTIN';
            $stdProd->cEANTrib = $cod ? $i->produto->codigo_barras : 'SEM GTIN';
            $stdProd->cProd = $i->produto->id;
            $stdProd->xProd = $this->retiraAcentos($i->produto->nome) . ' ' . $this->retiraAcentos($i->produto->acabamento->nome);

            $ncm = $i->produto->ncm;
            $ncm = str_replace(".", "", $ncm);
            $stdProd->NCM = $ncm;

            $stdProd->CFOP = $emitente->uf != $venda->cliente->uf ? $i->produto->cfop_externo : $i->produto->cfop_interno;

            $stdProd->uCom = $i->produto->und_venda;
            $stdProd->qCom = $i->qtd; //ajustar quantidade com fator de conversão M² para Pallet por exemplo
            $stdProd->vUnCom = $this->format($i->valor); //ajustar valor com fator de conversão M² para Pallet por exemplo
            $stdProd->vProd = $this->format(($i->qtd * $i->valor));
            $stdProd->uTrib = $i->produto->und_venda;
            $stdProd->qTrib = $i->qtd;
            $stdProd->vUnTrib = $this->format($i->valor);
            $stdProd->indTot = 1; //verificar
            $nfe->tagprod($stdProd);

            //////////////////////////////////

            $stdImposto = new \stdClass();
            $stdImposto->item = $key + 1;
            $nfe->tagimposto($stdImposto);


            switch ($emitente->situacao_tributaria) {
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                    /////////////////         IMPOSTOS PARA SIMPLES NACIONAL     //////////////////////////////////
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                case 1:
                    //ICMS Simples Nacional
                    $stdICMSSN = new \stdClass();
                    $stdICMSSN->item = $key + 1;
                    $stdICMSSN->orig = $i->produto->orig;
                    $stdICMSSN->CSOSN = $i->produto->cst_csosn;
                    $stdICMSSN->pCredSN = $emitente->percentual_aliquota_icms; //Percentual da Aliquota
                    $stdICMSSN->vCredICMSSN = $stdProd->vProd * ($emitente->percentual_aliquota_icms / 100);
                    $nfe->tagICMSSN($stdICMSSN);

                    //IPI Simples Nacional
                    $stdIPI = new \stdClass();
                    $stdIPI->item = $key + 1;
                    $stdIPI->cEnq = '999'; //verificar
                    $stdIPI->CST = $i->produto->cst_ipi;
                    $stdIPI->vBC = $this->format($i->produto->perc_ipi) > 0 ? $stdProd->vProd : 0.00;
                    $stdIPI->pIPI = $this->format($i->produto->perc_ipi);
                    $stdIPI->vIPI = $stdProd->vProd * ($i->produto->perc_ipi / 100);
                    $nfe->tagIPI($stdIPI);

                    //PIS
                    $stdPIS = new \stdClass();
                    $stdPIS->item = $key + 1;
                    $stdPIS->CST = $i->produto->cst_pis;
                    $stdPIS->vBC = $this->format($i->produto->perc_pis) > 0 ? $stdProd->vProd : 0.00;
                    $stdPIS->pPIS = $this->format($i->produto->perc_pis);
                    $stdPIS->vPIS = $this->format(($stdProd->vProd) * ($i->produto->perc_pis / 100));
                    $nfe->tagPIS($stdPIS);

                    //COFINS
                    $stdCOFINS = new \stdClass();
                    $stdCOFINS->item = $key + 1;
                    $stdCOFINS->CST = $i->produto->cst_cofins;
                    $stdCOFINS->vBC = $this->format($i->produto->perc_cofins) > 0 ? $stdProd->vProd : 0.00;
                    $stdCOFINS->pCOFINS = $this->format($i->produto->perc_cofins);
                    $stdCOFINS->vCOFINS = $this->format(($stdProd->vProd) *     ($i->produto->perc_cofins / 100));
                    $nfe->tagCOFINS($stdCOFINS);

                    break;
                    ////////////////////////////////////////////////////////////////////
                case 2:
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                    ///////////////////                       LUCRO PRESUMIDO                   ///////////////////
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                    ///////////////////////////////////////////////////////////////////////////////////////////////

                    //ICMS
                    $stdICMS = new \stdClass();
                    $stdICMS->item = $key + 1;
                    $stdICMS->orig = $i->produto->orig;
                    $stdICMS->CST = $i->produto->cst_csosn;
                    $stdICMS->modBC = 3; //verificar - variaveis universais

                    $stdICMS->vBC = $stdProd->vProd;
                    $stdICMS->pICMS = $this->format($i->produto->perc_icms);
                    $stdICMS->vICMS = $stdICMS->vBC * ($stdICMS->pICMS / 100);

                    $stdICMS->pCredSN = $emitente->percentual_aliquota_icms; //Percentual da Aliquota
                    $stdICMS->vCredICMSSN = $stdProd->vProd * ($emitente->percentual_aliquota_icms / 100);
                    $nfe->tagICMS($stdICMS);

                    //IPI Simples Nacional
                    $stdIPI = new \stdClass();
                    $stdIPI->item = $key + 1;
                    $stdIPI->cEnq = '999'; //verificar
                    $stdIPI->CST = $i->produto->cst_ipi;
                    $stdIPI->vBC = $this->format($i->produto->perc_ipi) > 0 ? $stdProd->vProd : 0.00;
                    $stdIPI->pIPI = $this->format($i->produto->perc_ipi);
                    $stdIPI->vIPI = $stdProd->vProd * ($i->produto->perc_ipi / 100);
                    $nfe->tagIPI($stdIPI);

                    //PIS
                    $stdPIS = new \stdClass();
                    $stdPIS->item = $key + 1;
                    $stdPIS->CST = $i->produto->cst_pis;
                    $stdPIS->vBC = $this->format($i->produto->perc_pis) > 0 ? $stdProd->vProd : 0.00;
                    $stdPIS->pPIS = $this->format($i->produto->perc_pis);
                    $stdPIS->vPIS = $this->format(($stdProd->vProd) * ($i->produto->perc_pis / 100));
                    $nfe->tagPIS($stdPIS);

                    //COFINS
                    $stdCOFINS = new \stdClass();
                    $stdCOFINS->item = $key + 1;
                    $stdCOFINS->CST = $i->produto->cst_cofins;
                    $stdCOFINS->vBC = $this->format($i->produto->perc_cofins) > 0 ? $stdProd->vProd : 0.00;
                    $stdCOFINS->pCOFINS = $this->format($i->produto->perc_cofins);
                    $stdCOFINS->vCOFINS = $this->format(($stdProd->vProd) *     ($i->produto->perc_cofins / 100));
                    $nfe->tagCOFINS($stdCOFINS);


                    break;

                case 3:
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                    /////////////                             LUCRO REAL                              /////////////
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                    ///////////////////////////////////////////////////////////////////////////////////////////////


                    //ICMS
                    $stdICMS = new \stdClass();
                    $stdICMS->item = $key + 1;
                    $stdICMS->orig = $i->produto->orig;
                    $stdICMS->CST = $i->produto->cst_csosn;
                    $stdICMS->modBC = 3; //verificar - variaveis universais

                    $stdICMS->vBC = $stdProd->vProd;
                    $stdICMS->pICMS = $this->format($i->produto->perc_icms);
                    $stdICMS->vICMS = $stdICMS->vBC * ($stdICMS->pICMS / 100);

                    $stdICMS->pCredSN = $emitente->percentual_aliquota_icms; //Percentual da Aliquota
                    $stdICMS->vCredICMSSN = $stdProd->vProd * ($emitente->percentual_aliquota_icms / 100);
                    $nfe->tagICMS($stdICMS);

                    //IPI
                    $stdIPI = new \stdClass();
                    $stdIPI->item = $key + 1;
                    $stdIPI->cEnq = '999'; //verificar
                    $stdIPI->CST = $i->produto->cst_ipi;
                    $stdIPI->vBC = $this->format($i->produto->perc_ipi) > 0 ? $stdProd->vProd : 0.00;
                    $stdIPI->pIPI = $this->format($i->produto->perc_ipi);
                    $stdIPI->vIPI = $stdProd->vProd * ($i->produto->perc_ipi / 100);
                    $nfe->tagIPI($stdIPI);

                    //PIS
                    $stdPIS = new \stdClass();
                    $stdPIS->item = $key + 1;
                    $stdPIS->CST = $i->produto->cst_pis;
                    $stdPIS->vBC = $this->format($i->produto->perc_pis) > 0 ? $stdProd->vProd : 0.00;
                    $stdPIS->pPIS = $this->format($i->produto->perc_pis);
                    $stdPIS->vPIS = $this->format(($stdProd->vProd) * ($i->produto->perc_pis / 100));
                    $nfe->tagPIS($stdPIS);

                    //COFINS
                    $stdCOFINS = new \stdClass();
                    $stdCOFINS->item = $key + 1;
                    $stdCOFINS->CST = $i->produto->cst_cofins;
                    $stdCOFINS->vBC = $this->format($i->produto->perc_cofins) > 0 ? $stdProd->vProd : 0.00;
                    $stdCOFINS->pCOFINS = $this->format($i->produto->perc_cofins);
                    $stdCOFINS->vCOFINS = $this->format(($stdProd->vProd) * ($i->produto->perc_cofins / 100));
                    $nfe->tagCOFINS($stdCOFINS);

                    break;
            }
        }

        //ICMS TOTAL
        $stdICMSTot = new \stdClass();
        $stdICMSTot->vProd = $this->format($venda->valorTotal);
        $stdICMSTot->vBC = $emitente->situacao_tributaria != 3 && $emitente->situacao_tributaria != 2 ? 0.00 : $this->format($venda->valorTotal);
        $stdICMSTot->vICMS = $emitente->situacao_tributaria != 3 && $emitente->situacao_tributaria != 2 ? 0.00 : $this->format($venda->valorTotal) * ($stdICMS->pICMS / 100);
        $stdICMSTot->vICMSDeson = 0.00;
        $stdICMSTot->vBCST = 0.00;
        $stdICMSTot->vST = 0.00;
        $stdICMSTot->vFrete = $venda->vFrete;
        $stdICMSTot->vSeg = 0.00;
        $stdICMSTot->vDesc = 0.00;
        $stdICMSTot->vII = 0.00;
        $stdICMSTot->vIPI = $stdICMSTot->vBC * ($i->produto->perc_ipi / 100);
        $stdICMSTot->vPIS = 0.00; // Faz calculo automatico no xml
        $stdICMSTot->vCOFINS = 0.00; // Faz calculo automatico no xml
        $stdICMSTot->vOutro = 0.00;
        $stdICMSTot->vTotTrib = 0.00;
        $stdICMSTot->vNF = $this->format($venda->valorTotal) + $stdICMSTot->vIPI;
        $nfe->tagICMSTot($stdICMSTot);

        ////////////////////////////////////////////////////////////////////
        //Transportadora/Frete
        $stdTransp = new \stdClass();
        $stdTransp->modFrete = $venda->modFrete; //verificar, colocar no cadastro da venda
        $nfe->tagtransp($stdTransp);

        ////////////////////////////////////////////////////////////////////

        //FATURA
        $stdFat = new \stdClass();
        $stdFat->nFat = (int)$numeroNFe; //mesmo numero da nfe
        $stdFat->vOrig = $stdICMSTot->vNF;
        $stdFat->vDesc = 0.00; //verificar, colocar no cadastro da venda
        $stdFat->vLiq = ($stdICMSTot->vNF - $stdFat->vDesc);
        if ($venda->tipo_pagamento != '90') {
            $nfe->tagfat($stdFat);
        }

        foreach ($venda->fatura as $key => $fat) {

            //DUPLICATAS
            $stdDup = new \stdClass();
            $stdDup->nDup = '00' . ($key + 1);
            $stdDup->dVenc = $fat->vencimento;
            $stdDup->vDup =  $fat->valor + ($fat->valor * ($i->produto->perc_ipi / 100));
            $nfe->tagdup($stdDup);

            //PAGAMENTO
            $stdPag = new \stdClass();
            $nfe->tagpag($stdPag);

            $stdDetPag = new \stdClass();
            $stdDetPag->tPag = $fat->forma_pagamento;
            $stdDetPag->vPag = $fat->forma_pagamento != '90' ? $stdFat->vLiq : 0.00;
            $stdDetPag->indPag = 0;
            $stdDetPag->vTroco = 0;
            if ($venda->forma_pagamento == '03' || $fat->forma_pagamento == '04') {
                $stdDetPag->tBand = '01';
                $stdDetPag->tpIntegra = 2;
            }
            $nfe->tagdetPag($stdDetPag);
        }

        ////////////////////////////////////////////////////////////////////

        //TAG AUTORIZADOR XML VARIAVEL NO ARQUIVO .ENV, ESTADO DA BAHIA OBRIGATORIO
        if (getenv('AUT_XML') != '') {
            $std = new \stdClass();

            $cnpj = getenv('AUT_XML');
            $cnpj = str_replace(".", "", $cnpj);
            $cnpj = str_replace("-", "", $cnpj);
            $cnpj = str_replace("/", "", $cnpj);
            $cnpj = str_replace(" ", "", $cnpj);

            $std->CNPJ = $cnpj;
            $nfe->tagautXML($std);
        }

        switch ($emitente->situacao_tributaria) {
            case 1:
                // Informações adicionais da NFe
                $stdInfos = new \stdClass();
                $stdInfos->infAdFisco = '';

                $totalCredito =  number_format($stdFat->vLiq * ($emitente->percentual_aliquota_icms / 100), 2, ',', ' ');
                $stdInfos->infCpl = "Empresa optante pelo Simples Nacional. Permite crédito do ICMS no valor de R$$totalCredito, correspondente á alíquota de {$emitente->percentual_aliquota_icms}%, nos termos do Art. 23, da LC  nº 123/06.";
                $nfe->taginfAdic($stdInfos);
                break;

            default:
                # code...
                break;
        }

        ////////////////////////////////////////////////////////////////////

        //TAG RESPONSAVEL TECNICO
        $std = new \stdClass();
        $std->CNPJ = getenv('RESP_CNPJ'); //CNPJ da pessoa jurídica responsável pelo sistema utilizado na emissão do documento fiscal eletrônico
        $std->xContato = getenv('RESP_NOME'); //Nome da pessoa a ser contatada
        $std->email = getenv('RESP_EMAIL'); //E-mail da pessoa jurídica a ser contatada
        $std->fone = getenv('RESP_FONE');
        $nfe->taginfRespTec($std);

        ////////////////////////////////////////////////////////////////////

        try {
            $nfe->montaNFe();
            $arr = [
                'chave' => $nfe->getChave(),
                'xml' => $nfe->getXML(),
                'nNf' => $stdIde->nNF
            ];
            return $arr;
        } catch (\Exception) {
            return [
                'erros_xml' => $nfe->getErrors()
            ];
        }
    }

    public function sign($xml)
    {
        return $this->tools->signNFe($xml);
    }

    public function transmitir($signXml)
    {

        try {
            $idLote = str_pad(100, 15, '0', STR_PAD_LEFT);
            $resp = $this->tools->sefazEnviaLote([$signXml], $idLote);

            $st = new Standardize();
            $std = $st->toStd($resp);
            sleep(3);
            if ($std->cStat != 103) {

                return [
                    'erro' => "[$std->cStat] - $std->xMotivo"
                ];
            }
            $recibo = $std->infRec->nRec;
            $protocolo = $this->tools->sefazConsultaRecibo($recibo);
            sleep(3);
            try {
                $xml = Complements::toAuthorize($signXml, $protocolo);          
                return [                   
                    'sucesso' => $xml
                ];
                // $this->printDanfe($xml);
            } catch (\Exception $e) {
                return [
                    'erro' => $e->getMessage()
                ];
            }
        } catch (\Exception $e) {
            return [
                'erro' => $e->getMessage()
            ];
        }
    }

    public function cancelar($venda, $justificativa)
    {
        try {

            $chave = $venda->chave;
            $response = $this->tools->sefazConsultaChave($chave);
            sleep(2);
            $stdCl = new Standardize($response);
            $arr = $stdCl->toArray();
            $xJust = $justificativa;
            $nProt = $arr['protNFe']['infProt']['nProt'];

            $response = $this->tools->sefazCancela($chave, $xJust, $nProt);
            sleep(2);
            $stdCl = new Standardize($response);
            $std = $stdCl->toStd();
            $arr = $stdCl->toArray();
            $json = $stdCl->toJson();

            if ($std->cStat != 128) {
            } else {
                $cStat = $std->retEvento->infEvento->cStat;
                if ($cStat == '101' || $cStat == '135' || $cStat == '155') {
                    $xml = Complements::toAuthorize($this->tools->lastRequest, $response);
                    file_put_contents(public_path('xml_nfe_cancelada/') . $chave . '.xml', $xml);

                    return $response;
                    
                } else {
                    return ['erro' => true, 'data' => $arr];
                }
            }
        } catch (\Exception $e) {
            return ['erro' => true, 'data' => $e->getMessage()];
        }
    }

    public function cartaCorrecao($venda, $justificativa)
    {
        try {

            $chave = $venda->chave;
            $xCorrecao = $justificativa;
            $nSeqEvento = $venda->sequencia_evento + 1;
            $response = $this->tools->sefazCCe($chave, $xCorrecao, $nSeqEvento);
            sleep(2);
            $stdCl = new Standardize($response);
            $std = $stdCl->toStd();
            $arr = $stdCl->toArray();
            $json = $stdCl->toJson();
            if ($std->cStat != 128) {
            } else {
                $cStat = $std->retEvento->infEvento->cStat;
                if ($cStat == '135' || $cStat == '136') {
                    $xml = Complements::toAuthorize($this->tools->lastRequest, $response);
                    file_put_contents(public_path('xml_nfe_correcao/') . $chave . '.xml', $xml);

                    $venda->sequencia_evento += 1;
                    $venda->save();
                    return $json;
                } else {
                    return ['erro' => true, 'data' => $arr];
                }
            }
        } catch (\Exception $e) {
            return ['erro' => true, 'data' => $e->getMessage()];
        }
    }



    ////////// Funções secundárias /////////
    private function validate_EAN13Barcode($ean)
    {

        $sumEvenIndexes = 0;
        $sumOddIndexes  = 0;

        $eanAsArray = array_map('intval', str_split($ean));

        if (!$this->has13Numbers($eanAsArray)) {
            return false;
        };

        for ($i = 0; $i < count($eanAsArray) - 1; $i++) {
            if ($i % 2 === 0) {
                $sumOddIndexes  += $eanAsArray[$i];
            } else {
                $sumEvenIndexes += $eanAsArray[$i];
            }
        }

        $rest = ($sumOddIndexes + (3 * $sumEvenIndexes)) % 10;

        if ($rest !== 0) {
            $rest = 10 - $rest;
        }

        return $rest === $eanAsArray[12];
    }

    private function has13Numbers(array $ean)
    {
        return count($ean) === 13;
    }

    private function retiraAcentos($texto)
    {
        return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/", "/(ç)/"), explode(" ", "a A e E i I o O u U n N c"), $texto);
    }

    public function format($number, $dec = 2)
    {
        return number_format((float) $number, $dec, ".", "");
    }
}
