const campo_cep = $("#cep");
let cep_temp = '';

function limpaFormularioCep() {

    $("#rua, #bairro, #cidade, #uf, #ibge").each(function(){
        $(this).val("");
    });
}

function validarCep(cep){

    let erro = '';

    if(cep.length != 9)
        erro = 'O CEP deve ter 9 caracteres';

    cep = cep.replace(/\D/g, '');
    if(cep.length == 0)
        erro = 'O CEP deve ter somente números';

    let valida_cep = /^[0-9]{8}$/;
    if(!valida_cep.test(cep))
        erro = 'O CEP deve ter o formato 00000-000';

    if(cep_temp === cep){
        campo_cep[0].dispatchEvent(new CustomEvent("CEP_ERRO", {
            detail: 'O CEP atual já foi conferido',
        }));

        return false;
    }

    if(erro.length > 0){
        limpaFormularioCep();
        campo_cep[0].dispatchEvent(new CustomEvent("CEP_ERRO", {
            detail: erro,
        }));

        return false;
    }

    $("#rua, #bairro, #cidade, #uf, #ibge").each(function(){
        $(this).val("...");
    });

    cep_temp = cep;

    return cep;
}

function wsCep(cep){

    campo_cep[0].dispatchEvent(new CustomEvent("CEP", {
        detail: 'buscando',
    }));

    $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {
        if(!("erro" in dados)) {
            $("#rua").val(dados.logradouro);
            $("#bairro").val(dados.bairro);
            $("#cidade").val(dados.localidade);
            $("#uf").val(dados.uf);
            $("#ibge").val(dados.ibge);

            campo_cep[0].dispatchEvent(new CustomEvent("CEP", {
                detail: 'encontrado',
            }));

            return;
        }

        limpaFormularioCep();
        campo_cep[0].dispatchEvent(new CustomEvent("CEP_ERRO", {
            detail: "CEP não encontrado",
        }));
    });
}

export function getCep(){

    campo_cep.on('input', function(e) {
        let cep = $(this).masked($(this).val());
        let dado = e.originalEvent.data;

        if((dado !== undefined) && (dado !== null) && (dado.length == 1) && (dado.search(/[^0-9]/) > -1))
            return;

        if(cep.length !== 9)
            return;

        cep = validarCep(cep);
        if(typeof cep === "string")
            wsCep(cep);
    });
}