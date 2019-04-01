@extends('admin.layout.app')

@section('content')

@php
use \App\Http\Controllers\Helpers\LicitacaoHelper;
use \App\Http\Controllers\Helper;
$modalidades = LicitacaoHelper::modalidades();
$situacoes = LicitacaoHelper::situacoes();
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Editar Licitação</h1>
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
              Preencha as informações para editar a licitação
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
                      @if($licitacao->modalidade === $modalidade)
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
                  <label for="situacao">Situação</label>
                  <select name="situacao" class="form-control">
                    @foreach($situacoes as $situacao)
                      @if($situacao == $licitacao->situacao)
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
              </div>
              <div class="form-row mt-2">
                <div class="col">
                  <label for="titulo">Título da Licitação</label>
                  <input type="text" class="form-control {{ $errors->has('nrprocesso') ? 'is-invalid' : '' }}" placeholder="Título" name="titulo" value="{{ $licitacao->titulo }}" />
                  @if($errors->has('titulo'))
                  <div class="invalid-feedback">
                    {{ $errors->first('titulo') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="edital">Edital</label>
                  <div class="input-group">
                    <span class="input-group-btn">
                      <a id="edital" data-input="file" data-preview="holder" class="btn btn-default">
                        <i class="fas fa-file-o"></i> Inserir Edital
                      </a>
                    </span>
                    <input id="file" class="form-control" type="text" name="edital" value="{{ $licitacao->edital }}" />
                  </div>
                </div>
              </div>
              <div class="form-row mt-2">
                <div class="col">
                  <label for="nrprocesso">Nº do Processo</label>
                  <input type="text" class="form-control {{ $errors->has('nrprocesso') ? 'is-invalid' : '' }}" placeholder="Número" name="nrprocesso" value="{{ $licitacao->nrprocesso }}" />
                  @if($errors->has('nrprocesso'))
                  <div class="invalid-feedback">
                    {{ $errors->first('nrprocesso') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="nrlicitacao">Nº da Licitação</label>
                  <input type="text" class="form-control {{ $errors->has('nrlicitacao') ? 'is-invalid' : '' }}" placeholder="Número" name="nrlicitacao" value="{{ $licitacao->nrlicitacao }}" />
                  @if($errors->has('nrlicitacao'))
                  <div class="invalid-feedback">
                    {{ $errors->first('nrlicitacao') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="datarealizacao">Data de Realização</label>
                  <input type="datetime-local" class="form-control" name="datarealizacao" value="{{ LicitacaoHelper::getData($licitacao->datarealizacao) }}">
                </div>
              </div>
              <div class="form-group mt-2">
                <label for="objeto">Objeto da Licitação</label>
                <textarea name="objeto" class="form-control {{ $errors->has('objeto') ? 'is-invalid' : '' }} my-editor" id="conteudo" rows="10">
                  {!! $licitacao->objeto !!}
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
              <button type="submit" class="btn btn-primary ml-1">Editar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="row mt-2 mb-4">
      <div class="col">
        @if($licitacao->updated_at == $licitacao->created_at)
        <div class="callout callout-info">
          <strong>Criado por:</strong> {{ $licitacao->user->nome }}, às {{ Helper::organizaData($licitacao->created_at) }}
        </div>
        @else
        <div class="callout callout-info">
          <strong>Ultima alteração:</strong> {{ $licitacao->user->nome }}, às {{ Helper::organizaData($licitacao->updated_at) }}
        </div>
        @endif
      </div>
    </div>
  </div>
</section>

<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
<script type="text/javascript" src="{{ asset('js/tinymce.js') }}"></script>

@endsection