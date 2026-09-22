<form role="form" method="GET" action="{{ route('admin.representante.buscaGerenti') }}">
    @csrf
    <div class="card-body">
        <div class="form-group  ">
            <label for="nome">Nome</label>
            <input
                type="text"
                name="nome"
                placeholder="Nome ou Razão Social"
                class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                @if(!empty(Request::old('nome')))
                    value="{{ Request::old('nome') }}"
                @endif
                @if(!empty(Request::input('nome')))
                    value="{{ Request::input('nome') }}"
                @endif
            >
            @if($errors->has('nome'))
                <div class="invalid-feedback">
                    {{ $errors->first('nome') }}
                </div>
            @endif
        </div>
        <div class="form-row">
            <div class="col">
                <label for="cpf_cnpj">CPF ou CNPJ</label>
                <input
                    type="text"
                    name="cpf_cnpj"
                    placeholder="CPF ou CNPJ"
                    class="form-control cpfOuCnpj {{ $errors->has('cpf_cnpj') ? 'is-invalid' : '' }}"
                    @if(!empty(Request::old('cpf_cnpj')))
                        value="{{ Request::old('cpf_cnpj') }}"
                    @endif
                    @if(!empty(Request::input('cpf_cnpj')))
                        value="{{ Request::input('cpf_cnpj') }}"
                    @endif
                >
                @if($errors->has('cpf_cnpj'))
                    <div class="invalid-feedback">
                        {{ $errors->first('cpf_cnpj') }}
                    </div>
                @endif
            </div>
            <div class="col">
                <label for="registro">Registro</label>
                <input
                    type="text"
                    name="registro"
                    placeholder="Registro"
                    class="form-control {{ $errors->has('registro') ? 'is-invalid' : '' }}"
                    id="registro_core"
                    @if(!empty(Request::old('registro')))
                        value="{{ Request::old('registro') }}"
                    @endif
                    @if(!empty(Request::input('registro')))
                        value="{{ Request::input('registro') }}"
                    @endif
                >
                @if($errors->has('registro'))
                    <div class="invalid-feedback">
                        {{ $errors->first('registro') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="form-row mt-3">
            <div class="col">
                <label for="regional">Regional</label>
                <select name="regional" class="form-control {{ $errors->has('regional') ? 'is-invalid' : '' }}" id="regional">
                    <option value="" {{ empty(old('regional')) ? 'selected' : '' }}>Escolha uma regional...</option>
                @foreach($regionais as $regional)
                    <option value="{{ $regional->regional }}" {{ old('regional') && (old('regional') == $regional->regional) ? 'selected' : '' }}>{{ $regional->regional }}</option>
                @endforeach
                </select>

                @if($errors->has('regional'))
                    <div class="invalid-feedback">
                        {{ $errors->first('regional') }}
                    </div>
                @endif
            </div>
            <div class="col">
                <label for="municipio">Município</label>
                <input
                    type="text"
                    name="municipio"
                    id="municipio"
                    placeholder="Município"
                    class="form-control {{ $errors->has('municipio') ? 'is-invalid' : '' }}"
                    @if(!empty(Request::old('municipio')))
                        value="{{ Request::old('municipio') }}"
                    @endif
                    @if(!empty(Request::input('municipio')))
                        value="{{ Request::input('municipio') }}"
                    @endif
                >
                @if($errors->has('municipio'))
                    <div class="invalid-feedback">
                        {{ $errors->first('municipio') }}
                    </div>
                @endif
            </div>
            <div class="col">
                <label for="anoCadastro">Ano de cadastro</label>
                <input
                    type="text"
                    name="anoCadastro"
                    class="form-control anoInput {{ $errors->has('anoCadastro') ? 'is-invalid' : '' }}"
                    id="anoCadastro"
                    @if(!empty(Request::old('anoCadastro')))
                        value="{{ Request::old('anoCadastro') }}"
                    @endif
                    @if(!empty(Request::input('anoCadastro')))
                        value="{{ Request::input('anoCadastro') }}"
                    @endif
                >
                @if($errors->has('anoCadastro'))
                    <div class="invalid-feedback">
                        {{ $errors->first('anoCadastro') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="form-group mt-3 mb-0">
        <button type="submit" class="btn btn-primary">Buscar {{ isset($tabela) ? 'novamente' : '' }}</button>
        </div>
    </div>
</form>
<div class="ml-4">
    <small class="light"><i>{!! isset($count) ? 'Sua busca retornou <strong>' . $count . '</strong> resultado(s).' : '' !!}</i></small>
</div>
@if (isset($tabela))
    @if ($tabela === 'vazia')
        <hr>
        <div class="card-body">
            <p><strong>Nenhum Representante encontrado!</strong></p>
        </div>
    @else
        <hr>
        <div class="card-body pt-0">
            {!! $tabela !!}
        </div>
    @endif
@endif