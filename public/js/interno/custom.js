"use strict";

import($('#modulo-init').attr('src'))
.then((init) => {
    init.default();
    init.opcionais();
})
.catch((err) => {
    console.log(err);
    alert('Erro na página! Módulo não carregado! Tente novamente mais tarde!');
});

// Funcionalidade Sala Reunião

function setCampoHorariosSala(sala)
{
  $('#horariosBloqueio option').show();
  $('#horariosBloqueio option').each(function(){
    var valor = $(this).val();
    if(jQuery.inArray(valor, sala) != -1)
      $(this).show();
    else
      $(this).hide();
  });
}

function ajaxSalaBloqueio(valor)
{
  $.ajax({
    method: "GET",
    data: {
      "id": valor,
    },
    dataType: 'json',
    url: "/admin/salas-reunioes/bloqueios/horarios-ajax",
    success: function(response) {
      sala = response;
      setCampoHorariosSala(sala);
    },
    error: function() {
      alert('Erro ao carregar os horários. Recarregue a página.');
    }
  });
}

$('#salaBloqueio').ready(function(){
  var valor = $('#salaBloqueio').val();
    if(valor > 0)
      ajaxSalaBloqueio(valor);
});

$('#salaBloqueio').change(function(){
  var valor = $('#salaBloqueio').val();
  if(valor > 0)
    ajaxSalaBloqueio(valor);
});
// Fim da Funcionalidade Sala Reunião

(function($){
  
  // Funcionalidade Simulador Refis
  $(document).on('change', ".nParcela", function() {
		var id = $(this).attr('id');
		var nParcela = parseFloat($('option:selected',this).attr('value'));
		var total = parseFloat($('#total' + id).attr('value'));
		var valorParcelado =  (total/nParcela).toFixed(2);

		$('#parcelamento' + id).attr('value', valorParcelado);
		$('#parcelamento' + id).html('R$ ' + valorParcelado.replace('.', ','));
	});
  // Fim da Funcionalidade Simulador Refis

})(jQuery);

// Funcionalidade Sala de Reunião +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

function mesaIgualParticipantesReuniao()
{
  var input = $('input[name="participantes_reuniao"]');
  var com_itens = "Mesa com " + input.val() + " cadeira(s)";

  var adicionado = $('#itens_reuniao option[value^="Mesa com "]');
  if(adicionado.length > 0)
    adicionado.val(com_itens).text(com_itens);
}

$("#itens_reuniao, #itens_coworking").on('dblclick', 'option', function(){
  var texto = this.text;
  var valor = this.value;
  var numero = texto.replace(/[^0-9_,]/ig, '');
  if(numero.trim().length > 0){
    $('#sala_reuniao_itens').modal({backdrop: 'static', keyboard: false, show: true});
    $('#sala_reuniao_itens')
    .removeClass('itens_reuniao')
    .removeClass('itens_coworking')
    .addClass($(this).parent().attr('id'));
    $('#sala_reuniao_itens .modal-body')
    .html(texto.substr(0, texto.search(numero)) + ' <input type="text" id="'+ valor +'">' + texto.substr(texto.search(numero) + numero.length, texto.length));
  }
});

$('#editar_item').click(function(){
  var tipo = $('#sala_reuniao_itens').hasClass('itens_reuniao') ? 'itens_reuniao' : 'itens_coworking';
  var id = $('#sala_reuniao_itens .modal-body input').attr('id');
  var valor = $('#sala_reuniao_itens .modal-body input').val();
  var texto = $('#' + tipo + ' option[value="' + id + '"]').text();
  var numero = texto.replace(/[^0-9_,]/ig, '');
  texto = texto.replace(numero, valor);
  $('#sala_reuniao_itens .modal-body input').remove();
  $('#' + tipo + ' option[value="' + id + '"]').val(texto).text(texto);
  $('#sala_reuniao_itens').modal('hide');
});

$('button.addItem, button.removeItem').click(function(){
  var tipo = (this.id == 'btnAddReuniao') || (this.id == 'btnRemoveReuniao') ? 'reuniao' : 'coworking';
  var itens = $(this).hasClass('addItem') ? $('#todos_itens_' + tipo + ' option:selected') : $('#itens_' + tipo + ' option:selected');
  var opcao = $(this).hasClass('addItem') ? 'add' : 'remove';
  itens.each(function() {
    if(opcao == 'add')
      $('#itens_' + tipo).append('<option value="' + this.value + '">' + this.text +'</option>');
    else{
      var texto = this.text;
      var numero = texto.replace(/[^0-9_,]/ig, '');
      texto = numero.trim().length > 0 ? texto.replace(numero, '_') : texto;
      $('#todos_itens_' + tipo).append('<option value="' + texto + '">' + texto +'</option>');
    }
    $(this).remove();
  });

  mesaIgualParticipantesReuniao();
});

$('#form_salaReuniao input[name="participantes_reuniao"]').change(function(){
  mesaIgualParticipantesReuniao();
});

$('#form_salaReuniao button[type="submit"]').click(function(){
  $('#itens_reuniao option').prop('selected', true);
  $('#itens_coworking option').prop('selected', true);
});

function hideShowHorasLimitesSala()
{
  var hide_proxima_hora = false;
  $('#horarios_reuniao option, #horarios_coworking option').each(function(){
    if(this.value == $('#hora_limite_final_manha').val() || hide_proxima_hora){
      $(this).hide().prop('selected', false);
      hide_proxima_hora = hide_proxima_hora ? false : true;
    }else if(this.value >= $('#hora_limite_final_tarde').val())
      $(this).hide().prop('selected', false);
    else
      $(this).show();
  });
}

function ajaxHorariosViewSala(id)
{
  const selectedValues = Array.from($('#' + id + ' option:selected')).map(
    option => option.value,
  );

  $.ajax({
    type: "POST",
    data: {
      _method: "POST",
      _token: $('meta[name="csrf-token"]').attr('content'),
      horarios: selectedValues,
      hora_limite_final_manha: $('#hora_limite_final_manha').val(),
      hora_limite_final_tarde: $('#hora_limite_final_tarde').val(),
    },
    dataType: 'json',
    url: "/admin/salas-reunioes/sala-horario-formatado/" + $('#valor_id').val(),
    success: function(response) {
      $('#' + id + '_rep').html(response);
    },
    error: function() {
      alert('Erro ao carregar os horários formatados. Recarregue a página.');
    }
  });
}

$('#form_salaReuniao #hora_limite_final_manha, #form_salaReuniao #hora_limite_final_tarde').ready(function(){
    hideShowHorasLimitesSala();
});

$('#form_salaReuniao #hora_limite_final_manha, #form_salaReuniao #hora_limite_final_tarde').change(function(){
  hideShowHorasLimitesSala();
  ajaxHorariosViewSala('horarios_reuniao');
  ajaxHorariosViewSala('horarios_coworking');
});

$('#form_salaReuniao #horarios_reuniao, #form_salaReuniao #horarios_coworking').change(function(){
  ajaxHorariosViewSala(this.id);
});

// FIM da Funcionalidade Sala de Reunião ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

// Funcionalidade Curso +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

function desabilitaHabilitaCampoAdd(valor){
  var desabilita = valor == '0';
  var obr = !desabilita;
  if(desabilita)
      $('select[name="campo_rotulo"] option[value=""]').prop('selected', true);
  $('select[name="campo_rotulo"], select[name="campo_required"]').prop('disabled', desabilita).prop('required', obr);
}

$('select[name="add_campo"]').ready(function(){
  if($('select[name="add_campo"]').length > 0)
    desabilitaHabilitaCampoAdd($('select[name="add_campo"]').val());
});

$('select[name="add_campo"]').change(function(){
  desabilitaHabilitaCampoAdd($(this).val());
});

// Fim Funcionalidade Curso +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

// Funcionalidade Sala Reunião / Agendados / Criar agendamento

function verificarDadosCriarAgendaSala(nome_campo){
  var final;
  switch(nome_campo) {
    case "cpf_cnpj":
      final = '"' + nome_campo + '":' + '"' + $('#criarAgendaSala input[name="cpf_cnpj"]').val() + '"';
      break;
    case "sala_reuniao_id":
      final = '"' + nome_campo + '":' + '"' + $('#criarAgendaSala select[name="sala_reuniao_id"]').val();
      final = final + '", "tipo_sala":"' + $('select[name="tipo_sala"]').val() + '"';
      break;
    default:
      var cpfs = [$('#criarAgendaSala input[name="cpf_cnpj"]').val()];
      $('#criarAgendaSala :input[name="participantes_cpf[]"]').each(function() {
        if($(this).val().length == 14)
          cpfs.push(this.value);
      });
      final = '"' + nome_campo + '":' + JSON.stringify(cpfs);
  }

  var json = JSON.parse('{"_method":"POST", "_token":"' + $('meta[name="csrf-token"]').attr('content') + '", ' + final + '}');
  
  $.ajax({
    method: "POST",
    dataType: 'json',
    url: '/admin/salas-reunioes/agendados/verifica',
    data: json,
    beforeSend: function(){
      if(nome_campo == "cpf_cnpj")
        $('#modal-load-criar_agenda').modal({backdrop: 'static', keyboard: false, show: true});
    },
    complete: function(){
      $('#modal-load-criar_agenda').modal('hide');
    },
    success: function(response) {
      $('#modal-load-criar_agenda').modal('hide');
      switch(nome_campo) {
        case "cpf_cnpj":
          var resultado = response.situacaoGerenti;
          var situacao = resultado != null ? resultado.substring(0, resultado.indexOf(',')) : 'Ativo';
          $.each(response, function(i, valor) {
            $('#' + i).text(valor);
          });
          $('#area_gerenti').show();
          $('#cpfResponsavel').val($('#criarAgendaSala input[name="cpf_cnpj"]').val());
          $('#nomeResponsavel').val(response['nomeGerenti']);
          if(response.registroGerenti == null){
            $('#modal-criar_agenda').modal({backdrop: 'static', keyboard: false, show: true});
            $('.modal-footer').hide();
            $('#modal-criar_agenda .modal-body')
            .html('<strong>Sem registro no Gerenti! Não pode criar o agendamento.</strong>');
          }else if(situacao != 'Ativo'){
            $('#modal-criar_agenda').modal({backdrop: 'static', keyboard: false, show: true});
            $('.modal-footer').hide();
            $('#modal-criar_agenda .modal-body')
            .html('<strong>Sem registro Ativo no Gerenti! Não pode criar o agendamento.</strong>');
          }
          break;
        case "sala_reuniao_id":
          $(".participante:gt(0)").remove();
          if(response.total_participantes <= 0){
            $('#area_participantes').hide();
            $('#modal-criar_agenda').modal({backdrop: 'static', keyboard: false, show: true});
            $('.modal-footer').hide();
            $('#modal-criar_agenda .modal-body')
            .html('<strong>A regional não está com a Sala de '+ 
            $('select[name="tipo_sala"] option:selected').text() +' habilitada! Não pode criar o agendamento.</strong>');
            return;
          }
          if((response.total_participantes > 0) && ($('select[name="tipo_sala"]').val() == 'reuniao')){
            for (let i = 1; i < response.total_participantes; i++)
              $('#area_participantes').append($('.participante:last').clone());
            $('.participante :input[name="participantes_cpf[]"]').val('').unmask().mask('999.999.999-99');
            $('.participante :input[name="participantes_nome[]"]').val('');
            $('#area_participantes').show();
          }
          break;
        default:
          if(response.suspenso == ''){
            $('#criarAgendaSala').submit();
            return;
          }
          $('#modal-criar_agenda').modal({backdrop: 'static', keyboard: false, show: true});
          $('.modal-footer').show();
          $('#modal-criar_agenda .modal-body')
          .html(response.suspenso + '<br><br><strong>Confirmar esse agendamento?</strong>');
      }
    },
    error: function() {
      $('#modal-criar_agenda').modal({backdrop: 'static', keyboard: false, show: true});
      $('.modal-footer').hide();
      $('#modal-criar_agenda .modal-body')
      .html('<span class="text-danger">Deu erro! Recarregue a página.</span>');
    }
  });
}

$('#criarAgendaSala').ready(function(){
  var tam = $('#criarAgendaSala input[name="cpf_cnpj"]').length > 0 ? $('#criarAgendaSala input[name="cpf_cnpj"]').val().length : 0;
  if((tam == 14) || (tam == 18))
      verificarDadosCriarAgendaSala("cpf_cnpj");

  $('input[name="cpf_cnpj"]').change(function(){
    var tamanho = $('#criarAgendaSala input[name="cpf_cnpj"]').val().length;
    if((tamanho == 14) || (tamanho == 18))
      verificarDadosCriarAgendaSala("cpf_cnpj");
  });
  
  $('select[name="sala_reuniao_id"]').change(function(){
    if(this.value == "")
      $(".participante:gt(0)").remove();
    verificarDadosCriarAgendaSala("sala_reuniao_id");
  });

  $('select[name="tipo_sala"]').change(function(){
    if(this.value != 'reuniao')
      $('#area_participantes').hide();
    verificarDadosCriarAgendaSala("sala_reuniao_id");
  });

  $('#verificaSuspensos').click(function(){
    verificarDadosCriarAgendaSala("participantes_cpf[]");
  });

  $('select[name="periodo_entrada"]').change(function(){
    var valor = this.value;
    var indice = 0;
    if(valor != '')
      $('select[name="periodo_saida"] option').each(function(i) {
        $(this).val() <= valor ? $(this).hide() : $(this).show();
        indice = $(this).val() == valor ? i + 1 : indice;
      });
      $('select[name="periodo_saida"] option:eq(' + indice + ')').prop('selected', true);
  });
});

$('#enviarCriarAgenda').click(function(){
  $('#criarAgendaSala').submit();
});

// FIM Funcionalidade Sala Reunião / Agendados / Criar agendamento

// Funcionalidade Home Imagem / Itens Home

var caminho = '';
var openStorage_id = '';
var folder_name = '';
const PastaItensHomePrincipal = 'img/';

function preencheTabelaPath(value, index, array) {
  var href_path = caminho + value;
  var texto_html = '<div class="card-body text-center pt-0 pl-0 pr-0"><div class="card-img-top"><a href="/' + href_path + '" target="_blank" rel="noopener" data-toggle="lightbox" data-gallery="itens_home_storage"><img src="/' + href_path + '"></a></div><br>';
  texto_html += '<button class="btn btn-link text-break storagePath" value="' + href_path + '">' + value + '</button><br>';
  texto_html += '<hr><a href="/admin/imagens/itens-home/armazenamento/download/' + folder_name + '/' + value + '" class="btn btn-sm btn-primary mr-2"><i class="fas fa-download"></i></a>';
  texto_html += caminho == PastaItensHomePrincipal ? '</div>' : '<button class="btn btn-sm btn-danger deleteFileStorage" type="button" value="' + value + '"><i class="fas fa-trash"></i></button></div>';
  $('#armazenamento #cards').append('<div class="card storageFile w-100 border border-primary"></div>');
  $('#armazenamento #cards .storageFile:last').append(texto_html);
}

$(document).ready(function(){
  $("#filtrarFile").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#cards .card .storagePath").filter(function() {
      $(this).parent().parent().toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});

$('.openStorage').click(function(){
  openStorage_id = $(this).parent().parent().find('input').attr('id');
  receberArquivos(openStorage_id, null);
});

$('.openStoragePasta').click(function(){
  var pasta = this.value == "" ? null : this.value;
  $('.openStoragePasta').attr('disabled', false);
  $(this).attr('disabled', true);
  receberArquivos(openStorage_id, pasta);
});

$("#armazenamento").on('hidden.bs.modal', function(){
  $('#armazenamento #msgStorage').hide();
  $("#filtrarFile").val('');
  cleanTabelaStorage();
});

$("#armazenamento").on('shown.bs.modal', function () {
  $('.openStoragePasta[value=""]').attr('disabled', true);
  $('.openStoragePasta[value!=""]').attr('disabled', false);
});

$("#header_fundo_cor").change(function() {
  $('#header_fundo').val('');
  $('#header_fundo').attr('placeholder', 'Cor selecionada');
});

$("#header_fundo_cor").ready(function() {
  if($("#header_fundo_default").prop('checked') || ($("#header_fundo").val() != ""))
    return;
  $('#header_fundo').val('');
  $('#header_fundo').attr('placeholder', 'Cor selecionada');
});

$("#header_fundo_default").change(function() {
  if(!this.checked){
    $('#header_fundo').attr('placeholder', '');
  }else{
    $('#header_fundo').val('');
    $('#header_fundo').attr('placeholder', 'Imagem padrão escolhida');
  }
});

$('#popup_video_vazio, #popup_video_default').change(function() {
  if(this.checked)
    $('#popup_video_novo').val('');
});

$('#armazenamento #file_itens_home').change(function(e){
  if($(this).val() == '')
    return;

  var form = new FormData();
  form.append('_method', "POST");
  form.append('_token', $('meta[name="csrf-token"]').attr('content'));
  form.append('file_itens_home', e.target.files[0]);

  $.ajax({
    method: "POST",
    data: form,
    contentType : false,
		processData : false,
    url: "/admin/imagens/itens-home/armazenamento",
    success: function(response) {
      if(response.novo_arquivo != null){
        $('#armazenamento .custom-file-label').text('Selecionar arquivo...');
        receberArquivos(openStorage_id, null);
        $('#armazenamento #msgStorage').removeClass('alert-danger')
        .addClass('alert-success').html('Arquivo <strong><i>"' + response.novo_arquivo + '"</i></strong> foi adicionado da pasta!').show();
        $('.openStoragePasta[value=""]').attr('disabled', true);
        $('.openStoragePasta[value!=""]').attr('disabled', false);
      }
    },
    error: function(xhr) {
      var txt = xhr.status == 422 ? xhr.responseJSON.errors.file_itens_home[0] : 'Erro ao adicionar o arquivo. Recarregue a página.';
      $('#armazenamento #msgStorage').removeClass('alert-success')
      .addClass('alert-danger').html(txt).show();
      $('#armazenamento .custom-file-label').text('Selecionar arquivo...');
    }
  });
});

function cleanTabelaStorage()
{
  $('#armazenamento .card-columns .card').remove();
}

function receberArquivos(id, pasta){
  $.ajax({
    method: "GET",
    data: {},
    dataType: 'json',
    url: pasta == null ? "/admin/imagens/itens-home/armazenamento" : "/admin/imagens/itens-home/armazenamento/" + pasta,
    success: function(response) {
      cleanTabelaStorage();
      caminho = response.caminho;
      folder_name = response.folder;
      response.path.forEach(preencheTabelaPath);
      eventClickSelecionar(id);
      eventClickExcluir();
    },
    error: function() {
      alert('Erro ao carregar os arquivos. Recarregue a página.');
    }
  });
}

function eventClickSelecionar(id){
  $('.storagePath').on('click', function(){
    var linha = this.value;
    $('#' + id).val(linha);
    $("#armazenamento").modal("hide");
  });
}

function eventClickExcluir(){
  if(caminho == PastaItensHomePrincipal)
    return;
  $('.deleteFileStorage').on('click', function(){
    $('#confirmDelete #confirmFile').text(this.value);
    $('#confirmDelete #deleteFileStorage').val(this.value);
    $('#confirmDelete').modal({backdrop: 'static', keyboard: false, show: true});
  });
}

$('#deleteFileStorage').on('click', function(){
  var arquivo = this.value;
  $('#confirmDelete').modal('hide');
  $.ajax({
    method: "POST",
    data: {
      _method: "DELETE",
      _token: $('meta[name="csrf-token"]').attr('content'),
    },
    dataType: 'json',
    url: "/admin/imagens/itens-home/armazenamento/delete-file/" + arquivo,
    success: function(response) {
      if(response != 'Não foi removido.'){
        $('.deleteFileStorage[value="' + arquivo + '"]').parent().parent().remove();
        $('#armazenamento #msgStorage').removeClass('alert-danger')
        .addClass('alert-success').html('Arquivo <strong><i>"' + arquivo + '"</i></strong> foi removido da pasta!').show();
      }else{
        $('#armazenamento #msgStorage').removeClass('alert-success')
        .addClass('alert-danger').html('Arquivo <strong><i>"' + arquivo + '"</i></strong> NÃO foi removido da pasta!').show();
      }
      $('#armazenamento .modal-body').scrollTop(0);
    },
    error: function() {
      $('#armazenamento #msgStorage').removeClass('alert-success')
        .addClass('alert-danger').html('Erro ao excluir o arquivo <strong><i>"' + arquivo + '"</i></strong>. Recarregue a página.').show();
        $('#armazenamento .modal-body').scrollTop(0);
    }
  });
});

// FIM Funcionalidade Home Imagem / Itens Home

// Funcionalidade GerarTexto ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

function crudGerarTexto(acao, objeto){

  $('#avisoTextos').modal('hide');
  var token = $('meta[name="csrf-token"]').attr('content');
  var id = $(objeto).val();
  var textoLoading = '';
  var link = '';
  var metodo = '';
  var dados = '';

  switch(acao) {
    case 'carregar':
      textoLoading = '<div class="spinner-border text-primary"></div>&nbsp;&nbsp;Carregando texto...';
      link = '/admin/textos/' + $('#tipo_doc').val() + '/' + id;
      metodo = 'GET';
      dados = {};
      break;
    case 'atualizar':
      textoLoading = '<div class="spinner-border text-primary"></div>&nbsp;&nbsp;Atualizando campos...';
      link = '/admin/textos/' + $('#tipo_doc').val() + '/' + id;
      metodo = 'POST';
      dados = {
        _token: token,
        tipo: $('#tipo').val(),
        texto_tipo: $('#texto_tipo').val(),
        com_numeracao: $('#com_numeracao').val(),
        nivel: $('#nivel').val(),
        conteudo: tinymce.get('conteudo').getContent(),
      };
      break;
    case 'excluir_varios':
      textoLoading = '<div class="spinner-border text-primary"></div>&nbsp;&nbsp;Excluindo textos...';
      link = '/admin/textos/' + $('#tipo_doc').val() + '/excluir';
      metodo = 'DELETE';
      dados = {
        _token: token,
        excluir_ids: objeto.val()
      };
      break;
  }

	$.ajax({
      url: link,
      method: metodo,
      dataType: "json",
      data: dados,
      beforeSend: function(){
        $('#loadingIndice').modal({backdrop: 'static', keyboard: false, show: true});
        $('#loadingIndice .modal-body').html(textoLoading);
      },
      complete: function(){
        $('#loadingIndice').modal('hide');
      },
      success: function(response) {
        $('.updateCampos').val(id);
        $('.deleteTexto').val(id);
        atualizarTextoCrud(acao, response);
        gerarTextoAvisosCrud(acao, response, id);
      },
      error: function(erro, textStatus, errorThrown) {
        var resposta = erro.status == 422 ? JSON.stringify(erro.responseJSON.errors) : 
        'Código: ' + erro.status + ' | Mensagem: ' + erro.responseJSON.message;
        gerarTextoAvisosCrud('erro', resposta, null);
      }
  });
}

function atualizarTextoCrud(acao, response){
  var texto_campo = acao == 'carregar' ? response[0].tipo : response.tipo;
  var cor = texto_campo == 'Título' ? 'warning' : 'dark';
  var upper = texto_campo == 'Título' ? 'text-uppercase' : '';

  if(acao == 'carregar'){
    var resultado = response[0];
    var indice = resultado.indice != null ? resultado.indice : '';
    $('#span-tipo').attr('class', 'text-' + cor).text(texto_campo);
    $('#span-nivel').text(resultado.nivel);
    $('#span-texto_tipo').attr('class', upper).text(indice + ' - ' + resultado.texto_tipo);
    $('#texto_tipo').val(resultado.texto_tipo);
    $('#tipo option[value="' + resultado.tipo + '"]').prop('selected', true);
    $('#com_numeracao option[value="' + resultado.com_numeracao + '"]').prop('selected', true);
    $('#nivel option[value="' + resultado.nivel + '"]').prop('selected', true);
    resultado.conteudo != null ? tinymce.activeEditor.setContent(resultado.conteudo) : tinymce.activeEditor.setContent('');
    hideShowOptions();
  }

  if(acao == 'atualizar'){
    $('#span-tipo').attr('class', 'text-' + cor).text(texto_campo);
    $('#span-nivel').text(response.nivel);
    $('#span-texto_tipo').attr('class', upper).text(response.texto_tipo);
    $('button[value="' + response.id + '"] .indice-texto').text(response.texto_tipo);
  }
  return;
}

function gerarTextoAvisosCrud(acao, response, valor){
  var texto = '';
  var title = acao == 'erro' ? '<i class="fas fa-times" style="color: #e70d0d;"></i> Erro!' : 
  '<i class="fas fa-check-circle" style="color: #40c011;"></i> Sucesso!';

  switch (acao) {
    case 'excluir_varios':
      texto = 'Exclusão realizada com sucesso!';
      break;
    case 'atualizar':
      texto = 'Campos do texto foram atualizados!';
      response = 1;
      break;
    case 'erro':
      texto = response;
      break;
  }

  if((acao == 'excluir_varios') && (response != null) && (typeof response == 'object')){
    response.forEach(function(id, i){
      $('button[value="' + id + '"]').parents('.form-check').remove();
    });
    $('#lista').hide();
    selecionarTodos(false);
    response = 1;
  }

  if((acao == 'erro') || (response == 1)){
    $('#avisoTextos').modal({backdrop: 'static', keyboard: false, show: true});
    $('#avisoTextos .modal-title').html(title);
		$('#avisoTextos .modal-body').html(texto);
		$('#avisoTextos .modal-footer').hide();
    if(response == 1)
      setTimeout(function(){
        $('#avisoTextos').modal('hide');
      }, 1500);
    return;
  }

  if((acao == 'excluir_varios') && (response == null)){
    var textos_ids = '';
    var valor_final = JSON.parse(valor);
    var total = valor_final.length;
    var text = total > 1 ? 'todos estes textos selecionados' : 'este texto';

    valor_final.forEach(function(id, i){
      textos_ids += '<strong>Texto: </strong><i>' + $('button[value="' + id + '"]').text() + '</i><br>';
    });

    $('#avisoTextos').modal({backdrop: 'static', keyboard: false, show: true});
    $('#avisoTextos .modal-title')
      .html('<i class="fas fa-trash" style="color: #dc0909;"></i> Excluir');
		$('#avisoTextos .modal-body')
			.html(textos_ids + 'Tem certeza que deseja excluir ' + text + '?<br>Esta ação não é reversível!');
    $('#avisoTextos .modal-footer #excluirTexto').val(valor_final);
		$('#avisoTextos .modal-footer').show();
    return;
  }
}

function hideShowOptions(){
  if($(".textoTipo").val() == 'Título'){
    $('#nivel option').hide();
    $('#nivel option').each(function(){
      if($(this).val() == '0')
        $(this).show();
    });
    $('#nivel')[0].selectedIndex = 0;
    $('#com_numeracao option').hide();
    $('#com_numeracao option').each(function(){
        $(this).show();
    });
    if(!$('#texto_tipo').hasClass('text-uppercase'))
      $('#texto_tipo').addClass('text-uppercase');
  }else{
    $('#nivel option').show();
    $('#nivel option').each(function(){
      if($(this).val() == '0')
        $(this).hide();
    });
    $('#nivel')[0].selectedIndex = 1;
    $('#com_numeracao option').each(function(){
      if($(this).val() == '0')
        $(this).hide();
    });
    $('#com_numeracao')[0].selectedIndex = 0;
    if($('#texto_tipo').hasClass('text-uppercase'))
      $('#texto_tipo').removeClass('text-uppercase');
  }
}

function selecionarTodos(inverso){
  var texto, quadrado;

  if(!inverso){
    texto = $('[name="excluir_ids"]:checked').length == 0 ? 'Selecionar Todos' : 'Limpar Seleção';
    quadrado = $('[name="excluir_ids"]:checked').length == 0 ? '<i class="fas fa-check-square"></i>' : '<i class="fas fa-square"></i>';
  }else{
    texto = $('[name="excluir_ids"]:checked').length == 0 ? 'Limpar Seleção' : 'Selecionar Todos';
    quadrado = $('[name="excluir_ids"]:checked').length == 0 ? '<i class="fas fa-square"></i>' : '<i class="fas fa-check-square"></i>';
  }
  
  $('.selecionarTextos').html(quadrado + '&nbsp;&nbsp;' + texto);
}

$(".criarTexto").click(function(){
	var token = $('meta[name="csrf-token"]').attr('content');
	var link = '/admin/textos/' + $('#tipo_doc').val();
	var form = $('<form action="' + link + '" method="POST"><input type="hidden" name="_token" value="' + token + '"></form>');
	$('body').append(form);
	$(form).submit();
});

$("#publicarTexto").click(function(){
  var token = $('meta[name="csrf-token"]').attr('content');
	var link = '/admin/textos/publicar/' + $('#tipo_doc').val();
	var form = $('<form action="' + link + '" method="POST"><input type="hidden" name="_token" value="' + token + '"><input type="hidden" name="publicar" value="' + $(this).val() + '"></form>');
	$('body').append(form);
	$(form).submit();
});

$(".updateCampos").click(function(){
	crudGerarTexto('atualizar', $(this));
});

$(".deleteTexto").click(function(){
  if($(".deleteTexto").length > 0)
    gerarTextoAvisosCrud('excluir_varios', null, JSON.stringify([$(this).val()]));
});

$(".excluirTextos").click(function(){
  var excluirIds = [];
  if($('[name="excluir_ids"]:checked').length > 0){
    $('[name="excluir_ids"]:checked').each(function(){
      excluirIds.push($(this).val());
    });
    gerarTextoAvisosCrud('excluir_varios', null, JSON.stringify(excluirIds));
  }
});

$("#excluirTexto").click(function(){
  crudGerarTexto('excluir_varios', $(this));
});

$(".textoTipo").change(function(){
  hideShowOptions();
});

$("#updateIndice").click(function(){
  $('#loadingIndice').modal({backdrop: 'static', keyboard: false, show: true});
  $('#loadingIndice .modal-body').html('<div class="spinner-border text-primary"></div>&nbsp;&nbsp;Atualizando a índice...');
});

// link no sumário para abrir e ir no texto
$('button.abrir').click(function(){
  crudGerarTexto('carregar', $(this));
  $('#lista').hide();
  $('#lista').show();
  $('#tipo').focus();
});

$('#formGerarTexto').ready(function(){
  if($('button.abrir .badge').length > 0)
    $('button.abrir .badge').click();
});

$('[name="excluir_ids"]').change(function(){
  selecionarTodos(false);
});

$('.selecionarTextos').click(function(){
  selecionarTodos(true);
  var selecionados = $('[name="excluir_ids"]:checked').length > 0 ? false : true;
  $('[name="excluir_ids"]').prop('checked', selecionados);
  $('[name="excluir_ids"]:first').prop('checked', false);
});

// FIM da Funcionalidade GerarTexto ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++