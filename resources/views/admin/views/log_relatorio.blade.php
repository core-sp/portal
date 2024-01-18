<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Core-SP — Relatório</title>
        <link rel="icon" href="{{ asset('img/favicon.ico') }}" type="image/ico" />

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>
        <div class="container mt-2">
            <h2>Relatório do Portal via Log</h2>
            @if(isset($tabela))
            <table class="table">
                <thead>
                    <tr>
                        <th>Área</th>
                        <th>Filtro</th>
                        <th>Período</th>
                        <th>Total geral</th>
                        <th>Total distintos</th>
                        <th>Gerado em</th>
                    </tr>
                </thead>
                <tbody>
                    {!! $tabela !!}
                </tbody>
            </table>
            @else
                <h4>Sem dados para gerar relatório.</h4>
            @endif
            <hr />
            <a class="btn btn-primary" href="{{ route('suporte.log.externo.index') }}">Voltar</a>
            @if(isset($relat))
            <a class="btn btn-success float-right" href="{{ route('suporte.log.externo.relatorios.acoes', ['relat' => $relat, 'acao' => 'exportar-csv']) }}">Exportar .csv</a>
            @endif
        </div>
    </body>
</html>