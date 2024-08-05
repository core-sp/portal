<div class="form-check border border-left-0 border-info rounded-right mb-2 pl-2 {{ ($orientacao_sumario == 'vertical') && !$texto->tipoTitulo() ? 'ml-' . (string) ($texto->nivel + 2) : '' }}">
    <button type="button" 
        class="btn btn-success btn-sm ml-0 mt-0 mb-0 mr-2 pt-0 pb-0 mover" 
        value="{{ $texto->id }}"
    >
        <i class="{{ $orientacao_sumario == 'horizontal' ? 'fas fa-exchange-alt' : 'fas fa-exchange-alt fa-rotate-90' }}"></i>
    </button>

    <label class="form-check-label ml-4">
        <input type="hidden" name="id-{{ $texto->id }}" value="{{ $texto->id }}" />
        <input type="checkbox" class="form-check-input mt-2" name="excluir_ids" value="{{ $texto->id }}">
        <button type="button" class="btn btn-link btn-sm pl-0 abrir" value="{{ $texto->id }}">
        @if($texto->tipoTitulo())
            {!! (session()->exists('novo_texto') && ($texto->id == session()->get('novo_texto'))) || (session()->exists('novos_textos') && in_array($texto->id, session()->get('novos_textos'))) ? 
                '<span class="badge badge-warning">Novo</span>&nbsp;&nbsp;' : '' !!}
            <span class="indice-texto">{{ $texto->tituloFormatado() }}</span>
        @else
            <strong><span class="indice-texto">{{ $texto->subtituloFormatado() }}</span></strong>
        @endif
        </button>
    </label>
</div>