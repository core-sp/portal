@php
use App\PreCadastro;
@endphp

<div class="card-body">
    <div class="row">
        <div class="col">
            @switch($resultado->status)
                @case(PreCadastro::STATUS_APROVADO)
                    <p><strong class="text-success"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;Aprovado em {{ formataData($resultado->updated_at) }}</strong></p>
                    <hr>
                @break
                @case(PreCadastro::STATUS_RECUSADO)
                    <p class="{{ isset($resultado->motivo) ? 'mb-0' : '' }}"><strong class="text-danger"><i class="fas fa-ban"></i>&nbsp;&nbsp;Recusado em {{ formataData($resultado->updated_at) }}</strong></p>
                    @isset($resultado->motivo)
                        <p class="light"><small class="light">{!! '—————<br><strong>Motivo:</strong> ' . $resultado->motivo !!}</small></p>
                    @endisset
                    <hr>
                @break
            @endswitch

            <h4>Solicitação de pré-cadastro para {{ $resultado->tipo }}</h4>
            <hr>
            
            <h5>Informações de cadastro</h5>
            <p class="mb-0">Nome completo: <strong>{{ $resultado->nome }}</strong></p>
            <p class="mb-0">CPF: <strong>{{ $resultado->cpf }}</strong></p>
            <p class="mb-0">Número {{ $resultado->tipoDocumento }}: <strong>{{ $resultado->numeroDocumento }}</strong></p>
            <p class="mb-0">Orgão Emissor: <strong>{{ $resultado->orgaoEmissorDocumento }}</strong></p>
            <p class="mb-0">Data Emissão: <strong>{{ onlyDate($resultado->dataEmissaoDocumento) }}</strong></p>
            <p class="mb-0">Data Nascimento: <strong>{{ onlyDate($resultado->dataNascimento) }}</strong></p>
            <p class="mb-0">Estado Civil: <strong>{{ $resultado->estadoCivil }}</strong></p>
            <p class="mb-0">Sexo: <strong>{{ $resultado->sexo }}</strong></p>
            <p class="mb-0">Naturalizado: <strong>{{ $resultado->naturalizado }}</strong></p>
            <p class="mb-0">Nacionalidade: <strong>{{ $resultado->nacionalidade }}</strong></p>
            <p class="mb-0">Naturalizado: <strong>{{ $resultado->naturalizado }}</strong></p>
            <p class="mb-0">Nome do Pai: <strong>{{ $resultado->nomePai }}</strong></p>
            <p class="mb-0">Nome da Mãe: <strong>{{ $resultado->nomeMae }}</strong></p>
            <hr>

            <h5>Informações de contato</h5>
            <p class="mb-0">E-mail: <strong>{{ $resultado->email }}</strong></p>
            <p class="mb-0">Celular: <strong>{{ $resultado->celular }}</strong></p>
            <p class="mb-0">Telefone Fixo: <strong>{{ $resultado->telefoneFixo }}</strong></p>
            <hr>

            <h5>Informações de endereço</h5>
            <p class="mb-0">CEP: <strong>{{ $resultado->cep }}</strong></p>
            <p class="mb-0">Logradouro: <strong>{{ $resultado->logradouro }}</strong></p>
            <p class="mb-0">Número: <strong>{{ $resultado->numero }}</strong></p>
            <p class="mb-0">Complemento: <strong>{{ $resultado->complemento }}</strong></p>
            <p class="mb-0">Bairro: <strong>{{ $resultado->cpf }}</strong></p>
            <p class="mb-0">Município: <strong>{{ $resultado->municipio }}</strong></p>
            <p class="mb-0">Estado: <strong>{{ $resultado->estado }}</strong></p>
            <hr>

            <h5>Informações sobre atividade</h5>
            <p class="mb-0">Segmento: <strong>{{ $resultado->segmento }}</strong></p>
            <hr>

            @if($resultado->status === PreCadastro::STATUS_PENDENTE)
                <h5>Anexos:</h5>

                <table class="table table-bordered bg-white mb-0">
                    <tbody>
                        @foreach($listaAnexos as $nome => $anexo)
                            <tr>
                                <td class="ls-meio-neg">
                                    {{ $nome }}
                                </td>
                                <td class="ls-meio-neg">
                                    <a href="{{ route('pre-cadastro.visualizar', ['arquivo' => $anexo]) }}" class="btn btn-sm btn-info" target="_blank">Visualizar</a>
                                    <a href="{{ route('pre-cadastro.baixar', ['arquivo' => $anexo]) }}" class="btn btn-sm btn-secondary" target="_blank">Baixar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
 
            @if($resultado->status === PreCadastro::STATUS_PENDENTE)
                <hr>
                <h4 class="mb-3">Ações</h4>
                <form action="{{ route('pre-cadastro.atualizarStatus') }}" method="POST" class="d-inline form-pre-cadastro">
                    @csrf
                    <input type="hidden" name="id" value="{{ $resultado->id }}">
                    <input type="hidden" name="status" value="{{ PreCadastro::STATUS_APROVADO}}">
                    <button type="submit" class="btn btn-primary btn-pre-cadastro">Aprovar</button>
                </form>
                <button class="btn btn-info" id="recusar-trigger">Recusar&nbsp;&nbsp;<i class="fas fa-chevron-down"></i></button>
                <div class="w-100" id="recusar-form">
                    <form action="{{ route('pre-cadastro.atualizarStatus') }}" method="POST" class="mt-2 form-pre-cadastro">
                        @csrf
                        <input type="hidden" name="id" value="{{ $resultado->id }}">
                        <input type="hidden" name="status" value="{{ PreCadastro::STATUS_RECUSADO }}">
                        <textarea name="motivo" rows="3" placeholder="Insira aqui o motivo pelo qual a solicitação foi recusada..." class="form-control"></textarea>
                        <button type="submit" class="btn btn-danger mt-2 btn-pre-cadastro">Recusar</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>