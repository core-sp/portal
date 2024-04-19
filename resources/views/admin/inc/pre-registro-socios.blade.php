@php
    $camposEditados = $resultado->getCamposEditados();
    $socios = $resultado->pessoaJuridica->possuiSocio() ? $resultado->pessoaJuridica->socios : collect();
@endphp

<div class="card-body bg-light">

    <p><strong><i>Legenda:</i></strong>&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span> Indica que o sócio é também Responsável Técnico neste pré-registro.</p>

    <hr>

    <p>
        {!! $resultado->pessoaJuridica->possuiRTSocio() ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times text-danger"></i>' !!}
        &nbsp;<span class="font-weight-bolder">{{ $nome_campos['checkRT_socio'] }} - Responsável Técnico pertence ao quadro societário</span>
    </p>

    <hr>

    <p id="cpf_cnpj_socio">
        <span class="font-weight-bolder">{{ $nome_campos['cpf_cnpj_socio'] }} - CPF / CNPJ: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'cpf_cnpj_socio',
        ])
        @endcomponent
        @if(array_key_exists('cpf_cnpj_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                &nbsp;&nbsp;{{ isset($socio->cpf_cnpj) ? formataCpfCnpj($socio->cpf_cnpj) : '------' }}
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="registro_socio">
        <span class="font-weight-bolder">{{ $nome_campos['registro_socio'] }} - Registro: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'registro_socio',
        ])
        @endcomponent
        @if(array_key_exists('registro_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if(!$socio->socioRT())
                &nbsp;&nbsp;{{ isset($socio->registro) ? formataRegistro($socio->registro) : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>{{ $socio->socioRT() ? 'Aba "' . $abas[3] . '"' : 'Não precisa' }}</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="nome_socio">
        <span class="font-weight-bolder">{{ $nome_campos['nome_socio'] }} - Nome: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'nome_socio',
        ])
        @endcomponent
        @if(array_key_exists('nome_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if(!$socio->socioRT())
                &nbsp;&nbsp;{{ isset($socio->nome) ? $socio->nome : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>{{ $socio->socioRT() ? 'Aba "' . $abas[3] . '"' : 'Não precisa' }}</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="nome_social_socio">
        <span class="font-weight-bolder">{{ $nome_campos['nome_social_socio'] }} - Nome Social: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'nome_social_socio',
        ])
        @endcomponent
        @if(array_key_exists('nome_social_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if($socio->socioPF() && !$socio->socioRT())
                &nbsp;&nbsp;{{ isset($socio->nome_social) ? $socio->nome_social : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>{{ $socio->socioRT() ? 'Aba "' . $abas[3] . '"' : 'Não precisa' }}</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="dt_nascimento_socio">
        <span class="font-weight-bolder">{{ $nome_campos['dt_nascimento_socio'] }} - Data de Nascimento: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'dt_nascimento_socio',
        ])
        @endcomponent
        @if(array_key_exists('dt_nascimento_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if($socio->socioPF() && !$socio->socioRT())
                &nbsp;&nbsp;{{ isset($socio->dt_nascimento) ? onlyDate($socio->dt_nascimento) : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>{{ $socio->socioRT() ? 'Aba "' . $abas[3] . '"' : 'Não precisa' }}</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="identidade_socio">
        <span class="font-weight-bolder">{{ $nome_campos['identidade_socio'] }} - Identidade: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'identidade_socio',
        ])
        @endcomponent
        @if(array_key_exists('identidade_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if($socio->socioPF() && !$socio->socioRT())
                &nbsp;&nbsp;{{ isset($socio->identidade) ? $socio->identidade : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>{{ $socio->socioRT() ? 'Aba "' . $abas[3] . '"' : 'Não precisa' }}</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="orgao_emissor_socio">
        <span class="font-weight-bolder">{{ $nome_campos['orgao_emissor_socio'] }} - Órgão Emissor: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'orgao_emissor_socio',
        ])
        @endcomponent
        @if(array_key_exists('orgao_emissor_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if($socio->socioPF() && !$socio->socioRT())
                &nbsp;&nbsp;{{ isset($socio->orgao_emissor) ? $socio->orgao_emissor : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>{{ $socio->socioRT() ? 'Aba "' . $abas[3] . '"' : 'Não precisa' }}</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="cep_socio">
        <span class="font-weight-bolder">{{ $nome_campos['cep_socio'] }} - CEP: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'cep_socio',
        ])
        @endcomponent
        @if(array_key_exists('cep_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if(!$socio->socioRT())
                &nbsp;&nbsp;{{ isset($socio->cep) ? $socio->cep : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>{{ $socio->socioRT() ? 'Aba "' . $abas[3] . '"' : 'Não precisa' }}</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="bairro_socio">
        <span class="font-weight-bolder">{{ $nome_campos['bairro_socio'] }} - Bairro: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'bairro_socio',
        ])
        @endcomponent
        @if(array_key_exists('bairro_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if(!$socio->socioRT())
                &nbsp;&nbsp;{{ isset($socio->bairro) ? $socio->bairro : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>{{ $socio->socioRT() ? 'Aba "' . $abas[3] . '"' : 'Não precisa' }}</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="logradouro_socio">
        <span class="font-weight-bolder">{{ $nome_campos['logradouro_socio'] }} - Logradouro: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'logradouro_socio',
        ])
        @endcomponent
        @if(array_key_exists('logradouro_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if(!$socio->socioRT())
                &nbsp;&nbsp;{{ isset($socio->logradouro) ? $socio->logradouro : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>{{ $socio->socioRT() ? 'Aba "' . $abas[3] . '"' : 'Não precisa' }}</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="numero_socio">
        <span class="font-weight-bolder">{{ $nome_campos['numero_socio'] }} - Número: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'numero_socio',
        ])
        @endcomponent
        @if(array_key_exists('numero_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if(!$socio->socioRT())
                &nbsp;&nbsp;{{ isset($socio->numero) ? $socio->numero : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>{{ $socio->socioRT() ? 'Aba "' . $abas[3] . '"' : 'Não precisa' }}</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="complemento_socio">
        <span class="font-weight-bolder">{{ $nome_campos['complemento_socio'] }} - Complemento: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'complemento_socio',
        ])
        @endcomponent
        @if(array_key_exists('complemento_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if(!$socio->socioRT())
                &nbsp;&nbsp;{{ isset($socio->complemento) ? $socio->complemento : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>{{ $socio->socioRT() ? 'Aba "' . $abas[3] . '"' : 'Não precisa' }}</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="cidade_socio">
        <span class="font-weight-bolder">{{ $nome_campos['cidade_socio'] }} - Município: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'cidade_socio',
        ])
        @endcomponent
        @if(array_key_exists('cidade_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if(!$socio->socioRT())
                &nbsp;&nbsp;{{ isset($socio->cidade) ? $socio->cidade : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>{{ $socio->socioRT() ? 'Aba "' . $abas[3] . '"' : 'Não precisa' }}</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="uf_socio">
        <span class="font-weight-bolder">{{ $nome_campos['uf_socio'] }} - Estado: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'uf_socio',
        ])
        @endcomponent
        @if(array_key_exists('uf_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if(!$socio->socioRT())
                &nbsp;&nbsp;{{ isset($socio->uf) ? $socio->uf : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>{{ $socio->socioRT() ? 'Aba "' . $abas[3] . '"' : 'Não precisa' }}</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="nome_mae_socio">
        <span class="font-weight-bolder">{{ $nome_campos['nome_mae_socio'] }} - Nome Mãe: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'nome_mae_socio',
        ])
        @endcomponent
        @if(array_key_exists('nome_mae_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if($socio->socioPF() && !$socio->socioRT())
                &nbsp;&nbsp;{{ isset($socio->nome_mae) ? $socio->nome_mae : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>{{ $socio->socioRT() ? 'Aba "' . $abas[3] . '"' : 'Não precisa' }}</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="nome_pai_socio">
        <span class="font-weight-bolder">{{ $nome_campos['nome_pai_socio'] }} - Nome Pai: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'nome_pai_socio',
        ])
        @endcomponent
        @if(array_key_exists('nome_pai_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if($socio->socioPF() && !$socio->socioRT())
                &nbsp;&nbsp;{{ isset($socio->nome_pai) ? $socio->nome_pai : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>{{ $socio->socioRT() ? 'Aba "' . $abas[3] . '"' : 'Não precisa' }}</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="nacionalidade_socio">
        <span class="font-weight-bolder">{{ $nome_campos['nacionalidade_socio'] }} - Nacionalidade: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'nacionalidade_socio',
        ])
        @endcomponent
        @if(array_key_exists('nacionalidade_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if($socio->socioPF())
                &nbsp;&nbsp;{{ isset($socio->nacionalidade) ? $socio->nacionalidade : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>Não precisa</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>

    <hr>

    <p id="naturalidade_estado_socio">
        <span class="font-weight-bolder">{{ $nome_campos['naturalidade_estado_socio'] }} - Naturalidade - Estado: </span>
        @component('components.justificativa_pre_registro_admin', [
            'preRegistro' => $resultado,
            'campo' => 'naturalidade_estado_socio',
        ])
        @endcomponent
        @if(array_key_exists('naturalidade_estado_socio', $camposEditados))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif

        @if($socios->isNotEmpty())
        <br><br>
        @endif

        @foreach($socios as $socio)
            <span class="text-nowrap">
                <span class="font-weight-bolder">Sócio <span class="text-primary">ID {{ $socio->id }}</span>&nbsp;&nbsp;-</span>
                {!! $socio->socioRT() ? '&nbsp;&nbsp;<span class="badge badge-warning pt-1">RT</span>&nbsp;&nbsp;-' : '' !!}
                @if($socio->socioPF())
                &nbsp;&nbsp;{{ isset($socio->naturalidade_estado) ? $socio->naturalidade_estado : '------' }}
                @else
                &nbsp;&nbsp;<span class="text-danger"><i>Não precisa</i></span>
                @endif
            </span>

            @if(!$loop->last)
            &nbsp;&nbsp;<span class="font-weight-bolder text-danger">|</span>&nbsp;&nbsp;
            @endif

        @endforeach
    </p>
</div>