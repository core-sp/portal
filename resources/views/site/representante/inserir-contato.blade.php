@extends('site.representante.app')

@section('content-representante')

<div class="representante-content w-100">
    <div class="conteudo-txt-mini light w-100">
        <h4 class="pt-0 pb-0">Inserir contato</h4>
        <div class="linha-lg-mini mb-3"></div>
        <p>Preencha as informações abaixo para {{ isset($id) && isset($tipo) ? 'alterar o' : 'inserir um novo' }} contato.</p>
        <form action="{{ route('representante.inserir-ou-alterar-contato') }}" method="POST">
            @csrf
            {{-- @if (isset($id) && isset($tipo))
                <input type="hidden" name="id" value="{{ $id }}">
                <input type="hidden" name="tipo" value="{{ $tipo }}">
            @endif --}}
            <div class="form-group mb-2 cadastroRepresentante">
                <label for="gerentiTipoContato">Tipo</label>
                <select name="tipo" id="gerentiTipoContato" class="form-control">
                    <option selected disabled>Selecione o tipo...</option>
                    @foreach(gerentiTiposContatosInserir() as $key => $tipoCru)
                        <option value="{{ $key }}">{{ $tipoCru }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-3 cadastroRepresentante">
                <label for="gerentiInserirContato">Conteúdo</label>
                <input
                    type="text"
                    name="contato"
                    class="form-control gerentiContato {{ $errors->has('contato') ? 'is-invalid' : '' }}"
                    id="gerentiInserirContato"
                    placeholder="Conteúdo do contato"
                    disabled
                >
                @if($errors->has('contato'))
                    <div class="invalid-feedback">
                        {{ $errors->first('contato') }}
                    </div>
                @endif
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary loadingPagina">Inserir</button>
            </div>
        </form>
    </div>
</div>

<script type="module" src="{{ asset('/js/restrita-rc/modulos/contato.js?'.hashScriptJs()) }}" data-modulo-id="contato" data-modulo-acao="editar"></script>

@endsection