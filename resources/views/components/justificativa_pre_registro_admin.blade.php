@php
$resultado = $preRegistro->getJustificativaPorCampo($campo);
@endphp

@if(in_array($preRegistro->status, [$preRegistro::STATUS_ANALISE_INICIAL, $preRegistro::STATUS_CORRECAO, $preRegistro::STATUS_ANALISE_CORRECAO]))
    <button 
        class="btn btn-outline-{{ isset($resultado) ? 'danger' : 'success' }} btn-sm ml-2 justificativaPreRegistro" 
        type="button" 
        value="{{ $campo }}"
        data-toggle="tooltip" 
        data-placement="right"
        title="Inserir justificativa"
    >
        <i class="fas fa-{{ isset($resultado) ? 'edit' : 'user-edit' }}"></i>
    </button>
    @if(isset($resultado))
    <span class="badge badge-warning ml-2">Justificado</span>
    @endif
    <span class="valorJustificativaPR" style="display:none;">{{ isset($resultado) ? $resultado : '' }}</span>
@endif