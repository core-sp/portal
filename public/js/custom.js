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

  $(".custom-file-input").on("change", function(e) {
    var fileName = e.target.files[0].name;
		$(this).next('.custom-file-label').html(fileName);
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
  var icons = {
    header: "fas fa-angle-right",
    activeHeader: "fas fa-angle-down"
  };

  // $(".textosSortable").sortable({
  //   items: "> div > div > div.form-check",
  //   placeholder: "sortable-placeholder",
  //   forcePlaceholderSize: true,
  // });
  // $(".textosSortable").disableSelection();

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

  $('.toast').toast({delay: 2000});
  $('.toast').toast('show');
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

  // Funcionalidade Suporte / Logs
  $(document).on('keydown', function(e) {
    if((e.keyCode == 27) && (window.location.href.indexOf('/admin/suporte/logs'))){
      $("#modalSuporte").modal('hide');
    }
  });

  $('[name="relat_opcoes"]').change(function(){
    var somente_rc = $('[name="relat_opcoes"] option[value="' + $(this).val() + '"]').text().search('do RC') > -1;
    if(somente_rc){
      $('[name="relat_tipo"] option[value="externo"]').prop('selected', true);
      $('[name="relat_tipo"] option[value="interno"]').hide();
    }else
      $('[name="relat_tipo"] option[value="interno"]').show();
  });

  $('#buscar-mes [type="radio"], #relat-buscar-mes [type="radio"]').change(function(){
    var id = $(this).parents('.input-group').attr('id');
    var nome = id == 'relat-buscar-mes' ? 'relat_' : '';
    var outra_id = id == 'relat-buscar-mes' ? 'relat-' : '';
    if(this.checked){
      $('#' + outra_id + 'buscar-mes [name="' + nome + 'mes"]').prop('disabled', false);
      $('#' + outra_id + 'buscar-ano [name="' + nome + 'ano"]').prop('disabled', true);
    }
  });

  $('#buscar-ano [type="radio"], #relat-buscar-ano [type="radio"]').change(function(){
    var id = $(this).parents('.input-group').attr('id');
    var nome = id == 'relat-buscar-ano' ? 'relat_' : '';
    var outra_id = id == 'relat-buscar-ano' ? 'relat-' : '';
    if(this.checked){
      $('#' + outra_id + 'buscar-mes [name="' + nome + 'mes"]').prop('disabled', true);
      $('#' + outra_id + 'buscar-ano [name="' + nome + 'ano"]').prop('disabled', false);
    }
  });

  // FIM Funcionalidade Suporte / Logs

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
    if(($('#nivel option:selected').length == 0) || (($('#nivel option:selected').length > 0) && ($('#nivel option:selected').val() == 0)))
      $('#nivel option[value="1"]').prop('selected', true);
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
  var n_vezes = $(this).parents('.input-group').find('input');

  if((n_vezes.length > 0) && (n_vezes.val().trim() === ''))
    return;

  n_vezes = n_vezes.length > 0 ? '<input type="hidden" name="n_vezes" value="' + n_vezes.val() + '">' : null;
  
	var form = $('<form action="' + link + '" method="POST"><input type="hidden" name="_token" value="' + token + '">' + n_vezes + '</form>');
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
$("#sumario").on('click', 'button.abrir', function(){
  crudGerarTexto('carregar', $(this));
  $('#lista').hide();
  $('#lista').show();
  $('#tipo').focus();
});

$('#formGerarTexto').ready(function(){
  if($('button.abrir .badge').length > 0)
    $('button.abrir .badge').click();
});

$('#sumario').on('change', '[name="excluir_ids"]', function(){
  selecionarTodos(false);
});

$('.selecionarTextos').click(function(){
  selecionarTodos(true);
  var selecionados = $('[name="excluir_ids"]:checked').length > 0 ? false : true;
  $('[name="excluir_ids"]').prop('checked', selecionados);
  $('[name="excluir_ids"]:first').prop('checked', false);
});

$('#sumario').on('click', 'button.mover', function(e){
  var botao = 'button.mover';
  var orientacao = $('div.sumario-horizontal').length > 0 ? 'horizontal' : 'vertical';
  var trocar = orientacao == 'horizontal' ? '<i class="fas fa-long-arrow-alt-right"></i>' : '<i class="fas fa-long-arrow-alt-down"></i>';
  var mover = orientacao == 'horizontal' ? '<i class="fas fa-exchange-alt"></i>' : '<i class="fas fa-exchange-alt fa-rotate-90"></i>';

  if($(this).hasClass('btn-secondary')){
    var temp = $(botao + '.btn-warning').parent();
    if(temp.length == 0)
      return;

    $(this).parent().after(temp.prop("outerHTML"));
    var item = $(this).parent().next();
    item.addClass('blink_me').attr('style', 'background-color: yellow');
    setTimeout(function(){ 
      item.removeClass('blink_me').attr('style', '');
    }, 2000);
    temp.remove();
  }

  if($(this).hasClass('btn-success')){
    $(this).removeClass('btn-success').addClass('btn-warning');
    $(this).text('Cancelar');
    $(botao + '.btn-success').each(function(){
      $(this).html(trocar);
      $(this).removeClass('btn-success').addClass('btn-secondary');
    });
  }
  else
    $(botao).html(mover)
    .removeClass('btn-secondary')
    .removeClass('btn-warning')
    .addClass('btn-success');
});

// FIM da Funcionalidade GerarTexto ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++