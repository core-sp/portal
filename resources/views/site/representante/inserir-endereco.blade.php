@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light w-100">
        <h4 class="pt-0 pb-0">Inserir endereço</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p>Preencha as informações abaixo para inserir um <strong>novo endereço de correspondência.</strong></p>
        <form action="{{ route('representante.inserir-endereco') }}" method="POST" enctype="multipart/form-data">
            @csrf
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
                    >
                    @if($errors->has('municipio'))
                        <div class="invalid-feedback">
                            {{ $errors->first('municipio') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="cadastroRepresentante">
                <label>Comprovante de residência *</label>
            </div>
            <div class="custom-file">
                <input
                    type="file"
                    name="crimage"
                    class="custom-file-input {{ $errors->has('crimage') ? 'is-invalid' : '' }}"
                    id="comprovante-residencia"
                    role="button"
                >
                <label class="custom-file-label" for="comprovante-residencia" data-clarity-mask="True">Selecionar arquivo...</label>
                @if($errors->has('crimage'))
                    <div class="invalid-feedback">
                        {{ $errors->first('crimage') }}
                    </div>
                @endif
            </div>
            <div id="showCrimageDois" class="mt-2">
                <p><i>* Se necessário, anexe outro arquivo <a id="linkShowCrimageDois" class="pointer vermelho">clicando aqui.</a></i></p>
            </div>
            <div class="custom-file mt-2" id="divCrimageDois">
                <input
                    type="file"
                    name="crimagedois"
                    class="custom-file-input {{ $errors->has('crimagedois') ? 'is-invalid' : '' }}"
                    id="comprovante-residencia-dois"
                    role="button"
                >
                <label class="custom-file-label" for="comprovante-residencia" data-clarity-mask="True">Selecionar outro arquivo... <i>(opcional)</i></label>
                @if($errors->has('crimagedois'))
                    <div class="invalid-feedback">
                        {{ $errors->first('crimagedois') }}
                    </div>
                @endif
            </div>
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">Cadastrar</button>
            </div>
        </form>
    </div>
</div>

@endsection