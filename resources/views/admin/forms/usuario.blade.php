<form role="form" method="POST" autocomplete="false">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    <div class="card-body">
        <div class="form-row mb-2">
        <div class="col">
            <label for="nome">Nome Completo</label>
            <input type="text"
                class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                placeholder="Nome"
                name="nome"
                @if(!empty(old('nome')))
                    value="{{ old('nome') }}"
                @else
                    @if(isset($resultado))
                        value="{{ $resultado->nome }}"
                    @endif
                @endif
                />
            @if($errors->has('nome'))
            <div class="invalid-feedback">
            {{ $errors->first('nome') }}
            </div>
            @endif
        </div>
        <div class="col">
            <label for="nome">Nome de usuário</label>
            <input type="text"
                class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                placeholder="Nome"
                name="username"
                @if(!empty(old('username')))
                    value="{{ old('username') }}"
                @else
                    @if(isset($resultado))
                        value="{{ $resultado->username }}"
                    @endif
                @endif
                />
            @if($errors->has('username'))
            <div class="invalid-feedback">
            {{ $errors->first('username') }}
            </div>
            @endif
        </div>
        <div class="col">
            <label for="idperfil">Perfil</label>
            <select name="idperfil" class="form-control">
            @foreach($perfis as $perfil)
                @if(!empty(old('idperfil')))
                    @if(old('idperfil') == $perfil->idperfil)
                        <option value="{{ $perfil->idperfil }}" selected>{{ $perfil->nome }}</option>
                    @else
                        <option value="{{ $perfil->idperfil }}">{{ $perfil->nome }}</option>
                    @endif
                @else
                    @if(isset($resultado))
                        @if($resultado->perfil == $perfil)
                            <option value="{{ $perfil->idperfil }}" selected>{{ $perfil->nome }}</option>
                        @else
                            <option value="{{ $perfil->idperfil }}">{{ $perfil->nome }}</option>
                        @endif
                    @else
                        <option value="{{ $perfil->idperfil }}">{{ $perfil->nome }}</option>
                    @endif
                @endif
            @endforeach
            </select>
        </div>
        </div>
        <div class="form-row mt-2">
        <div class="col">
        <label for="email">Email</label>
            <input type="text"
            class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
            placeholder="Email"
            name="email"
            @if(!empty(old('email')))
                value="{{ old('email') }}"
            @else
                @if(isset($resultado))
                    value="{{ $resultado->email }}"
                @endif
            @endif
            />
            @if($errors->has('email'))
            <div class="invalid-feedback">
            {{ $errors->first('email') }}
            </div>
            @endif
        </div>
        <div class="col">
            <label for="idregional">Regional</label>
            <select name="idregional" class="form-control">
            @foreach($regionais as $regional)
                @if(!empty(old('idregional')))
                    @if(old('idregional') == $regional->idregional)
                        <option value="{{ $regional->idregional }}" selected>{{ $regional->regional }}</option>
                    @else
                        <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                    @endif
                @else
                    @if(isset($resultado))
                        @if($resultado->regional->idregional == $regional->idregional)
                            <option value="{{ $regional->idregional }}" selected>{{ $regional->regional }}</option>
                        @else
                            <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                        @endif
                    @else
                        <option value="{{ $regional->idregional }}">{{ $regional->regional }}</option>
                    @endif
                @endif
            @endforeach
            </select>
        </div>
        </div>
        @if(!isset($resultado))
        <div class="form-row mt-2">
            <div class="col">
                <label for="password">Senha</label>
                <input id="password"
                    type="password"
                    class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}"
                    name="password"
                    autocomplete="new-password"
                    />
                @if($errors->has('password'))
                <div class="invalid-feedback">
                {{ $errors->first('password') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="password-confirm">Confirme a senha</label>
                <input id="password-confirm"
                    type="password"
                    class="form-control {{ $errors->has('password-confirm') ? ' is-invalid' : '' }}"
                    name="password_confirmation"
                    />
                @if($errors->has('password-confirm'))
                <div class="invalid-feedback">
                {{ $errors->first('password-confirm') }}
                </div>
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