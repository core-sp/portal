<form role="form" method="POST">
    @csrf
    <div class="card-body">
        <div class="form-row mb-2">

            <div class="col-3">
                <label for="data">Data</label>
                <input type="text"
                    class="form-control dataInput {{ $errors->has('data') ? 'is-invalid' : '' }}"
                    placeholder="dd/mm/aaaa"
                    name="data"
                    @if(!empty(old('data')))
                        value="{{ old('data') }}"
                    @elseif(isset($resultado))
                        value="{{ onlyDate($resultado->data) }}"
                    @endif
                />
                @if($errors->has('data'))
                <div class="invalid-feedback">
                {{ $errors->first('data') }}
                </div>
                @endif

            </div>

            <div class="col-3">
                <label for="horarioinicio">Início</label>
                <input type="text"
                    class="form-control {{ $errors->has('horarioinicio') ? 'is-invalid' : '' }}"
                    id="horaInicio"
                    name="horarioinicio"
                    placeholder="hh:mm"
                    @if(!empty(old('horarioinicio')))
                        value="{{ old('horarioinicio') }}"
                    @elseif(isset($resultado))
                        value="{{ $resultado->horarioinicio }}"
                    @endif
                />
                @if($errors->has('horarioinicio'))
                  <div class="invalid-feedback">
                    {{ $errors->first('horarioinicio') }}
                  </div>
                @endif
            </div>

            <div class="col-3">
                <label for="horariotermino">Término</label>
                <input type="text"
                    class="form-control {{ $errors->has('horariotermino') ? 'is-invalid' : '' }}"
                    id="horaTermino"
                    name="horariotermino"
                    placeholder="hh:mm"
                    @if(!empty(old('horariotermino')))
                        value="{{ old('horariotermino') }}"
                    @elseif(isset($resultado))
                        value="{{ $resultado->horariotermino }}"
                    @endif
                />
                @if($errors->has('horariotermino'))
                  <div class="invalid-feedback">
                    {{ $errors->first('horariotermino') }}
                  </div>
                @endif
            </div>

            <div class="col-3">
                <label for="local">Local</label>
                <input type="text"
                    class="form-control {{ $errors->has('local') ? 'is-invalid' : '' }}"
                    placeholder="Digite o local do compromisso"
                    name="local"
                    @if(!empty(old('local')))
                        value="{{ old('local') }}"
                    @elseif(isset($resultado))
                        value="{{ $resultado->local }}"
                    @endif
                    />
                @if($errors->has('local'))
                <div class="invalid-feedback">
                {{ $errors->first('local') }}
                </div>
                @endif
            </div>
        </div>

        <div class="form-row mb-2">
            <div class="col">
                <label for="titulo">Título</label>
                <input type="text"
                    class="form-control {{ $errors->has('titulo') ? 'is-invalid' : '' }}"
                    placeholder="Digite o título do compromisso"
                    name="titulo"
                    @if(!empty(old('titulo')))
                        value="{{ old('titulo') }}"
                    @elseif(isset($resultado))
                        value="{{ $resultado->titulo }}"
                    @endif
                    />
                @if($errors->has('titulo'))
                <div class="invalid-feedback">
                {{ $errors->first('titulo') }}
                </div>
                @endif

            </div>
        </div>

        <div class="form-row mb-2">
            <label for="descricao">Descrição</label>

            @php
                $value = null;

                if(!empty(old('descricao'))) {
                    $value = old('descricao');
                }
                elseif(isset($resultado)) {
                    $value = $resultado->descricao;
                }
            @endphp

            <textarea name="descricao"
                class="form-control {{ $errors->has('descricao') ? 'is-invalid' : '' }}"
                rows="5"
                placeholder="Digite a descrição do compromisso"
                >{{ $value }}</textarea>
            @if($errors->has('descricao'))
                <div class="invalid-feedback">
                    {{ $errors->first('descricao') }}
                </div>
            @endif
        </div>

    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/compromissos" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">Salvar</button>
        </div>
    </div>
</form>