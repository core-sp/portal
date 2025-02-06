const plantao_txt = "Plantão Jurídico";

function validarDtPlantaoJuridico(datas){

    if((datas[0] != null) && (datas[1] != null))
        return [new Date(datas[0] + " 00:00:00"), new Date(datas[1] + " 00:00:00")];

    if((datas[0] == null) && (datas[1] != null))
        return ['+1', new Date(datas[1] + " 00:00:00")];

    return null;
}

function getDadosAgendamento(acao, link, dados){

    $.ajax({
        method: "GET",
        data: dados,
        dataType: 'json',
        url: link,
        beforeSend: function(){
            document.dispatchEvent(new CustomEvent("MSG_GERAL_CARREGAR"));
        },
        success: function(response) {
            document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));

            document.dispatchEvent(new CustomEvent("AGENDA_" + acao, {
                detail: {retorno: response}
            }));
        },
        error: function() {
            document.dispatchEvent(new CustomEvent("MSG_GERAL_BTN_ACAO", {
                detail: {
                    titulo: '<i class="fas fa-times text-danger"></i> Erro!', 
                    texto: '<span class="text-danger">' + 
                        'Falha ao recuperar calendário. <br> ' + 
                        'Por favor verifique se o uso de cookies está habilitado e recarregue a página ou tente mais tarde.</span>',
                    botao: '<button type="button" class="btn btn-sm btn-primary" onclick="location.reload(true)">Recarregar</button>'
                }
            }));
        }
    });
}

function avisosServicos(){
    if($('#selectServicos').val() != 'Outros')
        return;

    document.dispatchEvent(new CustomEvent("MSG_GERAL_VARIOS_BTN_ACAO", {
        detail: {
            titulo: 'Atenção', 
            texto: 'Você é Representante Comercial?',
            botao: ['<button type="button" class="btn btn-success btn-sm" data-dismiss="modal">Sim</button>', 
                '<button type="button" class="btn btn-secondary btn-sm" id="notRC">Não</button>']
        }
    }));
}

function avisos(){
    let id = $('#idregional').val();

    if((id == 6) || (id == 8))
        document.dispatchEvent(new CustomEvent("MSG_GERAL_CONT_TITULO", {
            detail: {
                titulo: 'Atenção, Representante Comercial!', 
                texto: 'As cidades de Araraquara e São José do Rio Preto  instituíram, por meio dos Decretos n° 12.892/2022 e n° 19.213/2022, respectivamente,' + 
                    ' a volta da obrigatoriedade do uso de máscaras de proteção em locais fechados devido à alta nos números de infecções pelo coronavírus. ' + 
                    '<br>Assim, o Core-SP solicita a todos os visitantes dos Escritórios Seccionais nessas localidades, usem o acessório e não se esqueçam de agendar,' + 
                    ' neste espaço, o horário em que pretendem comparecer.'
            }
        }));
}

function final(retorno){
    $('#horarios').prop('disabled', false).empty();

    $.each(retorno, function(i, horario) {
        $('#horarios').append($('<option>', { 
            value: horario,
            text : horario 
        }));
    });
}

function importAgendamento(){
    const link = $('#modulo-utils-agendamento').attr('src');

    import(link)
    .then((module) => {
        console.log('Módulo utils-agendamento importado por opcional e carregado.');
        console.log('Local do módulo: ' + link);
        module.inicializa();
    })
    .catch((err) => {
        console.log(err);
        alert('Erro na página! Módulo não carregado! Tente novamente mais tarde!');
    });
}

function editar(){

    importAgendamento();

    $(document).on('AGENDA_RESET', function(e){
        if(e.detail == 'selectServicos')
            $("#idregional").val("");

        $('#horarios').prop('disabled', true)
        .find('option').remove().end()
        .append('<option value="" disabled selected>Selecione o dia do atendimento</option>');
    });

    $(document).on('AGENDA_ERRO', function(){
        $('#horarios').find('option').remove().end()
        .append('<option value="" disabled selected>Falha ao recuperar os dados para o agendamento</option>');
    });

    // ************** Request *********************************************

    $(document).on('AGENDA_GETREGIONAIS', function(e){
        $('#idregional option').show();

        if($('#selectServicos').val() == plantao_txt)
            getDadosAgendamento('RESP_REGIONAIS', "/regionais-plantao-juridico", e.detail);

        avisosServicos();
    });

    $(document).on('AGENDA_GETDIAS AGENDA_GETHORAS', function(e){
        let acao = 'RESP_' + e.type.replace('AGENDA_GET', '');

        getDadosAgendamento(acao, "/dias-horas", e.detail);
    });

    $(document).on('AGENDA_GETDIAS_PJ AGENDA_LOTDIAS_PJ', function(e){
        if(e.type == 'AGENDA_GETDIAS_PJ'){
            e.detail.servico = null;
            getDadosAgendamento('LOTDIAS_PJ', "/dias-horas", e.detail);
            return;
        }

        let datasNovas = validarDtPlantaoJuridico(e.detail.retorno);

        if(datasNovas == null){
            document.dispatchEvent(new CustomEvent("AGENDA_EMPTY_DTPICKER"));
            avisos();
            return;
        }

        document.dispatchEvent(new CustomEvent("AGENDA_OPTIONS_DTPICKER", {
            detail: {minDate: datasNovas[0], maxDate: datasNovas[1]}
        }));

        getDadosAgendamento('RESP_DIAS', "/dias-horas", {
            idregional: $('#idregional').val(), servico: plantao_txt
        });
    });

    // ************** Response ********************************************

    $(document).on('AGENDA_RESP_REGIONAIS', function(e){
        let regionaisAtivas = e.detail.retorno;

        $('#idregional option').each(function(){
            if(($(this).val() != "") && ($.inArray(parseInt($(this).val()), regionaisAtivas) == -1))
                $(this).hide();
        });
    });

    $(document).on('AGENDA_RESP_DIAS', function(e){
        if($('#selectServicos').val() != plantao_txt)
            document.dispatchEvent(new CustomEvent("AGENDA_OPTIONS_DTPICKER", {
                detail: {minDate: +1, maxDate: '+1m'}
            }));

        document.dispatchEvent(new CustomEvent("AGENDA_DTPICKER", {
            detail: {lotados: e.detail.retorno}
        }));
        avisos();
    });

    $(document).on('AGENDA_RESP_HORAS', function(e){
        $.isEmptyObject(e.detail.retorno) ? 
            $('#horarios').prop('disabled', true).empty()
            .append('<option value="" disabled selected>Nenhum horário disponível</option>') : 
            final(e.detail.retorno);
    });

    // ************** Ações ************************************************

    $('#idregional option').show();

    $('.modal-footer').on('click', '#notRC', function(){
        document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
        document.dispatchEvent(new CustomEvent("MSG_GERAL_CONT_TITULO", {
            detail: {
                titulo: 'Atenção', 
                texto: 'Você está no Portal dos Representantes Comerciais, este agendamento é para uso exclusivo à categoria de Representante Comercial'
            }
        }));
    });

    $('#selectServicos').change(function(){
        document.dispatchEvent(new CustomEvent("AGENDA_RESET", {
            detail: this.id
        }));

        document.dispatchEvent(new CustomEvent("AGENDA_GETREGIONAIS", {
            detail: {}
        }));
    });	

    $('#idregional').change(function(){
        let serv = $('#selectServicos').val();

        document.dispatchEvent(new CustomEvent("AGENDA_RESET"));

        if(serv == "")
            return false;

        if($(this).val() == "")
            return false;

        let evento = serv == plantao_txt ? "AGENDA_GETDIAS_PJ" : "AGENDA_GETDIAS";

        document.dispatchEvent(new CustomEvent(evento, {
            detail: {idregional: $(this).val(), servico: serv}
        }));
    });

    $('#datepicker').change(function(){
        let serv = $('#selectServicos').val();

        if(serv == "")
            return false;

        if($("#idregional").val() == "")
            return false;

        document.dispatchEvent(new CustomEvent("AGENDA_RESET", {
            detail: this.id
        }));
        document.dispatchEvent(new CustomEvent("AGENDA_GETHORAS", {
            detail: {idregional: $('#idregional').val(), servico: serv, dia: this.value}
        }));
    });

    if($("#selectServicos option:selected").val() == plantao_txt)
        $('#selectServicos').change();

    if($("#idregional option:selected").val() != "")
        $('#idregional').change();
}

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}

export let scripts_para_importar = {
    modulo: ['utils-agendamento'], 
    local: ['externo/modulos/']
};