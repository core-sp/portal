<form role="form" method="POST" autocomplete="false">
    @csrf
    @if(isset($resultado))
        @method('patch')
    @endif
    <div class="card-body">

        <!-- TODOS -->
        <h4>ID: <strong>{{ $resultado->id }}</strong> - Representante:</h4>
        <p class="mb-0">Nome: <strong>{{ $resultado->representante->nome }}</strong></p>
        <p class="mb-0">Registro: <strong>{{ $resultado->representante->registro_core }}</strong></p>
        <p class="mb-0">CNPJ: <strong>{{ $resultado->representante->cpf_cnpj }}</strong></p>
        <p class="mb-0">Telefone: <strong>{{ $resultado->telefone }}</strong></p>
        <p class="mb-0">E-mail: <strong>{{ $resultado->email }}</strong></p>
        <p class="mb-0">Endereço: <strong>{{ $resultado->endereco }}</strong></p>

        <!-- COMUNICAÇÃO -->
        @if(auth()->user()->isAdmin() || auth()->user()->isEditor())
        <p class="mb-0">Municípios: <strong>{{ implode(' | ', json_decode($resultado->regioes)->municipios) }}</strong></p>
        @endif

        <!-- ATENDIMENTO E COMUNICAÇÃO -->
        @if(!auth()->user()->isFinanceiro())
        <p class="mb-0">Regional: <strong>{{ json_decode($resultado->regioes)->seccional }}</strong></p>
        <p class="mb-0">Segmento: <strong>{{ $resultado->segmento }}</strong></p>
        @endif

        <hr>

        <!-- COMUNICAÇÃO -->
        @if(auth()->user()->isAdmin() || auth()->user()->isEditor())
        <div class="form-row mb-2">
            <div class="col">
                <label for="descricao">Descrição</label>
                <textarea 
                    rows="5" 
                    class="form-control {{ $errors->has('descricao') ? 'is-invalid' : '' }}"
                    name="descricao"
                    maxlength="700"
                    {{ auth()->user()->isAdmin() || auth()->user()->isEditor() ? '' : 'readonly' }}
                >{{ old('descricao') ? old('descricao') : $resultado->descricao }}</textarea>

                @if($errors->has('descricao'))
                <div class="invalid-feedback">
                {{ $errors->first('descricao') }}
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="card-footer">
        <div class="float-right">
            <a href="{{ route('bdorepresentantes.lista') }}" class="btn btn-default">Cancelar</a>
        </div>
    </div>
</form>