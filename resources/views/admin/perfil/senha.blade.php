@extends('admin.layout.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12">
      	<h1>Mudar senha</h1>
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
  	  	  	<h3 class="card-title">
	  	  	  Preencha o formulário abaixo para alterar a senha
	  	  	</h3>
  	  	  </div>
  	  	  <div class="card-body">
  	  	  	<form id="form-change-password" role="form" method="POST" novalidate class="form-horizontal" autocomplete="off">
              @csrf
              {{ method_field('PUT') }}
              <div class="form-group">
                <label for="current-password">Senha atual</label>
                <input type="password"
                  class="form-control {{ $errors->has('current-password') ? 'is-invalid' : '' }}"
                  id="current-password"
                  name="current-password"
                  placeholder="Senha atual">
                @if($errors->has('current-password'))
                <div class="invalid-feedback">
                  {{ $errors->first('current-password') }}
                </div>
                @endif
              </div>
              <div class="form-group">
                <label for="password">Nova senha</label>
                <input type="password"
                  class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                  id="password"
                  name="password"
                  placeholder="Nova senha" />
                  <small class="form-text text-muted">
                    <em>* A senha deve conter no mínimo: 6 caracteres, uma letra maiúscula, uma letra minúscula e um número</em><br />
                  </small>
                @if($errors->has('password'))
                <div class="invalid-feedback">
                  {{ $errors->first('password') }}
                </div>
                @endif
              </div>
              <div class="form-group">
                <label for="password_confirmation">Confirme a senha</label>
                <input type="password"
                  class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                  id="password_confirmation"
                  name="password_confirmation"
                  placeholder="Insira a nova senha novamente">
                @if($errors->has('password_confirmation'))
                <div class="invalid-feedback">
                  {{ $errors->first('password_confirmation') }}
                </div>
                @endif
              </div>

              <div class="mt-2">
                @component('components.verifica_forca_senha')
                @endcomponent
              </div>

              <div class="form-group text-right">
                <a href="/admin/perfil" type="cancel" class="btn btn-default">Cancelar</a>&nbsp;
                <button type="submit" class="btn btn-danger">Alterar</button>
              </div>
            </form>
  	  	  </div>
  	  	  <div class="card-footer">
  	  	  	CORE-SP
  	  	  </div>
  	  	</div>
  	  </div>
  	</div>
  </div>	
</section>

<script type="text/javascript" src="{{ asset('/js/zxcvbn.js?'.time()) }}"></script>
<script type="text/javascript" src="{{ asset('/js/security.js?'.time()) }}"></script>

@endsection