
<form role="form" method="POST" action="{{ route('avisos.editar', $resultado->id) }}">
    @csrf
    @method('PUT')
    <div class="card-body">
        <h5><strong>Status:</strong> <span class="{{ $resultado->isAtivado() ? 'text-success' : 'text-danger' }}">{{ $resultado->status }}</span></h5>

        @if($resultado->area == $resultado::areas()[1])
        <p><strong><span class="text-danger">ATENÇÃO!</span></strong> Esse aviso <strong>ATIVADO</strong> desabilita o envio de formulário para anunciar vaga!</p>
        @endif
        <div class="form-row mb-3">
            @if(!$resultado->isComponenteSimples())
            <div class="col-8 mt-2">
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
            @endif
            <div class="col mt-2">
                <label for="cor_fundo_titulo">Cor de fundo</label>
                <br>
                @foreach($cores as $cor)
                <div class="form-check-inline">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input {{ $errors->has('cor_fundo_titulo') ? 'is-invalid' : '' }}" name="cor_fundo_titulo" value="{{ $cor }}" {{ $resultado->cor_fundo_titulo == $cor ? 'checked' : '' }}>
                        <i class="fas fa-square fa-border text-{{ $cor }}"></i>
                        @if($errors->has('cor_fundo_titulo'))
                        <div class="invalid-feedback">
                            {{ $errors->first('cor_fundo_titulo') }}
                        </div>
                        @endif
                    </label>
                </div>
                @endforeach
            </div>
        </div>
        <div class="form-row mb-3">
            <div class="col">
                <label for="dia_hora_ativar">Dia e hora para <span class="text-success">ativar</span> o aviso <small>(opcional)</small></label>
                <input type="datetime-local"
                    class="form-control {{ $errors->has('dia_hora_ativar') ? 'is-invalid' : '' }}"
                    name="dia_hora_ativar"
                    value="{{ isset($resultado) ? $resultado->formatDtAtivarToInput() : old('dia_hora_ativar') }}"
                />
                @if($errors->has('dia_hora_ativar'))
                    <div class="invalid-feedback">
                        {{ $errors->first('dia_hora_ativar') }}
                    </div>
                @endif
            </div>
            <div class="col">
                <label for="dia_hora_desativar">Dia e hora para <span class="text-danger">desativar</span> o aviso <small>(opcional)</small></label>
                <input type="datetime-local"
                    class="form-control {{ $errors->has('dia_hora_desativar') ? 'is-invalid' : '' }}"
                    name="dia_hora_desativar"
                    value="{{ isset($resultado) ? $resultado->formatDtDesativarToInput() : old('dia_hora_desativar') }}"
                />
                @if($errors->has('dia_hora_desativar'))
                    <div class="invalid-feedback">
                        {{ $errors->first('dia_hora_desativar') }}
                    </div>
                @endif
            </div>
        </div>
        <div class="form-group mt-2">
            <label for="conteudo">Conteúdo do aviso na área do {{ $resultado->area }}</label>
            <textarea name="conteudo"
                class="form-control my-editor {{ $errors->has('conteudo') ? 'is-invalid' : '' }}"
                id="conteudo"
                rows="25"
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
            <a href="{{ route('avisos.index') }}" class="btn btn-default">Voltar</a>
            <button type="submit" class="btn btn-primary ml-1">
                Salvar
            </button>
        </div>
    </div>
</form>