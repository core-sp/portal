<div class="space">
    <div class="card card-info card-outline mb-0">
        <div class="card-header secondary-bg">
            <h3 class="card-title">
                <i class="fas fa-user"></i>
                    &nbsp;{{ $nome }}
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-5 col-sm-3">
                    <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="vert-tabs-home-tab" data-toggle="pill" href="#vert-tabs-home" role="tab" aria-controls="vert-tabs-home" aria-selected="true">
                            Dados Gerais
                        </a>
                        <a class="nav-link" id="vert-tabs-profile-tab" data-toggle="pill" href="#vert-tabs-profile" role="tab" aria-controls="vert-tabs-profile" aria-selected="false">
                            Contatos
                        </a>
                        <a class="nav-link" id="vert-tabs-messages-tab" data-toggle="pill" href="#vert-tabs-messages" role="tab" aria-controls="vert-tabs-messages" aria-selected="false">
                            Endereço de Correspondência
                        </a>
                        <a class="nav-link" id="vert-tabs-settings-tab" data-toggle="pill" href="#vert-tabs-settings" role="tab" aria-controls="vert-tabs-settings" aria-selected="false">
                            Situação Financeira
                        </a>
                    </div>
                </div>
                <div class="col-7 col-sm-9 setecinco">
                    <div class="tab-content" id="vert-tabs-tabContent">
                        <div class="tab-pane text-left fade show active" id="vert-tabs-home" role="tabpanel" aria-labelledby="vert-tabs-home-tab">
                            <h5 class="mb-2">
                                <i class="fas fa-level-up-alt rotate-90"></i>
                                &nbsp;&nbsp;DADOS GERAIS
                            </h5>
                            @if (isset($dados_gerais))
                                <table class="table table-sm">
                                    @foreach ($dados_gerais as $key => $dado)
                                        <tr>
                                            <td>
                                                <strong>{{ $key }}: </strong>{!! empty($dado) ? '----------' : $dado !!}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            @else
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td> Nada a mostrar aqui.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="vert-tabs-profile" role="tabpanel" aria-labelledby="vert-tabs-profile-tab">
                            <h5 class="mb-2">
                                <i class="fas fa-level-up-alt rotate-90"></i>
                                &nbsp;&nbsp;CONTATOS
                            </h5>
                            @if (isset($contatos))
                                <table class="table table-sm">
                                    @foreach ($contatos as $contato)
                                        <tr>
                                            <td>
                                                <strong>{{ gerentiTiposContatos()[$contato['CXP_TIPO']] }}:</strong> {{ $contato['CXP_VALOR'] }}
                                                <small class="light">{{ $contato['CXP_STATUS'] === 1 ? '(Ativo)' : '(Inativo)' }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            @else
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td> Nada a mostrar aqui.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="vert-tabs-messages" role="tabpanel" aria-labelledby="vert-tabs-messages-tab">
                            <h5 class="mb-2">
                                <i class="fas fa-level-up-alt rotate-90"></i>
                                &nbsp;&nbsp;ENDEREÇO CADASTRADO
                            </h5>
                            @if (isset($enderecos))
                                <table class="table table-sm">
                                    @foreach ($enderecos as $key => $item)
                                        <tr>
                                            <td>
                                            <strong>{{ $key }}:</strong> {{ !empty($item) ? $item : '-----' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            @else
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td> Nada a mostrar aqui.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="vert-tabs-settings" role="tabpanel" aria-labelledby="vert-tabs-settings-tab">
                            @if (isset($cobrancas))
                                <h5 class="mb-2">
                                    <i class="fas fa-level-up-alt rotate-90"></i>
                                    &nbsp;&nbsp;ANUIDADES
                                </h5>
                                @if(count($cobrancas['anuidades']))
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Descrição</th>
                                                <th class="quinze">Valor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($cobrancas['anuidades'] as $cobranca)
                                                <tr>
                                                    <td>
                                                        {{ $cobranca['DESCRICAO'] }} ⋅ {!! secondLine($cobranca['SITUACAO'], $cobranca['VENCIMENTOBOLETO'], $cobranca['LINK'], $cobranca['DESCRICAO'], $cobranca['BOLETO']) !!}
                                                    </td>
                                                    <td>R$ {{ toReais($cobranca['VALOR']) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <td> Nada a mostrar aqui.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
                                <h5 class="mt-4 mb-2">
                                    <i class="fas fa-level-up-alt rotate-90"></i>
                                    &nbsp;&nbsp;OUTRAS COBRANÇAS
                                </h5>
                                @if(count($cobrancas['outros']))
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Descrição</th>
                                                <th class="quinze">Valor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($cobrancas['outros'] as $cobranca)
                                                <tr>
                                                    <td>
                                                        {{ $cobranca['DESCRICAO'] }} ⋅ {!! secondLine($cobranca['SITUACAO'], $cobranca['VENCIMENTOBOLETO'], $cobranca['LINK'], $cobranca['DESCRICAO'], $cobranca['BOLETO']) !!}
                                                    </td>
                                                    <td>R$ {{ toReais($cobranca['VALOR']) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <td> Nada a mostrar aqui.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
                            @else
                                -----
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>