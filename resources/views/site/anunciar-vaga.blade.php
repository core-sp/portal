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
                <p>Preencha o formulário abaixo para solicitar a inclusão de sua vaga(s) no <strong>Balcão de Oportunidades</strong> do <strong>Core-SP.</strong></p>
                <p>A(s) vaga(s) será(ão) disponibilizada(s) em até 03 (três) dias úteis, após a verificação dos dados informados.</p>
                <p>Para mais esclarecimentos, entre em contato conosco através do email <strong>informacoes@core-sp.org.br</strong>.</p>
                <h4>Informações da Empresa</h4>
                <form method="POST" class="w-100 simulador">
                    @csrf
                    <input type="hidden" name="descricao" value="Empresa cadastrada pelo site.">
                    <div class="form-row">
                        <div class="col-sm mb-2-576">
                            <label for="razaosocial">Razão Social</label>
                            <input
                                type="text"
                                class="form-control {{ $errors->has('razaosocial') ? 'is-invalid' : '' }}"
                                name="razaosocial"
                                placeholder="Razão Social"
                                value="{{ old('razaosocial') }}"
                            >
                            @if($errors->has('razaosocial'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('razaosocial') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-sm mb-2-576">
                            <label for="fantasia">Nome Fantasia</label>
                            <input
                                type="text"
                                class="form-control {{ $errors->has('fantasia') ? 'is-invalid' : '' }}"
                                name="fantasia"
                                placeholder="Nome Fantasia"
                                value="{{ old('fantasia') }}"
                            >
                            @if($errors->has('fantasia'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('fantasia') }}
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
                            <label for="site">Site / Rede Social</label>
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
                    <div class="form-group">
                        <label for="titulo">Título da Oportunidade</label>
                        <input
                            type="text"
                            name="titulo"
                            class="form-control {{ $errors->has('titulo') ? 'is-invalid' : '' }}"
                            placeholder="Título"
                            value="{{ old('titulo') }}"
                        >
                        @if($errors->has('titulo'))
                            <div class="invalid-feedback">
                                {{ $errors->first('titulo') }}
                            </div>
                        @endif
                    </div>
                    <div class="form-row mt-2">
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
                        <div class="col-sm-4 mb-2-576">
                            <label for="nrVagas">Quantidade de vagas</label>
                            <input
                                type="text"
                                name="nrVagas"
                                class="form-control numeroInput {{ $errors->has('nrVagas') ? 'is-invalid' : '' }}"
                                placeholder="00"
                                value="{{ old('nrVagas') }}"
                            >
                            @if($errors->has('nrVagas'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('nrVagas') }}
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
                            @foreach (regioes() as $key => $regiao)
                                <option value="{{ $key }}" {{ in_array($regiao, $oldRA) ? 'selected' : '' }}>{{ $regiao }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('regiaoAtuacao'))
                            <div class="invalid-feedback">
                                {{ $errors->first('regiaoAtuacao') }}
                            </div>
                        @endif
                        <small class="form-text text-muted">
                            <em>* Segure Ctrl para selecionar mais de uma região ou Shift para selecionar um grupo de regiões.</em>
                        </small>
                    </div>
                    <div class="form-group mt-2">
                        <label for="descricaoOportunidade">Descrição da Oportunidade</label> <i class="fas fa-question-circle d-inline azul" id="interrogation"></i>
                        <textarea
                            name="descricaoOportunidade"
                            rows="5"
                            class="form-control {{ $errors->has('descricaoOportunidade') ? 'is-invalid' : '' }}"
                            placeholder="Descreva brevemente a oportunidade."
                        >{{ old('descricaoOportunidade') }}</textarea>
                        @if($errors->has('descricaoOportunidade'))
                            <div class="invalid-feedback">
                                {{ $errors->first('descricaoOportunidade') }}
                            </div>
                        @endif
                    </div>
                    <h5 class="pb-2 pt-2 cinza-escuro">Informações do Contato</h5>
                    <div class="form-row">
                        <div class="col-sm mb-2-576">
                            <label for="contatonome">Nome</label>
                            <input
                                type="text"
                                name="contatonome"
                                class="form-control {{ $errors->has('contatonome') ? 'is-invalid' : '' }}"
                                placeholder="Nome"
                                value="{{ old('contatonome') }}"
                            >
                            @if($errors->has('contatonome'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('contatonome') }}
                                </div>
                            @endif
                        </div>
                        <div class="col-sm mb-2-576">
                            <label for="contatotelefone">Telefone</label>
                            <input
                                type="text"
                                name="contatotelefone"
                                class="form-control telefoneInput {{ $errors->has('contatotelefone') ? 'is-invalid' : '' }}"
                                placeholder="Telefone"
                                value="{{ old('contatotelefone') }}"
                            >
                            @if($errors->has('contatotelefone'))
                                <div class="invalid-feedback">
                                    {{ $errors->first('contatotelefone') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="form-group mt-2">
                        <label for="contatoemail">Email</label>
                        <input
                            type="text"
                            name="contatoemail"
                            class="form-control {{ $errors->has('contatoemail') ? 'is-invalid' : '' }}"
                            placeholder="Email"
                            value="{{ old('contatoemail') }}"
                        >
                        @if($errors->has('contatoemail'))
                            <div class="invalid-feedback">
                                {{ $errors->first('contatoemail') }}
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