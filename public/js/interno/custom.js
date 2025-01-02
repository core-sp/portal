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



// // Funcionalidade Pre-Registro
// function putDadosPreRegistro(campo, valor, acao)
// {
//     var id = $('[name="idPreRegistro"]').val();

//     $('#modalJustificativaPreRegistro').modal('hide');

//     if(acao == 'justificar'){
//         // evitar de fazer request quando não há justificativa escrita e nem salva
//         if((valor == "") && ($('#' + campo + ' i.fa-times').length > 0))
//             return false;

//         // evitar de fazer request quando não há alteração do valor com o texto salvo
//         if(valor == $('#' + campo + ' .valorJustificativaPR').text())
//             return false;
//     }

//     $("#modalLoadingBody").html('<i class="spinner-border text-info"></i> Salvando');
// 	  $('#modalLoadingPreRegistro').modal({backdrop: "static", keyboard: false, show: true});

//     $.ajax({
//         method: 'POST',
//         headers: {
//           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//         },
//         data: {
//           'acao': acao,
//           'campo': campo,
//           'valor': valor
//         },
//         dataType: 'json',
//         url: '/admin/pre-registros/update-ajax/' + id,
//         cache: false,
//         timeout: 60000,
//         success: function(response) {
//             $("#modalLoadingPreRegistro").modal('hide');
//             if(campo == 'negado')
//               submitNegar();
//             if(acao == 'justificar')
//                 addJustificado(campo, valor);
//             if(acao == 'exclusao_massa')
//               valor.forEach(function(value, index, array){
//                 addJustificado(value, '');
//               });
//             else if(acao == 'editar'){
//                 $("#modalLoadingBody").html('<i class="icon fa fa-check text-success"></i> Salvo');
//                 $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
//                 setTimeout(function() {
//                   $("#modalLoadingPreRegistro").modal('hide');
//                 }, 1200); 
//             }
//             $('#userPreRegistro').text(response['user']);
//             $('#atualizacaoPreRegistro').text(response['atualizacao']);
//             verificaJustificados();
//         },
//         error: function(request, status, error) {
//             var errorFunction = getErrorMsg(request);
//             $("#modalLoadingBody").html('<i class="icon fa fa-times text-danger"></i> ' + errorFunction[0]);
//             $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
//             setTimeout(function() {
//               $("#modalLoadingPreRegistro").modal('hide');
//             }, errorFunction[1]); 
//             console.clear();
//         }
//     });
// }

// function submitNegar(){
//   $('#submitNegarPR').submit();
//   $("#modalLoadingBody").html('<span class="spinner-border text-danger mr-3"></span> Enviando...');
//   $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
// }

// function getErrorMsg(request)
// {
//     var time = 5000;
//     var errorMessage = request.status + ': ' + request.statusText;
//     var nomesCampo = ['campo', 'valor'];
//     if(request.status == 422){
//         for(var nome of nomesCampo){
//           var msg = request.responseJSON.errors[nome];
//           if(msg != undefined)
//             errorMessage = msg[0];
//         }
//         time = 2000;
//     }
//     if(request.status == 401){
//         errorMessage = request.responseJSON.message;
//         time = 2000;
//     }
//     if(request.status == 419){
//         errorMessage = "Sua sessão expirou! Recarregue a página";
//         time = 2000;
//     }
//     if(request.status == 429){
//       var aguarde = request.getResponseHeader('Retry-After');
//       errorMessage = "Excedeu o limite de requisições por minuto.<br>Aguarde " + aguarde + " segundos";
//       time = 2500;
//     }
//     return [errorMessage, time];
// }

// function contJustificativaPR(obj)
// {
//     var total = 500 - obj.val().length;
//     if(total == -1)
//         return;
//     $('#contChar').text(total);
// }

// function addJustificado(campo, valor)
// {
//     if(valor != ""){
//         if($('#' + campo + ' .just').length == 0){
//             $('#' + campo + ' .justificativaPreRegistro').after('<span class="badge badge-warning just ml-2">Justificado</span>');
//             $('#' + campo + ' button.justificativaPreRegistro').removeClass('btn-outline-success').addClass('btn-outline-danger');
//             $('#' + campo + ' button.justificativaPreRegistro').html('<i class="fas fa-edit"></i>');
//         }
//     }else{
//         $('#' + campo + ' .just').remove();
//         $('#' + campo + ' button.justificativaPreRegistro').removeClass('btn-outline-danger').addClass('btn-outline-success');
//         $('#' + campo + ' button.justificativaPreRegistro').html('<i class="fas fa-user-edit"></i>');
//     }

//     $('#' + campo + ' span.valorJustificativaPR').text(valor);
// }

// function confereAnexos()
// {
//     var aprovado = $('.confirmaAnexoPreRegistro:checked').length == $('.confirmaAnexoPreRegistro').length;

//     if(!aprovado){
//       aprovado = true;
//       $('.confirmaAnexoPreRegistro:not(:checked)').each(function() {
//         if(!$(this).hasClass('opcional'))
//           aprovado = false;
//       });
//     }
  
//     return aprovado;
// }

// function habilitarBotoesStatus(justificado)
// {
//     if(justificado > 0){
//         $('button[value="aprovado"], #submitNegarPR button[value="negado"]').addClass('disabled').attr('type', 'button');
//         $('button[value="correcao"]').removeClass('disabled').attr('type', 'submit');
//     }else{
//         $('button[value="aprovado"], #submitNegarPR button[value="negado"]').removeClass('disabled').attr('type', 'submit');
//         $('button[value="correcao"]').addClass('disabled').attr('type', 'button');
//     }
// }

// function verificaJustificados()
// {
//     $('#accordionPreRegistro div.card-body').each(function() {
//         var menu = $(this).parentsUntil('#accordionPreRegistro').find('.menuPR');
//         if($(this).find('.valorJustificativaPR').text().length > 0){
//             if(menu.find('.justMenu').length == 0)
//                 menu.append('<span class="badge badge-sm badge-warning justMenu ml-3">Justificado</span>');
//         }else
//             menu.find('.justMenu').remove();
//     });

//     var justificado = $('.menuPR .justMenu').length;
//     var aprovado = confereAnexos();

//     habilitarBotoesStatus(justificado);
//     if(aprovado && (justificado == 0))
//         $('button[value="aprovado"]').removeClass('disabled').attr('type', 'submit');
//     else
//         $('button[value="aprovado"]').addClass('disabled').attr('type', 'button');
// }

// function verificarArquivo(arquivo){

//   if(arquivo === undefined)
//     return false;
  
// 	if(Math.round((arquivo.size / 1024)) > 2048){
//     $("#modalLoadingBody").html('<i class="icon fa fa-times text-danger"></i> O arquivo deve ter até 2MB de tamanho!');
//     $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
//     setTimeout(function() {
//       $("#modalLoadingPreRegistro").modal('hide');
//     }, 3000);

//     return false;
//   }

// 	return true;
// }

// $('#accordionPreRegistro').ready(function() {
//     verificaJustificados();
// });

// $('.justificativaPreRegistro').click(function() {
//     var campo = this.value;
//     var texto = campo == 'negado' ? '' : $('#' + campo + ' span.valorJustificativaPR').text();

//     if((campo == 'negado') && $('#submitNegarPR button[value="negado"]').hasClass('disabled'))
//         return false;

//     var input = $('#modalJustificativaPreRegistro .modal-body textarea');
//     var titleModal = texto.length > 0 ? ' Editar justificativa' : ' Adicionar justificativa';
//     input.val(texto);
//     contJustificativaPR(input);
//     $('#submitJustificativaPreRegistro').show();
//     $('#submitJustificativaPreRegistro').val(campo);
//     $('#modalJustificativaPreRegistro .modal-title #titulo').text(titleModal);
//     $('#modalJustificativaPreRegistro').modal({backdrop: "static", keyboard: false, show: true});
// });

// $('#modalJustificativaPreRegistro .modal-body textarea').keyup(function() {
//     contJustificativaPR($(this));
// });

// $('#submitJustificativaPreRegistro').click(function() {
//     var campo = this.value;
//     var value = $('#modalJustificativaPreRegistro .modal-body textarea').val();
//     putDadosPreRegistro(campo, value, 'justificar');
// });

// $('.remove_todas_justificativas').click(function() {
//   const campos_array = [];
//   $('#' + this.value + ' .justificativaPreRegistro').each(function(){
//     if($(this).html().trim() == '<i class="fas fa-edit"></i>'){
//       campos_array.push($(this).val());
//     }
//   });

//   if(campos_array.length > 0)
//     putDadosPreRegistro('exclusao_massa', campos_array, 'exclusao_massa');
// });

// $('.textoJustHist').click(function() {
//   $('#modalJustificativaPreRegistro').modal('hide');
//   $("#modalLoadingBody").html('<i class="spinner-border text-info"></i> Carregando');
// 	$('#modalLoadingPreRegistro').modal({backdrop: "static", keyboard: false, show: true});

//   var item = this.innerText;
//   $.ajax({
//     method: 'GET',
//     dataType: 'json',
//     url: this.value,
//     cache: false,
//     timeout: 60000,
//     success: function(response) {
//       $("#modalLoadingPreRegistro").modal('hide');
//       $('#modalJustificativaPreRegistro #submitJustificativaPreRegistro').hide();
//       $('#modalJustificativaPreRegistro .modal-title #titulo').html('Histórico da Justificativa <strong>' + item + ' do dia ' + response.data_hora + '</strong>');
//       $('#modalJustificativaPreRegistro .modal-body textarea').val(response.justificativa);
//       contJustificativaPR($('#modalJustificativaPreRegistro .modal-body textarea'));
//       $('#modalJustificativaPreRegistro').modal({backdrop: "static", keyboard: false, show: true});
//     },
//     error: function(request, status, error) {
//         var errorFunction = getErrorMsg(request);
//         $("#modalLoadingBody").html('<i class="icon fa fa-times text-danger"></i> ' + errorFunction[0]);
//         $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
//         setTimeout(function() {
//           $("#modalLoadingPreRegistro").modal('hide');
//         }, errorFunction[1]); 
//         console.clear();
//     }
//   });
// });

// $('.addValorPreRegistro').click(function() {
//     var campo = this.value;
//     var valor = $(this).parent().find('input').val();
//     putDadosPreRegistro(campo, valor, 'editar');
// });

// $('.confirmaAnexoPreRegistro').change(function() {
//     var campo = $(this).attr('name');
//     var valor = $(this).val();
//     putDadosPreRegistro(campo, valor, 'conferir');
// });

// $('#submitNegarPR button[value="negado"]').click(function(e) {
//     e.preventDefault();
// });

// $('#doc_pre_registro').on('change',function(e){
//   var fileName = e.target.files[0].name;
//   $(this).next('.custom-file-label').html(fileName);
//   verificarArquivo(e.target.files[0]);
// });

// $('#form-anexo-docs').submit(function(e){
//   let pode_enviar = verificarArquivo($('#doc_pre_registro')[0].files[0]);
//   let possui_doc = $('[name="tipo"]:checked').length > 0;

//   if(!possui_doc){
//     $("#modalLoadingBody").html('<i class="icon fa fa-times text-danger"></i> Deve selecionar o tipo de documento!');
//     $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
//     setTimeout(function() {
//       $("#modalLoadingPreRegistro").modal('hide');
//     }, 3000);
//   }

//   if((pode_enviar === false) || !possui_doc)
//     e.preventDefault();
// });

// $('.link-tab-rt').click(function(){
// 	$("#accordionPreRegistro #parte_contato_rt.collapse").collapse('show');
// });

// $('button[value="aprovado"]').click(function(){
// 	$("#modalLoadingBody").html('<span class="spinner-border text-success mr-3"></span> Enviando...');
//   $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
// });

// // Fim da Funcionalidade Pre-Registro
