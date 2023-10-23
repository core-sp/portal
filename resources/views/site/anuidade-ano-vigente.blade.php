@extends('site.layout.app', ['title' => 'Anuidade do ano vigente'])

@section('content')

<section id="pagina-cabecalho">
    <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
        <img src="{{ asset('img/institucional.png') }}" />
        <div class="row position-absolute pagina-titulo" id="bdo-titulo">
            <div class="container text-center">
                <h1 class="branco text-uppercase">
                    Anuidade do ano vigente
                </h1>
            </div>
        </div>
    </div>
</section>
<section id="pagina-conteudo">
    <div class="container">
        <div class="row" id="conteudo-principal">
            <div class="col">
                <div class="row nomargin">
                    <div class="flex-one pr-4 align-self-center">
                        <h2 class="stronger">Baixe o boleto de anuidade do ano vigente</h2>
                    </div>
                    <div class="align-self-center">
                        <a href="/" class="btn-voltar">Voltar</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="linha-lg"></div>
        <div class="row mt-2">
            <div class="col-lg-8 conteudo-txt pr-4">

                @if(isset($aviso))
                    @component('components.aviso-simples', [
                        'cor_fundo_titulo' => $aviso->cor_fundo_titulo,
                        'titulo' => $aviso->titulo, 
                        'conteudo' => $aviso->conteudo
                    ])
                    @endcomponent
                @endif
                
                <p>Informe o CPF ou CNPJ abaixo para verificar a disponibilidade do boleto de anuidade do ano vigente, e então baixe-o clicando no link.</p>
                <p>Ou, se preferir, acesse a <a href="/representante/login">Área Restrita do Representante,</a> e confira também outras informações relativas ao seu cadastro junto ao Core-SP, incluindo seu extrato financeiro detalhado.</p>
                <p class="text-danger"><strong>
                    IMPORTANTE: para ter acesso ao boleto que unifica as cobranças de anuidade de responsável técnico e pessoa jurídica, digite o <u>CNPJ</u> no campo indicado.
                </strong></p>    
                <form method="post" class="cadastroRepresentante" id="anoVigente">
                    @csrf
                    <div class="form-group">
                        <label for="cpfCnpj">CPF ou CNPJ *</label>
                        <input
                            type="text"
                            name="cpfCnpj"
                            class="form-control cpfOuCnpj {{ $errors->has('cpfCnpj') ? 'is-invalid' : '' }}"
                            id="cpfCnpj"
                            value="{{ old('cpfCnpj') }}"
                            placeholder="CPF ou CNPJ"
                        >
                        @if($errors->has('cpfCnpj'))
                            <div class="invalid-feedback">
                                {{ $errors->first('cpfCnpj') }}
                            </div>
                        @endif
                    </div>
                    <div class="form-group mt-2">
                        @if(env('GOOGLE_RECAPTCHA_KEY'))
                            <div class="g-recaptcha {{ $errors->has('g-recaptcha-response') ? 'is-invalid' : '' }}" data-sitekey="{{ env('GOOGLE_RECAPTCHA_KEY') }}"></div>
                            @if($errors->has('g-recaptcha-response'))
                                <div class="invalid-feedback" style="display:block;">
                                    {{ $errors->first('g-recaptcha-response') }}
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-primary" id="anoVigenteButton">
                           Verificar {{ Session::get('nossonumero') || Session::get('notFound') ? 'novamente' : '' }}
                        </button>
                        <div id="loadingSimulador"><img src="{{ asset('img/ajax-loader.gif') }}" class="pt-0" alt="Loading"></div>
                    </div>
                </form>
                @if(Session::get('nossonumero'))
                    <hr>
                    <p class="pb-0"><strong>Anuidade encontrada!</strong> Baixe o boleto clicando no link abaixo:</p>
                    <h3 class="text-uppercase">
                        <a href="https://boletoonline.caixa.gov.br/ecobranca/SIGCB/imprimir/0779951/{{ Session::get('nossonumero')[0]['NOSSONUMERO'] }}" class="normal text-info">
                            <button class="btn btn-success btn-lg"
                                onClick="gtag('event', 'download', {
                                    'event_category': 'boleto',
                                    'event_label': 'Boleto do Ano Vigente'
                                });"
                            >
                                <i class="fas fa-download"></i>&nbsp;&nbsp;BAIXAR BOLETO
                            </button>
                        </a>
                    </h3>
                    <hr>
                    <p><small class="light">Observação: Atente-se às datas informadas no corpo do boleto para pagar a anuidade com desconto.</small></p>
                @endif
                @if(Session::get('notFound'))
                    <hr>
                    <strong>Nenhum boleto encontrado para o CPF/CNPJ informado.</strong>
                @endif
            </div>
            <div class="col-lg-4">
                @include('site.inc.content-sidebar')
            </div>
        </div>
    </div>
</section>

@endsection