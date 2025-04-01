"use strict";

$(document).ready(function(){

    let elemento_init = $('#modulo-init');

    import(elemento_init.attr('src'))
    .then((init) => {
        let subarea = window.location.pathname.search('/representante/') > -1 ? 'restrita-rc' : null;

        init.default('externo', subarea);
        init.opcionais();
        console.log('[MÓDULOS] # Versão dos scripts: ' + elemento_init.attr('class'));
    })
    .catch((err) => {
        console.log(err);
        alert('Erro na página! Módulo não carregado! Tente novamente mais tarde!');
    });

});



/*
**************************************************************************************************************
    Código de verificar participantes.
**************************************************************************************************************
*/

// Funcionalidade Agendamentos Salas Area Restrita RC ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

const idAgendaSala = $('#tempIdSala').length > 0 ? $('#tempIdSala').val() : null;

function formatSalas(response){
    regionaisAtivas = response;
    $('#agendamentoSala #sala_reuniao_id option').each(function(){
        var valor = parseInt($(this).val())
        jQuery.inArray(valor, regionaisAtivas) != -1 ? $(this).show() : $(this).hide();
    });
}

function formatDias(response){
    lotados = response;
    $('#agendamentoSala #datepicker')
        .prop('disabled', false)
        .prop('placeholder', 'dd/mm/aaaa');
}

function formatPeriodos(response, tipo){
    if(!jQuery.isEmptyObject(response['horarios'])) {
        $('#agendamentoSala #periodo').empty();
        $.each(response['horarios'], function(i, periodo) {
            var periodo_texto = periodo.replace(' - ', ' até ');
            periodo_texto = (i == 'manha') || (i == 'tarde') ? 'Período todo: ' + periodo_texto : periodo_texto;
            
            $('#agendamentoSala #periodo').append($('<option>', { 
                value: periodo,
                text : periodo_texto
            }));
        });
        var itens = '';
        $.each(response.itens, function(i, valor) {
            itens += i == 0 ? valor : '&nbsp;&nbsp;&nbsp;<strong>|</strong>&nbsp;&nbsp;&nbsp;' + valor;
        });
        $('#itensShow').html(itens).parent().show();
        $('#agendamentoSala #periodo').prop('disabled', false);
        $('#agendamentoSala #datepicker').css('background-color','#FFFFFF');
        if(tipo == 'reuniao'){
            cleanParticipanteCpf();
            var cont = $('.participante').length;
            if(cont < response.total)
                for (let i = cont; i < response.total; i++)
                    $('#area_participantes').append($('.participante:last').clone());
            $('.participante :input[name="participantes_cpf[]"]').val('').mask('999.999.999-99');
            $('.participante :input[name="participantes_nome[]"]').val('');
            changeParticipanteCpf();
            $('#area_participantes').show();
        }
    }else
        $('#agendamentoSala #periodo')
        .prop('disabled', true)
        .find('option')
        .remove()
        .end()
        .append('<option value="" disabled selected>Nenhum período disponível</option>');
}

function limpaDiasHorariosAgendamentoSala(error = false){
    if(error){
        $('#agendamentoSala #datepicker')
            .val('')
            .prop('disabled', true)
            .prop('placeholder', 'Falha ao recuperar calendário');
            $('#agendamentoSala #periodo')
                .find('option')
                .remove()
                .end()
                .append('<option value="" disabled selected>Falha ao recuperar os dados para o agendamento</option>');
            $("#dialog_agendamento")
                .empty()
                .append("Falha ao recuperar calendário. <br> Por favor verifique se o uso de cookies está habilitado e recarregue a página ou tente mais tarde.");
            $("#dialog_agendamento").dialog({
                draggable: false,
                buttons: [{
                    text: "Recarregar",
                    click: function() {
                        location.reload(true);
                    }
                }]	
            });
        return;
    }
    $('#agendamentoSala #datepicker')
        .val('')
        .prop('disabled', true)
        .prop('placeholder', 'dd/mm/aaaa')
        .css('background-color','#e9ecef');
    $('#agendamentoSala #periodo')
        .find('option')
        .remove()
        .end()
        .append('<option value="" disabled selected>Selecione o dia da reserva de sala</option>');
    $('#agendamentoSala #periodo').prop('disabled', true);
}

function getDadosSalas(acao, tipo, sala_id = '', dia = ''){
    if((tipo != 'reuniao') && (tipo != 'coworking'))
        return false;

    var dados_url = acao == 'getSalas' ? 
    "/admin/salas-reunioes/regionais-salas-ativas/" + tipo : 
    '/admin/salas-reunioes/sala-dias-horas/' + tipo;
    var dados_data = acao == 'getSalas' ? '' : 'sala_id=' + sala_id + '&dia=' + dia;

    $.ajax({
        method: "GET",
        dataType: 'json',
        url: dados_url,
        data: dados_data,
        beforeSend: function(){
            dia == '' ? $('#agendamentoSala #loadCalendario').show() : $('#agendamentoSala #loadHorario').show();
        },
        complete: function(){
            dia == '' ? $('#agendamentoSala #loadCalendario').hide() : $('#agendamentoSala #loadHorario').hide();
        },
        success: function(response) {
            $('#agendamentoSala #itensShow').html('').parent().hide();
            $('#agendamentoSala #area_participantes').hide();
            if(acao == 'getSalas')
                formatSalas(response);
            else if((acao == 'getDias') && (dia == ''))
                formatDias(response);
            else if((acao == 'getDias') && (dia != ''))
                formatPeriodos(response, tipo);
            else 
                $('#agendamentoSala #periodo')
                .find('option')
                .remove()
                .end()
                .append('<option value="" disabled selected>Nenhum período disponível</option>');				
        },
        error: function() {
            limpaDiasHorariosAgendamentoSala(true);
        }
    });
}

if(idAgendaSala == null){
    $('#agendamentoSala #tipo_sala').change(function(){
        $("#sala_reuniao_id").val("");
        if(this.value == "")
            return false;
        limpaDiasHorariosAgendamentoSala();
        getDadosSalas('getSalas', $("#tipo_sala").val());
    });	

    $('#agendamentoSala #sala_reuniao_id').change(function(){
        if($("#tipo_sala").val() == "")
            return false;
        limpaDiasHorariosAgendamentoSala();
        $('#agendamentoSala #sala_reuniao_id option[value=""]').hide();
        getDadosSalas('getDias', $("#tipo_sala").val(), $("#sala_reuniao_id").val());
    });	

    if($("#agendamentoSala #tipo_sala option:selected").val() != "")
        getDadosSalas('getSalas', $("#tipo_sala").val());
    
    if($("#agendamentoSala #sala_reuniao_id option:selected").val() != "")
        getDadosSalas('getDias', $("#tipo_sala").val(), $("#sala_reuniao_id").val());

    $('#agendamentoSala #datepicker').change(function(){
        getDadosSalas('getDias', $("#tipo_sala").val(), $("#sala_reuniao_id").val(), $('#datepicker').val());
    });
}

// verifica a situação do participante junto ao conselho e no Portal

// verifica ao editar
if(idAgendaSala != null)
    changeParticipanteCpf();

function cleanParticipanteCpf(){
    $(".participante:gt(0)").remove();
    $('.participante:eq(0) input[name="participantes_cpf[]"]').off('change.verificaCpf');
    $('.participante:eq(0) input[name="participantes_cpf[]"]').val('');
}

function changeParticipanteCpf(){
    $('input[name="participantes_cpf[]"]').on("change.verificaCpf", function(){
        if(this.value.length == 14){
            if(this.value == $('#partResp').val()){
                $('#verificaSala').modal({backdrop: 'static', keyboard: false, show: true});
                $('#verificaSala .modal-body #cpfIrregular').after('<div id="texto"><br><strong>Não pode inserir o próprio CPF!</strong></div>');
                this.value = "";
                return;
            }
        
            var final = '"participantes_cpf":' + '"' + this.value + '"';
            final = idAgendaSala != null ? final + ', "id":'+ '"' + idAgendaSala + '"' : final;
            $.ajax({
                method: "POST",
                dataType: 'json',
                url: '/representante/agendamento-sala/verificar',
                data: JSON.parse('{"_method":"POST", "_token":"' + $('meta[name="csrf-token"]').attr('content') + '", ' + final + '}'),
                beforeSend: function(){
                    $('#loadingSala').modal({backdrop: 'static', keyboard: false, show: true});
                    $('#loadingSala .modal-body')
                    .html('<div class="spinner-border text-danger"></div><br>Conferindo situação junto ao Conselho e no Portal...');
                },
                complete: function(){
                    $('#loadingSala').modal('hide');
                },
                success: function(response) {
                    $('#loadingSala').modal('hide');
                    if((response.participante_irregular != null) || (response.suspenso != null)){
                        var n_cpf = response.participante_irregular != null ? response.participante_irregular : response.suspenso;
                        var texto_c = 'situação junto ao Conselho';
                        var texto_s = 'suspensão no Portal para novos agendamentos de sala';
                        var texto = '';
                        if((response.participante_irregular != null) && (response.suspenso != null))
                            texto = texto_c + ' e ' + texto_s;
                        else
                            texto = response.participante_irregular != null ? texto_c : texto_s;
                        $('#verificaSala').modal({backdrop: 'static', keyboard: false, show: true});
                        $('#verificaSala .modal-body #cpfIrregular').html(n_cpf);
                        $('#verificaSala .modal-body #cpfIrregular')
                        .after('<div id="texto"><br><strong>Não será possível criar / editar o agendamento com este participante devido ' + texto + '.</strong></div>');
                    }
                },
                error: function() {
                    $('#loadingSala').modal('hide');
                    limpaDiasHorariosAgendamentoSala();
                }
            });
        }
    });
}

$("#verificaSala").on('hidden.bs.modal', function(){
    $('#verificaSala .modal-body #texto').remove();
});

// FIM Funcionalidade Agendamentos Salas Area Restrita RC++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++