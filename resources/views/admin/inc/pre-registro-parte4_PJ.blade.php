<div class="card-body">

    <p id="cpf_rt">
        <span class="font-weight-bolder">{{ array_search('cpf', $codRT) }} - CPF: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->cpf) ? formataCpfCnpj($resultado->pessoaJuridica->responsavelTecnico->cpf) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'cpf_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="registro">
        <span class="font-weight-bolder">{{ array_search('registro', $codRT) }} - Registro: </span>
        <input 
            type="text" 
            value="{{ isset($resultado->pessoaJuridica->responsavelTecnico->registro) ? $resultado->pessoaJuridica->responsavelTecnico->registro : '' }}"
            name="registro"
            maxlength="20"
        />
        <button class="btn btn-outline-success btn-sm ml-2 addValorPreRegistro" type="button" value="registro">
            <i class="fas fa-save"></i>
        </button>
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'registro',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="nome_rt">
        <span class="font-weight-bolder">{{ array_search('nome', $codRT) }} - Nome Completo: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->nome) ? $resultado->pessoaJuridica->responsavelTecnico->nome : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'nome_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="nome_social_rt">
        <span class="font-weight-bolder">{{ array_search('nome_social', $codRT) }} - Nome Social: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->nome_social) ? $resultado->pessoaJuridica->responsavelTecnico->nome_social : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'nome_social_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="dt_nascimento_rt">
        <span class="font-weight-bolder">{{ array_search('dt_nascimento', $codRT) }} - Data de Nascimento: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->dt_nascimento) ? onlyDate($resultado->pessoaJuridica->responsavelTecnico->dt_nascimento) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'dt_nascimento_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="sexo_rt">
        <span class="font-weight-bolder">{{ array_search('sexo', $codRT) }} - Gênero: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->sexo) ? generos()[$resultado->pessoaJuridica->responsavelTecnico->sexo] : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'sexo_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="tipo_identidade_rt">
        <span class="font-weight-bolder">{{ array_search('tipo_identidade', $codRT) }} - Tipo do documento de identidade: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->tipo_identidade) ? $resultado->pessoaJuridica->responsavelTecnico->tipo_identidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'tipo_identidade_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="identidade_rt">
        <span class="font-weight-bolder">{{ array_search('identidade', $codRT) }} - N° do documento de identidade: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->identidade) ? $resultado->pessoaJuridica->responsavelTecnico->identidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'identidade_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="orgao_emissor_rt">
        <span class="font-weight-bolder">{{ array_search('orgao_emissor', $codRT) }} - Órgão Emissor: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->orgao_emissor) ? $resultado->pessoaJuridica->responsavelTecnico->orgao_emissor : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'orgao_emissor_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="dt_expedicao_rt">
        <span class="font-weight-bolder">{{ array_search('dt_expedicao', $codRT) }} - Data de Expedição: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->dt_expedicao) ? onlyDate($resultado->pessoaJuridica->responsavelTecnico->dt_expedicao) : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'dt_expedicao_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="cep_rt">
        <span class="font-weight-bolder">{{ array_search('cep', $codRT) }} - CEP: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->cep) ? $resultado->pessoaJuridica->responsavelTecnico->cep : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'cep_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="bairro_rt">
        <span class="font-weight-bolder">{{ array_search('bairro', $codRT) }} - Bairro: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->bairro) ? $resultado->pessoaJuridica->responsavelTecnico->bairro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'bairro_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="logradouro_rt">
        <span class="font-weight-bolder">{{ array_search('logradouro', $codRT) }} - Logradouro: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->logradouro) ? $resultado->pessoaJuridica->responsavelTecnico->logradouro : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'logradouro_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="numero_rt">
        <span class="font-weight-bolder">{{ array_search('numero', $codRT) }} - Número: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->numero) ? $resultado->pessoaJuridica->responsavelTecnico->numero : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'numero_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="complemento_rt">
        <span class="font-weight-bolder">{{ array_search('complemento', $codRT) }} - Complemento: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->complemento) ? $resultado->pessoaJuridica->responsavelTecnico->complemento : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'complemento_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="cidade_rt">
        <span class="font-weight-bolder">{{ array_search('cidade', $codRT) }} - Município: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->cidade) ? $resultado->pessoaJuridica->responsavelTecnico->cidade : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'cidade_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="uf_rt">
        <span class="font-weight-bolder">{{ array_search('uf', $codRT) }} - Estado: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->uf) ? $resultado->pessoaJuridica->responsavelTecnico->uf : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'uf_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="nome_mae_rt">
        <span class="font-weight-bolder">{{ array_search('nome_mae', $codRT) }} - Nome da Mãe: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->nome_mae) ? $resultado->pessoaJuridica->responsavelTecnico->nome_mae : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'nome_mae_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

    <p id="nome_pai_rt">
        <span class="font-weight-bolder">{{ array_search('nome_pai', $codRT) }} - Nome do Pai: </span>
        {{ isset($resultado->pessoaJuridica->responsavelTecnico->nome_pai) ? $resultado->pessoaJuridica->responsavelTecnico->nome_pai : '------' }}
        @component('components.justificativa_pre_registro_admin', [
            'campo' => 'nome_pai_rt',
            'resultado' => $resultado->getJustificativaArray()
        ])
        @endcomponent
    </p>

</div>