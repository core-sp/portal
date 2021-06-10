<div class="space">
    <div class="card card-info card-outline mb-0">
        <div class="card-header secondary-bg">
            <div class="d-flex">
                <h3 class="card-title flex-one">
                    <i class="fas fa-user"></i>
                        &nbsp;{{ $nome }}
                </h3>
                <h5 class="situacao blink_me mb-0 pl-3"><strong>{{ $situacao }}</strong></h5>
            </div>
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
                        <a class="nav-link" id="vert-tabs-certidoes-tab" data-toggle="pill" href="#vert-tabs-certidoes" role="tab" aria-controls="vert-tabs-certidoes" aria-selected="false">
                            Certidões
                        </a>
                        <a class="nav-link" id="vert-tabs-refis-tab" data-toggle="pill" href="#vert-tabs-refis" role="tab" aria-controls="vert-tabs-refis" aria-selected="false">
                            Simulador Refis
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

                        <div class="tab-pane fade" id="vert-tabs-certidoes" role="tabpanel" aria-labelledby="vert-tabs-certidoes-tab">
                            @if (isset($certidoes))
                                <h5 class="mb-2">
                                    <i class="fas fa-level-up-alt rotate-90"></i>
                                    &nbsp;&nbsp;CERTIDÕES EMITIDAS
                                </h5>
                                @if(count($certidoes))
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Número</th>
                                                <th>Data emissão</th>
                                                <th>Hora emissão</th>
                                                <th>Status</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($certidoes as $certidao)
                                            <tr>
                                                <td>{{ $certidao['numeroDocumento'] }}</td>
                                                <td>{{ $certidao['dataEmissao'] }}</td>
                                                <td>{{ $certidao['horaEmissao'] }}</td>
                                                <td>{{ $certidao['status'] }}</td>
                                                @if(trim($certidao['status']) == 'Emitido')
                                                    <td>
                                                        <div class="contato-btns">
                                                            <form action="{{ route('admin.representante.baixarCertidao') }}" method="GET" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="numero" value="{{ $certidao['numeroDocumento'] }}" />
                                                                <input type="hidden" name="assId" value="{{ $assId }}" />
                                                                <input type="submit" value="Baixar" class="baixarCertidaoBtn btn btn-sm btn-success" />
                                                            </form>
                                                        </div>
                                                    </td>
                                                @else
                                                    <td></td>
                                                @endif
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

                        <div class="tab-pane fade" id="vert-tabs-refis" role="tabpanel" aria-labelledby="vert-tabs-refis-tab">
                        @if ($valoresRefis['total'] !== 0)
                            <p>Abaixo valores com descontos disponíveis com seus respectivos parcelamentos. Atenção, parcela deve ter o valor mínimo de R$ 100,00 (cem reais).</p>

                            <h5 class="mt-3 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;ANUIDADES COBRADAS</h5>
                            <table class="table table-bordered bg-white mb-0">
                                <thead>
                                    <tr>
                                        <th>Descrição</th>
                                        <th class="quinze">Valor Original</th>
                                        <th class="quinze">Juros</th>
                                        <th class="quinze">Multa</th>
                                        <th class="quinze">IPCA</th>
                                        <th class="quinze">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($valoresRefis['anuidadesRefis'] as $anuidade)
                                        <tr>
                                            <td class="ls-meio-neg">{{ $anuidade['descricao'] }}</td>
                                            <td class="ls-meio-neg" value="{{ $anuidade['valor'] }}">R$ {{ toReais($anuidade['valor']) }}</td>
                                            <td class="ls-meio-neg" value="{{ $anuidade['juros'] }}">R$ {{ toReais($anuidade['juros']) }}</td>
                                            <td class="ls-meio-neg" value="{{ $anuidade['multa'] }}">R$ {{ toReais($anuidade['multa']) }}</td>
                                            <td class="ls-meio-neg" value="{{ $anuidade['correcao'] }}">R$ {{ toReais($anuidade['correcao']) }}</td>
                                            <td class="ls-meio-neg" value="{{ $anuidade['total'] }}">R$ {{ toReais($anuidade['total']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <h5 class="mt-0 mb-2"><i class="fas fa-level-up-alt rotate-90"></i>&nbsp;&nbsp;VALORES</h5>
                            <table class="table table-bordered bg-white mb-0">
                                <tbody>
                                    <tr>
                                        <td class="ls-meio-neg">Total s/ desconto</td>
                                        <td class="ls-meio-neg"><div id="total" value="{{ $valoresRefis['total'] }}">R$ {{ toReais($valoresRefis['total']) }}</div></td>
                                    </tr>
                                    <tr>
                                        @if($valoresRefis['nParcelas90'][0] !== 0)
                                            <td class="ls-meio-neg">Total c/ 90% de desconto sobre juros e multas<p class="text-left">
                                            @if(count($valoresRefis['nParcelas90']) === 1)
                                                <small>* pagamento à vista, no boleto ou no cartão de crédito</small>
                                            @else
                                                <small>* pagamento à vista, no boleto ou em até {{ end($valoresRefis['nParcelas90']) }} parcelas no cartão de crédito</small>
                                            @endif
                                            </p></td>
                                            <td class="ls-meio-neg"><div id="total90" value="{{ $valoresRefis['total90'] }}">R$ {{ toReais($valoresRefis['total90']) }}<p class="text-left verde"><small><strong>Desconto: R$ {{ toReais($valoresRefis['total'] - $valoresRefis['total90']) }}</strong></small></p></div></td>
                                            <td class="ls-meio-neg">
                                                <select id="90" class="form-control nParcela">
                                                    @foreach($valoresRefis['nParcelas90'] as $n)
                                                        <option value="{{ $n }}">{{ $n }}x</option>
                                                    @endforeach
                                            </td>
                                            <td id="parcelamento90" class="ls-meio-neg">R$ {{ toReais($valoresRefis['total90']/$valoresRefis['nParcelas90'][0]) }}</td>
                                        @else
                                            <td class="ls-meio-neg">Total c/ 90% de desconto sobre juros e multas<p class="text-left vermelho"><small>* não disponível devido ao valor mínimo de parcela</small></p></td>
                                            <td class="ls-meio-neg"><div id="total90" value="0">-</div></td>
                                            <td class="ls-meio-neg">-</td>
                                            <td class="ls-meio-neg">-</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        @if($valoresRefis['nParcelas80'][0] !== 0)
                                            <td class="ls-meio-neg">Total c/ 80% de desconto sobre juros e multas<p class="text-left">
                                            @if(count($valoresRefis['nParcelas80']) === 1)
                                                <small>* pagamento em {{ $valoresRefis['nParcelas80'][0] }} parcelas no boleto</small>
                                            @else
                                                <small>* pagamento de {{ $valoresRefis['nParcelas80'][0] }} a {{ end($valoresRefis['nParcelas80']) }} parcelas no boleto</small>
                                            @endif
                                            </p></td>
                                            <td class="ls-meio-neg"><div id="total80" value="{{ $valoresRefis['total80'] }}">R$ {{ toReais($valoresRefis['total80']) }}<p class="text-left verde"><small><strong>Desconto: R$ {{ toReais($valoresRefis['total'] - $valoresRefis['total80']) }}</strong></small></p></div></td>
                                            <td class="ls-meio-neg">
                                                <select id="80" class="form-control nParcela">
                                                    @foreach($valoresRefis['nParcelas80'] as $n)
                                                        <option value="{{ $n }}">{{ $n }}x</option>
                                                    @endforeach
                                            </td>
                                            <td id="parcelamento80" class="ls-meio-neg">R$ {{ toReais($valoresRefis['total80']/$valoresRefis['nParcelas80'][0]) }}</td>
                                        @else
                                            <td class="ls-meio-neg">Total c/ 80% de desconto sobre juros e multas<p class="text-left vermelho"><small>* não disponível devido ao valor mínimo de parcela</small></p></td>
                                            <td class="ls-meio-neg"><div id="total80" value="0">-</div></td>
                                            <td class="ls-meio-neg">-</td>
                                            <td class="ls-meio-neg">-</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        @if($valoresRefis['nParcelas60'][0] !== 0)
                                            <td class="ls-meio-neg">Total c/ 60% de desconto sobre juros e multas<p class="text-left">
                                            @if(count($valoresRefis['nParcelas60']) === 1)
                                                <small>* pagamento em {{ $valoresRefis['nParcelas60'][0] }} parcelas no boleto</small>
                                            @else
                                                <small>* pagamento de {{ $valoresRefis['nParcelas60'][0] }} a {{ end($valoresRefis['nParcelas60']) }} parcelas no boleto</small>
                                            @endif
                                            </p></td>
                                            <td class="ls-meio-neg"><div id="total60" value="{{ $valoresRefis['total60'] }}">R$ {{ toReais($valoresRefis['total60']) }}<p class="text-left verde"><small><strong>Desconto: R$ {{ toReais($valoresRefis['total'] - $valoresRefis['total60']) }}</strong></small></p></div></td>
                                            <td class="ls-meio-neg">
                                                <select id="60" class="form-control nParcela">
                                                    @foreach($valoresRefis['nParcelas60'] as $n)
                                                        <option value="{{ $n }}">{{ $n }}x</option>
                                                    @endforeach
                                            </td>
                                            <td id="parcelamento60" class="ls-meio-neg">R$ {{ toReais($valoresRefis['total60']/$valoresRefis['nParcelas60'][0]) }}</td>
                                        @else
                                        <td class="ls-meio-neg">Total c/ 60% de desconto sobre juros e multas<p class="text-left vermelho"><small>* não disponível devido ao valor mínimo de parcela</small></p></td>
                                            <td class="ls-meio-neg"><div id="total60" value="0">-</div></td>
                                            <td class="ls-meio-neg">-</td>
                                            <td class="ls-meio-neg">-</td>
                                        @endif
                                    </tr>
                                </tbody>
                            </table>

                        @else
                        <p>Não é possível simular valores Refis para quitar anuidades em aberto devido a situação do representante comercial.</p>
                        @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>