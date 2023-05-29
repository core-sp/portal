@if(in_array($preRegistro->status, [$preRegistro::STATUS_ANALISE_INICIAL, $preRegistro::STATUS_CORRECAO, $preRegistro::STATUS_ANALISE_CORRECAO]))
    <button 
        class="btn btn-outline-{{ isset($resultado[$campo]) ? 'danger' : 'success' }} btn-sm ml-2 justificativaPreRegistro" 
        type="button" 
        value="{{ $campo }}"
        data-toggle="tooltip" 
        data-placement="right"
        title="Inserir justificativa"
    >
        <i class="fas fa-{{ isset($resultado[$campo]) ? 'edit' : 'user-edit' }}"></i>
    </button>
    @if(isset($resultado[$campo]))
    <span class="badge badge-warning ml-2">Justificado</span>
    @endif
    <span class="valorJustificativaPR" style="display:none;">{{ isset($resultado[$campo]) ? $resultado[$campo] : '' }}</span>
@endif