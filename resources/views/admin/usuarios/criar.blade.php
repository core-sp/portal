@extends('admin.layout.app')

@section('content')

<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-12">
        <h1>Criar Usuário</h1>
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
              Preencha as informações para criar um novo usuário
            </div>
          </div>
          <form role="form" method="POST" autocomplete="false">
            @csrf
            <div class="card-body">
              <div class="form-row mb-2">
                <div class="col">
                  <label for="nome">Nome do Usuário</label>
                  <input type="text" class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}" placeholder="Nome" name="nome" />
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
                    <option value="{{ $perfil->idperfil }}">{{ $perfil->nome }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label for="email">Email</label>
                <input type="text" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="Email" name="email" />
                @if($errors->has('email'))
                <div class="invalid-feedback">
                  {{ $errors->first('email') }}
                </div>
                @endif
              </div>
              <div class="form-row">
                <div class="col">
                  <label for="password">Senha</label>
                  <input id="password" type="password" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" autocomplete="new-password" />
                  @if ($errors->has('password'))
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('password') }}</strong>
                  </span>
                  @endif
                </div>
                <div class="col">
                  <label for="password-confirm">Confirme a senha</label>
                  <input id="password-confirm" type="password" class="form-control {{ $errors->has('password-confirm') ? ' is-invalid' : '' }}" name="password_confirmation" />
                  @if ($errors->has('password-confirm'))
                  <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('password-confirm') }}</strong>
                  </span>
                  @endif
                </div>
              </div>
            </div>
            <div class="card-footer float-right">
              <a href="/admin/usuarios" class="btn btn-default">Cancelar</a>
              <button type="submit" class="btn btn-primary ml-1">Criar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection