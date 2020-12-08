@php
use App\PreCadastro;
@endphp

<div class="card-body">
    <div class="row">
        <div class="col">
            @switch($resultado->status)
                @case(PreCadastro::STATUS_APROVADO)
                    <p><strong class="text-success"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;Enviado ao Gerenti em {{ formataData($resultado->updated_at) }}</strong></p>
                    <hr>
                @break
                @case(PreCadastro::STATUS_RECUSADO)
                    <p class="{{ isset($resultado->observacao) ? 'mb-0' : '' }}"><strong class="text-danger"><i class="fas fa-ban"></i>&nbsp;&nbsp;Recusado em {{ formataData($resultado->updated_at) }}</strong></p>
                    @isset($resultado->observacao)
                        <p class="light"><small class="light">{!! '—————<br><strong>Motivo:</strong> ' . $resultado->observacao !!}</small></p>
                    @endisset
                    <hr>
                @break
            @endswitch

            <h4>Solicitação de de pré-cadastro:</h4>
            <p class="mb-0">Tipo: <strong>{{ $resultado->tipo }}</strong></p>
            <p class="mb-0">Nome: <strong>{{ $resultado->nome }}</strong></p>
            <p class="mb-0">CPF: <strong>{{ $resultado->cpf }}</strong></p>
            <p class="mb-0">CNPJ: <strong>{{ $resultado->cnpj }}</strong></p>
           
            <hr>

            <h5>Anexos:</h5>
            <p class="mb-0">
                <strong>Anexo: </strong>
                <a href="{{ route('pre-cadastro.visualizar', ['id' => $resultado->id, 'nomeArquivo' => $resultado->anexo]) }}" class="btn btn-sm btn-info" target="_blank">Visualizar</a>
                <a href="{{ route('pre-cadastro.baixar', ['id' => $resultado->id, 'nomeArquivo' => $resultado->anexo]) }}" class="btn btn-sm btn-secondary" target="_blank">Baixar</a>
            </p>
 
            @if ($resultado->status === PreCadastro::STATUS_PEDENTE)
                <hr>
                <h4 class="mb-3">Ações</h4>
                <form action="{{ route('admin.representante-endereco.post') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="id" value="{{ $resultado->id }}">
                    <button type="submit" class="btn btn-primary">Aprovar</button>
                </form>
                <button class="btn btn-info" id="recusar-trigger">Recusar&nbsp;&nbsp;<i class="fas fa-chevron-down"></i></button>
                <div class="w-100" id="recusar-form">
                    <form action="{{ route('admin.representante-endereco-recusado.post') }}" method="POST" class="mt-2">
                        @csrf
                        <input type="hidden" name="id" value="{{ $resultado->id }}">
                        <textarea name="observacao" rows="3" placeholder="Insira aqui o motivo pelo qual a solicitação foi recusada..." class="form-control"></textarea>
                        <button type="submit" class="btn btn-danger mt-2">Recusar</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>