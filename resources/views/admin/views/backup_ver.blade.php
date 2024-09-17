<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Core-SP — Backup {{ $nome_doc }}</title>
        <link rel="icon" href="{{ asset('img/favicon.ico') }}" type="image/ico" />
        <link type="text/css" href="{{ asset('/css/app.css') }}" rel="stylesheet">
    </head>
    <body>
        <nav class="navbar navbar-expand-sm bg-warning justify-content-center fixed-top">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <h1 class="navbar-text"><i class="fas fa-database"></i>&nbsp;&nbsp;Visualizar backup <strong>{{ $nome_doc }}</strong></h1>
                </li>
                <li class="nav-item d-flex flex-wrap align-content-center ml-5">
                    <a href="{{ route('textos.view', ['tipo_doc' => $tipo_doc]) }}" class="nav-link btn btn-lg btn-success px-2 py-1">Voltar</a>
                </li>
            </ul>
        </nav>

        <div class="container mb-5" style="margin-top:130px">
            <hr />
            @if(isset($backup) && !isset($backup['message']))

                @foreach(json_decode($backup, true) as $chave => $item)
                    <p class="mb-0"><strong>ID -</strong> {{ $item['id'] }}</p>
                    <p class="mb-0"><strong>{{ $item['tipo'] }} -</strong> {{ $item['texto_tipo'] }}</p>
                    <p class="mb-0"><strong>Índice -</strong> {{ $item['indice'] }}</p>
                    <p class="mb-0"><strong>Atualizado em -</strong> {{ formataData($item['updated_at']) }}</p>
                    <p class="mb-0"><strong>Conteúdo:</strong></p>
                    <div class="border border-dark p-2">
                        {!! !filter_var($item['conteudo'], FILTER_VALIDATE_URL) ? '<p class="mb-0">' . $item['conteudo'] . '</p>' : 
                            '<a href="' . $item['conteudo'] . '" target="_blank" class="mb-0"> ' . $item['conteudo'] . '</a>' !!}
                    </div>
                    <hr />
                @endforeach

            @else
                <h4>Sem backup para visualizar.</h4>
                <hr />
            @endif

        </div>

        <script type="text/javascript" src="{{ asset('/js/app.js') }}"></script>
        <script type="text/javascript" src="{{ asset('/js/jquery-ui.min.js') }}"></script>
    </body>
</html>