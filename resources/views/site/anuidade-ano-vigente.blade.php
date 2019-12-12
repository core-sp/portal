@extends('site.layout.app', ['title' => 'Anuidade do ano vigente'])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
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
                        <h2 class="stronger">Imprima o boleto de anuidade do ano vigente</h2>
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
                <p>Informe o CPF abaixo para imprimir o boleto de anuidade do ano vigente.</p>
                <form method="post" class="cadastroRepresentante">
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
                            <div class="g-recaptcha {{ $errors->has('g-recaptcha-response') ? 'is-invalid' : '' }}" data-sitekey="{{env('GOOGLE_RECAPTCHA_KEY')}}"></div>
                            @if($errors->has('g-recaptcha-response'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('g-recaptcha-response') }}
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-primary">
                            Imprimir
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-lg-4">
                @include('site.inc.content-sidebar')
            </div>
        </div>
    </div>
</section>

@endsection