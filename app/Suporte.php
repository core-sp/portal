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

    public static function filtros()
    {
        return [
            self::FILTRO_ACESSO => 'Total de acessos (logins)',
        ];
    }

    public static function textosFiltros()
    {
        return [
            self::FILTRO_ACESSO . '_externo' => 'conectou-se à Área do Representante.',
            self::FILTRO_ACESSO . '_interno' => 'conectou-se ao Painel Administrativo.',
        ];
    }

    private static function inicioRelatorioHTML()
    {
        return '<!DOCTYPE html><html lang="en">
        <head>
            <title>Relatório</title>
            <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
            <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
        </head>
        <body>
        <div class="container">
          <h2>Relatório via Log</h2>
          <table class="table">';
    }

    private static function finalRelatorioHTML()
    {
        return '</table><hr>
            <a href="'.route('suporte.log.externo.index').'">Voltar</a>
            <span class="float-right"><i>Gerado em '.now()->format('d\/m\/Y, \à\s H:i').'</i></span>
            </div></body></html>';
    }

    private static function tabelaRelatorioHTML()
    {
        return '<thead>
            <tr>
                <th>Área</th>
                <th>Filtro</th>
                <th>Data escolhida</th>
                <th>Total geral</th>
                <th>Total distintos</th>
            </tr></thead>';
    }

    public static function getRelatorioHTML($dados)
    {
        $dados['data'] = Carbon::hasFormat($dados['data'], 'Y-m') ? Carbon::parse($dados['data'])->format('m\/Y') : $dados['data'];

        $texto = self::inicioRelatorioHTML(). self::tabelaRelatorioHTML(). '
            <tbody>
              <tr>
                <td>'.self::tipos()[$dados['tipo']].'</td>
                <td>'.self::filtros()[$dados['opcoes']].'</td>
                <td>'.$dados['data'].'</td>
                <td>'.$dados['geral'].'</td>
                <td>'.$dados['distintos'].'</td>
              </tr>
            </tbody>' . self::finalRelatorioHTML();

        return $texto;
    }
}
