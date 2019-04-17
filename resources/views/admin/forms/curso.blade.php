@php
use \App\Http\Controllers\Helpers\CursoHelper;
use \App\Http\Controllers\Helper;
$tipos = CursoHelper::tipos();
@endphp

<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
        {{ method_field('PUT') }}
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
    <div class="card-body">
        <div class="form-row">
            <div class="col-sm-3">
                <label for="tipo">Tipo</label>
                <select name="tipo" class="form-control">
                @foreach($tipos as $tipo)
                    @if(isset($resultado))
                        @if($tipo == $resultado->tipo)
                        <option value="{{ $tipo }}" selected>{{ $tipo }}</option>
                        @else
                        <option value="{{ $tipo }}">{{ $tipo }}</option>
                        @endif
                    @else
                    <option value="{{ $tipo }}">{{ $tipo }}</option>
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
                    @if(isset($resultado))
                    value="{{ $resultado->tema }}"
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
                <input type="number"
                    name="nrvagas"
                    class="form-control"
                    max="10000"
                    @if(isset($resultado))
                    value="{{ $resultado->nrvagas }}"
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
                    @if(isset($resultado))
                        @if($regional->idregional == $resultado->idregional)
                        <option value="{{ $regional->idregional }}" selected>
                        {{ $regional->regional }}
                        </option>
                        @else
                        <option value="{{ $regional->idregional }}">
                        {{ $regional->regional }}
                        </option>
                        @endif
                    @else
                    <option value="{{ $regional->idregional }}">
                    {{ $regional->regional }}
                    </option>
                    @endif
                @endforeach
                </select>
            </div>
            <div class="col">
                <label for="endereco">Endereço</label>
                <input type="text"
                    name="endereco"
                    class="form-control"
                    maxlength="191"
                    placeholder="Endereço"
                    @if(isset($resultado))
                    value="{{ $resultado->endereco }}"
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
                    class="form-control dataInput" 
                    name="datarealizacao" 
                    placeholder="dd/mm/aaaa"
                    @if(isset($resultado))
                    value="{{ Helper::onlyDate($resultado->datarealizacao) }}"
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
            <div class="col">
                <label for="datatermino">Data de Término</label>
                <input type="text" 
                    class="form-control dataInput" 
                    name="datatermino"
                    id="datatermino"
                    placeholder="dd/mm/aaaa"
                    @if(isset($resultado))
                    value="{{ Helper::onlyDate($resultado->datatermino) }}"
                    @endif
                    />
                @if($errors->has('datatermino'))
                <div class="invalid-feedback">
                {{ $errors->first('datatermino') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="horatermino">Horário de Término</label>
                <input type="text" 
                    class="form-control timeInput" 
                    name="horatermino"
                    placeholder="00:00"
                    @if(isset($resultado))
                    value="{{ Helper::onlyHour($resultado->datatermino) }}"
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
                <span class="input-group-btn">
                    <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-default">
                    <i class="fas fa-picture-o"></i> Inserir imagem
                    </a>
                </span>
                <input id="thumbnail"
                    class="form-control"
                    type="text"
                    name="img"
                    @if(isset($resultado))
                    value="{{ $resultado->img }}"
                    @endif
                    />
                </div>
            </div>
        </div>
        <div class="form-group mt-3">
            <label for="resumo">Resumo</label>
            <textarea name="resumo"
                class="form-control {{ $errors->has('resumo') ? 'is-invalid' : '' }}"
                id="resumo"
                placeholder="Resumo do curso"
                rows="3">@if(isset($resultado)) {!! $resultado->resumo !!} @endif</textarea>
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
                rows="10">
                @if(isset($resultado))
                {!! $resultado->descricao !!}    
                @endif
            </textarea>
            @if($errors->has('descricao'))
            <div class="invalid-feedback">
                {{ $errors->first('descricao') }}
            </div>
            @endif
        </div>
    </div>
    <div class="card-footer float-right">
        <a href="/admin/cursos" class="btn btn-default">Cancelar</a>
        <button type="submit" class="btn btn-primary ml-1">
        @if(isset($resultado))
            Salvar
        @else
            Publicar
        @endif    
        </button>
    </div>
</form>