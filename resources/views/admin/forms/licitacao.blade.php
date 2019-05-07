@php
use \App\Http\Controllers\Helpers\LicitacaoHelper;
use \App\Http\Controllers\Helper;
$modalidades = LicitacaoHelper::modalidades();
$situacoes = LicitacaoHelper::situacoes();
@endphp

<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
    {{ method_field('PUT') }}
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
    <div class="card-body">
        <div class="form-row">
        <div class="col-sm-6">
            <label for="modalidade">Modalidade</label>
            <select name="modalidade" class="form-control">
            @foreach($modalidades as $modalidade)
                @if(isset($resultado))
                    @if($resultado->modalidade === $modalidade)
                    <option value="{{ $modalidade }}" selected>{{ $modalidade }}</option>
                    @else
                    <option value="{{ $modalidade }}">{{ $modalidade }}</option>
                    @endif
                @else
                <option value="{{ $modalidade }}">{{ $modalidade }}</option>
                @endif
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
            <select name="situacao" class="form-control">
            @foreach($situacoes as $situacao)
                @if(isset($resultado))
                    @if($situacao == $resultado->situacao)
                    <option value="{{ $situacao }}" selected>{{ $situacao }}</option>
                    @else
                    <option value="{{ $situacao }}">{{ $situacao }}</option>
                    @endif
                @else
                <option value="{{ $situacao }}">{{ $situacao }}</option>
                @endif
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
                @if(isset($resultado))
                value="{{ $resultado->uasg }}"
                @endif
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
                @if(isset($resultado))
                value="{{ $resultado->titulo }}"
                @endif
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
                class="form-control"
                type="text"
                name="edital"
                @if(isset($resultado))
                value="{{ $resultado->edital }}"
                @endif
                />
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
                    @if(isset($resultado))
                    value="{{ $resultado->nrprocesso }}"
                    @endif
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
                    @if(isset($resultado))
                    value="{{ $resultado->nrlicitacao }}"
                    @endif
                    />
                @if($errors->has('nrlicitacao'))
                <div class="invalid-feedback">
                {{ $errors->first('nrlicitacao') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="datarealizacao">Data de Realização</label>
                <input type="text"
                    class="form-control dataInput"
                    name="datarealizacao"
                    placeholder="dd/mm/aaaa"
                    @if(isset($resultado))
                    value="{{ Helper::OnlyDate($resultado->datarealizacao) }}"
                    @endif
                    />
            </div>
            <div class="col">
                <label for="horainicio">Horário de Início</label>
                <input type="text" 
                    class="form-control timeInput" 
                    name="horainicio"
                    placeholder="00:00"
                    @if(isset($resultado))
                    value="{{ Helper::onlyHour($resultado->datarealizacao) }}"
                    @endif
                    />
                @if($errors->has('horainicio'))
                <div class="invalid-feedback">
                {{ $errors->first('horainicio') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-group mt-2">
        <label for="objeto">Objeto da Licitação</label>
        <textarea name="objeto"
            class="form-control {{ $errors->has('objeto') ? 'is-invalid' : '' }} my-editor"
            id="conteudo"
            rows="10">
            @if(isset($resultado))
                {!! $resultado->objeto !!}
            @endif
        </textarea>
        @if($errors->has('objeto'))
        <div class="invalid-feedback">
            {{ $errors->first('objeto') }}
        </div>
        @endif
        </div>
    </div>
    <div class="card-footer float-right">
        <a href="/admin/licitacoes" class="btn btn-default">Cancelar</a>
        <button type="submit" class="btn btn-primary ml-1">
        @if(isset($resultado))
            Salvar
        @else
            Publicar
        @endif
        </button>
    </div>
    </form>