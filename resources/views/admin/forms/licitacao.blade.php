<form role="form" method="POST" action="{{ isset($resultado) ? route('licitacoes.update', $resultado->idlicitacao) : route('licitacoes.store') }}">
    @csrf
    @if(isset($resultado))
        @method('PATCH')
    @endif
    <div class="card-body">
        <div class="form-row">
            <div class="col-sm-6">
                <label for="modalidade">Modalidade</label>
                <select name="modalidade" class="form-control {{ $errors->has('modalidade') ? 'is-invalid' : '' }}">
                @foreach($modalidades as $modalidade)
                    <option value="{{ $modalidade }}" {{ (!empty(old('modalidade')) && old('modalidade') == $modalidade) || (isset($resultado->modalidade) && $resultado->modalidade == $modalidade) ? 'selected' : '' }}>{{ $modalidade }}</option>
                @endforeach
                </select>
                @if($errors->has('modalidade'))
                <div class="invalid-feedback">
                    {{ $errors->first('modalidade') }}
                </div>
                @endif
            </div>
            <div class="col-sm-3">
                <label for="situacao">Situação</label>
                <select name="situacao" class="form-control {{ $errors->has('situacao') ? 'is-invalid' : '' }}">
                @foreach($situacoes as $situacao)
                    <option value="{{ $situacao }}" {{ (!empty(old('situacao')) && old('situacao') == $situacao) || (isset($resultado->situacao) && $resultado->situacao == $situacao) ? 'selected' : '' }}>{{ $situacao }}</option>
                @endforeach
                </select>
                @if($errors->has('situacao'))
                <div class="invalid-feedback">
                    {{ $errors->first('situacao') }}
                </div>
                @endif
                </div>
            <div class="col-sm-3">
                <label for="uasg">UASG</label>
                <input type="text"
                    class="form-control {{ $errors->has('uasg') ? 'is-invalid' : '' }}"
                    placeholder="000000"
                    name="uasg"
                    value="{{ empty(old('uasg')) && isset($resultado->uasg) ? $resultado->uasg : old('uasg') }}"
                />
                @if($errors->has('uasg'))
                <div class="invalid-feedback">
                    {{ $errors->first('uasg') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="titulo">Título da Licitação</label>
                <input type="text"
                    class="form-control {{ $errors->has('titulo') ? 'is-invalid' : '' }}"
                    placeholder="Título"
                    name="titulo"
                    value="{{ empty(old('titulo')) && isset($resultado->titulo) ? $resultado->titulo : old('titulo') }}"
                />
                @if($errors->has('titulo'))
                <div class="invalid-feedback">
                    {{ $errors->first('titulo') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="edital">Edital</label>
                <div class="input-group">
                    <span class="input-group-prepend">
                        <a id="edital" data-input="file" data-preview="holder" class="btn btn-default">
                            <i class="fas fa-file-o"></i> Inserir Edital
                        </a>
                    </span>
                    <input id="file"
                        class="form-control {{ $errors->has('edital') ? 'is-invalid' : '' }}"
                        type="text"
                        name="edital"
                        value="{{ empty(old('edital')) && isset($resultado->edital) ? $resultado->edital : old('edital') }}"
                    />
                    @if($errors->has('edital'))
                    <div class="invalid-feedback">
                        {{ $errors->first('edital') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="nrprocesso">Nº do Processo</label>
                <input type="text"
                    class="form-control nrprocessoInput {{ $errors->has('nrprocesso') ? 'is-invalid' : '' }}"
                    placeholder="Número"
                    name="nrprocesso"
                    value="{{ empty(old('nrprocesso')) && isset($resultado->nrprocesso) ? $resultado->nrprocesso : old('nrprocesso') }}"
                />
                @if($errors->has('nrprocesso'))
                <div class="invalid-feedback">
                    {{ $errors->first('nrprocesso') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="nrlicitacao">Nº da Licitação</label>
                <input type="text"
                    class="form-control nrlicitacaoInput {{ $errors->has('nrlicitacao') ? 'is-invalid' : '' }}"
                    placeholder="Número"
                    name="nrlicitacao"
                    value="{{ empty(old('nrlicitacao')) && isset($resultado->nrlicitacao) ? $resultado->nrlicitacao : old('nrlicitacao') }}"
                />
                @if($errors->has('nrlicitacao'))
                <div class="invalid-feedback">
                    {{ $errors->first('nrlicitacao') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="datarealizacao">Data e Hora de Realização</label>
                <input type="datetime-local"
                    class="form-control {{ $errors->has('datarealizacao') ? 'is-invalid' : '' }}"
                    name="datarealizacao"
                    value="{{ empty(old('datarealizacao')) && isset($resultado->datarealizacao) ? $resultado->formatDtRealizacaoToInput() : old('datarealizacao') }}"
                />
                @if($errors->has('datarealizacao'))
                <div class="invalid-feedback">
                    {{ $errors->first('datarealizacao') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-group mt-2">
            <label for="objeto">Objeto da Licitação</label>
            <textarea name="objeto"
                class="form-control {{ $errors->has('objeto') ? 'is-invalid' : '' }} my-editor"
                id="conteudo"
                rows="25"
            >    
                {!! empty(old('objeto')) && isset($resultado->objeto) ? $resultado->objeto : old('objeto') !!}
            </textarea>
            @if($errors->has('objeto'))
            <div class="invalid-feedback">
                {{ $errors->first('objeto') }}
            </div>
            @endif
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('licitacoes.index') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
              {{ isset($resultado->idlicitacao) ? 'Salvar' : 'Publicar' }}
            </button>
        </div>
    </div>
</form>