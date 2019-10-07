@php
use \App\Http\Controllers\Helper;
use \App\Http\Controllers\Helpers\BdoOportunidadeControllerHelper;
$status = BdoOportunidadeControllerHelper::status();
@endphp

<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
        @method('PUT')
        <input type="hidden" name="idempresa" value="{{ $resultado->empresa->idempresa }}">
    @else    
        <input type="hidden" name="idempresa" value="{{ $empresa->idempresa }}">
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
    <div class="card-body">
        <div class="form-row">
        <div class="col">
            <label for="titulo">Título</label>
            <input type="text"
                name="titulo"
                class="form-control {{ $errors->has('titulo') ? 'is-invalid' : '' }}"
                placeholder="Título"
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
            <label for="empresafake">Empresa&nbsp;&nbsp;<a href="/admin/bdo/empresas/editar/{{ isset($resultado->empresa->idempresa) ? $resultado->empresa->idempresa : $empresa->idempresa }}" target="_blank"><small>Editar empresa</small></a></label>
            <input type="text"
                name="empresafake"
                class="form-control"
                placeholder="{{ isset($resultado->empresa->razaosocial) ? $resultado->empresa->razaosocial : $empresa->razaosocial }}"
                readonly />
        </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <div class="row nomargin">
                    <label for="segmento">Segmento</label>
                    <select name="segmento" class="form-control" id="segmento">
                    @foreach(segmentos() as $segmento)
                        @if(!empty(old('segmento')))
                            @if(old('segmento') === $segmento)
                                <option class="{{ $segmento }}" selected>{{ $segmento }}</option>
                            @else
                                <option class="{{ $segmento }}">{{ $segmento }}</option>
                            @endif
                        @else
                            @if(isset($resultado))
                                @if($resultado->segmento == $segmento)
                                    <option class="{{ $segmento }}" selected>{{ $segmento }}</option>
                                @else
                                    <option value="{{ $segmento }}">{{ $segmento }}</option>
                                @endif
                            @else
                                <option value="{{ $segmento }}">{{ $segmento }}</option>
                            @endif
                        @endif
                    @endforeach
                    </select>
                    @if($errors->has('segmento'))
                    <div class="invalid-feedback">
                    {{ $errors->first('segmento') }}
                    </div>
                    @endif
                </div>
                <div class="row mt-2">
                    <div class="col">
                        <label for="vagasdisponiveis">Vagas Disponíveis</label>
                        <input type="text"
                            class="form-control vagasInput {{ $errors->has('vagasdisponiveis') ? 'is-invalid' : '' }}"
                            name="vagasdisponiveis"
                            placeholder="00"
                            @if(!empty(old('vagasdisponiveis')))
                                value="{{ old('vagasdisponiveis') }}"
                            @else
                                @if(isset($resultado))
                                    value="{{ $resultado->vagasdisponiveis }}"
                                @endif
                            @endif
                            />
                        @if($errors->has('vagasdisponiveis'))
                        <div class="invalid-feedback">
                        {{ $errors->first('vagasdisponiveis') }}
                        </div>
                        @endif
                    </div>
                    <div class="col">
                        <label for="status">Status</label>
                        <select name="status" class="form-control">
                        @foreach($status as $s)
                            @if(!empty(old('status')))
                                @if(old('status') === $s)
                                    <option value="{{ $s }}" selected>{{ $s }}</option>
                                @else
                                    <option value="{{ $s }}">{{ $s }}</option>
                                @endif
                            @else
                                @if(isset($resultado))
                                    @if($resultado->status == $s)
                                    <option value="{{ $s }}" selected>{{ $s }}</option>
                                    @else
                                    <option value="{{ $s }}">{{ $s }}</option>
                                    @endif
                                @else
                                <option value="{{ $s }}">{{ $s }}</option>
                                @endif
                            @endif
                        @endforeach
                        </select>
                        @if($errors->has('status'))
                        <div class="invalid-feedback">
                        {{ $errors->first('status') }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col">
                <label for="regiaoatuacao">Região de Atuação</label>
                <select name="regiaoatuacao[]" id="regiaoAtuacaoOportunidade" class="form-control" size="4" multiple>
                @foreach($regioes as $regiao)
                    @if(isset($resultado))
                        @if($regioesEdit->contains('idregional',$regiao->idregional))
                        <option value="{{ $regiao->idregional }}" selected>{{ $regiao->regional }}</option>
                        @else
                        <option value="{{ $regiao->idregional }}">{{ $regiao->regional }}</option>
                        @endif
                    @else
                    <option value="{{ $regiao->idregional }}">{{ $regiao->regional }}</option>
                    @endif
                @endforeach
                </select>
                <small class="form-text text-muted">
                    <em>* Segure Ctrl para selecionar mais de uma região ou Shift para selecionar um grupo de regiões</em>
                </small>
                @if($errors->has('regiaoatuacao'))
                <div class="invalid-feedback">
                {{ $errors->first('regiaoatuacao') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-group mt-2">
            <label for="descricao">Descrição</label>
            <textarea name="descricao"
            class="form-control {{ $errors->has('descricao') ? 'is-invalid' : '' }}"
            id="descricao"
            rows="10">@if(!empty(old('descricao'))){{ old('descricao') }}@else @if(isset($resultado)){{ $resultado->descricao }}@endif @endif</textarea>
            @if($errors->has('descricao'))
            <div class="invalid-feedback">
                {{ $errors->first('descricao') }}
            </div>
            @endif
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/bdo" class="btn btn-default">Cancelar</a>
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