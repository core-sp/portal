"use strict";

$(document).ready(function(){

    let elemento_init = $('#modulo-init');

    import(elemento_init.attr('src'))
    .then((init) => {
        init.default();
        init.opcionais();
        console.log('[MÓDULOS] # Versão dos scripts: ' + elemento_init.attr('class'));
    })
    .catch((err) => {
        console.log(err);
        alert('Erro na página! Módulo não carregado! Tente novamente mais tarde!');
    });

});

// Funcionalidade Sala Reunião / Agendados / Criar agendamento

// function verificarDadosCriarAgendaSala(nome_campo, valor){
//     var final;
//     switch(nome_campo) {
//       case "cpf_cnpj":
//         final = '"' + nome_campo + '":' + '"' + $('#criarAgendaSala input[name="cpf_cnpj"]').val() + '"';
//         break;
//       case "sala_reuniao_id":
//         final = '"' + nome_campo + '":' + '"' + $('#criarAgendaSala select[name="sala_reuniao_id"]').val();
//         final = final + '", "tipo_sala":"' + $('select[name="tipo_sala"]').val() + '"';
//         break;
//       case "participantes_cpf":
//         final = '"participantes_cpf":' + '"' + valor + '"';
//         break;
//       default:
//         var cpfs = [$('#criarAgendaSala input[name="cpf_cnpj"]').val()];
//         $('#criarAgendaSala :input[name="participantes_cpf[]"]').each(function() {
//           if($(this).val().length == 14)
//             cpfs.push(this.value);
//         });
//         final = '"' + nome_campo + '":' + JSON.stringify(cpfs);
//     }
  
//     var json = JSON.parse('{"_method":"POST", "_token":"' + $('meta[name="csrf-token"]').attr('content') + '", ' + final + '}');
    
//     $.ajax({
//       method: "POST",
//       dataType: 'json',
//       url: '/admin/salas-reunioes/agendados/verifica',
//       data: json,
//       beforeSend: function(){
//         if((nome_campo == "cpf_cnpj") || (nome_campo == "participantes_cpf"))
//           $('#modal-load-criar_agenda').modal({backdrop: 'static', keyboard: false, show: true});
//       },
//       complete: function(){
//         $('#modal-load-criar_agenda').modal('hide');
//       },
//       success: function(response) {
//         $('#modal-load-criar_agenda').modal('hide');
//         switch(nome_campo) {
//           case "cpf_cnpj":
//             var situacao = response.situacaoGerenti != null ? response.situacaoGerenti.substring(0, response.situacaoGerenti.indexOf(',')) : 'Ativo';
//             var ver_registro = (response.registroGerenti == null) || (situacao != 'Ativo');
//             $.each(response, function(i, valor) {
//               $('#' + i).text(valor);
//             });
//             $('#area_gerenti').show();
//             $('#cpfResponsavel').val($('#criarAgendaSala input[name="cpf_cnpj"]').val());
//             $('#nomeResponsavel').val(response['nomeGerenti']);
//             if(ver_registro || (response.suspenso != null)){
//                           var texto_c = response.registroGerenti == null ? 'sem registro no Gerenti não pode criar o agendamento' : 'sem registro Ativo no Gerenti não pode criar o agendamento';
//                           var texto_s = 'está suspenso no Portal para novos agendamentos de sala pela área restrita do representante';
//                           var texto = '';
//                           if(ver_registro && (response.suspenso != null))
//                               texto = texto_c + ' e ' + texto_s;
//                           else
//                               texto = (response.registroGerenti == null) || (situacao != 'Ativo') ? texto_c : texto_s;
//               $('#modal-criar_agenda').modal({backdrop: 'static', keyboard: false, show: true});
//               $('.modal-footer').hide();
//               $('#modal-criar_agenda .modal-body')
//               .html('<strong><i>Situação:</i> ' + texto + '.</strong>');
//             }
//             break;
//           case "sala_reuniao_id":
//             cleanParticipanteCpf();
//             if(response.total_participantes <= 0){
//               $('#area_participantes').hide();
//               $('#modal-criar_agenda').modal({backdrop: 'static', keyboard: false, show: true});
//               $('.modal-footer').hide();
//               $('#modal-criar_agenda .modal-body')
//               .html('<strong>A regional não está com a Sala de '+ 
//               $('select[name="tipo_sala"] option:selected').text() +' habilitada! Não pode criar o agendamento.</strong>');
//               return;
//             }
//             if((response.total_participantes > 0) && ($('select[name="tipo_sala"]').val() == 'reuniao')){
//               for (let i = 1; i < response.total_participantes; i++)
//                 $('#area_participantes').append($('.participante:last').clone());
//               $('.participante :input[name="participantes_cpf[]"]').val('').mask('999.999.999-99');
//               $('.participante :input[name="participantes_nome[]"]').val('');
//               changeParticipanteCpf();
//               $('#area_participantes').show();
//             }
//             break;
//           case "participantes_cpf":
//             if((response.participante_irregular != null) || (response.suspenso != null)){
//                           var n_cpf = response.participante_irregular != null ? response.participante_irregular : response.suspenso;
//                           var texto_c = 'está ativo, porém não está em dia no Gerenti';
//                           var texto_s = 'está suspenso no Portal para novos agendamentos de sala pela área restrita do representante';
//                           var texto = '';
//                           if((response.participante_irregular != null) && (response.suspenso != null))
//                               texto = texto_c + ' e ' + texto_s;
//                           else
//                               texto = response.participante_irregular != null ? texto_c : texto_s;
//                           $('#modal-criar_agenda').modal({backdrop: 'static', keyboard: false, show: true});
//               $('.modal-footer').hide();
//                           $('#modal-criar_agenda .modal-body')
//                           .html(n_cpf + '<br><strong>Participante ' + texto + '.</strong>');
//                       }
//             break;
//           default:
//             if(response.suspenso == ''){
//               $('#criarAgendaSala').submit();
//               return;
//             }
//             $('#modal-criar_agenda').modal({backdrop: 'static', keyboard: false, show: true});
//             $('.modal-footer').show();
//             $('#modal-criar_agenda .modal-body')
//             .html(response.suspenso + '<br><br><strong>Confirmar esse agendamento?</strong>');
//         }
//       },
//       error: function() {
//         $('#modal-criar_agenda').modal({backdrop: 'static', keyboard: false, show: true});
//         $('.modal-footer').hide();
//         $('#modal-criar_agenda .modal-body')
//         .html('<span class="text-danger">Deu erro! Recarregue a página.</span>');
//       }
//     });
//   }
  
//   $('#criarAgendaSala').ready(function(){
//     var tam = $('#criarAgendaSala input[name="cpf_cnpj"]').length > 0 ? $('#criarAgendaSala input[name="cpf_cnpj"]').val().length : 0;
//     if((tam == 14) || (tam == 18)){
//       $('select[name="sala_reuniao_id"]').prop('disabled', false);
//       verificarDadosCriarAgendaSala("cpf_cnpj");
//     }else{
//       $('select[name="sala_reuniao_id"]').prop('disabled', true);
//       $('select[name="tipo_sala"]').prop('disabled', true);
//     }
  
//     $('input[name="cpf_cnpj"]').change(function(){
//       var tamanho = $('#criarAgendaSala input[name="cpf_cnpj"]').val().length;
//       if((tamanho == 14) || (tamanho == 18)){
//         $('select[name="sala_reuniao_id"]').prop('disabled', false);
//         verificarDadosCriarAgendaSala("cpf_cnpj");
//       }else{
//         $('select[name="sala_reuniao_id"]').prop('disabled', true);
//         $('select[name="tipo_sala"]').prop('disabled', true);
//       }
//     });
    
//     $('select[name="sala_reuniao_id"]').change(function(){
//       if(this.value == ""){
//         cleanParticipanteCpf();
//         $('select[name="tipo_sala"]').prop('disabled', true);
//         return;
//       }
//       verificarDadosCriarAgendaSala("sala_reuniao_id");
//       $('select[name="tipo_sala"]').prop('disabled', false);
//     });
  
//     $('select[name="tipo_sala"]').change(function(){
//       if(this.value != 'reuniao')
//         $('#area_participantes').hide();
//       verificarDadosCriarAgendaSala("sala_reuniao_id");
//     });
  
//     $('#verificaSuspensos').click(function(){
//       verificarDadosCriarAgendaSala("participantes_cpf[]");
//     });
  
//     $('select[name="periodo_entrada"]').change(function(){
//       var valor = this.value;
//       var indice = 0;
//       if(valor != '')
//         $('select[name="periodo_saida"] option').each(function(i) {
//           $(this).val() <= valor ? $(this).hide() : $(this).show();
//           indice = $(this).val() == valor ? i + 1 : indice;
//         });
//         $('select[name="periodo_saida"] option:eq(' + indice + ')').prop('selected', true);
//     });
//   });
  
//   function cleanParticipanteCpf(){
//     $(".participante:gt(0)").remove();
//     $('.participante:eq(0) input[name="participantes_cpf[]"]').off('change.verificaCpf');
//     $('.participante:eq(0) input[name="participantes_cpf[]"]').val('');
//   }
  
//   function changeParticipanteCpf(){
//     $('input[name="participantes_cpf[]"]').on("change.verificaCpf", function(){
//       if(this.value.length == 14){
//         if(this.value == $('input[name="cpf_cnpj"]').val()){
//           $('#modal-criar_agenda').modal({backdrop: 'static', keyboard: false, show: true});
//           $('.modal-footer').hide();
//           $('#modal-criar_agenda .modal-body').html('<strong>Não pode inserir CPF do representante responsável!</strong>');
//           this.value = "";
//           return;
//         }
//         verificarDadosCriarAgendaSala("participantes_cpf", this.value);
//       }
//     });
//   }
  
//   $('#enviarCriarAgenda').click(function(){
//     $('#criarAgendaSala').submit();
//   });
  
  // FIM Funcionalidade Sala Reunião / Agendados / Criar agendamento
