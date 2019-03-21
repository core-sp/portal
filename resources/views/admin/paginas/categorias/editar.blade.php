@extends('admin.layout.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Editar Categoria</h1>
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
              Preencha as informações para editar a categoria
            </div>
          </div>
          <form role="form" method="POST">
            @csrf
            {{ method_field('PUT') }}
            <div class="card-body">
              <div class="form-group">
                <label for="nome">Nome da Categoria</label>
                <input type="text" class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}" placeholder="Título" name="nome" value="{{ $categoria->nome }}" />
                @if($errors->has('nome'))
                <div class="invalid-feedback">
                  {{ $errors->first('nome') }}
                </div>
                @endif
              </div>
            </div>
            <div class="card-footer float-right">
              <a href="/admin/paginas/categorias" class="btn btn-default">Cancelar</a>
              <button type="submit" class="btn btn-primary ml-1">Editar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection