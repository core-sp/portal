<form role="form" method="POST">
    @csrf
    @if(\Route::is('perfis.permissoes.edit'))
        @method('PUT')
    @endif
    <div class="card-body">

    @if(!\Route::is('perfis.permissoes.edit'))
        <div class="form-group">
            <label for="nome">Nome</label>
            <input type="text"
                class="form-control {{ $errors->has('nome') ? 'is-invalid' : '' }}"
                placeholder="Nome"
                name="nome"
                value="{{ old('nome') }}"
            />
            @if($errors->has('nome'))
            <div class="invalid-feedback">
                {{ $errors->first('nome') }}
            </div>
            @endif
        </div>

    @else

        @if(isset($permissoes) && $permissoes->isNotEmpty())

            @if($errors->has('permissoes') || $errors->has('permissoes.*'))
            <div class="row">
                <div class="col">
                    <div class="alert alert-danger">
                        {{ $errors->has('permissoes') ? $errors->first('permissoes') : $errors->first('permissoes.*') }}
                    </div>
                </div>
            </div>
            @endif
            
        <table class="table table-bordered perfilEdit table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Visualizar</th>
                    <th>Criar</th>
                    <th>Editar</th>
                    <th>Show</th>
                    <th>Apagar</th>
                </tr>
            </thead>
            <tbody>

            @foreach($permissoes as $controller => $permissao)
                @php
                    $metodos = $permissao->pluck('metodo', 'idpermissao');
                @endphp
                <tr>
                    <!-- Nome Grupo Permissão -->
                    <td>{{ $permissao->get(0)->nome }}</td>

                    <!-- Ações Permissão -->
                    @foreach(['index', 'create', 'edit', 'show', 'destroy'] as $acao)
                    <td>
                        <input 
                            type="checkbox" 
                            class="form-check-input" 
                            {!! $metodos->contains($acao) ? 'name="permissoes[]" value="' . $metodos->search($acao) . '" ' : 'disabled' !!}
                            {{ $perfil->temPermissao($controller, $acao) ? 'checked' : '' }} 
                        />
                    </td>
                    @endforeach

                </tr>    
            @endforeach

            </tbody>
        </table>

        @else

        <p>Sem permissões no momento</p>

        @endif

    @endif

    </div>
    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('perfis.lista') }}" class="btn btn-default">Cancelar</a>
            <button type="submit" class="btn btn-primary ml-1">Salvar</button>
        </div>
    </div>
</form>