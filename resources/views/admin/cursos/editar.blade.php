@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helpers\CursoHelper;
use \App\Http\Controllers\Helper;
$tipos = CursoHelper::tipos();
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Editar Curso</h1>
      </div>
    </div>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12">
        <div class="card card-info">
          <div class="card-header">
            <div class="card-title">
              Preencha as informações para editar o curso
            </div>
          </div>
          <form role="form" method="POST">
            @csrf
            {{ method_field('PUT') }}
            <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
            <div class="card-body">
              <div class="form-row">
                <div class="col">
                  <label for="tipo">Tipo</label>
                  <select name="tipo" class="form-control">
                    @foreach($tipos as $tipo)
                      @if($tipo == $curso->tipo)
                      <option value="{{ $tipo }}" selected>{{ $tipo }}</option>
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
                    value="{{ $curso->tema }}"
                    maxlength="191" />
                  @if($errors->has('tema'))
                  <div class="invalid-feedback">
                    {{ $errors->first('tema') }}
                  </div>
                  @endif
                </div>
              </div>
              <div class="form-row mt-2">
                <div class="col">
                  <label for="datarealizacao">Data de Realização</label>
                  <input type="datetime-local" 
                    class="form-control" 
                    name="datarealizacao" 
                    value="{{ CursoHelper::getData($curso->datarealizacao) }}" />
                  @if($errors->has('datarealizacao'))
                  <div class="invalid-feedback">
                    {{ $errors->first('datarealizacao') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="duracao">Duração do evento</label>
                  <input type="number"
                    step="0.01"
                    class="form-control"
                    name="duracao"
                    value="{{ $curso->duracao }}"
                    min="1"
                    max="1000" />
                  @if($errors->has('duracao'))
                  <div class="invalid-feedback">
                    {{ $errors->first('duracao') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="nrvagas">Nº de vagas</label>
                  <input type="number"
                    name="nrvagas"
                    class="form-control"
                    value="{{ $curso->nrvagas }}"
                    max="10000" />
                  @if($errors->has('nrvagas'))
                  <div class="invalid-feedback">
                    {{ $errors->first('nrvagas') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="idregional">Regional</label>
                  <select name="idregional" class="form-control" id="idregional">
                    @foreach($regionais as $regional)
                      @if($regional->idregional == $curso->idregional)
                      <option value="{{ $regional->idregional }}" selected>
                        {{ $regional->regional }}
                      </option>
                      @else
                      <option value="{{ $regional->idregional }}">
                        {{ $regional->regional }}
                      </option>
                      @endif
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="form-row mt-2">
                <div class="col">
                  <label for="endereco">Endereço</label>
                  <input type="text"
                    name="endereco"
                    class="form-control"
                    value="{{ $curso->endereco }}"
                    maxlength="191" />
                  @if($errors->has('endereco'))
                  <div class="invalid-feedback">
                    {{ $errors->first('endereco') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="lfm">Imagem principal</label>
                  <div class="input-group">
                    <span class="input-group-btn">
                      <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-default">
                        <i class="fas fa-picture-o"></i> Inserir imagem
                      </a>
                    </span>
                    <input id="thumbnail" class="form-control" type="text" name="img" value="{{ $curso->img }}" />
                  </div>
                </div>
              </div>
              <div class="form-group mt-3">
                <label for="resumo">Resumo</label>
                <textarea name="resumo"
                  class="form-control {{ $errors->has('resumo') ? 'is-invalid' : '' }}"
                  id="resumo"
                  rows="3">{!! $curso->resumo !!}</textarea>
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
                  {!! $curso->descricao !!}    
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
              <button type="submit" class="btn btn-primary ml-1">Editar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="row mt-2 mb-4">
      <div class="col">
        @if($curso->updated_at == $curso->created_at)
        <div class="callout callout-info">
          <strong>Criado por:</strong> {{ $curso->user->nome }}, às {{ Helper::organizaData($curso->created_at) }}
        </div>
        @else
        <div class="callout callout-info">
          <strong>Ultima alteração:</strong> {{ $curso->user->nome }}, às {{ Helper::organizaData($curso->updated_at) }}
        </div>
        @endif
      </div>
    </div>
  </div>
</section>

<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
<script type="text/javascript" src="{{ asset('js/tinymce.js') }}"></script>

@endsection