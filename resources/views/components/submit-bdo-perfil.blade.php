<div class="mt-2">
    <div class="float-left">
        <form action="{{ route('bdorepresentantes.update', $resultado->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" name="setor" value="{{ $setor }}" />
            <input type="hidden" name="status" value="aceito" />
            <button 
                type="submit" 
                class="btn btn-primary {{ $errors->has('status') ? 'is-invalid' : '' }}"
            >
                Aceito
            </button>
            @if($errors->has('status'))
                <div class="invalid-feedback">
                    {{ $errors->first('status') }}
                </div>
            @endif
        </form>
    </div>

    <button class="btn btn-info ml-2" type="button" data-toggle="collapse" data-target="#perfil_justificar_{{ $setor }}">
        Recusar&nbsp;&nbsp;<i class="fas fa-chevron-down"></i>
    </button>

    <a href="{{ route('bdorepresentantes.lista') }}" class="btn btn-default ml-2">
        Cancelar
    </a>

    <div id="perfil_justificar_{{ $setor }}" class="collapse">
        <form action="{{ route('bdorepresentantes.update', $resultado->id) }}" method="POST" class="mt-2">
            @csrf
            @method('PATCH')
            <input type="hidden" name="setor" value="{{ $setor }}" />
            <input type="hidden" name="status" value="recusado">
            <label for="justificativa">Insira a justificativa:</label>
            <textarea 
                name="justificativa" 
                rows="3" 
                class="form-control {{ $errors->has('justificativa') ? 'is-invalid' : '' }}"
                maxlength="600"
            >{{ old('justificativa') }}</textarea>

            @if($errors->has('justificativa'))
                <div class="invalid-feedback">
                    {{ $errors->first('justificativa') }}
                </div>
            @endif
            <button type="submit" class="btn btn-danger mt-2">
                Recusar
            </button>
        </form>
    </div>
        
</div>