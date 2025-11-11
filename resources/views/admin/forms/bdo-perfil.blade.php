<div class="card-body">

    <!-- TODOS -->
    <h4>ID: <strong>{{ $resultado->id }}</strong> - Representante:</h4>
    <p class="mb-0">Nome: <strong>{{ $resultado->representante->nome }}</strong></p>
    <p class="mb-0">
        Registro: <strong>{{ $resultado->representante->registro_core }}</strong>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        CNPJ: <strong>{{ $resultado->representante->cpf_cnpj }}</strong>
    </p>
    <p class="mb-0">
        Telefone: <strong>{{ $resultado->telefone }}</strong>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        E-mail: <strong>{{ $resultado->email }}</strong>
    </p>
    <p class="mb-0">Endereço: <strong>{{ $resultado->endereco }}</strong></p>

    <!-- COMUNICAÇÃO -->
    @if(auth()->user()->isAdmin() || auth()->user()->isEditor())
        <p class="mb-0">Municípios: <strong>{{ implode(' | ', json_decode($resultado->regioes)->municipios) }}</strong></p>
    @endif

    <!-- ATENDIMENTO E COMUNICAÇÃO -->
    @if(!auth()->user()->isFinanceiro())

        @if($resultado->statusContemAtendimento())
            <hr class="mb-4 mt-4"/>

            <h5 class="text-primary"><i class="fas fa-user-edit"></i>&nbsp;<strong>ATENDIMENTO</strong> - Solicitação de alteração no Gerenti:</h5>

            <dl class="mb-4">
            @foreach($resultado->alteracoesRC as $campo)
                <dt>
                    {{ $campo->informacao }}:
                </dt>
                <dd>
                    <i>- Valor atual no Gerenti:</i> {{ $campo->valor_antigo }}
                </dd>
                <dd>
                    <i>- Novo valor solicitado:</i> <mark>{{ $campo->valor_atual }}</mark>
                </dd>
            @endforeach
            </dl>

            @if($resultado->atendimentoPendente())
                @component('components.submit-bdo-perfil', ['setor' => 'atendimento'])
                @endcomponent
            @endif

        @endif

        @if(!$resultado->statusContemAtendimento())
        <p class="mb-0">
            Regional: <strong>{{ json_decode($resultado->regioes)->seccional }}</strong>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            Segmento: <strong>{{ $resultado->segmento }}</strong>
        </p>
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
            @component('components.submit-bdo-perfil', ['setor' => 'financeiro'])
            @endcomponent
        @endif

    @endif


    <!-- COMUNICAÇÃO -->
    @if(auth()->user()->isAdmin() || auth()->user()->isEditor())
        <hr class="mb-4 mt-4"/>

        <h5 class="text-success mb-3"><i class="fas fa-user-edit"></i>&nbsp;<strong>COMUNICAÇÃO</strong> - Solicitação de publicação do perfil público:</h5>

        <div class="form-row mb-4">
            <div class="col">
                <label for="descricao">Descrição</label>
                <textarea 
                    rows="5" 
                    class="form-control {{ $errors->has('descricao') ? 'is-invalid' : '' }}"
                    name="descricao"
                    maxlength="700"
                    {{ (auth()->user()->isAdmin() || auth()->user()->isEditor()) && $resultado->statusEtapaFinal() ? '' : 'readonly' }}
                >{{ old('descricao') ? old('descricao') : $resultado->descricao }}</textarea>

                @if($errors->has('descricao'))
                <div class="invalid-feedback">
                {{ $errors->first('descricao') }}
                </div>
                @endif
            </div>
        </div>

        @if($resultado->statusEtapaFinal())
            @component('components.submit-bdo-perfil', ['setor' => 'final'])
            @endcomponent
        @endif

    @endif
</div>
