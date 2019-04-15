@php
use \App\Http\Controllers\Helper;
use \App\Http\Controllers\Helpers\BdoOportunidadeControllerHelper;
$status = BdoOportunidadeControllerHelper::status();
$segmentos = BdoOportunidadeControllerHelper::segmentos();
@endphp

<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
        {{ method_field('PUT') }}
        <input type="hidden" name="empresa" value="{{ $resultado->empresa->idempresa }}">
    @else    
        <input type="hidden" name="empresa" value="{{ $empresa->idempresa }}">
    @endif
    <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
    <div class="card-body">
        <div class="form-row">
        <div class="col">
            <label for="titulo">Título</label>
            <input type="text"
                name="titulo"
                class="form-control"
                placeholder="Título"
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
            <label for="empresafake">Empresa</label>
            <input type="text"
                name="empresafake"
                class="form-control"
                @if(isset($resultado))
                placeholder="{{ $resultado->empresa->razaosocial }}"
                @else
                placeholder="{{ $empresa->razaosocial }}"
                @endif
                readonly />
        </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <div class="row nomargin">
                    <label for="segmento">Segmento</label>
                    <select name="segmento" class="form-control" id="segmento">
                    @foreach($segmentos as $segmento)
                        @if(isset($resultado))
                            @if($resultado->segmento == $segmento)
                            <option class="{{ $segmento }}" selected>{{ $segmento }}</option>
                            @else
                            <option value="{{ $segmento }}">{{ $segmento }}</option>
                            @endif
                        @else
                        <option value="{{ $segmento }}">{{ $segmento }}</option>
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
                        <input type="number"
                            class="form-control {{ $errors->has('vagasdisponiveis') ? 'is-invalid' : '' }}"
                            name="vagasdisponiveis"
                            placeholder="00"
                            @if(isset($resultado))
                            value="{{ $resultado->vagasdisponiveis }}"
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
                            @if(isset($resultado))
                                @if($resultado->status == $s)
                                <option value="{{ $s }}" selected>{{ $s }}</option>
                                @else
                                <option value="{{ $s }}">{{ $s }}</option>
                                @endif
                            @else
                            <option value="{{ $s }}">{{ $s }}</option>
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
                <select name="regiaoatuacao[]" id="regiaoAtuacaoOportunidade" class="form-control" size="5" multiple>
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
            rows="10">@if(isset($resultado)) {{ $resultado->descricao }} @endif</textarea>
            @if($errors->has('descricao'))
            <div class="invalid-feedback">
                {{ $errors->first('descricao') }}
            </div>
            @endif
        </div>
    </div>
    <div class="card-footer float-right">
        <a href="/admin/bdo" class="btn btn-default">Cancelar</a>
        <button type="submit" class="btn btn-primary ml-1">
        @if(isset($resultado))
            Editar
        @else
            Publicar
        @endif
        </button>
    </div>
</form>