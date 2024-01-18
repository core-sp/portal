<?php

namespace App;

use Carbon\Carbon;

class Suporte
{
    const ERROS = 'erros';
    const INTERNO = 'interno';
    const EXTERNO = 'externo';

    const FILTRO_ACESSO = 'acessos';
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

    private static function separarLinhasRelatHTML($relat_final)
    {
        return array_filter(explode('</tr>', strip_tags($relat_final, '<td><tr>')));
    }

    private static function limparLinhaHTML($linha)
    {
        return explode('<br>', strip_tags(str_replace('</td>', '<br>', strip_tags($linha, '<td>')), '<br>'));
    }

    private static function formatarExportar($relat, $relatorioHTML)
    {
        $array = array();

        $todas_linhas = self::separarLinhasRelatHTML($relatorioHTML);
        foreach($todas_linhas as $valor)
            array_push($array, self::limparLinhaHTML($valor));

        return $array;
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

    public static function getRelatorioHTML($dados)
    {
        $dados['data'] = Carbon::hasFormat($dados['data'], 'Y-m') ? Carbon::parse($dados['data'])->format('m\/Y') : $dados['data'];

        $texto = '<tr>';
        $texto .= '<td>'.self::tipos()[$dados['tipo']].'</td>';
        $texto .= '<td>'.self::filtros()[$dados['opcoes']].'</td>';
        $texto .= '<td>'.$dados['data'].'</td>';
        $texto .= '<td>'.$dados['geral'].'</td>';
        $texto .= '<td>'.$dados['distintos'].'</td>';
        $texto .= '<td><small>'.now()->format('d\/m\/Y, \à\s H:i').'</small></td>';
        $texto .= '</tr>';

        return $texto;
    }

    public static function getRelatorioFinalHTML($dados)
    {
        $tabela = '';
        $total_geral = 0;
        $total_distinto = 0;

        foreach($dados as $key => $value)
        {
            if($key == 'relatorio_final')
                continue;
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

        return $tabela;
    }

    public static function exportarCsv($relat, $relatorioHTML)
    {
        $array = self::formatarExportar($relat, $relatorioHTML['tabela']);

        array_unshift($array, ['Área', 'Filtro', 'Período', 'Total geral', 'Total distintos', 'Gerado em']);
        $callback = function() use($array) {
            $fh = fopen('php://output','w');
            fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));
            foreach($array as $linha) 
                fputcsv($fh,$linha,';');
            fclose($fh);
        };

        return $callback;
    }
}
