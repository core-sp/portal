<div class="card-body">
    <div class="row">
        <div class="col">
            @switch($resultado->status)
                @case('Enviado')
                    <p><strong class="text-success"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;Enviado ao Gerenti em {{ formataData($resultado->updated_at) }}</strong></p>
                    <hr>
                @break
                @case('Recusado')
                    <p class="{{ isset($resultado->observacao) ? 'mb-0' : '' }}"><strong class="text-danger"><i class="fas fa-ban"></i>&nbsp;&nbsp;Recusado em {{ formataData($resultado->updated_at) }}</strong></p>
                    @isset($resultado->observacao)
                        <p class="light"><small class="light">{!! '—————<br><strong>Motivo:</strong> ' . $resultado->observacao !!}</small></p>
                    @endisset
                    <hr>
                @break
            @endswitch
            <h4>Representante:</h4>
            <p class="mb-0">Registro: <strong>{{ $representante->registro_core }}</strong></p>
            <p>CPF/CNPJ: <strong>{{ $representante->cpf_cnpj }}</strong></p>
            <hr>
            <h4>Solicitação de novo endereço de correspondência:</h4>
            <p class="mb-0">CEP: <strong>{{ $resultado->cep }}</strong></p>
            <p class="mb-0">Bairro: <strong>{{ $resultado->bairro }}</strong></p>
            <p class="mb-0">Logradouro: <strong>{{ $resultado->logradouro }}</strong></p>
            <p class="mb-0">Número: <strong>{{ $resultado->numero }}</strong></p>
            <p class="mb-0">Complemento: <strong>{{ isset($resultado->complemento) ? $resultado->complemento : '---' }}</strong></p>
            <p class="mb-0">Estado: <strong>{{ $resultado->estado }}</strong></p>
            <p>Município: <strong>{{ $resultado->municipio }}</strong></p>
            <h5>Comprovante de residência:</h5>
            <p class="mb-0">
                @isset($resultado->crimagedois)
                    <strong>1- </strong>
                @endisset
                <a href="{{ url('imagens/representantes/enderecos') . '/' . $resultado->crimage }}" class="btn btn-sm btn-info" target="_blank">Visualizar</a>
                <a href="{{ url('imagens/representantes/enderecos') . '/' . $resultado->crimage }}" class="btn btn-sm btn-secondary" target="_blank" download>Baixar</a>
            </p>
                @isset($resultado->crimagedois)
                    <div class="mt-2">
                        <p>
                            <strong>2- </strong>
                            <a href="{{ url('imagens/representantes/enderecos') . '/' . $resultado->crimagedois }}" class="btn btn-sm btn-info" target="_blank">Visualizar</a>
                            <a href="{{ url('imagens/representantes/enderecos') . '/' . $resultado->crimagedois }}" class="btn btn-sm btn-secondary" target="_blank" download>Baixar</a>
                        </p>
                    </div>
                @endisset
            </p>
            @if ($resultado->status === 'Aguardando confirmação')
                <hr>
                <h4 class="mb-3">Ações</h4>
                <form action="{{ route('admin.representante-endereco.post') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="id" value="{{ $resultado->id }}">
                    <input type="hidden" name="ass_id" value="{{ $resultado->ass_id }}">
                    <input type="hidden" name="infos" value="{{ serialize($resultado->toArray()) }}">
                    <button type="submit" class="btn btn-primary">
                        Enviar para o Gerenti
                    </button>
                </form>
                <button class="btn btn-info" id="recusar-trigger">Recusar&nbsp;&nbsp;<i class="fas fa-chevron-down"></i></button>
                <div class="w-100" id="recusar-form">
                    <form action="{{ route('admin.representante-endereco-recusado.post') }}" method="POST" class="mt-2">
                        @csrf
                        <input type="hidden" name="id" value="{{ $resultado->id }}">
                        <textarea name="observacao" rows="3" placeholder="Insira aqui o motivo pelo qual a solicitação foi recusada..." class="form-control"></textarea>
                        <button type="submit" class="btn btn-danger mt-2">
                            Recusar
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>