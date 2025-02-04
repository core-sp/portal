function formatPeriodos(horarios){

    $('#periodo').prop('disabled', false);
    $.each(horarios, function(indice, periodo) {
        let periodo_texto = periodo.replace(' - ', ' até ');

        periodo_texto = (indice == 'manha') || (indice == 'tarde') ? 'Período todo: ' + periodo_texto : periodo_texto;
        $('#periodo').append($('<option>', { 
            value: periodo,
            text : periodo_texto
        }));
    });
}

function formatItens(_itens){

    let itens = '';
    
    $.each(_itens, function(i, valor) {
        itens += i == 0 ? valor : '&nbsp;&nbsp;&nbsp;<strong>|</strong>&nbsp;&nbsp;&nbsp;' + valor;
    });
    $('#itensShow').html(itens).parent().show();
}

function formatReuniao(total){

    $(".participante:gt(0)").remove();
    let cont = $('.participante').length;

    if(cont < total)
        for (let i = cont; i < total; i++)
            $('#area_participantes').append($('.participante:last').clone());

    $('.participante input[name="participantes_cpf[]"]')[0].dispatchEvent(new CustomEvent("MASK"));
    $('.participante :input[name="participantes_nome[]"]').val('');
    $('#area_participantes').show();
}

function final(retorno, tipo){

    formatPeriodos(retorno.horarios);
    formatItens(retorno.itens);

    if(tipo == 'reuniao')
        formatReuniao(retorno.total);
}

function getDadosSalas(acao, _tipo, url, dados = ''){
    
    $.ajax({
        method: "GET",
        dataType: 'json',
        url: url,
        data: dados,
        beforeSend: function(){
            document.dispatchEvent(new CustomEvent("MSG_GERAL_CARREGAR"));
        },
        success: function(response) {
            console.log(response);
            document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));

            $('#itensShow').html('').parent().hide();
            $('#area_participantes').hide();

            document.dispatchEvent(new CustomEvent("AGENDA_" + acao, {
                detail: {retorno: response, tipo: _tipo}
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

function validarTipo(tipo){
    return (tipo == 'reuniao') || (tipo == 'coworking');
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
        if(e.detail == 'tipo_sala')
            $("#sala_reuniao_id").val("");

        $('#periodo').empty().prop('disabled', true)
        .find('option').remove().end()
        .append('<option value="" disabled selected>Selecione o dia da reserva de sala</option>');
    });

    $(document).on('AGENDA_ERRO', function(){
        $('#periodo').find('option').remove().end()
        .append('<option value="" disabled selected>Falha ao recuperar os dados para o agendamento</option>');
    });

    // ************** Request *********************************************

    $(document).on('AGENDA_GETSALAS', function(e){
        if(!validarTipo(e.detail.tipo))
            return false;

        let url = "/admin/salas-reunioes/regionais-salas-ativas/" + e.detail.tipo;
        getDadosSalas('RESP_SALAS', e.detail.tipo, url);
    });

    $(document).on('AGENDA_GETDIAS', function(e){
        if(!validarTipo(e.detail.tipo))
            return false;

        let url = "/admin/salas-reunioes/sala-dias-horas/" + e.detail.tipo;
        let dados = 'sala_id=' + e.detail.sala + '&dia=';
        getDadosSalas('RESP_DIAS', e.detail.tipo, url, dados);
    });

    $(document).on('AGENDA_GETHORAS', function(e){
        if(!validarTipo(e.detail.tipo))
            return false;

        let url = "/admin/salas-reunioes/sala-dias-horas/" + e.detail.tipo;
        let dados = 'sala_id=' + e.detail.sala + '&dia=' + e.detail.dia;
        getDadosSalas('RESP_HORAS', e.detail.tipo, url, dados);
    });

    // ************** Response ********************************************

    $(document).on('AGENDA_RESP_SALAS', function(e){
        let regionaisAtivas = e.detail.retorno;

        $('#sala_reuniao_id option').each(function(){
            let valor = parseInt($(this).val());
            $.inArray(valor, regionaisAtivas) != -1 ? $(this).show() : $(this).hide();
        });
    });

    $(document).on('AGENDA_RESP_DIAS', function(e){
        document.dispatchEvent(new CustomEvent("AGENDA_DTPICKER", {
            detail: {lotados: e.detail.retorno}
        }));
    });

    $(document).on('AGENDA_RESP_HORAS', function(e){
        $.isEmptyObject(e.detail.retorno.horarios) ? 
            $('#periodo').prop('disabled', true).empty()
            .append('<option value="" disabled selected>Nenhum período disponível</option>') : 
            final(e.detail.retorno, e.detail.tipo);
    });

    // ************** Ações ************************************************

    $('#tipo_sala').change(function(){
        document.dispatchEvent(new CustomEvent("AGENDA_RESET", {
            detail: this.id
        }));

        if(this.value == "")
            return false;

        document.dispatchEvent(new CustomEvent("AGENDA_GETSALAS", {
            detail: {tipo: this.value}
        }));
    });	

    $('#sala_reuniao_id').change(function(){
        if($("#tipo_sala").val() == "")
            return false;

        document.dispatchEvent(new CustomEvent("AGENDA_RESET"));
        document.dispatchEvent(new CustomEvent("AGENDA_GETDIAS", {
            detail: {tipo: $("#tipo_sala").val(), sala: this.value}
        }));
    });

    $('#datepicker').change(function(){
        if($("#tipo_sala").val() == "")
            return false;

        if($("#sala_reuniao_id").val() == "")
            return false;

        document.dispatchEvent(new CustomEvent("AGENDA_RESET", {
            detail: this.id
        }));
        document.dispatchEvent(new CustomEvent("AGENDA_GETHORAS", {
            detail: {tipo: $("#tipo_sala").val(), sala: $("#sala_reuniao_id").val(), dia: this.value}
        }));
    });

    if($("#tipo_sala option:selected").val() != "")
        $('#tipo_sala').change();
}

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}

export let scripts_para_importar = {
    modulo: ['utils-agendamento'], 
    local: ['externo/modulos/']
};