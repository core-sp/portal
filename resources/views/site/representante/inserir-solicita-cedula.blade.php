@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light w-100">
        <h4 class="pt-0 pb-0">Solicitar cédula</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p>Preencha as informações abaixo para solicitar sua <strong>cédula profissional.</strong></p>
        <form action="{{ route('representante.inserirSolicitarCedula') }}" method="POST" id="cedula">
            @csrf

            <p>
                <span class="text-danger"><strong>*</strong></span><small><em> Preenchimento obrigatório</em></small>
            </p>

            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="nome">Nome <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        name="nome"
                        class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                        id="nome"
                        placeholder="Nome Completo"
                        value="{{ isset($nome) ? $nome : old('nome') }}"
                        {{ isset($nome) ? 'readonly' : ''}}
                        minlength="6"
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
                    <label for="rg">RG <span class="text-danger">*</span></label>
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
                    <label for="cpf">CPF <span class="text-danger">*</span></label>
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
                    <label for="cep">CEP <span class="text-danger">*</span></label>
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
                    <label for="bairro">Bairro <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        name="bairro"
                        class="form-control {{ $errors->has('bairro') ? 'is-invalid' : '' }}"
                        id="bairro"
                        placeholder="Bairro"
                        value="{{ old('bairro') }}"
                        minlength="4"
                        maxlength="100"
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
                <label for="rua">Logradouro <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="logradouro"
                    class="form-control {{ $errors->has('logradouro') ? 'is-invalid' : '' }}"
                    id="rua"
                    placeholder="Logradouro"
                    value="{{ old('logradouro') }}"
                    minlength="4"
                    maxlength="100"
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
                    <label for="numero">Número <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        name="numero"
                        class="form-control numero {{ $errors->has('numero') ? 'is-invalid' : '' }}"
                        id="numero"
                        placeholder="Número"
                        value="{{ old('numero') }}"
                        minlength="1"
                        maxlength="15"
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
                        maxlength="100"
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
                    <label for="uf">Estado <span class="text-danger">*</span></label>
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
                    <label for="cidade">Município <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        name="municipio"
                        id="cidade"
                        class="form-control {{ $errors->has('municipio') ? 'is-invalid' : '' }}"
                        placeholder="Município"
                        value="{{ old('municipio') }}"
                        minlength="3"
                        maxlength="100"
                        required
                    >
                    @if($errors->has('municipio'))
                        <div class="invalid-feedback">
                            {{ $errors->first('municipio') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="tipo">Tipo da cédula <span class="text-danger">*</span></label>
                    <select name="tipo" id="tipo" class="form-control {{ $errors->has('tipo') ? 'is-invalid' : '' }}">
                    @if(auth()->guard('representante')->user()->tipoPessoa() == 'PJ')
                        <option value="{{ $tipos[0] }}" selected>{{ $tipos[0] }}</option>
                    @else
                        @foreach ($tipos as $tipo)
                        <option value="{{ $tipo }}" {{ old('tipo') === $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                        @endforeach
                    @endif
                    </select>
                    @if($errors->has('tipo'))
                        <div class="invalid-feedback">
                            {{ $errors->first('tipo') }}
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