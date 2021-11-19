
<form role="form" method="POST" action="{{ route('avisos.editar', $resultado->id) }}">
    @csrf
    @method('PUT')
    <div class="card-body">
        <div class="form-row">
            <div class="col-8">
                <label for="titulo">Título do aviso na área do {{ $resultado->area }}</label>
                <input type="text"
                    class="form-control {{ $errors->has('titulo') ? 'is-invalid' : '' }}"
                    placeholder="Título"
                    name="titulo"
                    value="{{ isset($resultado) ? $resultado->titulo : old('titulo') }}"
                    />
                @if($errors->has('titulo'))
                    <div class="invalid-feedback">
                        {{ $errors->first('titulo') }}
                    </div>
                @endif
            </div>
            <div class="col">
                <label for="cor_fundo_titulo">Cor de fundo do Título</label>
                <br>
                @foreach($cores as $cor)
                <div class="form-check-inline">
                    <label class="form-check-label" for="radio1">
                        <input type="radio" class="form-check-input" name="cor_fundo_titulo" value="{{ $cor }}" {{ $resultado->cor_fundo_titulo == $cor ? 'checked' : '' }}>
                        <i class="fas fa-square fa-border text-{{ str_replace('bg-', '', $cor) }}"></i>
                    </label>
                </div>
                @endforeach
                @if($errors->has('cor_fundo_titulo'))
                    <div class="invalid-feedback">
                        {{ $errors->first('cor_fundo_titulo') }}
                    </div>
                @endif
            </div>
        </div>
        <div class="form-group mt-2">
            <label for="conteudo">Conteúdo do aviso na área do {{ $resultado->area }}</label>
            <textarea name="conteudo"
                class="form-control my-editor {{ $errors->has('conteudo') ? 'is-invalid' : '' }}"
                id="conteudo"
                rows="10"
            >{!! isset($resultado) ? $resultado->conteudo : old('conteudo') !!}</textarea>
            @if($errors->has('conteudo'))
                <div class="invalid-feedback">
                    {{ $errors->first('conteudo') }}
                </div>
            @endif
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('avisos.index') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
                Salvar
            </button>
        </div>
    </div>
</form>