@extends('site.layout.app', ['title' => 'Anunciar Vaga'])

@section('content')

<section id="pagina-cabecalho" class="mt-1">
    <div class="container-fluid text-center nopadding position-relative pagina-titulo-img">
        <img src="{{ asset('img/bdo.png') }}" />
        <div class="row position-absolute pagina-titulo" id="bdo-titulo">
            <div class="container text-center">
                <h1 class="branco text-uppercase">
                    Anunciar vaga
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
                        <h2 class="stronger">Solicite a inclusão de sua vaga no Balcão de Oportunidades do Core-SP</h2>
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
                <p>Preencha o formulário abaixo para solicitar a inclusão de sua vaga no <strong>Balcão de Oportunidades</strong> do <strong>Core-SP.</strong></p>
                <p>Você será notificado sobre a inclusão da vaga através do email informado no formulário, uma vez que as informações forem verificadas e incluídas em nosso sistema.</p>
                <p>Para mais esclarecimentos, entre em contato conosco através do informacoes@core-sp.org.br</p>
                <h4>Informações da Empresa</h4>
                <form method="POST" class="w-100 simulador">
                    @csrf
                    <div class="form-row">
                        <div class="col-sm mb-2-576">
                            <label for="razaoSocial">Razão Social</label>
                            <input
                                type="text"
                                class="form-control {{ $errors->has('razaoSocial') ? 'is-invalid' : '' }}"
                                name="razaoSocial"
                                placeholder="Razão Social"
                                value="{{ old('razaoSocial') }}"
                            >
                            @if($errors->has('razaoSocial'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('razaoSocial') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-sm mb-2-576">
                            <label for="nomeFantasia">Nome Fantasia</label>
                            <input
                                type="text"
                                class="form-control {{ $errors->has('nomeFantasia') ? 'is-invalid' : '' }}"
                                name="nomeFantasia"
                                placeholder="Nome Fantasia"
                                value="{{ old('nomeFantasia') }}"
                            >
                            @if($errors->has('nomeFantasia'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('nomeFantasia') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="form-row mt-2">
                        <div class="col-sm mb-2-576">
                            <label for="cnpj">CNPJ</label>
                            <input type="text"
                                class="form-control cnpjInput {{ $errors->has('cnpj') ? 'is-invalid' : '' }}"
                                placeholder="CNPJ"
                                name="cnpj"
                                id="cnpj"
                                maxlength="191"
                                value="{{ old('cnpj') }}"
                            >
                            @if($errors->has('cnpj'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('cnpj') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-sm mb-2-576">
                            <label for="capital">Capital Social</label>
                            <select name="capital" class="form-control">
                                @foreach (capitais() as $capital)
                                    <option value="{{ $capital }}" {{ $capital == old('capital') ? 'selected' : '' }}>{{ $capital }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('capital'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('capital') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="form-row mt-2">
                        <div class="col-sm mb-2-576">
                            <label for="segmento">Segmento</label>
                            <select name="segmento" class="form-control">
                                @foreach (segmentos() as $segmento)
                                    <option value="{{ $segmento }}" {{ $segmento == old('segmento') ? 'selected' : '' }}>{{ $segmento }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('segmento'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('segmento') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="form-row mt-2">
                        <div class="col-sm mb-2-576">
                            <label for="endereco">Endereço</label>
                            <input
                                type="text"
                                name="endereco"
                                class="form-control {{ $errors->has('endereco') ? 'is-invalid' : '' }}"
                                placeholder="Endereço"
                                value="{{ old('endereco') }}"
                            >
                            @if($errors->has('endereco'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('endereco') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-sm mb-2-576">
                            <label for="telefone">Telefone</label>
                            <input
                                type="text"
                                name="telefone"
                                class="form-control telefoneInput {{ $errors->has('telefone') ? 'is-invalid' : '' }}"
                                placeholder="Telefone"
                                value="{{ old('telefone') }}"
                            >
                            @if($errors->has('telefone'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('telefone') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="form-row mt-2">
                        <div class="col-sm mb-2-576">
                            <label for="site">Site</label>
                            <input
                                type="text"
                                name="site"
                                class="form-control {{ $errors->has('site') ? 'is-invalid' : '' }}"
                                placeholder="Website"
                                value="{{ old('site') }}"
                            >
                            @if($errors->has('site'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('site') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-sm mb-2-576">
                            <label for="email">Email</label>
                            <input
                                type="text"
                                name="email"
                                class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                placeholder="Email"
                                value="{{ old('email') }}"
                            >
                            @if($errors->has('email'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('email') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <h4 class="mt-3">Informações da Oportunidade</h4>
                    <div class="form-row">
                        <div class="col-sm mb-2-576">
                            <label for="nrVagas">Quantidade de vagas</label>
                            <input
                                type="text"
                                name="nrVagas"
                                class="form-control numeroInput {{ $errors->has('nrvagas') ? 'is-invalid' : '' }}"
                                placeholder="00"
                                value="{{ old('nrVagas') }}"
                            >
                            @if($errors->has('nrVagas'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('razaoSocial') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-sm mb-2-576">
                            <label for="segmentoOportunidade">Segmento da Oportunidade</label>
                            <select name="segmentoOportunidade" class="form-control">
                                @foreach (segmentos() as $segmento)
                                    <option value="{{ $segmento }}" {{ $segmento == old('segmentoOportunidade') ? 'selected' : '' }}>{{ $segmento }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('segmentoOportunidade'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('segmentoOportunidade') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    @php
                        old('regiaoAtuacao') !== null ? $oldRA = old('regiaoAtuacao') : $oldRA = []
                    @endphp
                    <div class="form-group mt-2">
                        <label for="regiaoAtuacao">Região de Atuação</label>
                        <select name="regiaoAtuacao[]" class="form-control {{ $errors->has('regiaoAtuacao') ? 'is-invalid' : '' }}" multiple>
                            @foreach (regioes() as $regiao)
                                <option value="{{ $regiao }}" {{ in_array($regiao, $oldRA) ? 'selected' : '' }}>{{ $regiao }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('regiaoAtuacao'))
                            <div class="invalid-feedback">
                                {{ $errors->first('regiaoAtuacao') }}
                            </div>
                        @endif
                    </div>
                    <div class="form-group mt-2">
                        <label for="descricao">Descrição da Oportunidade</label>
                        <textarea
                            name="descricao"
                            rows="5"
                            class="form-control {{ $errors->has('descricao') ? 'is-invalid' : '' }}"
                            placeholder="Descreva brevemente a oportunidade"
                        >{{ old('descricao') }}</textarea>
                        @if($errors->has('descricao'))
                            <div class="invalid-feedback">
                                {{ $errors->first('descricao') }}
                            </div>
                        @endif
                    </div>
                    <h5 class="pb-2 pt-2 cinza-escuro">Informações do Contato</h5>
                    <div class="form-row">
                        <div class="col-sm mb-2-576">
                            <label for="contatoNome">Nome</label>
                            <input
                                type="text"
                                name="contatoNome"
                                class="form-control {{ $errors->has('contatoNome') ? 'is-invalid' : '' }}"
                                placeholder="Nome"
                                value="{{ old('contatoNome') }}"
                            >
                            @if($errors->has('contatoNome'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('contatoNome') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-sm mb-2-576">
                            <label for="contatoTelefone">Telefone</label>
                            <input
                                type="text"
                                name="contatoTelefone"
                                class="form-control telefoneInput {{ $errors->has('contatoTelefone') ? 'is-invalid' : '' }}"
                                placeholder="Telefone"
                                value="{{ old('contatoTelefone') }}"
                            >
                            @if($errors->has('contatoTelefone'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('contatoTelefone') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="form-group mt-2">
                        <label for="contatoEmail">Email</label>
                        <input
                            type="text"
                            name="contatoEmail"
                            class="form-control {{ $errors->has('contatoEmail') ? 'is-invalid' : '' }}"
                            placeholder="Email"
                            value="{{ old('contatoEmail') }}"
                        >
                        @if($errors->has('contatoEmail'))
                            <div class="invalid-feedback">
                                {{ $errors->first('contatoEmail') }}
                            </div>
                        @endif
                    </div>
                    <div class="form-group mt-3">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Enviar
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