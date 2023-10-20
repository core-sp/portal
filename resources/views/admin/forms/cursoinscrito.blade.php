<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
        @method('PUT')
    @endif
    <div class="card-body">
        <div class="form-row">
            <div class="col">
                <label for="nome">Nome</label>
                <input type="text"
                    name="nome"
                    class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                    placeholder="Nome"
                    value="{{ isset($resultado->nome) ? $resultado->nome : old('nome') }}"
                    required
                />
                @if($errors->has('nome'))
                <div class="invalid-feedback">
                {{ $errors->first('nome') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="email">Email</label>
                <input type="text"
                    name="email"
                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    placeholder="Email"
                    value="{{ isset($resultado->email) ? $resultado->email : old('email') }}"
                    required
                />
                @if($errors->has('email'))
                <div class="invalid-feedback">
                {{ $errors->first('email') }}
                </div>
                @endif
            </div>
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="telefone">Telefone</label>
                <input type="text"
                    name="telefone"
                    class="form-control celularInput {{ $errors->has('telefone') ? 'is-invalid' : '' }}"
                    placeholder="(00) 00000-0000"
                    value="{{ isset($resultado->telefone) ? $resultado->telefone : old('telefone') }}"
                    required
                />
                @if($errors->has('telefone'))
                <div class="invalid-feedback">
                {{ $errors->first('telefone') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="cpf">CPF</label>
                <input type="text"
                    name="{{ isset($resultado->cpf) ? '' : 'cpf' }}"
                    class="form-control cpfInput {{ $errors->has('cpf') ? 'is-invalid' : '' }}"
                    placeholder="000.000.000-00"
                    value="{{ isset($resultado->cpf) ? $resultado->cpf : old('cpf') }}"
                    {{ isset($resultado->cpf) ? 'disabled' : 'required' }}
                />
                @if($errors->has('cpf'))
                <div class="invalid-feedback">
                {{ $errors->first('cpf') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="registrocore">Registro no CORE</label>
                <input type="text"
                    name="registrocore"
                    class="form-control {{ $errors->has('registrocore') ? 'is-invalid' : '' }}"
                    placeholder="Nº do registro no CORE (opcional)"
                    value="{{ isset($resultado->registrocore) ? $resultado->registrocore : old('registrocore') }}"
                />
                @if($errors->has('registrocore'))
                <div class="invalid-feedback">
                {{ $errors->first('registrocore') }}
                </div>
                @endif
            </div>
            <div class="col">
                <label for="tipo_inscrito">Tipo da incrição</label>
                <select name="tipo_inscrito" class="form-control {{ $errors->has('tipo_inscrito') ? 'is-invalid' : '' }}" required>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo }}" {{ isset($resultado) && ($resultado->tipo_inscrito == $tipo) ? 'selected' : '' }}>{{ $tipo }}</option>
                @endforeach
                </select>
                @if($errors->has('tipo_inscrito'))
                <div class="invalid-feedback">
                {{ $errors->first('tipo_inscrito') }}
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('inscritos.index', $idcurso) }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">
                {{ isset($resultado) ? 'Salvar' : 'Publicar' }}
            </button>
        </div>
    </div>
</form>