@component('components.justificativa_pre_registro', [
    'resultado' => $resultado,
    'correcoes' => $resultado->getCodigosJustificadosByAba($nome_campos)
])
@endcomponent

<div id="acoes_socio">

    <button type="button" id="criar_socio" class="btn btn-success btn-sm mr-4" {{ $resultado->userPodeEditar() && $resultado->pessoaJuridica->podeCriarSocio() ? '' : 'disabled' }}>
        <i class="fas fa-plus"></i> Sócio
    </button>

    <div id="checkSocio" class="form-check-inline mt-3 mt-sm-3">
        <label for="checkRT_socio" class="form-check-label">
            <input type="checkbox" 
                id="checkRT_socio" 
                class="{{ $classe }} form-check-input" 
                name="checkRT_socio" 
                value="{{ $resultado->pessoaJuridica->possuiRTSocio() ? 'on' : 'off' }}"
                {{ $resultado->userPodeEditar() && $resultado->pessoaJuridica->possuiRT() ? '' : 'disabled' }}
                {{ $resultado->pessoaJuridica->possuiRTSocio() ? 'checked' : '' }}
            >
            <span class="bold">{{ $nome_campos['checkRT_socio'] }} - &nbsp;<span class="badge badge-warning pt-1">RT</span> Responsável Técnico pertence ao quadro societário</span>
        </label>
    </div>

    @if($resultado->pessoaJuridica->possuiSocio())
        <p class="mt-3">
            <small class="text-muted text-left">
                <em>Ordem dos sócios por atualização mais antiga.</em>
            </small>
        </p>
        @foreach($resultado->pessoaJuridica->socios->sortBy('pivot.updated_at') as $socio)
            {!! $socio->tabHTML() !!}
        @endforeach

    @else

    <p class="mt-3"><i>Ainda não consta sócios. É obrigatório ao menos um.</i></p>

    @endif
</div>

<div id="form_socio" style="display: none">

    <button type="button" id="mostrar_socios" class="btn btn-outline-primary btn-sm mb-2">Voltar para os Sócios</button>
    <br>

    @if(!$resultado->userPodeEditar())
    <fieldset id="analiseCorrecao" disabled>
    @endif

    <small class="text-muted text-left">
        <em>Após inserir um CPF / CNPJ válido, aguarde {{ $resultado->pessoaJuridica::TOTAL_HIST_DIAS_UPDATE_SOCIO * 24 }}h para trocar caso alcance o limite de <span id="limite-socios">{{ $resultado->pessoaJuridica::TOTAL_HIST_SOCIO }}</span> sócios.</em>
    </small>

    <div class="form-row mb-2 mt-2">
        <div class="col-sm-2 mb-2-576">
            <label for="id_socio">ID</label>
            <input 
                type="text" 
                class="form-control" 
                name="id_socio" 
                value="" 
                readonly 
            />
        </div>

        <div class="col-lg mb-2-576">
            <label for="cpf_cnpj_socio">{{ $nome_campos['cpf_cnpj_socio'] }} - CPF / CNPJ <span class="text-danger">*</span></label>
            <input
                type="text"
                id="cpf_cnpj_socio"
                class="{{ $classe }} form-control cpfOuCnpj obrigatorio"
                name="cpf_cnpj_socio"
                value=""
            />
        </div>

        <div class="col-lg mb-2-576 esconder-rt-socio">
            <label for="registro_socio">{{ $nome_campos['registro_socio'] }} - Registro</label>
            <input
                type="text"
                class="{{ $classe }} form-control"
                name="registro_socio"
                id="registro_socio"
                value=""
                disabled
                readonly
            />
        </div>
    </div>

    <fieldset id="campos_socio">
        <div class="form-row mb-2 esconder-rt-socio">
            <div class="col-sm mb-2-576">
                <label for="nome_socio">{{ $nome_campos['nome_socio'] }} - Nome Completo <span class="text-danger">*</span></label>
                <input
                    name="nome_socio"
                    id="nome_socio"
                    type="text"
                    class="{{ $classe }} text-uppercase form-control obrigatorio"
                    value=""
                    maxlength="191"
                />
            </div>
        </div>

        <div class="form-row mb-2 esconder-campo-socio esconder-rt-socio">
            <div class="col-sm mb-2-576">
                <label for="nome_social_socio">{{ $nome_campos['nome_social_socio'] }} - Nome Social</label>
                <input
                    name="nome_social_socio"
                    id="nome_social_socio"
                    type="text"
                    class="{{ $classe }} text-uppercase form-control"
                    value=""
                    maxlength="191"
                />
            </div>
        </div>

        <div class="form-row mb-2 esconder-campo-socio esconder-rt-socio">
            <div class="col-sm mb-2-576">
                <label for="dt_nascimento_socio">{{ $nome_campos['dt_nascimento_socio'] }} - Data de Nascimento <span class="text-danger">*</span></label>
                <input
                    name="dt_nascimento_socio"
                    id="dt_nascimento_socio"
                    type="date"
                    class="{{ $classe }} form-control obrigatorio"
                    value=""
                    max="{{ now()->subYears(18)->format('Y-m-d') }}"
                />
            </div>

            <div class="col-md mb-2-576">
                <label>{{ $nome_campos['identidade_socio'] }} - </label>
                <label for="identidade_socio">N° do documento de identidade <span class="text-danger">*</span></label>
                <input
                    name="identidade_socio"
                    type="text"
                    id="identidade_socio"
                    class="{{ $classe }} form-control text-uppercase obrigatorio"
                    value=""
                    maxlength="30"
                />
            </div>

            <div class="col-sm mb-2-576">
                <label for="orgao_emissor_socio">{{ $nome_campos['orgao_emissor_socio'] }} - Órgão Emissor <span class="text-danger">*</span></label>
                <input
                    name="orgao_emissor_socio"
                    id="orgao_emissor_socio"
                    type="text"
                    class="{{ $classe }} text-uppercase form-control obrigatorio"
                    value=""
                    maxlength="191"
                />
            </div>
        </div>

        <div class="linha-lg-mini mt-3 mb-3 esconder-rt-socio"></div>

        <div class="form-row mb-2 esconder-rt-socio">
            <div class="col-sm-4 mb-2-576">
                <label for="cep_socio">{{ $nome_campos['cep_socio'] }} - CEP <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="cep_socio"
                    class="{{ $classe }} form-control cep obrigatorio"
                    id="cep_socio"
                    value=""
                />
            </div>

            <div class="col-sm mb-2-576">
                <label for="bairro_socio">{{ $nome_campos['bairro_socio'] }} - Bairro <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="bairro_socio"
                    class="{{ $classe }} text-uppercase form-control obrigatorio"
                    id="bairro_socio"
                    value=""
                    maxlength="191"
                />
            </div>
        </div>

        <div class="form-row mb-2 esconder-rt-socio">
            <div class="col-md col-lg mb-2-576">
                <label for="rua_socio">{{ $nome_campos['logradouro_socio'] }} - Logradouro <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="logradouro_socio"
                    class="{{ $classe }} text-uppercase form-control obrigatorio"
                    id="rua_socio"
                    value=""
                    maxlength="191"
                />
            </div>

            <div class="col-md-3 col-lg-2 mb-2-576">
                <label for="numero_socio">{{ $nome_campos['numero_socio'] }} - Número <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="numero_socio"
                    class="{{ $classe }} text-uppercase form-control obrigatorio"
                    id="numero_socio"
                    value=""
                    maxlength="10"
                />
            </div>
        </div>

        <div class="form-row mb-2 esconder-rt-socio">
            <div class="col-md-3 col-lg-3 col-xl-3 mb-2-576">
                <label for="complemento_socio">{{ $nome_campos['complemento_socio'] }} - Complemento</label>
                <input
                    type="text"
                    name="complemento_socio"
                    class="{{ $classe }} text-uppercase form-control"
                    id="complemento_socio"
                    value=""
                    maxlength="50"
                />
            </div>

            <div class="col-md col-lg-5 col-xl-5 mb-2-576">
                <label for="cidade_socio">{{ $nome_campos['cidade_socio'] }} - Município <span class="text-danger">*</span></label>
                <input
                    type="text"
                    name="cidade_socio"
                    id="cidade_socio"
                    class="{{ $classe }} text-uppercase form-control obrigatorio"
                    value=""
                    maxlength="191"
                />
            </div>

            <div class="col-lg-4 col-xl-4 mb-2-576">
                <label for="uf_socio">{{ $nome_campos['uf_socio'] }} - Estado <span class="text-danger">*</span></label>
                <select 
                    name="uf_socio" 
                    id="uf_socio" 
                    class="{{ $classe }} form-control obrigatorio"
                >
                    <option value="">Selecione a opção...</option>
                @foreach(estados() as $key => $estado)
                    <option value="{{ $key }}">{{ $estado }}</option>
                @endforeach
                </select>
            </div>
        </div>

        <div class="linha-lg-mini mt-3 mb-3 esconder-campo-socio esconder-rt-socio"></div>

        <div class="form-row mb-2 esconder-campo-socio esconder-rt-socio">
            <div class="col-lg mb-2-576">
                <label for="nome_mae_socio">{{ $nome_campos['nome_mae_socio'] }} - Nome da Mãe <span class="text-danger">*</span></label>
                <input
                    name="nome_mae_socio"
                    id="nome_mae_socio"
                    type="text"
                    class="{{ $classe }} text-uppercase form-control obrigatorio"
                    value=""
                    maxlength="191"
                />
            </div>

            <div class="col-lg mb-2-576">
                <label for="nome_pai_socio">{{ $nome_campos['nome_pai_socio'] }} - Nome do Pai <span class="text-danger">*</span></label>
                <input
                    name="nome_pai_socio"
                    id="nome_pai_socio"
                    type="text"
                    class="{{ $classe }} text-uppercase form-control"
                    value=""
                    maxlength="191"
                />
            </div>
        </div>

        <div class="form-row mb-2 esconder-campo-socio">
            <div class="col-sm mb-2-576">
                <label for="nacionalidade_socio">{{ $nome_campos['nacionalidade_socio'] }} - Nacionalidade <span class="text-danger">*</span></label>
                <select 
                    name="nacionalidade_socio" 
                    id="nacionalidade_socio"
                    class="{{ $classe }} form-control obrigatorio" 
                >
                    <option value="">Selecione a opção...</option>
                @foreach(nacionalidades() as $nacionalidade)
                    <option value="{{ $nacionalidade }}">{{ mb_strtoupper($nacionalidade, 'UTF-8') }}</option>
                @endforeach
                </select>
            </div>

            <div class="col-sm mb-2-576">
                <label for="naturalidade_estado_socio">{{ $nome_campos['naturalidade_estado_socio'] }} - Naturalidade - Estado <span class="text-danger">*</span></label>
                <select 
                    name="naturalidade_estado_socio" 
                    id="naturalidade_estado_socio"
                    class="{{ $classe }} form-control obrigatorio" 
                >
                    <option value="">Selecione a opção...</option>
                @foreach(estados() as $key => $naturalidade)
                    <option value="{{ $key }}">{{ $naturalidade }}</option>
                @endforeach
                </select>
            </div>
        </div>
    </fieldset>

    @if(!$resultado->userPodeEditar())
    </fieldset>
    @endif

</div>
