@extends('admin.layout.app')

@section('content')

<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Editar Usuário</h1>
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
              Preencha as informações para editar o usuário
            </div>
          </div>
          <form role="form" method="POST" autocomplete="false">
            @csrf
            {{ method_field('PUT') }}
            <div class="card-body">
              <div class="form-row mb-2">
                <div class="col">
                  <label for="nome">Nome do Usuário</label>
                  <input type="text" class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}" placeholder="Nome" name="nome" value="{{ $usuario->nome }}" />
                  @if($errors->has('nome'))
                  <div class="invalid-feedback">
                    {{ $errors->first('nome') }}
                  </div>
                  @endif
                </div>
                <div class="col">
                  <label for="perfil">Perfil</label>
                  <select name="perfil" class="form-control">
                    @foreach($perfis as $perfil)
                      @if($usuario->perfil[0]->idperfil == $perfil->idperfil)
                      <option value="{{ $perfil->idperfil }}" selected>{{ $perfil->nome }}</option>
                      @else
                      <option value="{{ $perfil->idperfil }}">{{ $perfil->nome }}</option>
                      @endif
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="form-group">
                  <label for="email">Email</label>
                  <input type="text" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="Email" name="email" value="{{ $usuario->email }}" />
                  @if($errors->has('email'))
                  <div class="invalid-feedback">
                    {{ $errors->first('email') }}
                  </div>
                  @endif
              </div>
            </div>
            <div class="card-footer float-right">
              <a href="/admin/usuarios" class="btn btn-default">Cancelar</a>
              <button type="submit" class="btn btn-primary ml-1">Editar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection