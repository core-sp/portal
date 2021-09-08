<?php

namespace App\Http\Controllers;

use App\Connections\FirebirdConnection;

class SimuladorController extends Controller
{
    private $connection;

    protected function connect()
    {
        // Conexão e criação do objeto de conexão na variável $connection
        $this->connection = new FirebirdConnection();
    }

    protected function taxas($tipo)
    {
        // Retorno das taxas de acordo com o tipo de pessoa
        // $run = $this->connection->prepare("select TAX_ID, TAX_DESCRICAO, TAX_VALOR from PROCTAXAINICIAIS(:tipo)");
        // $run->execute(['tipo' => $tipo]);

        // return $run->fetchAll();
    }

    protected function taxasTotal($tipo)
    {
        // Cálculo do total de taxas de acordo com o tipo de pessoa
        // $total = 0;

        // foreach($this->taxas($tipo) as $calc) {
        //     $total += $calc['TAX_VALOR'];
        // }

        // return $total;
    }

    protected function simulador($tipoPessoa, $dataInicio, $capitalSocial = 1, $filial = 24)
    {
        // Retorno do extrato de acordo com o tipo de pessoa, data de início, capital social e filial
        $run = $this->connection->prepare("SELECT descricao, valor_total, data_vencimento FROM procextrato ('', :tipopessoa, :datainicio, :capitalsocial, cast('NOW' as date), :filial)");

        $run->execute([
            'tipopessoa' => $tipoPessoa,
            'datainicio' => $dataInicio,
            'capitalsocial' => $capitalSocial,
            'filial' => $filial
        ]);

        $array = $run->fetchAll();
        dd($array);
        foreach($array as $single) {
            if (strpos($single['DESCRICAO'], 'Anuidade') !== false) {
                $desconto = $this->descontoAnuidade($single['VALOR_TOTAL']);
                if(isset($desconto))
                    array_push($array, $desconto);
            }
        }

        return $array;
    }

    protected function descontoAnuidade($anuidade)
    {
        $mes = date('m');

        $porcentagem = $this->porcentagemDesconto($mes);

        if(isset($porcentagem)) {
            $desconto = ($anuidade * $porcentagem * -1);

            $desconto = number_format($desconto, 2);
    
            return [
                'DESCRICAO' => $this->descricaoDesconto($mes),
                'VALOR_TOTAL' => $desconto
            ];
        }

        return null;        
    }

    protected function porcentagemDesconto($mes)
    {
        switch ($mes) {
            case '01':
                return 0.2;
            break;

            case '02':
                return 0.15;
            break;

            case '03':
                return 0.1;
            break;
            
            default:
                return null;
            break;
        }
    }

    protected function descricaoDesconto($mes)
    {
        switch ($mes) {
            case '01':
                return 'Desconto de anuidade (Janeiro)'; 
            break;
            
            case '02':
                return 'Desconto de anuidade (Fevereiro)'; 
            break;

            case '03':
                return 'Desconto de anuidade (Março)'; 
            break;

            default:
                return null;
            break;
        }
    }

    protected function simuladorTotal($tipoPessoa, $dataInicio, $capitalSocial = 1, $filial = 24)
    {
        // Cálculo do total do extrato de acordo com o tipo de pessoa, data de início, capital social e filial
        $total = 0;

        foreach ($this->simulador($tipoPessoa, $dataInicio, $capitalSocial, $filial) as $calc) {
            $total += $calc['VALOR_TOTAL'];
        }

        return $total;
    }

    public function view()
    {
        $hoje = date('Y-m-d');
        return view('site.simulador', compact('hoje'));
    }

    protected function consertaData($data)
    {
        // Formatação da data
        $novaDt = str_replace('-', '.', $data);
        return $novaDt;
    }

    protected function validateRequest()
    {
        // Validação dos campos no formulário
        return request()->validate([
            'tipoPessoa' => 'required',
            'dataInicio' => 'required|min:10'
        ], [
            'tipoPessoa.required' => 'É necessário informar o tipo de pessoa',
            'dataInicio.required' => 'É necessário informar a data de início das atividades',
            'dataInicio.min' => 'Data inválida'
        ]);
    }

    protected function validateAndConnect()
    {
        // Validação e conexão
        $this->validateRequest();

        $this->connect();
    }

    protected function simpleRt()
    {
        // Retorno do extrato do RT na data atual
        return $this->simulador(5, date('Y.m.d'));
    }

    protected function simpleRtTaxas()
    {
        // Retorno das taxas do RT na data atual
        // return $this->taxas(5);
    }

    public function extrato()
    {
        // Validação e conexão
        $this->validateAndConnect();

        // Validações do formulário
        empty(request('capitalSocial')) ? $capitalSocial = 1 : $capitalSocial = str_replace(',', '.', str_replace('.', '', request('capitalSocial')));
        request('tipoPessoa') !== '1' || request('filial') === '50' || request('filial') === null ? $filial = 24 : $filial = request('filial');

        // Total, extrato, e taxas separados por variáveis únicas
        $total = $this->simuladorTotal($this->validateRequest()['tipoPessoa'], $this->consertaData($this->validateRequest()['dataInicio']), $capitalSocial, $filial)/* + $this->taxasTotal(request('tipoPessoa'))*/;

        $extrato = $this->simulador($this->validateRequest()['tipoPessoa'], $this->consertaData($this->validateRequest()['dataInicio']), $capitalSocial, $filial);    
        
        $taxas/* = $this->taxas($this->validateRequest()['tipoPessoa'])*/;

        // Regras para mostrar opções do extrato na tela do Portal
        if(request('tipoPessoa') == 1 && request('empresaIndividual') != 'on') {
            $rt = $this->simpleRt();
            $rtTaxas = $this->simpleRtTaxas();
            $rtTotal = number_format($this->simuladorTotal(5, date('Y.m.d'))/* + $this->taxasTotal(5)*/, 2);
            $totalGeral = number_format($total + $rtTotal, 2, ',', '.');
        } else {
            $rt = null;
            $rtTaxas = null;
            $rtTotal = null;
            $totalGeral = null;
        }

        $total = number_format($total, 2, ',', '.');
        $hoje = date('Y-m-d');

        return view('site.simulador', compact('extrato', /*'taxas', */'total', 'rt', /*'rtTaxas', */'rtTotal', 'totalGeral', 'hoje'));
    }
}
