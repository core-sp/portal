<div class="card-body">
    <div class="row">
        <div class="col">
            @switch($resultado->status)
                @case('Aprovado')
                    <p><strong class="text-success"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;Enviado ao Gerenti em {{ formataData($resultado->updated_at) }}</strong></p>
                    <hr>
                @break
                @case('Reprovado')
                    <p class="{{ isset($resultado->justificativa) ? 'mb-0' : '' }}"><strong class="text-danger"><i class="fas fa-ban"></i>&nbsp;&nbsp;Reprovado em {{ formataData($resultado->updated_at) }}</strong></p>
                    @isset($resultado->justificativa)
                        <p class="light"><small class="light">{!! '—————<br><strong>Motivo:</strong> ' . $resultado->justificativa !!}</small></p>
                    @endisset
                    <hr>
                @break
            @endswitch
            <h4>Representante:</h4>
            <p class="mb-0">Nome: <strong>{{ $resultado->representante->nome }}</strong></p>
            <p class="mb-0">Email: <strong>{{ $resultado->representante->email }}</strong></p>
            <p class="mb-0">Registro: <strong>{{ $resultado->representante->registro_core }}</strong></p>
            <p>CPF/CNPJ: <strong>{{ $resultado->representante->cpf_cnpj }}</strong></p>
            <hr>
            <h4>Solicitação de envio de cédula:</h4>
            <p class="mb-0">CEP: <strong>{{ $resultado->cep }}</strong></p>
            <p class="mb-0">Bairro: <strong>{{ $resultado->bairro }}</strong></p>
            <p class="mb-0">Logradouro: <strong>{{ $resultado->logradouro }}</strong></p>
            <p class="mb-0">Número: <strong>{{ $resultado->numero }}</strong></p>
            <p class="mb-0">Complemento: <strong>{{ isset($resultado->complemento) ? $resultado->complemento : '---' }}</strong></p>
            <p class="mb-0">Estado: <strong>{{ $resultado->estado }}</strong></p>
            <p>Município: <strong>{{ $resultado->municipio }}</strong></p>
            @if ($resultado->status === 'Em andamento')
            <h5>Solicitação de cédula:</h5>
                <hr>
                <h4 class="mb-3">Ações</h4>
                <form action="{{ route('admin.representante-solicita-cedula.post') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="id" value="{{ $resultado->id }}">
                    <input type="hidden" name="ass_id" value="{{ $resultado->representante->ass_id }}">
                    <input type="hidden" name="infos" value="{{ serialize($resultado->representante->toArray()) }}">
                    <button type="submit" class="btn btn-primary">
                        Enviar para o Gerenti
                    </button>
                </form>
                <button class="btn btn-info" id="recusar-trigger">Reprovar&nbsp;&nbsp;<i class="fas fa-chevron-down"></i></button>
                <div class="w-100" id="recusar-form">
                    <form action="{{ route('admin.representante-solicita-cedula-reprovada.post') }}" method="POST" class="mt-2">
                        @csrf
                        <input type="hidden" name="id" value="{{ $resultado->id }}">
                        <textarea name="justificativa" rows="3" placeholder="Insira aqui o motivo pelo qual a solicitação foi reprovada..." class="form-control"></textarea>
                        <button type="submit" class="btn btn-danger mt-2">
                            Reprovar
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>