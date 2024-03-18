<div class="form-check border border-left-0 border-info rounded-right mb-2 pr-4">
    <label class="form-check-label pl-4">
        <input type="hidden" name="id-{{ $texto->id }}" value="{{ $texto->id }}" />
        <input type="checkbox" class="form-check-input mt-2" name="excluir_ids" value="{{ $texto->id }}">
        <button type="button" class="btn btn-link btn-sm pl-0 abrir" value="{{ $texto->id }}">
        @if($texto->tipoTitulo())
            {!! session()->exists('novo_texto') && ($texto->id == session()->get('novo_texto')) ? '<span class="badge badge-warning">Novo</span>&nbsp;&nbsp;' : '' !!}
            <span class="indice-texto">{{ $texto->tituloFormatado() }}</span>
        @else
            <strong><span class="indice-texto">{{ $texto->subtituloFormatado() }}</span></strong>
        @endif
        </button>
    </label>
</div>