@if($resultado->userPodeCorrigir() && !empty($correcoes))
    <div class="d-block w-100">
        <div class="alert alert-warning">
            <span class="bold">Justificativa(s):</span>
            <br>
        @foreach($correcoes as $key => $texto)
            <p>
                <span class="bold">{{ $key . ': ' }}</span>{{ $texto }}
            </p>
        @endforeach
        </div>
    </div>
@endif