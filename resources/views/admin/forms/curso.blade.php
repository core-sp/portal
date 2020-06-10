@php
use \App\Http\Controllers\Helpers\CursoHelper;
use \App\Http\Controllers\Helper;
$tipos = CursoHelper::tipos();
@endphp

<form role="form" method="POST" action="{{ !isset($resultado) ? route('cursos.store') : route('cursos.update', Request::route('id')) }}">
    @csrf
    @if(isset($resultado))
        @method('PATCH')
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
    <div class="card-body">
        <div class="form-row">
            <div class="col-sm-3">
                <label for="tipo">Tipo</label>
                <select name="tipo" class="form-control">
                @foreach($tipos as $tipo)
                    @if(!empty(old('tipo')))
                        @if(old('tipo') === $tipo)
                            <option value="{{ $tipo }}" selected>{{ $tipo }}</option>
                        @else
                            <option value="{{ $tipo }}"">{{ $tipo }}</option>
                        @endif
                    @else
                        @if(isset($resultado))
                            @if($tipo == $resultado->tipo)
                            <option value="{{ $tipo }}" selected>{{ $tipo }}</option>
                            @else
                            <option value="{{ $tipo }}">{{ $tipo }}</option>
                            @endif
                        @else
                        <option value="{{ $tipo }}">{{ $tipo }}</option>
                        @endif
                    @endif
                @endforeach
                </select>
                @if($errors->has('tipo'))
                <div class="invalid-feedback">
                {{ $errors->first('tipo') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="tema">Tema</label>
                <input type="text"
                    class="form-control {{ $errors->has('tema') ? 'is-invalid' : '' }}" 
                    placeholder="Tema" 
                    name="tema"
                    maxlength="191"
                    @if(!empty(old('tema')))
                        value="{{ old('tema') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $resultado->tema }}"
                        @endif
                    @endif
                    />
                @if($errors->has('tema'))
                <div class="invalid-feedback">
                {{ $errors->first('tema') }}
                </div>
                @endif
            </div>
            <div class="col-2">
                <label for="nrvagas">Nº de vagas</label>
                <input type="text"
                    name="nrvagas"
                    class="form-control vagasInput {{ $errors->has('nrvagas') ? 'is-invalid' : '' }}"
                    placeholder="00"
                    @if(!empty(old('nrvagas')))
                        value="{{ old('nrvagas') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $resultado->nrvagas }}"
                        @endif
                    @endif
                    />
                @if($errors->has('nrvagas'))
                <div class="invalid-feedback">
                {{ $errors->first('nrvagas') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col-sm-3">
                <label for="idregional">Regional</label>
                <select name="idregional" class="form-control" id="idregional">
                @foreach($regionais as $regional)
                    @if(!empty(old('idregional')))
                        @if(old('idregional') == $regional->idregional)
                            <option value="{{ $regional->idregional }}" selected>{{ $regional->regional }}</option>
                        @else
                            <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                        @endif
                    @else
                        @if(isset($resultado))
                            @if($regional->idregional == $resultado->idregional)
                                <option value="{{ $regional->idregional }}" selected>{{ $regional->regional }}</option>
                            @else
                                <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                            @endif
                        @else
                            <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                        @endif
                    @endif
                @endforeach
                </select>
            </div>
            <div class="col">
                <label for="endereco">Endereço</label>
                <input type="text"
                    name="endereco"
                    class="form-control {{ $errors->has('endereco') ? 'is-invalid' : '' }}"
                    maxlength="191"
                    placeholder="Endereço"
                    @if(!empty(old('endereco')))
                        value="{{ old('endereco') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $resultado->endereco }}"
                        @endif
                    @endif
                    />
                @if($errors->has('endereco'))
                <div class="invalid-feedback">
                {{ $errors->first('endereco') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="datarealizacao">Data de Realização</label>
                <input type="text" 
                    class="form-control {{ $errors->has('datarealizacao') ? 'is-invalid' : '' }}" 
                    name="datarealizacao" 
                    placeholder="dd/mm/aaaa"
                    id="dataInicio"
                    @if(!empty(old('datarealizacao')))
                        value="{{ old('datarealizacao') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ Helper::onlyDate($resultado->datarealizacao) }}"
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
                <label for="datatermino">Data de Término</label>
                <input type="text" 
                    class="form-control {{ $errors->has('datatermino') ? 'is-invalid' : '' }}" 
                    name="datatermino"
                    id="dataTermino"
                    placeholder="dd/mm/aaaa"
                    @if(!empty(old('datatermino')))
                        value="{{ old('datatermino') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ Helper::onlyDate($resultado->datatermino) }}"
                        @endif
                    @endif
                    />
                @if($errors->has('datatermino'))
                <div class="invalid-feedback">
                {{ $errors->first('datatermino') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="horainicio">Horário de Início</label>
                <input type="text" 
                    class="form-control {{ $errors->has('horainicio') ? 'is-invalid' : '' }}" 
                    name="horainicio"
                    id="horaInicio"
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
            <div class="col">
                <label for="horatermino">Horário de Término</label>
                <input type="text" 
                    class="form-control {{ $errors->has('horatermino') ? 'is-invalid' : '' }}"
                    name="horatermino"
                    placeholder="00:00"
                    id="horaTermino"
                    @if(!empty(old('horatermino')))
                        value="{{ old('horatermino') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ Helper::onlyHour($resultado->datatermino) }}"
                        @endif
                    @endif
                    />
                @if($errors->has('horatermino'))
                <div class="invalid-feedback">
                {{ $errors->first('horatermino') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="lfm">Imagem principal</label>
                <div class="input-group">
                <span class="input-group-prepend">
                    <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-default">
                    <i class="fas fa-picture-o"></i> Inserir imagem
                    </a>
                </span>
                <input id="thumbnail"
                    class="form-control {{ $errors->has('img') ? 'is-invalid' : '' }}"
                    type="text"
                    name="img"
                    @if(!empty(old('img')))
                        value="{{ old('img') }}"
                    @else
                        @if(isset($resultado))
                            value="{{ $resultado->img }}"
                        @endif
                    @endif
                    />
                @if($errors->has('img'))
                <div class="invalid-feedback">
                {{ $errors->first('img') }}
                </div>
                @endif
                </div>
            </div>
            <div class="col-sm-3">
                <label for="publicado">Publicar agora?</label>
                <select name="publicado" class="form-control">
                    @if(isset($resultado))
                        @if($resultado->publicado == 'Sim')
                        <option value="Sim" selected>Sim</option>
                        <option value="Não">Não</option>
                        @else
                        <option value="Sim">Sim</option>
                        <option value="Não" selected>Não</option>
                        @endif
                    @else
                    <option value="Sim">Sim</option>
                    <option value="Não">Não</option>
                    @endif
                </select>
            </div>
        </div>
        <div class="form-group mt-3">
            <label for="resumo">Resumo</label>
            <textarea name="resumo"
                class="form-control {{ $errors->has('resumo') ? 'is-invalid' : '' }}"
                id="resumo"
                placeholder="Resumo do curso"
                rows="3">@if(!empty(old('resumo'))){{ old('resumo') }}@else @if(isset($resultado)) {!! $resultado->resumo !!}@endif @endif</textarea>
            @if($errors->has('resumo'))
            <div class="invalid-feedback">
                {{ $errors->first('resumo') }}
            </div>
            @endif
        </div>
        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea name="descricao" 
                class="form-control my-editor {{ $errors->has('descricao') ? 'is-invalid' : '' }}"
                id="descricao"
                rows="10">@if(!empty(old('descricao'))){{ old('descricao') }}@else @if(isset($resultado)){!! $resultado->descricao !!}@endif @endif</textarea>
            @if($errors->has('descricao'))
            <div class="invalid-feedback">
                {{ $errors->first('descricao') }}
            </div>
            @endif
        </div>
    </div>
    <div class="card-footer float-right">
        <div class="float-right">
            <a href="/admin/cursos" class="btn btn-default">Cancelar</a>
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