@component('components.justificativa_pre_registro', [
    'resultado' => $resultado,
    'correcoes' => $resultado->getCodigosJustificadosByAba($nome_campos)
])
@endcomponent

<label for="registro_preRegistro">{{ $nome_campos['cpf_cnpj_socio'] }} - CPF / CNPJ</label>
<input
    type="text"
    class="{{ $classe }} form-control"
    id="registro_preRegistro"
    value="{{-- isset($resultado->pessoaJuridica->responsavelTecnico->registro) ? $resultado->pessoaJuridica->responsavelTecnico->registro : '' --}}"
    disabled
    readonly
/>
