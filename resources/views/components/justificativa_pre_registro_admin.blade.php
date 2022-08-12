@if(in_array($preRegistro->status, [$preRegistro::STATUS_ANALISE_INICIAL, $preRegistro::STATUS_CORRECAO, $preRegistro::STATUS_ANALISE_CORRECAO]))
    <button class="btn btn-outline-danger btn-sm ml-2 justificativaPreRegistro" type="button" value="{{ $campo }}">
        <i class="fas fa-{{ isset($resultado[$campo]) ? 'edit' : 'times' }}"></i>
    </button>
    @if(isset($resultado[$campo]))
    <span class="badge badge-warning ml-2">Justificado</span>
    @endif
    <span class="valorJustificativaPR" style="display:none;">{{ isset($resultado[$campo]) ? $resultado[$campo] : '' }}</span>
@endif