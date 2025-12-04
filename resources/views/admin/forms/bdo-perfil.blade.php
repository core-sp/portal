<div class="card-body">

@if($errors->any())
    <ul class="list-group mb-3">
        @foreach($errors->all() as $error)
        <li class="list-group-item list-group-item-danger"><i class="fas fa-times text-danger mr-2"></i>{{ $error }}</li>
        @endforeach
    </ul>
@endif

    @if(auth()->user()->isAdmin() || auth()->user()->isEditor())
    <p>{!! $item_publicado !!}: <i>item a ser publicado.</i></p>
    @endif

    <!-- TODOS -->
    <h4>ID: <strong>{{ $resultado->id }}</strong> - Representante:</h4>
    <p class="mb-0">
        {!! $item_publicado !!}
        Nome: <strong>{{ $resultado->representante->nome }}</strong>
    </p>
    <p class="mb-0">
        {!! $item_publicado !!}
        Registro: <strong>{{ $resultado->representante->registro_core }}</strong>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        {!! $item_publicado !!}
        CNPJ: <strong>{{ $resultado->representante->cpf_cnpj }}</strong>
    </p>
    <p class="mb-0">
        {!! $item_publicado !!}
        Telefone: <strong>{{ $resultado->telefone }}</strong>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        {!! $item_publicado !!}
        E-mail: <strong>{{ $resultado->email }}</strong>
    </p>
    <p class="mb-0">
        {!! $item_publicado !!}
        Endereço: <strong>{{ $resultado->endereco }}</strong>
    </p>

    <!-- COMUNICAÇÃO -->
    @if(auth()->user()->isAdmin() || auth()->user()->isEditor())
        <p class="mb-0">
            {!! $item_publicado !!}
            Municípios: <strong>{{ implode(' | ', json_decode($resultado->regioes)->municipios) }}</strong>
        </p>
    @endif

    @if(!auth()->user()->isFinanceiro())
        <p class="mb-0">

        @if(!$resultado->statusContemAtendimento() || $resultado->alteracoesRC->where('informacao', $campos_atend[0])->isEmpty())
            {!! $item_publicado !!}
            Regional: <strong>{{ json_decode($resultado->regioes)->seccional }}</strong>
        @endif

        @if(!$resultado->statusContemAtendimento() || $resultado->alteracoesRC->whereIn('informacao', $campos_atend)->isEmpty())
            &nbsp;&nbsp;|&nbsp;&nbsp;
        @endif

        @if(!$resultado->statusContemAtendimento() || $resultado->alteracoesRC->where('informacao', $campos_atend[1])->isEmpty())
            {!! $item_publicado !!}
            Segmento: <strong>{{ $resultado->segmento }}</strong>
        @endif

        </p>
    @endif

    <!-- ATENDIMENTO E COMUNICAÇÃO -->
    @if(!auth()->user()->isFinanceiro())

        @if($resultado->statusContemAtendimento())
            <hr class="mb-4 mt-4"/>

            <h5 class="text-primary"><i class="fas fa-user-edit"></i>&nbsp;<strong>ATENDIMENTO</strong> - Solicitação de alteração no Gerenti:</h5>

            <dl class="mb-4">
            @foreach($resultado->alteracoesRC as $campo)
                <dt>
                    {!! $item_publicado !!}
                    {{ $campo->informacao }}:
                </dt>
                <dd>
                    <i>- Valor atual no Gerenti:</i> {{ strlen($campo->valor_antigo) < 3 ? 'SEM ' . $campo->informacao : $campo->valor_antigo }}
                </dd>
                <dd>
                    <i>- Novo valor solicitado:</i> <mark>{{ $campo->valor_atual }}</mark>
                    &nbsp;&nbsp;

                    @if($campo->aguardandoAlteracao())
                    <div class="form-check-inline {{ $muitos_campos ? 'visible' : 'invisible' }}">
                        <label class="form-check-label">{!! $muitos_campos ? 'Recusado&nbsp;' : '' !!}
                            <input type="checkbox" 
                                class="form-check-input align-middle" 
                                value="{{ $campo->informacao }}" 
                                name="campos_recusados[]" 
                                form="form_justificar_atendimento"
                                {{ $muitos_campos ? '' : 'checked' }}
                            />
                        </label>
                    </div>
                    @endif

                    @if(!$campo->aguardandoAlteracao())
                        {!! !$campo->alteracaoAceita() ? '<i class="fas fa-times text-danger"></i>' : '<i class="fas fa-check text-success"></i>' !!}
                    @endif
                </dd>
            @endforeach
            </dl>

            {!! $resultado->statusAcaoRealizadaHTML('atendimento') !!}

            @if($resultado->atendimentoPendente())
                @component('components.submit-bdo-perfil', ['setor' => 'atendimento', 'resultado' => $resultado])
                @endcomponent
            @endif

        @endif

    @endif

    <!-- FINANCEIRO E COMUNICAÇÃO -->
    @if((auth()->user()->isAdmin() || auth()->user()->isFinanceiro() || auth()->user()->isEditor()) && ($resultado->statusContemFinanceiro()))
        <hr class="mb-4 mt-4"/>

        <h5 class="text-secondary mb-3"><i class="fas fa-user-edit"></i>&nbsp;<strong>FINANCEIRO</strong> - Representante não estava em dia no Gerenti no momento do cadastro do perfil público!</h5>

        <dl class="mb-4">
            <dt>SITUAÇÃO ATUAL NO GERENTI</dt>
            <dd>
                - {{ $gerenti['situacao'] }}
            </dd>

        {!! $resultado->statusAcaoRealizadaHTML('financeiro') !!}

        @if(isset($gerenti['cobrancas']) && $resultado->financeiroPendente())
            <dt>ANUIDADES</dt>
            @if(count($gerenti['cobrancas']['anuidades']))
                @foreach($gerenti['cobrancas']['anuidades'] as $cobranca)
                <dd>
                    - {{ $cobranca['DESCRICAO'] }} ⋅ {!! secondLine($cobranca['SITUACAO'], $cobranca['VENCIMENTOBOLETO'], $cobranca['LINK'], $cobranca['DESCRICAO'], $cobranca['BOLETO']) !!}
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    R$ {{ toReais($cobranca['VALOR']) }}
                </dd>
                @endforeach
            @else
            <dd>
                - Nada a mostrar aqui.
            </dd>
            @endif

            <dt>OUTRAS COBRANÇAS</dt>
            @if(count($gerenti['cobrancas']['outros']))
                @foreach($gerenti['cobrancas']['outros'] as $cobranca)
                <dd>
                    - {{ $cobranca['DESCRICAO'] }} ⋅ {!! secondLine($cobranca['SITUACAO'], $cobranca['VENCIMENTOBOLETO'], $cobranca['LINK'], $cobranca['DESCRICAO'], $cobranca['BOLETO']) !!}
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    R$ {{ toReais($cobranca['VALOR']) }}
                </dd>
                @endforeach
            @else
            <dd>
                - Nada a mostrar aqui.
            </dd>
            @endif
        @endif
        </dl>

        @if($resultado->financeiroPendente())
            @component('components.submit-bdo-perfil', ['setor' => 'financeiro', 'resultado' => $resultado])
            @endcomponent
        @endif

    @endif


    <!-- COMUNICAÇÃO -->
    @if(auth()->user()->isAdmin() || auth()->user()->isEditor())
        <hr class="mb-4 mt-4"/>

        <h5 class="text-success mb-3"><i class="fas fa-user-edit"></i>&nbsp;<strong>COMUNICAÇÃO</strong> - Solicitação de publicação do perfil público:</h5>

        <div class="form-row mb-4">
            <div class="col">
                <label for="descricao">
                    {!! $item_publicado !!}
                    Descrição
                </label>
                <textarea 
                    rows="5" 
                    class="form-control {{ $errors->has('descricao') ? 'is-invalid' : '' }}"
                    name="descricao"
                    maxlength="700"
                    form="form_aprovar_final"
                    {{ (auth()->user()->isAdmin() || auth()->user()->isEditor()) && $resultado->statusEtapaFinal() ? '' : 'readonly' }}
                >{{ old('descricao') ? old('descricao') : $resultado->descricao }}</textarea>

                @if($errors->has('descricao'))
                <div class="invalid-feedback">
                {{ $errors->first('descricao') }}
                </div>
                @endif
            </div>
        </div>

        {!! $resultado->statusAcaoRealizadaHTML('final') !!}

        @if($resultado->statusEtapaFinal())
            @component('components.submit-bdo-perfil', ['setor' => 'final', 'resultado' => $resultado])
            @endcomponent
        @endif

    @endif

    @if($resultado->trashed())
        <p class="border border-danger rounded p-2 col-xl-6 mt-4">
            <i class="fas fa-trash text-danger"></i>
            &nbsp;&nbsp;<strong>Excluído pelo representante!</strong>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <i class="fas fa-clock"></i>
            &nbsp;&nbsp;{{ formataData($resultado->deleted_at) }}
        </p>
    @endif
    
</div>

<div class="card-footer">
    <div class="float-left">
        <a href="{{ route('bdorepresentantes.lista') }}" class="btn btn-default">
            Voltar
        </a>
    </div>
</div>
