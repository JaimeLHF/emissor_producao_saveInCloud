function lerArquivoXML(url, callback) {
    fetch(url)
        .then(response => response.text())
        .then(xml => {
            const parser = new DOMParser();
            const xmlDoc = parser.parseFromString(xml, "text/xml");
            const produtos = Array.from(xmlDoc.querySelectorAll('det')).map(det => {
                const prod = det.querySelector('prod');
                const imposto = det.querySelector('imposto');
                return {
                    nome: prod.querySelector('xProd').textContent,
                    valor: parseFloat(prod.querySelector('vUnCom').textContent),
                    cfop_interno: prod.querySelector('CFOP').textContent,
                    cfop_externo: prod.querySelector('CFOP').textContent,
                    ncm: prod.querySelector('NCM').textContent,
                    codigo_barras: prod.querySelector('cEAN').textContent,
                    und_venda: prod.querySelector('uCom').textContent,
                    cst_csosn: imposto.querySelector('ICMS ICMS40 CST').textContent,
                    cst_pis: imposto.querySelector('PIS PISOutr CST').textContent,
                    cst_cofins: imposto.querySelector('COFINS COFINSOutr CST').textContent,
                    cst_ipi: imposto.querySelector('IPI IPINT CST').textContent,
                    perc_icms: 0, // Valor fixo de exemplo
                    perc_pis: 0, // Valor fixo de exemplo
                    perc_cofins: 0, // Valor fixo de exemplo
                    perc_ipi: 0, // Valor fixo de exemplo
                    orig: imposto.querySelector('ICMS ICMS40 orig').textContent
                };
            });
            const resultado = { produtos };
            callback(null, resultado);
        })
        .catch(error => {
            callback(error, null);
        });
}

const caminho = './produtos.xml'
btn_produtos = document.getElementById('btn_produtos')

btn_produtos.addEventListener('click', () => {
    lerArquivoXML(caminho, (err, resultado) => {
        if (err) {
            console.error('Erro ao ler o arquivo XML:', err);
            return;
        }

        const json = JSON.stringify(resultado, null, 2);
        const blob = new Blob([json], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'resultado.json';
        a.click();
        URL.revokeObjectURL(url);

        console.log('Produtos Salvos!');
    });
})



btn_soma = document.getElementById('btn_soma')

const itens = {
    itens: [
        {
            "valor": 0.11,
            "id": 60,
            "qtd": 1108
        },
        {
            "valor": 0.02,
            "id": 61,
            "qtd": 3391
        },
        {
            "valor": 0.77,
            "id": 62,
            "qtd": 298
        },
        {
            "valor": 0.77,
            "id": 62,
            "qtd": 550
        },
        {
            "valor": 0.01,
            "id": 63,
            "qtd": 123799
        },
        {
            "valor": 0.01,
            "id": 69,
            "qtd": 113909
        },
        {
            "valor": 0.02,
            "id": 70,
            "qtd": 228140
        },
        {
            "valor": 0.02,
            "id": 71,
            "qtd": 100000
        },
        {
            "valor": 0.07,
            "id": 72,
            "qtd": 8182
        },
        {
            "valor": 0.43,
            "id": 75,
            "qtd": 1488
        },
        {
            "valor": 0.43,
            "id": 76,
            "qtd": 192
        },
        {
            "valor": 0.01,
            "id": 79,
            "qtd": 177902
        },
        {
            "valor": 0.04,
            "id": 84,
            "qtd": 960
        },
        {
            "valor": 0.04,
            "id": 85,
            "qtd": 240
        },
        {
            "valor": 6.91,
            "id": 88,
            "qtd": 100
        },
        {
            "valor": 5.42,
            "id": 96,
            "qtd": 180
        },
        {
            "valor": 5.72,
            "id": 110,
            "qtd": 240
        },
        {
            "valor": 0.02,
            "id": 112,
            "qtd": 15360
        },
        {
            "valor": 0.03,
            "id": 113,
            "qtd": 24990
        },
        {
            "valor": 0.03,
            "id": 114,
            "qtd": 6390
        },
        {
            "valor": 0.03,
            "id": 117,
            "qtd": 50000
        },
        {
            "valor": 0.05,
            "id": 120,
            "qtd": 18458
        },
        {
            "valor": 0.05,
            "id": 119,
            "qtd": 19860
        },
        {
            "valor": 24.35,
            "id": 124,
            "qtd": 1000
        },
        {
            "valor": 24.35,
            "id": 125,
            "qtd": 1500
        },
        {
            "valor": 0.19,
            "id": 127,
            "qtd": 18280.2
        },
        {
            "valor": 0.19,
            "id": 128,
            "qtd": 23104.75
        },
        {
            "valor": 0.26,
            "id": 136,
            "qtd": 10000
        }
    ]
}

btn_soma.addEventListener('click', () => {
    let soma = 0
    itens.itens.map((e) => {
        soma += e.valor * e.qtd
    })
    console.log(soma)
})