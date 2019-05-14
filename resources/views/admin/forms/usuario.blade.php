<form role="form" method="POST" autocomplete="false">
    @csrf
    @if(isset($resultado))
    {{ method_field('PUT') }}
    @endif
    <div class="card-body">
        <div class="form-row mb-2">
        <div class="col">
            <label for="nome">Nome do Usuário</label>
            <input type="text"
                class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                placeholder="Nome"
                name="nome"
                @if(isset($resultado))
                value="{{ $resultado->nome }}"
                @endif
                />
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
                @if(isset($resultado))
                    @if($resultado->perfil == $perfil)
                    <option value="{{ $perfil->idperfil }}" selected>{{ $perfil->nome }}</option>
                    @else
                    <option value="{{ $perfil->idperfil }}">{{ $perfil->nome }}</option>
                    @endif
                @else
                <option value="{{ $perfil->idperfil }}">{{ $perfil->nome }}</option>
                @endif
            @endforeach
            </select>
        </div>
        <div class="col">
            <label for="idregional">Regional</label>
            <select name="idregional" class="form-control">
            @foreach($regionais as $regional)
                @if(isset($resultado))
                    @if($resultado->regional->idregional == $regional->idregional)
                    <option value="{{ $regional->idregional }}" selected>{{ $regional->regional }}</option>
                    @else
                    <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                    @endif
                @else
                <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                @endif
            @endforeach
            </select>
        </div>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="text"
            class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
            placeholder="Email"
            name="email"
            @if(isset($resultado))
            value="{{ $resultado->email }}"
            @endif
            />
            @if($errors->has('email'))
            <div class="invalid-feedback">
            {{ $errors->first('email') }}
            </div>
            @endif
        </div>
        @if(!isset($resultado))
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
        @endif
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="/admin/usuarios" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
            @if(isset($resultado))
                Salvar
            @else
                Publicar
            @endif
            </button>
        </div>
    </div>
</form>