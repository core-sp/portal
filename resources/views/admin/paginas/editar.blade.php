@extends('admin.layout.app')

@section('content')

@php
use App\Http\Controllers\Helper;
@endphp

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Editar Página</h1>
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
              Preencha as informações para editar a página
            </div>
          </div>
          <form role="form" method="POST">
            @csrf
            {{ method_field('PUT') }}
            <input type="hidden" name="idusuario" value="{{ Auth::id() }}" />
            <div class="card-body">
              <div class="form-group">
                <label for="titulo">Título da página</label>
                <input type="text" class="form-control {{ $errors->has('titulo') ? 'is-invalid' : '' }}" placeholder="Título" name="titulo" value="{{ $pagina->titulo }}" />
                @if($errors->has('titulo'))
                <div class="invalid-feedback">
                  {{ $errors->first('titulo') }}
                </div>
                @endif
              </div>
              <div class="form-row">
                <div class="col">
                  <label for="lfm">Imagem principal</label>
                  <div class="input-group">
                    <span class="input-group-btn">
                      <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-default">
                        <i class="fas fa-picture-o"></i> Alterar/Inserir imagem
                      </a>
                    </span>
                    <input id="thumbnail" class="form-control" type="text" name="img" value="{{ $pagina->img }}" />
                  </div>
                </div>
                <div class="col">
                  <label for="categoria">Categoria</label>
                  <select name="categoria" class="form-control">
                    <option value="">Sem Categoria</option>
                    @foreach($categorias as $categoria)
                      @if ($categoria->idpaginacategoria == $pagina->idcategoria)
                        <option value="{{ $categoria->idpaginacategoria }}" selected>{{ $categoria->nome }}</option>
                      @else
                        <option value="{{ $categoria->idpaginacategoria }}" >{{ $categoria->nome }}</option>
                      @endif
                    @endforeach
                  </select>
                  <a href="/admin/paginas/categorias/criar" class="float-right"><small>Criar nova categoria</small></a>
                </div>
              </div>
              <div class="form-group">
                <label for="conteudopage">Conteúdo da página</label>
                <textarea name="conteudo" class="form-control my-editor {{ $errors->has('conteudo') ? 'is-invalid' : '' }}" id="conteudopage" rows="10">
                  {!! $pagina->conteudo !!}
                </textarea>
                @if($errors->has('conteudo'))
                <div class="invalid-feedback">
                  {{ $errors->first('conteudo') }}
                </div>
                @endif
              </div>
            </div>
            <div class="card-footer float-right">
              <a href="/admin/paginas" class="btn btn-default">Cancelar</a>
              <button type="submit" class="btn btn-primary ml-1">Editar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="row mt-2 mb-4">
      <div class="col">
        @if($pagina->updated_at == $pagina->created_at)
        <div class="callout callout-info">
          <strong>Criado por:</strong> {{ $pagina->user->nome }}, às {{ Helper::organizaData($pagina->created_at) }}
        </div>
        @else
        <div class="callout callout-info">
          <strong>Ultima alteração:</strong> {{ $pagina->user->nome }}, às {{ Helper::organizaData($pagina->updated_at) }}
        </div>
        @endif
      </div>
    </div>
  </div>
</section>

<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
<script type="text/javascript" src="{{ asset('js/tinymce.js') }}"></script>

@endsection