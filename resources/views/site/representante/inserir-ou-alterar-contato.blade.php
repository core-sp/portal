@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="conteudo-txt w-100">
        <h4 class="pt-0 pb-0">{{ isset($id) && isset($tipo) ? 'Alterar contato' : 'Inserir contato' }}</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p>Preencha as informações abaixo para {{ isset($id) && isset($tipo) ? 'alterar o' : 'inserir um novo' }} contato.</p>
        <form action="{{ route('representante.inserir-ou-alterar-contato') }}" method="POST">
            @csrf
            @if (isset($id) && isset($tipo))
                <input type="hidden" name="id" value="{{ $id }}">
                <input type="hidden" name="tipo" value="{{ $tipo }}">
            @endif
            <div class="form-group mb-2 cadastroRepresentante">
                <label for="gerentiTipoContato">Tipo</label>
                <select name="tipo" id="gerentiTipoContato" class="form-control" {{ isset($tipo) ? 'disabled' : '' }}>
                    <option selected disabled>Selecione o tipo...</option>
                    @foreach(gerentiTiposContatos() as $key => $tipoCru)
                        <option value="{{ $key }}" {{ $key == $tipo ? 'selected' : '' }}>{{ $tipoCru }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-3 cadastroRepresentante">
                <label for="gerentiInserirContato">Conteúdo</label>
                <input
                    type="text"
                    name="contato"
                    class="form-control {{ $errors->has('contato') ? 'is-invalid' : '' }}"
                    id="gerentiInserirContato"
                    placeholder="Conteúdo do contato"
                    value="{{ $conteudo }}"
                    {{ !isset($tipo) ? 'disabled' : '' }}
                >
                @if($errors->has('contato'))
                    <div class="invalid-feedback">
                        {{ $errors->first('contato') }}
                    </div>
                @endif
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">{{ isset($id) && isset($tipo) ? 'Alterar' : 'Inserir' }}</button>
            </div>
        </form>
    </div>
</div>

@endsection