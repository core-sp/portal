<?php

namespace App;

use Carbon\Carbon;

class Suporte
{
    const ERROS = 'erros';
    const INTERNO = 'interno';
    const EXTERNO = 'externo';

    const FILTRO_ACESSO = 'acessos';

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
            self::FILTRO_ACESSO => 'Total de acessos na área restrita (logins)',
        ];
    }

    public static function textosFiltros()
    {
        return [
            self::FILTRO_ACESSO . '_externo' => 'conectou-se à Área do Representante.',
            self::FILTRO_ACESSO . '_interno' => 'conectou-se ao Painel Administrativo.',
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
            $data = Carbon::hasFormat($value['relatorio']['data'], 'Y-m') ? Carbon::parse($value['relatorio']['data'])->format('m\/Y') : $value['relatorio']['data'];
            $tabela .= $value['tabela'];
            $total_geral += $value['relatorio']['geral'];
            $total_distinto += $value['relatorio']['distintos'];
        }

        $tabela .= '<tr>';
        $tabela .= '<td colspan="3" class="font-weight-bolder">Total Final</td>';
        $tabela .= '<td class="font-weight-bolder">'.$total_geral.'</td>';
        $tabela .= '<td class="font-weight-bolder">'.$total_distinto.'</td>';
        $tabela .= '<td class="font-weight-bolder"><small>'.now()->format('d\/m\/Y, \à\s H:i').'</small></td>';
        $tabela .= '</tr>';

        return $tabela;
    }
}
