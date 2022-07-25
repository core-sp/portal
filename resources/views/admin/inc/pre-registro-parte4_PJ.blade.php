<div class="card-body bg-light">

    <p id="cpf_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['cpf_rt'] }} - CPF: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->cpf) ? formataCpfCnpj($resultado->pessoaJuridica->responsavelTecnico->cpf) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'cpf_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['cpf_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="registro">
        <span class="font-weight-bolder">{{ $codigos[3]['registro'] }} - Registro: </span>
        <input 
            type="text" 
            value="{{ isset($resultado->pessoaJuridica->responsavelTecnico->registro) ? formataRegistro($resultado->pessoaJuridica->responsavelTecnico->registro) : '' }}"
            name="registro"
            maxlength="20"
            {{ $resultado->atendentePodeEditar() ? '' : 'disabled' }}
        />
        @if($resultado->atendentePodeEditar())
        <button class="btn btn-outline-success btn-sm ml-2 addValorPreRegistro" type="button" value="registro">
            <i class="fas fa-save"></i>
        </button>
        @endif
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'registro',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="nome_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['nome_rt'] }} - Nome Completo: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->nome) ? $resultado->pessoaJuridica->responsavelTecnico->nome : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'nome_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['nome_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="nome_social_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['nome_social_rt'] }} - Nome Social: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->nome_social) ? $resultado->pessoaJuridica->responsavelTecnico->nome_social : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'nome_social_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['nome_social_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="dt_nascimento_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['dt_nascimento_rt'] }} - Data de Nascimento: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->dt_nascimento) ? onlyDate($resultado->pessoaJuridica->responsavelTecnico->dt_nascimento) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'dt_nascimento_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['dt_nascimento_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="sexo_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['sexo_rt'] }} - Gênero: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->sexo) ? generos()[$resultado->pessoaJuridica->responsavelTecnico->sexo] : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'sexo_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['sexo_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="tipo_identidade_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['tipo_identidade_rt'] }} - Tipo do documento de identidade: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->tipo_identidade) ? $resultado->pessoaJuridica->responsavelTecnico->tipo_identidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'tipo_identidade_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['tipo_identidade_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="identidade_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['identidade_rt'] }} - N° do documento de identidade: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->identidade) ? $resultado->pessoaJuridica->responsavelTecnico->identidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'identidade_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['identidade_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="orgao_emissor_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['orgao_emissor_rt'] }} - Órgão Emissor: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->orgao_emissor) ? $resultado->pessoaJuridica->responsavelTecnico->orgao_emissor : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'orgao_emissor_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['orgao_emissor_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="dt_expedicao_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['dt_expedicao_rt'] }} - Data de Expedição: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->dt_expedicao) ? onlyDate($resultado->pessoaJuridica->responsavelTecnico->dt_expedicao) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'dt_expedicao_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['dt_expedicao_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="cep_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['cep_rt'] }} - CEP: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->cep) ? $resultado->pessoaJuridica->responsavelTecnico->cep : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'cep_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['cep_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="bairro_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['bairro_rt'] }} - Bairro: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->bairro) ? $resultado->pessoaJuridica->responsavelTecnico->bairro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'bairro_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['bairro_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="logradouro_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['logradouro_rt'] }} - Logradouro: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->logradouro) ? $resultado->pessoaJuridica->responsavelTecnico->logradouro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'logradouro_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['logradouro_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="numero_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['numero_rt'] }} - Número: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->numero) ? $resultado->pessoaJuridica->responsavelTecnico->numero : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'numero_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['numero_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="complemento_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['complemento_rt'] }} - Complemento: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->complemento) ? $resultado->pessoaJuridica->responsavelTecnico->complemento : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'complemento_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['complemento_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="cidade_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['cidade_rt'] }} - Município: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->cidade) ? $resultado->pessoaJuridica->responsavelTecnico->cidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'cidade_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['cidade_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="uf_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['uf_rt'] }} - Estado: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->uf) ? $resultado->pessoaJuridica->responsavelTecnico->uf : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'uf_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['uf_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="nome_mae_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['nome_mae_rt'] }} - Nome da Mãe: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->nome_mae) ? $resultado->pessoaJuridica->responsavelTecnico->nome_mae : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'nome_mae_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['nome_mae_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

    <p id="nome_pai_rt">
        <span class="font-weight-bolder">{{ $codigos[3]['nome_pai_rt'] }} - Nome do Pai: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->nome_pai) ? $resultado->pessoaJuridica->responsavelTecnico->nome_pai : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'nome_pai_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
        @if(isset($resultado->getCamposEditados()['nome_pai_rt']))
        <span class="badge badge-danger ml-2">Campo alterado</span>
        @endif
    </p>

</div>