<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>CORE-SP | Log in</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <a href="#"><b>CORE-</b>SP</a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Insira as credenciais para fazer login</p>

      <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="input-group mb-3">
          <input id="login" type="text"
            class="form-control{{ $errors->has('username') || $errors->has('email') ? ' is-invalid' : '' }}"
            name="login" value="{{ old('username') ?: old('email') }}" placeholder="Email ou Nome de usuário" required autofocus>
          <div class="input-group-append login-icon">
              <span class="fas fa-user input-group-text" style="line-height:1.5;"></span>
          </div>
          @if ($errors->has('username') || $errors->has('email'))
            <span class="invalid-feedback">
              <strong>{{ $errors->first('username') ?: $errors->first('email') }}</strong>
            </span>
          @endif
        </div>
        <div class="input-group mb-3">         
          <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" placeholder="Senha" required>
           <div class="input-group-append login-icon">
              <span class="fa fa-lock input-group-text" style="line-height:1.5;"></span>
          </div>
          @if ($errors->has('password'))
          <span class="invalid-feedback" role="alert">
            <strong>{{ $errors->first('password') }}</strong>
          </span>
          @endif
        </div>
        <div class="row">
          <div class="col-8">
          <label>
            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            Lembrar senha
          </label>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block btn-flat">Entrar</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <p class="mb-1">
        @if (Route::has('password.request'))
        <a class="btn btn-link" href="{{ route('password.request') }}">
          {{ __('Esqueceu sua senha?') }}
        </a>
        @endif
      </p>
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

</body>
</html>
