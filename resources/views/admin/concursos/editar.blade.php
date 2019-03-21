@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helpers\ConcursoHelper;
use \App\Http\Controllers\Helper;
$modalidades = ConcursoHelper::modalidades();
$situacoes = ConcursoHelper::situacoes();
@endphp

<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Editar Concurso</h1>
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
              Preencha as informações para editar o concurso
            </div>
          </div>
          <form role="form" method="POST">
            @csrf
            {{ method_field('PUT') }}
            <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
            <div class="card-body">
              <div class="form-row">
                <div class="col">
                  <label for="modalidade">Modalidade</label>
                  <select name="modalidade" class="form-control">
                    @foreach($modalidades as $modalidade)
                      @if($concurso->modalidade === $modalidade)
                      <option value="{{ $modalidade }}" selected>{{ $modalidade }}</option>
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
                <div class="col">
                  <label for="nrprocesso">Nº do Processo</label>
                  <input type="text" class="form-control {{ $errors->has('nrprocesso') ? 'is-invalid' : '' }}" placeholder="Número" name="nrprocesso" value="{{ $concurso->nrprocesso }}" maxlength="19" />
                  @if($errors->has('nrprocesso'))
                  <div class="invalid-feedback">
                    {{ $errors->first('nrprocesso') }}
                  </div>
                  @endif
                </div>
              </div>
              <div class="form-row mt-2">
                <div class="col">
                  <label for="situacao">Situação</label>
                  <select name="situacao" class="form-control">
                    @foreach($situacoes as $situacao)
                      @if($situacao == $concurso->situacao)
                      <option value="{{ $situacao }}" selected>{{ $situacao }}</option>
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
                <div class="col">
                  <label for="datarealizacao">Data de Realização</label>
                  <input type="datetime-local" class="form-control" name="datarealizacao" value="{{ ConcursoHelper::getData($concurso->datarealizacao) }}">
                </div>
              </div>
              <div class="form-group mt-2">
                <label for="objeto">Objeto da Licitação</label>
                <textarea name="objeto" class="form-control {{ $errors->has('objeto') ? 'is-invalid' : '' }} my-editor" id="conteudo" rows="10">
                  {!! $concurso->objeto !!}
                </textarea>
                @if($errors->has('objeto'))
                <div class="invalid-feedback">
                  {{ $errors->first('objeto') }}
                </div>
                @endif
              </div>
            </div>
            <div class="card-footer float-right">
              <a href="/admin/concursos" class="btn btn-default">Cancelar</a>
              <button type="submit" class="btn btn-primary ml-1">Editar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="row mt-2 mb-4">
      <div class="col">
        <div class="callout callout-info">
          <strong>Ultima alteração:</strong> {{ $concurso->user->nome }}, às {{ Helper::organizaData($concurso->updated_at) }}
        </div>
      </div>
    </div>
  </div>
</section>

@endsection