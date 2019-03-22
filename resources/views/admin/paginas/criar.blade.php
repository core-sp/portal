@extends('admin.layout.app')

@section('content')

<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Criar Página</h1>
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
              Preencha as informações para criar uma nova página
            </div>
          </div>
          <form role="form" method="POST">
            @csrf
            <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
            <div class="card-body">
              <div class="form-group">
                <label for="titulo">Título da página</label>
                <input type="text" class="form-control {{ $errors->has('titulo') ? 'is-invalid' : '' }}" placeholder="Título" name="titulo" />
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
                        <i class="fas fa-picture-o"></i> Inserir imagem
                      </a>
                    </span>
                    <input id="thumbnail" class="form-control" type="text" name="img" />
                  </div>
                </div>
                <div class="col">
                  <label for="categoria">Categoria</label>
                  <select name="categoria" class="form-control">
                    <option value="">Nenhum Categoria</option>
                    @foreach($categorias as $categoria)
                    <option value="{{ $categoria->idpaginacategoria }}">{{ $categoria->nome }}</option>
                    @endforeach
                  </select>
                  <a href="/admin/paginas/categorias/criar" class="float-right"><small>Criar nova categoria</small></a>
                </div>
              </div>
              <div class="form-group">
                <label for="conteudopage">Conteúdo da página</label>
                <textarea name="conteudo" class="form-control my-editor {{ $errors->has('conteudo') ? 'is-invalid' : '' }}" id="conteudopage" rows="10"></textarea>
                @if($errors->has('conteudo'))
                <div class="invalid-feedback">
                  {{ $errors->first('conteudo') }}
                </div>
                @endif
              </div>
            </div>
            <div class="card-footer float-right">
              <a href="/admin/paginas" class="btn btn-default">Cancelar</a>
              <button type="submit" class="btn btn-primary ml-1">Publicar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection