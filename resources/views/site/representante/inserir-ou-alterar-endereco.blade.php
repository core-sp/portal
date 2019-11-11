@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light w-100">
        <h4 class="pt-0 pb-0">{{ isset($infos) ? 'Alterar endereço' : 'Inserir endereço' }}</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p>Preencha as informações abaixo para {{ isset($infos) ? 'alterar o' : 'inserir um novo' }} endereço.</p>
        <form action="{{ route('representante.inserir-ou-alterar-endereco') }}" method="POST">
            @csrf
            @if (isset($sequencia))
                <input type="hidden" name="sequencia" value="{{ $sequencia }}">
            @endif
            <div class="form-row mb-2 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="cep">CEP *</label>
                    <input
                        type="text"
                        name="cep"
                        class="form-control cep {{ $errors->has('cep') ? 'is-invalid' : '' }}"
                        id="cep"
                        placeholder="CEP"
                        value="{{ $infos[0]['END_CEP'] }}"
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
                        value="{{ $infos[0]['END_BAIRRO'] }}"
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
                    value="{{ $infos[0]['END_LOGRADOURO'] }}"
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
                        value="{{ $infos[0]['END_NUMERO'] }}"
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
                        value="{{ $infos[0]['END_CONPLEMENTO'] }}"
                    >
                    @if($errors->has('complemento'))
                        <div class="invalid-feedback">
                            {{ $errors->first('complemento') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="form-row mb-3 cadastroRepresentante">
                <div class="col-sm mb-2-576">
                    <label for="uf">Estado *</label>
                    <select name="estado" id="uf" class="form-control {{ $errors->has('estado') ? 'is-invalid' : '' }}">
                        @foreach (estados() as $key => $estado)
                            <option value="{{ $key }}" {{ $key === $infos[0]['END_ESTADO'] ? 'selected' : '' }}>{{ $estado }}</option>
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
                        value="{{ utf8_encode($infos[0]['END_MUNICIPIO']) }}"
                    >
                    @if($errors->has('municipio'))
                        <div class="invalid-feedback">
                            {{ $errors->first('municipio') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="form-check mb-4">
                <input type="checkbox" class="form-check-input" id="corresp" name="corresp" {{ $infos[0]['END_CORRESP'] === 'T' ? 'checked' : '' }}>
                <label class="form-check-label" for="corresp">Endereço para correspondência</label>
            </div>
            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary">{{ isset($sequencia) ? 'Alterar' : 'Inserir' }}</button>
            </div>
        </form>
    </div>
</div>

@endsection