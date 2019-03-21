@extends('admin.layout.app')

@section('content')

<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Criar Notícia</h1>
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
              Preencha as informações para criar uma nova notícia
            </div>
          </div>
          <form role="form" method="POST">
            @csrf
            <input type="hidden" name="idusuario" value="{{ Auth::id() }}">
            <div class="card-body">
              <div class="form-row">
                <div class="col">
                  <label for="titulo">Título da notícia</label>
                  <input type="text" class="form-control {{ $errors->has('titulo') ? 'is-invalid' : '' }}" placeholder="Título" name="titulo" />
                  @if($errors->has('titulo'))
                  <div class="invalid-feedback">
                    {{ $errors->first('titulo') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="regionais">Regional</label>
                  <select name="regionais" class="form-control">
                    <option value="">Todas</option>
                    @foreach($regionais as $regional)
                    <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label for="conteudo">Conteúdo da página</label>
                <textarea name="conteudo" class="form-control {{ $errors->has('conteudo') ? 'is-invalid' : '' }} my-editor" id="conteudo" rows="10"></textarea>
                @if($errors->has('conteudo'))
                <div class="invalid-feedback">
                  {{ $errors->first('conteudo') }}
                </div>
                @endif
              </div>
            </div>
            <div class="card-footer float-right">
              <a href="/admin/noticias" class="btn btn-default">Cancelar</a>
              <button type="submit" class="btn btn-primary ml-1">Publicar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection