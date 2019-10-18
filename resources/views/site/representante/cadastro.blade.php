@extends('site.layout.app', ['title' => 'Cadastro'])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
    <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
        <img src="{{ asset('img/news-interna.png') }}" />
        <div class="row position-absolute pagina-titulo">
            <div class="container text-center">
                <h1 class="branco text-uppercase">
                    Cadastro
                </h1>
            </div>
        </div>
    </div>
</section>

<section id="pagina-noticias">
    <div class="container">
        <div class="row" id="conteudo-principal">
            <div class="col">
                <div class="row nomargin">
                    <div class="flex-one pr-4 align-self-center">
                        <h2 class="stronger">Cadastro de Representante Comercial</h2>
                    </div>
                    <div class="align-self-center">
                        <a href="/" class="btn-voltar">Voltar</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha-lg-mini"></div>
        <div class="row">

        </div>
    </div>
</section>

@endsection