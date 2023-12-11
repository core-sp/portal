$(document).ready(function(){
  // Btns
  $('#lfm').filemanager('image');
  $('#lfm-1').filemanager('image');
  $('#lfm-2').filemanager('image');
  $('#lfm-3').filemanager('image');
  $('#lfm-4').filemanager('image');
  $('#lfm-5').filemanager('image');
  $('#lfm-6').filemanager('image');
  $('#lfm-7').filemanager('image');
  $('#lfm-m-1').filemanager('image');
  $('#lfm-m-2').filemanager('image');
  $('#lfm-m-3').filemanager('image');
  $('#lfm-m-4').filemanager('image');
  $('#lfm-m-5').filemanager('image');
  $('#lfm-m-6').filemanager('image');
  $('#lfm-m-7').filemanager('image');
  $('#edital').filemanager('file');
  // Máscaras gerais
  $('.nrlicitacaoInput').mask('99999/9999');
  $('.nrprocessoInput').mask('999/9999');
  $('.cnpjInput').mask('99.999.999/9999-99');
  $('.telefoneInput').mask('(00) 0000-00009').focusout(function (event) {  
    var target, phone, element;
    target = (event.currentTarget) ? event.currentTarget : event.srcElement;
    phone = target.value.replace(/\D/g, '');
    element = $(target);
    element.unmask();
    if(phone.length > 10) {
        element.mask("(99) 99999-9999");  
    } else {  
        element.mask("(99) 9999-99999");  
    }  
  });
  $('.celularInput').mask('(00) 0000-00009');
  $('.fixoInput').mask('(00) 0000-0000');
  $('.cepInput').mask('00000-000');
  $('.dataInput').mask('00/00/0000');
  $('.cpfInput').mask('000.000.000-00');
  $('#registro_core').mask('0000000/0000', {reverse: true});
  var options = {
		onKeyPress: function (cpf, ev, el, op) {
			var masks = ['000.000.000-000', '00.000.000/0000-00'];
			$('.cpfOuCnpj').mask((cpf.length > 14) ? masks[1] : masks[0], op);
		}
	}
	$('.cpfOuCnpj').index() > -1 && $('.cpfOuCnpj').val().length > 11 ? 
	$('.cpfOuCnpj').mask('00.000.000/0000-00', options) : 
	$('.cpfOuCnpj').mask('000.000.000-00#', options);
  
  // copiado
	$('.placaVeiculo').mask('AAA 0U00', {
		translation: {
			'A': {
				pattern: /[A-Za-z]/
			},
			'U': {
				pattern: /[A-Za-z0-9]/
			},
		},
		onKeyPress: function (value, e, field, options) {
			// Convert to uppercase
			e.currentTarget.value = value.toUpperCase();
	
			// Get only valid characters
			let val = value.replace(/[^\w]/g, '');
	
			// Detect plate format
			let isNumeric = !isNaN(parseFloat(val[4])) && isFinite(val[4]);
			let mask = 'AAA 0U00';
			if(val.length > 4 && isNumeric) {
				mask = 'AAA-0000';
			}
			$(field).mask(mask, options);
		}
	});

  // Máscaras para datas
  // $('#dataTermino').mask('00/00/0000', {
  //   onComplete: function() {
  //     var dataInicioPura = $('#dataInicio').val().split('/');
  //     var dataInicio = new Date(dataInicioPura[2], dataInicioPura[1] - 1, dataInicioPura[0]);
  //     var dataTerminoPura = $('#dataTermino').val().split('/');
  //     var dataTermino = new Date(dataTerminoPura[2], dataTerminoPura[1] - 1, dataTerminoPura[0]);
  //     if(dataInicio) {
  //       if(dataTermino < dataInicio) {
  //         alert('A data de término do curso não pode ser menor que a data de início.');
  //         $('#dataTermino').val('');
  //       }
  //     }
  //   }
  // });
  // $('#dataInicio').mask('00/00/0000', {
  //   onComplete: function() {
  //     var dataInicioPura = $('#dataInicio').val().split('/');
  //     var dataInicio = new Date(dataInicioPura[2], dataInicioPura[1] - 1, dataInicioPura[0]);
  //     var dataTerminoPura = $('#dataTermino').val().split('/');
  //     var dataTermino = new Date(dataTerminoPura[2], dataTerminoPura[1] - 1, dataTerminoPura[0]);
  //     if(dataTermino) {
  //       if(dataInicio > dataTermino) {
  //         alert('A data de início do curso não pode ser maior que a data de término.');
  //         $('#dataInicio').val('');
  //       }
  //     }
  //   }
  // });
  $('#horaTermino').mask('00:00', {
    onComplete: function() {
      var horaInicio = $('#horaInicio').val();
      var horaTermino = $('#horaTermino').val();
      if(horaInicio) {
        if(horaTermino <= horaInicio) {
          alert('O horário de término não pode ser menor ou igual ao horário de início.');
          $('#horaTermino').val('');
        }
      }
    }
  });
  $('#horaInicio').mask('00:00', {
    onComplete: function() {
      var horaInicio = $('#horaInicio').val();
      var horaTermino = $('#horaTermino').val();
      if(horaTermino) {
        if(horaInicio > horaTermino) {
          alert('O horário de início não pode ser maior que o horário de término.');
          $('#horaInicio').val('');
        }
      }
    }
  });

  $('.timeInput').mask('00:00');
  $('.vagasInput').mask('000');
  // Draggable
  $("#sortable").sortable();
  $( "#sortable" ).disableSelection();

  // Regra de data no filtro de agendamento +++ Será removido depois de refatorar todos que o utilizam 
  $('#filtroAgendamento').submit(function(e){
    var maxDataFiltro = $('#maxdiaFiltro').val().split('/');
    var maxdiaFiltro = new Date(maxDataFiltro[2], maxDataFiltro[1] - 1, maxDataFiltro[0]);
    var minDataFiltro = $('#mindiaFiltro').val().split('/');
    var mindiaFiltro = new Date(minDataFiltro[2], minDataFiltro[1] - 1, minDataFiltro[0]);
    if(mindiaFiltro) {
      if(maxdiaFiltro < mindiaFiltro) {
        alert('O dia limite de filtro não pode ser menor que o dia inicial.');
        $(this).val($("#maxdiaFiltro").val());
        e.preventDefault();
      }
    }
  });

  // Buscar na tabela da página 'suporte_erros.blade.php'
  $("#myInput").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#myTable tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });

  $('#filtroDate').submit(function(e){
    var maxDataFiltro = $('#datemax').val();
    var minDataFiltro = $('#datemin').val();
    if(new Date(minDataFiltro) > new Date(maxDataFiltro)) {
      alert('Data inválida. A data inicial deve ser menor ou igual a data de término.');
      $('#datemin').focus();
      e.preventDefault();
    }
  });

  $('[data-toggle="popover"]').popover({html: true});
});

// Funcionalidade Agendamento Bloqueio
$('#horaTerminoBloqueio').change(function(){
  var horaTerminoBloqueio = $(this).val();
  var horaInicioBloqueio = $('#horaInicioBloqueio').val();
  if(horaInicioBloqueio) {
    if(horaTerminoBloqueio < horaInicioBloqueio) {
      alert('O horário de término do bloqueio não pode ser menor que o horário de início do bloqueio.');
      $(this).val($("#horaTerminoBloqueio option:first").val());
    }
  }
});

$('#horaInicioBloqueio').change(function(){
  var horaInicioBloqueio = $(this).val();
  var horaTerminoBloqueio = $('#horaTerminoBloqueio').val();
  if(horaTerminoBloqueio) {
    if(horaInicioBloqueio > horaTerminoBloqueio) {
      alert('O horário de início do bloqueio não pode ser maior que o horário de término do bloqueio.');
      $(this).val($("#horaInicioBloqueio option:first").val());
    }
  }
});

function ajaxAgendamentoBloqueio(valor)
{
  $.ajax({
    method: "GET",
    data: {
      "idregional": valor,
    },
    dataType: 'json',
    url: "/admin/agendamentos/bloqueios/dados-ajax",
    success: function(response) {
      horas_atendentes = response;
      setCamposAgeBloqueio(horas_atendentes);
    },
    error: function() {
      alert('Erro ao carregar os horários. Recarregue a página.');
    }
  });
}

function optionTodas(valor)
{
  if(valor == 'Todas'){
    $('#horarios').prop("disabled", true);
    $('#qtd_atendentes').val(0);
    $('#qtd_atendentes').text("0");
  }
  else
    $('#horarios').prop("disabled", false);
}

function setCamposAgeBloqueio(horas_atendentes)
{
  $('#horarios option').show();
  $('#horarios option').each(function(){
    var valor = $(this).val();
    jQuery.inArray(valor, horas_atendentes['horarios']) != -1 ? $(this).show() : $(this).hide();
  });

  $('#totalAtendentes').text(horas_atendentes['atendentes']);
}

$('#idregionalBloqueio').ready(function(){
  var valor = $('#idregionalBloqueio').val();
    optionTodas(valor);
  if(valor > 0)
    ajaxAgendamentoBloqueio(valor);
});

$('#idregionalBloqueio').change(function(){
  var valor = $('#idregionalBloqueio').val();
    optionTodas(valor);
  if(valor > 0)
    ajaxAgendamentoBloqueio(valor);
});
// Fim da Funcionalidade Agendamento Bloqueio

// Funcionalidade Plantão Jurídico
function setCamposDatas(plantao, tipo)
{
  if(tipo == 'change'){
    $("#dataInicialBloqueio").val('');
    $("#dataFinalBloqueio").val('');
  }

  $("#dataInicialBloqueio").prop('min', plantao['datas'][0]).prop('max', plantao['datas'][1]);
  $("#dataFinalBloqueio").prop('min', plantao['datas'][0]).prop('max', plantao['datas'][1]);

  var inicial = new Date(plantao['datas'][0] + ' 00:00:00');
  var final = new Date(plantao['datas'][1] + ' 00:00:00');
  var inicialFormatada = inicial.getDate() + '/' + (inicial.getMonth() + 1) + '/' + inicial.getFullYear(); 
  var finalFormatada = final.getDate() + '/' + (final.getMonth() + 1) + '/' + final.getFullYear(); 

  $("#bloqueioPeriodoPlantao").text(inicialFormatada + ' - ' + finalFormatada);
}

function setCampoHorarios(plantao)
{
  $('#horariosBloqueio option').show();
  $('#horariosBloqueio option').each(function(){
    var valor = $(this).val();
    if(jQuery.inArray(valor, plantao['horarios']) != -1)
      $(this).show();
    else
      $(this).hide();
  });
}

function setCampoAgendados(plantao)
{
  if(plantao['link-agendados'] != null)
  {
    $('#textoAgendados').prop('class', 'mb-3');
    $('#linkAgendadosPlantao').prop('href', plantao['link-agendados']);
  }else
    $('#textoAgendados').prop('class', 'text-hide');
}

function ajaxPlantaoJuridico(valor, e)
{
  $.ajax({
    method: "GET",
    data: {
      "id": valor,
    },
    dataType: 'json',
    url: "/admin/plantao-juridico/ajax",
    success: function(response) {
      plantao = response;
      setCampoAgendados(plantao);
      setCamposDatas(plantao, e.type);
      setCampoHorarios(plantao);
    },
    error: function() {
      alert('Erro ao carregar as datas e/ou os horários. Recarregue a página.');
    }
  });
}

$('#plantaoBloqueio').ready(function(e){
  var valor = $('#plantaoBloqueio').val();
    if(valor > 0)
      ajaxPlantaoJuridico(valor, e);
});

$('#plantaoBloqueio').change(function(e){
  var valor = $('#plantaoBloqueio').val();
  if(valor > 0)
    ajaxPlantaoJuridico(valor, e);
});
// Fim da Funcionalidade Plantão Jurídico

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

// Funcionalidade Agendamento
function selectAtendenteByStatus(valor)
{
  $('#idusuarioAgendamento option').show();
  $('#idusuarioAgendamento option').each(function(){
    var idUser = $(this).val();
    if((valor == '') && (idUser != ''))
      $(this).hide();
    if((valor == 'Compareceu') && (idUser == ''))
      $(this).hide();
  });
  if(valor != 'Compareceu')
    $('#idusuarioAgendamento')[0].selectedIndex = 0;
}

$('#statusAgendamentoAdmin').change(function(){
  var valor = $('#statusAgendamentoAdmin').val();
  selectAtendenteByStatus(valor);
});

$('#statusAgendamentoAdmin').ready(function(){
  if($('#statusAgendamentoAdmin').length > 0){
    var valor = $('#statusAgendamentoAdmin').val();
    selectAtendenteByStatus(valor);
  }
});
// Fim da Funcionalidade Agendamento

(function($){
  // Função para tornar menu ativo dinâmico
  $(function(){
    var url = window.location;

    $('ul.nav-sidebar a').filter(function() {
      return this.href == url;
    }).addClass('active');

    $('ul.nav-treeview a').filter(function() {
      return this.href == url;
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open')
    .prev('a').addClass('active');
  });

  // Botão standalone LFM
  $.fn.filemanager = function(type, options) {
    type = type || 'file';
    this.on('click', function(e) {
      // Define caminho para abrir o LFM
      var route_prefix = (options && options.prefix) ? options.prefix : '/laravel-filemanager';
      localStorage.setItem('target_input', $(this).data('input'));
      localStorage.setItem('target_preview', $(this).data('preview'));
      window.open(route_prefix + '?type=' + type, 'FileManager', 'width=900,height=600');
      window.SetUrl = function (url, file_path) {
        //set the value of the desired input to image url
        var target_input = $('#' + localStorage.getItem('target_input'));
        target_input.val(file_path).trigger('change');
        //set or change the preview image src
        var target_preview = $('#' + localStorage.getItem('target_preview'));
        target_preview.attr('src', url).trigger('change');
      };
      return false;
    });
  }

  // Recusar endereço
  $('#recusar-trigger').on('click', function(){
    $('#recusar-form').toggle();
  });

  $('.cedula_recusada').ready(function(e){
    if($('.cedula_recusada').length > 0)
      $('[name="justificativa"]').val('');
  });

  $('.anoInput').mask('0000');

  $(document).on('change', ".nParcela", function() {
		var id = $(this).attr('id');
		var nParcela = parseFloat($('option:selected',this).attr('value'));
		var total = parseFloat($('#total' + id).attr('value'));
		var valorParcelado =  (total/nParcela).toFixed(2);

		$('#parcelamento' + id).attr('value', valorParcelado);
		$('#parcelamento' + id).html('R$ ' + valorParcelado.replace('.', ','));
	});

  $(document).on('keydown', function(e) {
    if((e.keyCode == 27) && (window.location.href.indexOf('/admin/suporte/logs'))){
      $("#modalSuporte").modal('hide');
    }
  });

})(jQuery);

// Logout Interno
$("#logout-interno").click(function(){
	var token = $('meta[name="csrf-token"]').attr('content');
	var link = "/admin/logout";
	var form = $('<form action="' + link + '" method="POST"><input type="hidden" name="_token" value="' + token + '"></form>');
	$('body').append(form);
	$(form).submit();
});


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

// Fim da Funcionalidade Sala de Reunião +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

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

// Funcionalidade Pre-Registro
function putDadosPreRegistro(campo, valor, acao)
{
    var id = $('[name="idPreRegistro"]').val();

    $('#modalJustificativaPreRegistro').modal('hide');

    if(acao == 'justificar'){
        // evitar de fazer request quando não há justificativa escrita e nem salva
        if((valor == "") && ($('#' + campo + ' i.fa-times').length > 0))
            return false;

        // evitar de fazer request quando não há alteração do valor com o texto salvo
        if(valor == $('#' + campo + ' .valorJustificativaPR').text())
            return false;
    }

    $("#modalLoadingBody").html('<i class="spinner-border text-info"></i> Salvando');
	  $('#modalLoadingPreRegistro').modal({backdrop: "static", keyboard: false, show: true});

    $.ajax({
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
          'acao': acao,
          'campo': campo,
          'valor': valor
        },
        dataType: 'json',
        url: '/admin/pre-registros/update-ajax/' + id,
        cache: false,
        timeout: 60000,
        success: function(response) {
            $("#modalLoadingPreRegistro").modal('hide');
            if(campo == 'negado')
                $('#submitNegarPR').submit();
            if(acao == 'justificar')
                addJustificado(campo, valor);
            else if(acao == 'editar'){
                $("#modalLoadingBody").html('<i class="icon fa fa-check text-success"></i> Salvo');
                $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
                setTimeout(function() {
                  $("#modalLoadingPreRegistro").modal('hide');
                }, 1200); 
            }
            $('#userPreRegistro').text(response['user']);
            $('#atualizacaoPreRegistro').text(response['atualizacao']);
            verificaJustificados();
        },
        error: function(request, status, error) {
            var errorFunction = getErrorMsg(request);
            $("#modalLoadingBody").html('<i class="icon fa fa-times text-danger"></i> ' + errorFunction[0]);
            $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
            setTimeout(function() {
              $("#modalLoadingPreRegistro").modal('hide');
            }, errorFunction[1]); 
            console.clear();
        }
    });
}

function getErrorMsg(request)
{
    var time = 5000;
    var errorMessage = request.status + ': ' + request.statusText;
    var nomesCampo = ['campo', 'valor'];
    if(request.status == 422){
        for(var nome of nomesCampo){
          var msg = request.responseJSON.errors[nome];
          if(msg != undefined)
            errorMessage = msg[0];
        }
        time = 2000;
    }
    if(request.status == 401){
        errorMessage = request.responseJSON.message;
        time = 2000;
    }
    if(request.status == 419){
        errorMessage = "Sua sessão expirou! Recarregue a página";
        time = 2000;
    }
    if(request.status == 429){
      var aguarde = request.getResponseHeader('Retry-After');
      errorMessage = "Excedeu o limite de requisições por minuto.<br>Aguarde " + aguarde + " segundos";
      time = 2500;
    }
    return [errorMessage, time];
}

function contJustificativaPR(obj)
{
    var total = 500 - obj.val().length;
    if(total == -1)
        return;
    $('#contChar').text(total);
}

function addJustificado(campo, valor)
{
    if(valor != ""){
        if($('#' + campo + ' span.badge-warning').length == 0){
            $('#' + campo).append('<span class="badge badge-warning ml-2">Justificado</span>');
            $('#' + campo + ' button').removeClass('btn-outline-success').addClass('btn-outline-danger');
            $('#' + campo + ' button.justificativaPreRegistro').html('<i class="fas fa-edit"></i>');
        }
    }else{
        $('#' + campo + ' span.badge-warning').remove();
        $('#' + campo + ' button').removeClass('btn-outline-danger').addClass('btn-outline-success');
        $('#' + campo + ' button.justificativaPreRegistro').html('<i class="fas fa-user-edit"></i>');
    }

    $('#' + campo + ' span.valorJustificativaPR').text(valor);
}

function confereAnexos()
{
    // confere anexos: se pf, todos devem estar ok; senão apenas 2 podem faltar por não ser possível conferir via sistema
    var aprovado = false;
    var ok = $('.confirmaAnexoPreRegistro:checked').length;
    var total = $('.confirmaAnexoPreRegistro').length;

    if(ok == total)
        aprovado = true;

    if($('#tipo_cnpj').length){
        var eleitoral = false; 
        var reservista = false;
        $('.confirmaAnexoPreRegistro:checked').each(function() {
            if($(this).val() == 'Certidão de quitação eleitoral')
                eleitoral = true;
            if($(this).val() == 'Cerificado de reservista ou dispensa')
                reservista = true;
        });
        if(ok == (total - 2))
            if(!eleitoral && !reservista)
                aprovado = true;
        if(ok == (total - 1))
            if(!eleitoral || !reservista)
                aprovado = true;
    }
  
    return aprovado;
}

function habilitarBotoesStatus(justificado)
{
    if(justificado > 0){
        $('button[value="aprovado"], #submitNegarPR button[value="negado"]').addClass('disabled').attr('type', 'button');
        $('button[value="correcao"]').removeClass('disabled').attr('type', 'submit');
    }else{
        $('button[value="aprovado"], #submitNegarPR button[value="negado"]').removeClass('disabled').attr('type', 'submit');
        $('button[value="correcao"]').addClass('disabled').attr('type', 'button');
    }
}

function verificaJustificados()
{
    $('#accordionPreRegistro div.card-body').each(function() {
        var menu = $(this).parentsUntil('#accordionPreRegistro').find('.menuPR');
        if($(this).find('.valorJustificativaPR').text().length > 0){
            if(menu.find('span.badge-warning').length == 0)
                menu.append('<span class="badge badge-sm badge-warning ml-3">Justificado</span>');
        }else
            menu.find('span.badge-warning').remove();
    });

    var justificado = $('.menuPR span.badge-warning').length;
    var aprovado = confereAnexos();

    habilitarBotoesStatus(justificado);
    if(aprovado && (justificado == 0))
        $('button[value="aprovado"]').removeClass('disabled').attr('type', 'submit');
    else
        $('button[value="aprovado"]').addClass('disabled').attr('type', 'button');
}

$('#accordionPreRegistro').ready(function() {
    verificaJustificados();
});

$('.justificativaPreRegistro').click(function() {
    var campo = this.value;
    var texto = campo == 'negado' ? '' : $('#' + campo + ' span.valorJustificativaPR').text();

    if((campo == 'negado') && $('#submitNegarPR button[value="negado"]').hasClass('disabled'))
        return false;

    var input = $('#modalJustificativaPreRegistro .modal-body textarea');
    var titleModal = texto.length > 0 ? ' Editar justificativa' : ' Adicionar justificativa';
    input.val(texto);
    contJustificativaPR(input);
    $('#submitJustificativaPreRegistro').val(campo);
    $('#modalJustificativaPreRegistro .modal-title #titulo').text(titleModal);
    $('#modalJustificativaPreRegistro').modal({backdrop: "static", keyboard: false, show: true});
});

$('#modalJustificativaPreRegistro .modal-body textarea').keyup(function() {
    contJustificativaPR($(this));
});

$('#submitJustificativaPreRegistro').click(function() {
    var campo = this.value;
    var value = $('#modalJustificativaPreRegistro .modal-body textarea').val();
    putDadosPreRegistro(campo, value, 'justificar');
});

$('.addValorPreRegistro').click(function() {
    var campo = this.value;
    var valor = $(this).parent().find('input').val();
    putDadosPreRegistro(campo, valor, 'editar');
});

$('.confirmaAnexoPreRegistro').change(function() {
    var campo = $(this).attr('name');
    var valor = $(this).val();
    putDadosPreRegistro(campo, valor, 'conferir');
});

$('#submitNegarPR button[value="negado"]').click(function(e) {
    e.preventDefault();
});

$('#doc_pre_registro').on('change',function(e){
  var fileName = e.target.files[0].name;
  $(this).next('.custom-file-label').html(fileName);
});

// Fim da Funcionalidade Pre-Registro