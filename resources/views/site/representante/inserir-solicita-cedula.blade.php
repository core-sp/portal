@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light w-100">
        <h4 class="pt-0 pb-0">Solicitar cédula</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p>Preencha as informações abaixo para solicitar sua <strong>cédula profissional.</strong></p>
        <form action="{{ route('representante.inserirSolicitarCedula') }}" method="POST" id="cedula">
            @csrf
            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="nome">Nome *</label>
                    <input
                        type="text"
                        name="nome"
                        class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                        id="nome"
                        placeholder="Nome Completo"
                        value="{{ isset($nome) ? $nome : old('nome') }}"
                        {{ isset($nome) ? 'readonly' : ''}}
                        maxlength="191"
                        required
                    >
                    @if($errors->has('nome'))
                        <div class="invalid-feedback">
                            {{ $errors->first('nome') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="rg">RG *</label>
                    <input
                        type="text"
                        name="rg"
                        class="form-control rgInput {{ $errors->has('rg') ? 'is-invalid' : '' }}"
                        id="rg"
                        placeholder="RG"
                        value="{{ isset($rg) ? $rg : old('rg') }}"
                        {{ isset($rg) ? 'readonly' : ''}}
                        maxlength="20"
                        required
                    >
                    @if($errors->has('rg'))
                        <div class="invalid-feedback">
                            {{ $errors->first('rg') }}
                        </div>
                    @endif
                </div>
                <div class="col-sm">
                    <label for="cpf">CPF *</label>
                    <input
                        type="text"
                        name="cpf"
                        class="form-control cpfInput {{ $errors->has('cpf') ? 'is-invalid' : '' }}"
                        id="cpf"
                        placeholder="CPF"
                        value="{{ isset($cpf) ? $cpf : old('cpf') }}"
                        {{ isset($cpf) ? 'readonly' : ''}}
                        required
                    >
                    @if($errors->has('cpf'))
                        <div class="invalid-feedback">
                            {{ $errors->first('cpf') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="cep">CEP *</label>
                    <input
                        type="text"
                        name="cep"
                        class="form-control cep {{ $errors->has('cep') ? 'is-invalid' : '' }}"
                        id="cep"
                        placeholder="CEP"
                        value="{{ old('cep') }}"
                        required
                    >
                    @if($errors->has('cep'))
                        <div class="invalid-feedback">
                            {{ $errors->first('cep') }}
                        </div>
                    @endif
                </div>
                <div class="col-sm">
                    <label for="bairro">Bairro *</label>
                    <input
                        type="text"
                        name="bairro"
                        class="form-control {{ $errors->has('bairro') ? 'is-invalid' : '' }}"
                        id="bairro"
                        placeholder="Bairro"
                        value="{{ old('bairro') }}"
                        required
                    >
                    @if($errors->has('bairro'))
                        <div class="invalid-feedback">
                            {{ $errors->first('bairro') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="form-group mb-2 cadastroRepresentante">
                <label for="rua">Logradouro *</label>
                <input
                    type="text"
                    name="logradouro"
                    class="form-control {{ $errors->has('logradouro') ? 'is-invalid' : '' }}"
                    id="rua"
                    placeholder="Logradouro"
                    value="{{ old('logradouro') }}"
                    required
                >
                @if($errors->has('logradouro'))
                    <div class="invalid-feedback">
                        {{ $errors->first('logradouro') }}
                    </div>
                @endif
            </div>
            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="numero">Número *</label>
                    <input
                        type="text"
                        name="numero"
                        class="form-control numero {{ $errors->has('numero') ? 'is-invalid' : '' }}"
                        id="numero"
                        placeholder="Número"
                        value="{{ old('numero') }}"
                        required
                    >
                    @if($errors->has('numero'))
                        <div class="invalid-feedback">
                            {{ $errors->first('numero') }}
                        </div>
                    @endif
                </div>
                <div class="col-sm">
                    <label for="complemento">Complemento</label>
                    <input
                        type="text"
                        name="complemento"
                        class="form-control {{ $errors->has('complemento') ? 'is-invalid' : '' }}"
                        id="complemento"
                        placeholder="Complemento"
                        value="{{ old('complemento') }}"
                    >
                    @if($errors->has('complemento'))
                        <div class="invalid-feedback">
                            {{ $errors->first('complemento') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="uf">Estado *</label>
                    <select name="estado" id="uf" class="form-control {{ $errors->has('estado') ? 'is-invalid' : '' }}">
                        @foreach (estados() as $key => $estado)
                            <option value="{{ $key }}" {{ old('estado') === $key ? 'selected' : '' }}>{{ $estado }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('estado'))
                        <div class="invalid-feedback">
                            {{ $errors->first('estado') }}
                        </div>
                    @endif
                </div>
                <div class="col-sm">
                    <label for="cidade">Município *</label>
                    <input
                        type="text"
                        name="municipio"
                        id="cidade"
                        class="form-control {{ $errors->has('municipio') ? 'is-invalid' : '' }}"
                        placeholder="Município"
                        value="{{ old('municipio') }}"
                        required
                    >
                    @if($errors->has('municipio'))
                        <div class="invalid-feedback">
                            {{ $errors->first('municipio') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">Enviar</button>
            </div>
        </form>
    </div>
</div>

@endsection