<?php

namespace App;

use Carbon\Carbon;

class Suporte
{
    const TOTAL_RELAT = 50;

    const ERROS = 'erros';
    const INTERNO = 'interno';
    const EXTERNO = 'externo';

    const FILTRO_ACESSO = 'acessos';
    const FILTRO_BOLETOS = 'boletos';
    const FILTRO_CERTIDAO_GERADA = 'certidao_gerada';
    const FILTRO_CERTIDAO_BAIXADA = 'certidao_baixada';
    const FILTRO_NOVO_ACESSO = 'novo_acesso';
    const FILTRO_ABA_TODAS = 'aba_todas';
    const FILTRO_ABA_HOME = 'aba_home';
    const FILTRO_ABA_DADOS = 'aba_dados-gerais';
    const FILTRO_ABA_CONTATOS = 'aba_contatos';
    const FILTRO_ABA_ENDER = 'aba_ender';
    const FILTRO_ABA_FINANCA = 'aba_financa';
    const FILTRO_ABA_CERTIDAO = 'aba_certidao';
    const FILTRO_ABA_BDO = 'aba_bdo';
    const FILTRO_ABA_CEDULA = 'aba_cedula';
    const FILTRO_ABA_CURSOS = 'aba_cursos';
    const FILTRO_ABA_SALAS = 'aba_salas';

    private $relatorios;

    private static function separarLinhasRelatHTML($relat_final)
    {
        return array_filter(explode('</tr>', strip_tags($relat_final, '<td><tr>')));
    }

    private static function limparLinhaHTML($linha)
    {
        return explode('<br>', strip_tags(str_replace('</td>', '<br>', strip_tags($linha, '<td>')), '<br>'));
    }

    private static function formatarExportar($relatorioHTML)
    {
        $array = array();

        $todas_linhas = self::separarLinhasRelatHTML($relatorioHTML);
        foreach($todas_linhas as $valor)
            array_push($array, self::limparLinhaHTML($valor));

        return $array;
    }

    public static function camposTabelaRelatorio()
    {
        return ['Área', 'Filtro', 'Período', 'Total geral', 'Total distintos', 'Gerado em'];
    }

    public static function tipos()
    {
        return [
            self::EXTERNO => 'Site',
            self::INTERNO => 'Admin',
            self::ERROS => 'Erros',
        ];
    }

    public static function tiposTextos()
    {
        return [
            self::EXTERNO => 'do Site',
            self::INTERNO => 'do Admin',
            self::ERROS => 'de Erros',
        ];
    }

    public static function filtros()
    {
        return [
            self::FILTRO_ACESSO => 'Acessos na área restrita (logins)',
            self::FILTRO_BOLETOS => 'Download de boletos na área do RC',
            self::FILTRO_CERTIDAO_GERADA => 'Novas certidões geradas na área do RC',
            self::FILTRO_CERTIDAO_BAIXADA => 'Downloads das certidões na área do RC',
            self::FILTRO_NOVO_ACESSO => 'Novos acessos na área do RC',
            self::FILTRO_ABA_TODAS => 'Acessos em todas as seções do RC',
            self::FILTRO_ABA_HOME => 'Acessos na seção Home do RC',
            self::FILTRO_ABA_DADOS => 'Acessos na seção Dados Gerais do RC',
            self::FILTRO_ABA_CONTATOS => 'Acessos na seção Contatos do RC',
            self::FILTRO_ABA_ENDER => 'Acessos na seção End. de Correspondência do RC',
            self::FILTRO_ABA_FINANCA => 'Acessos na seção Situação Financeira do RC',
            self::FILTRO_ABA_CERTIDAO => 'Acessos na seção Emitir Certidão do RC',
            self::FILTRO_ABA_BDO => 'Acessos na seção Oportunidades do RC',
            self::FILTRO_ABA_CEDULA => 'Acessos na seção Solicitação de Cédula do RC',
            self::FILTRO_ABA_CURSOS => 'Acessos na seção Cursos do RC',
            self::FILTRO_ABA_SALAS => 'Acessos na seção Agendar Salas do RC',
        ];
    }

    public static function textosFiltros()
    {
        return [
            self::FILTRO_ACESSO . '_externo' => 'conectou-se à Área do Representante.',
            self::FILTRO_ACESSO . '_interno' => 'conectou-se ao Painel Administrativo.',
            self::FILTRO_BOLETOS . '_externo' => 'baixou o boleto ',
            self::FILTRO_CERTIDAO_GERADA . '_externo' => 'gerou certidão com código: ',
            self::FILTRO_CERTIDAO_BAIXADA . '_externo' => 'acessou a aba "Emitir Certidão" e realizou download.',
            self::FILTRO_NOVO_ACESSO . '_externo' => 'verificou o email após o cadastro.',
            self::FILTRO_ABA_TODAS . '_externo' => 'acessou a aba ',
            self::FILTRO_ABA_HOME . '_externo' => 'acessou a aba "Home"',
            self::FILTRO_ABA_DADOS . '_externo' => 'acessou a aba "Dados Gerais"',
            self::FILTRO_ABA_CONTATOS . '_externo' => 'acessou a aba "Contatos"',
            self::FILTRO_ABA_ENDER . '_externo' => 'acessou a aba "End. de Correspondência"',
            self::FILTRO_ABA_FINANCA . '_externo' => 'acessou a aba "Situação Financeira"',
            self::FILTRO_ABA_CERTIDAO . '_externo' => 'acessou a aba "Emitir Certidão"',
            self::FILTRO_ABA_BDO . '_externo' => 'acessou a aba "Oportunidades"',
            self::FILTRO_ABA_CEDULA . '_externo' => 'acessou a aba "Solicitação de Cédula"',
            self::FILTRO_ABA_CURSOS . '_externo' => 'acessou a aba "Cursos"',
            self::FILTRO_ABA_SALAS . '_externo' => 'acessou a aba "Agendar Salas"',
        ];
    }

    public static function criarRelatorio($dados)
    {
        $relat = 'relatorio_'.$dados['data'].'-'.$dados['tipo'].'-'.$dados['opcoes'];
        $dados['data'] = Carbon::hasFormat($dados['data'], 'Y-m') ? Carbon::parse($dados['data'])->format('m\/Y') : $dados['data'];

        $texto = '<tr>';
        $texto .= '<td>'.self::tipos()[$dados['tipo']].'</td>';
        $texto .= '<td>'.self::filtros()[$dados['opcoes']].'</td>';
        $texto .= '<td>'.$dados['data'].'</td>';
        $texto .= '<td>'.$dados['geral'].'</td>';
        $texto .= '<td>'.$dados['distintos'].'</td>';
        $texto .= '<td><small>'.now()->format('d\/m\/Y, \à\s H:i').'</small></td>';
        $texto .= '</tr>';

        session([
            $relat => [
                'tabela' => $texto,
                'relatorio' => $dados,
            ]
        ]);

        return $relat;
    }

    public function getRelatorioFinalHTML()
    {
        $tabela = '';
        $total_geral = 0;
        $total_distinto = 0;
        $dados = $this->todosRelatorios(true);
        
        foreach($dados as $key => $value)
        {
            $data = Carbon::hasFormat($value['relatorio']['data'], 'Y-m') ? Carbon::parse($value['relatorio']['data'])->format('m\/Y') : $value['relatorio']['data'];
            $tabela .= $value['tabela'];
            $total_geral += $value['relatorio']['geral'];
            $total_distinto += $value['relatorio']['distintos'];
        }

        $tabela .= '<tr>';
        $tabela .= '<td class="border border-left-0 border-right-0 border-bottom-0 text-white">-----</td>';
        $tabela .= '<td class="border border-left-0 border-right-0 border-bottom-0 text-white">-----</td>';
        $tabela .= '<td class="font-weight-bolder">Total Final</td>';
        $tabela .= '<td class="font-weight-bolder">'.$total_geral.'</td>';
        $tabela .= '<td class="font-weight-bolder">'.$total_distinto.'</td>';
        $tabela .= '<td class="font-weight-bolder"><small>'.now()->format('d\/m\/Y, \à\s H:i').'</small></td>';
        $tabela .= '</tr>';

        session(['relatorio_final' => ['tabela' => $tabela]]);

        return 'relatorio_final';
    }

    public function exportarCsv($relat)
    {
        $relatorioHTML = $this->getRelatorioPorNome($relat);
        $array = self::formatarExportar($relatorioHTML['tabela']);

        array_unshift($array, self::camposTabelaRelatorio());
        $callback = function() use($array) {
            $fh = fopen('php://output','w');
            fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));
            foreach($array as $linha) 
                fputcsv($fh,$linha,';');
            fclose($fh);
        };

        return $callback;
    }

    public function todosRelatorios($semRelatFinal = false)
    {
        $relatorios = array_filter(session()->all(), function($key) use($semRelatFinal) {
            return $semRelatFinal ? ((strpos($key, 'relatorio_') !== false) && ($key != 'relatorio_final')) : (strpos($key, 'relatorio_') !== false);
        }, ARRAY_FILTER_USE_KEY);

        ksort($relatorios, SORT_STRING);

        return $relatorios;
    }

    public function getRelatorioPorNome($relat)
    {
        if(gettype($relat) != 'string')
            throw new \Exception('Formato de relatório não existe.', 404);

        if(!session()->exists($relat))
            throw new \Exception('Relatório não existe para visualizar / exportar.csv.', 404);

        return session($relat);
    }

    public function removerRelatorioPorNome($relat)
    {
        if(gettype($relat) != 'string')
            throw new \Exception('Formato de relatório não existe.', 404);

        if(!session()->exists($relat))
            throw new \Exception('Relatório não existe para remover', 404);

        return session()->forget($relat);
    }

    public function conferePodeCriar()
    {
        $relatorios = $this->todosRelatorios(true);

        if(count($relatorios) >= self::TOTAL_RELAT)
            throw new \Exception('Alcançou limite de até '.self::TOTAL_RELAT.' relatórios!', 400);
    }
}
