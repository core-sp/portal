<form role="form" method="POST">
    @csrf
    @if(isset($resultado))
    {{ method_field('PUT') }}
    <input type="hidden" name="idcurso" value="{{ $resultado->idcurso }}" />
    @else
    <input type="hidden" name="idcurso" value="{{ $curso->idcurso }}" />
    @endif
    <div class="card-body">
        <div class="form-row">
            <div class="col">
                <label for="nome">Nome</label>
                <input type="text"
                    name="nome"
                    class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                    placeholder="Nome"
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
                <label for="email">Email</label>
                <input type="text"
                    name="email"
                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    placeholder="Email"
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
        </div>
        <div class="form-row mt-2">
            <div class="col">
                <label for="telefone">Telefone</label>
                <input type="text"
                    name="telefone"
                    class="form-control celularInput {{ $errors->has('telefone') ? 'is-invalid' : '' }}"
                    placeholder="(00) 00000-0000"
                    @if(isset($resultado))
                    value="{{ $resultado->telefone }}" 
                    @endif
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
                    name="cpf"
                    class="form-control cpfInput {{ $errors->has('cpf') ? 'is-invalid' : '' }}"
                    placeholder="000.000.000-00"
                    @if(isset($resultado))
                    value="{{ $resultado->cpf }}" 
                    @endif
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
                    @if(isset($resultado))
                    value="{{ $resultado->registrocore }}" 
                    @endif
                    />
                @if($errors->has('registrocore'))
                <div class="invalid-feedback">
                {{ $errors->first('registrocore') }}
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="card-footer float-right">
        @if(isset($resultado))
        <a href="/admin/cursos/inscritos/{{ $resultado->idcurso }}" class="btn btn-default">Cancelar</a>
        @else
        <a href="/admin/cursos/inscritos/{{ $curso->idcurso }}" class="btn btn-default">Cancelar</a>
        @endif
        <button type="submit" class="btn btn-primary ml-1">
        @if(isset($resultado))
            Salvar
        @else
            Publicar
        @endif    
        </button>
    </div>
</form>