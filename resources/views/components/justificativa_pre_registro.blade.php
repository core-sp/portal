@if(isset($menu))

    @if($resultado->userPodeCorrigir() && empty($correcoes))
    <span class="badge badge-success"><i class="icon fa fa-check"></i></span>
    @elseif(isset($correcoes) && !empty($correcoes))
        @foreach($correcoes as $correcao => $campo)
        <span class="badge badge-danger"> {{ $correcao }}</span>
        @endforeach
    @endif

@else

    @if($resultado->userPodeCorrigir() && !empty($correcoes))
    <div class="alert alert-warning">
        <h5>
            <span class="text-danger">Justificativa(s): </span>
            @foreach($correcoes as $key => $campo)
            <button 
                class="btn btn-danger btn-sm pb-0 pt-0 mb-1 textoJust" 
                value="{{ route('externo.preregistro.justificativa.view', ['preRegistro' => $resultado->id, 'campo' => $campo]) }}"
            >
                <strong>{{ $key }}</strong>
            </button>
            {{ $loop->last ? '' : '|' }} 
        @endforeach
        </h5>
    </div>
    @endif

@endif