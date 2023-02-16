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
  <div class="login-box-body">
    <p class="login-box-msg">Digite o email e confirme a nova senha.</p>
    <form method="POST" action="{{ route('password.update') }}" autocomplete="off">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <div class="input-group mb-2">
        <input id="login" type="email" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" placeholder="E-mail" required autofocus>
        <div class="input-group-append">
          <span class="fa fa-envelope input-group-text"></span>
        </div>
        @if ($errors->has('email'))
          <span class="invalid-feedback" role="alert">
            <strong>{{ $errors->first('email') }}</strong>
          </span>
        @endif
      </div>
      <div class="input-group mb-2">
        <input id="password" type="password" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" placeholder="Nova senha" required>
        <div class="input-group-append">
          <span class="fa fa-lock input-group-text"></span>
        </div>
        @if ($errors->has('password'))
          <span class="invalid-feedback" role="alert">
            <strong>{{ $errors->first('password') }}</strong>
          </span>
        @endif
      </div>
      <div class="input-group mb-2">
        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Confirmar senha" required>
        <div class="input-group-append">
          <span class="fa fa-lock input-group-text"></span>
        </div>
      </div>

      <div class="mt-2 mb-2">
        @component('components.verifica_forca_senha')
        @endcomponent
      </div>

      <button type="submit" class="btn btn-primary">
        Resetar Senha
      </button>
    </form>
  </div>
</div>
<!-- /.login-box -->

<script type="text/javascript" src="{{ asset('/js/zxcvbn.js?'.time()) }}"></script>
<script type="text/javascript" src="{{ asset('/js/security.js?'.time()) }}"></script>

</body>
</html>