<div class="card-body">
    <div class="row">
        <div class="col">
            @switch($resultado->status)
                @case($resultado::STATUS_ACEITO)
                    <p>
                        <strong class="text-success">
                            <i class="fas fa-check-circle"></i>&nbsp;&nbsp;Solicitação aceita pelo(a) atendente {{ $resultado->usuario->nome }} em {{ formataData($resultado->updated_at) }}
                        </strong>
                    </p>
                    <hr>
                @break
                @case($resultado::STATUS_RECUSADO)
                    <p class="{{ isset($resultado->justificativa) ? 'mb-0' : '' }}">
                        <strong class="text-danger">
                            <i class="fas fa-ban"></i>
                            &nbsp;&nbsp;Solicitação reprovada pelo(a) atendente {{ $resultado->usuario->nome }} em {{ formataData($resultado->updated_at) }}
                        </strong>
                    </p>
                    @if(isset($resultado->justificativa))
                        <p class="light"><small class="light">—————<br><strong>Motivo:&nbsp;&nbsp;</strong>{{ $resultado->justificativa }}</small></p>
                    @endif
                    <hr>
                @break
            @endswitch
            <h4>Representante:</h4>
            <p class="mb-0">Nome: <strong>{{ $resultado->representante->nome }}</strong></p>
            <p class="mb-0">Email: <strong>{{ $resultado->representante->email }}</strong></p>
            <p class="mb-0">Registro: <strong>{{ $resultado->representante->registro_core }}</strong></p>
            <p class="mb-0">CPF/CNPJ: <strong>{{ $resultado->representante->cpf_cnpj }}</strong></p>
            <p>Regional (desta solicitação): <strong>{{ $resultado->regional->regional }}</strong></p>
            <hr>
            <h4>Solicitação de envio de cédula:</h4>
            @if($resultado->representante->tipoPessoa() == 'PJ')
            <p class="mb-0">Nome: <strong>{{ $resultado->nome }}</strong></p>
            <p class="mb-0">CPF: <strong>{{ formataCpfCnpj($resultado->cpf) }}</strong></p>
            @endif
            <p class="mb-0">RG: <strong>{{ mascaraRG($resultado->rg) }}</strong></p>
            <p class="mb-0">CEP: <strong>{{ $resultado->cep }}</strong></p>
            <p class="mb-0">Bairro: <strong>{{ $resultado->bairro }}</strong></p>
            <p class="mb-0">Logradouro: <strong>{{ $resultado->logradouro }}</strong></p>
            <p class="mb-0">Número: <strong>{{ $resultado->numero }}</strong></p>
            <p class="mb-0">Complemento: <strong>{{ isset($resultado->complemento) ? $resultado->complemento : '---' }}</strong></p>
            <p class="mb-0">Estado: <strong>{{ $resultado->estado }}</strong></p>
            <p class="mb-0">Município: <strong>{{ $resultado->municipio }}</strong></p>
            @if(isset($resultado->tipo))
            <p>Tipo da cédula: <strong>{{ $resultado->tipo }}</strong></p>
            @endif

            <a href="{{ route('solicita-cedula.index') }}" class="btn btn-outline-secondary mt-2">
                Voltar
            </a>
            @if ($resultado->status === $resultado::STATUS_EM_ANDAMENTO)
                <hr>
                <h4 class="mb-3">Ações</h4>
                <form action="{{ route('solicita-cedula.update', $resultado->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="{{ $resultado::STATUS_ACEITO }}">
                    <button 
                        type="submit" 
                        class="btn btn-primary {{ $errors->has('status') ? 'is-invalid' : '' }}"
                    >
                        Aceito
                    </button>
                    @if($errors->has('status'))
                        <div class="invalid-feedback">
                        {{ $errors->first('status') }}
                        </div>
                    @endif
                </form>
                <button class="btn btn-info" id="recusar-trigger">Recusar&nbsp;&nbsp;<i class="fas fa-chevron-down"></i></button>
                <div class="w-100" id="recusar-form">
                    <form action="{{ route('solicita-cedula.update', $resultado->id) }}" method="POST" class="mt-2 cedula_recusada">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="{{ $resultado::STATUS_RECUSADO }}">
                        <label for="justificativaCedula">Insira a justificativa:</label>
                        <textarea 
                            name="justificativa" 
                            rows="3" 
                            class="form-control {{ $errors->has('justificativa') || $errors->has('status') ? 'is-invalid' : '' }}"
                            id="justificativaCedula"
                            maxlength="600"
                        >
                            {{ old('justificativa') }}
                        </textarea>
                        @if($errors->has('justificativa') || $errors->has('status'))
                            <div class="invalid-feedback">
                            {{ $errors->has('justificativa') ? $errors->first('justificativa') : $errors->first('status') }}
                            </div>
                        @endif
                        <button type="submit" class="btn btn-danger mt-2">
                            Recusar
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

<script type="module" src="{{ asset('/js/interno/modulos/cedula.js?'.hashScriptJs()) }}" id="modulo-cedula" class="modulo-editar"></script>