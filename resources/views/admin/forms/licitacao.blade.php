@php
use \App\Http\Controllers\Helpers\LicitacaoHelper;
use \App\Http\Controllers\Helper;
$modalidades = LicitacaoHelper::modalidades();
$situacoes = LicitacaoHelper::situacoes();
@endphp

<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
    <div class="card-body">
        <div class="form-row">
        <div class="col-sm-6">
            <label for="modalidade">Modalidade</label>
            <select name="modalidade" class="form-control {{ $errors->has('modalidade') ? 'is-invalid' : '' }}">
            @foreach($modalidades as $modalidade)
                @if(!empty(old('modalidade')))
                    @if(old('modalidade') === $modalidade)
                        <option value="{{ $modalidade }}" selected>{{ $modalidade }}</option>
                    @else
                        <option value="{{ $modalidade }}">{{ $modalidade }}</option>
                    @endif
                @else
                    @if(isset($resultado))
                        @if($resultado->modalidade === $modalidade)
                            <option value="{{ $modalidade }}" selected>{{ $modalidade }}</option>
                        @else
                            <option value="{{ $modalidade }}">{{ $modalidade }}</option>
                        @endif
                    @else
                        <option value="{{ $modalidade }}">{{ $modalidade }}</option>
                    @endif
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
            <select name="situacao" class="form-control {{ $errors->has('situacao') ? 'is-invalid' : '' }}">
            @foreach($situacoes as $situacao)
                @if(!empty(old('situacao')))
                    @if(old('situacao') === $situacao)
                            <option value="{{ $situacao }}" selected>{{ $situacao }}</option>
                    @else
                        <option value="{{ $situacao }}">{{ $situacao }}</option>
                    @endif
                @else
                    @if(isset($resultado))
                        @if($situacao == $resultado->situacao)
                           <option value="{{ $situacao }}" selected>{{ $situacao }}</option>
                        @else
                            <option value="{{ $situacao }}">{{ $situacao }}</option>
                        @endif
                    @else
                        <option value="{{ $situacao }}">{{ $situacao }}</option>
                    @endif
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
                @if(!empty(old('uasg')))
                    value="{{ old('uasg') }}"
                @else
                    @if(isset($resultado))
                        value="{{ $resultado->uasg }}"
                    @endif
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
                @if(!empty(old('titulo')))
                    value="{{ old('titulo') }}"
                @else
                    @if(isset($resultado))
                        value="{{ $resultado->titulo }}"
                    @endif
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
                @if(!empty(old('edital')))
                    value="{{ old('edital') }}"
                @else
                    @if(isset($resultado))
                        value="{{ $resultado->edital }}"
                    @endif
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
                    @if(!empty(old('nrprocesso')))
                        value="{{ old('nrprocesso') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $resultado->nrprocesso }}"
                        @endif
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
                    @if(!empty(old('nrlicitacao')))
                        value="{{ old('nrlicitacao') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $resultado->nrlicitacao }}"
                        @endif
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
                    class="form-control dataInput {{ $errors->has('datarealizacao') ? 'is-invalid' : '' }}"
                    name="datarealizacao"
                    placeholder="dd/mm/aaaa"
                    @if(!empty(old('datarealizacao')))
                        value="{{ old('datarealizacao') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ Helper::OnlyDate($resultado->datarealizacao) }}"
                        @endif
                    @endif
                    />
                @if($errors->has('datarealizacao'))
                <div class="invalid-feedback">
                {{ $errors->first('datarealizacao') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="horainicio">Horário de Início</label>
                <input type="text" 
                    class="form-control timeInput {{ $errors->has('horainicio') ? 'is-invalid' : '' }}"
                    name="horainicio"
                    placeholder="00:00"
                    @if(!empty(old('horainicio')))
                        value="{{ old('horainicio') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ Helper::onlyHour($resultado->datarealizacao) }}"
                        @endif
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
            rows="10">@if(!empty(old('objeto'))){{ old('objeto') }}@else @if(isset($resultado)){!! $resultado->objeto !!}@endif @endif</textarea>
        @if($errors->has('objeto'))
        <div class="invalid-feedback">
            {{ $errors->first('objeto') }}
        </div>
        @endif
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/licitacoes" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
            @if(isset($resultado))
                Salvar
            @else
                Publicar
            @endif
            </button>
        </div>
    </div>
</form>