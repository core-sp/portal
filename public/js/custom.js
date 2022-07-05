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
  $('.nrlicitacaoInput').mask('999/9999');
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
	$('.cpfOuCnpj').length > 11 ? $('.cpfOuCnpj').mask('00.000.000/0000-00', options) : $('.cpfOuCnpj').mask('000.000.000-00#', options);
  
  // Máscaras para datas
  $('#dataTermino').mask('00/00/0000', {
    onComplete: function() {
      var dataInicioPura = $('#dataInicio').val().split('/');
      var dataInicio = new Date(dataInicioPura[2], dataInicioPura[1] - 1, dataInicioPura[0]);
      var dataTerminoPura = $('#dataTermino').val().split('/');
      var dataTermino = new Date(dataTerminoPura[2], dataTerminoPura[1] - 1, dataTerminoPura[0]);
      if(dataInicio) {
        if(dataTermino < dataInicio) {
          alert('A data de término do curso não pode ser menor que a data de início.');
          $('#dataTermino').val('');
        }
      }
    }
  });
  $('#dataInicio').mask('00/00/0000', {
    onComplete: function() {
      var dataInicioPura = $('#dataInicio').val().split('/');
      var dataInicio = new Date(dataInicioPura[2], dataInicioPura[1] - 1, dataInicioPura[0]);
      var dataTerminoPura = $('#dataTermino').val().split('/');
      var dataTermino = new Date(dataTerminoPura[2], dataTerminoPura[1] - 1, dataTerminoPura[0]);
      if(dataTermino) {
        if(dataInicio > dataTermino) {
          alert('A data de início do curso não pode ser maior que a data de término.');
          $('#dataInicio').val('');
        }
      }
    }
  });
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

  // Será removido após refatorar a Cédula
  $('#filtroCedula').submit(function(e){
    var maxDataFiltro = $('#datemax').val();
    var minDataFiltro = $('#datemin').val();
    if(new Date(minDataFiltro) > new Date(maxDataFiltro)) {
      alert('Data inválida. A data inicial deve ser menor ou igual a data de término.');
      e.preventDefault();
    }
    if(!minDataFiltro || !maxDataFiltro) {
      alert('Selecione data de início e término.');
      e.preventDefault();
    }
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

  $('.cedula_recusada').submit(function(e){
    if($('[name="justificativa"]').val().trim().length < 5) {
      e.preventDefault();
      alert("O campo de justificativa deve ter, no mínimo, 5 caracteres");
    }else if($('[name="justificativa"]').val().trim().length > 600) {
      e.preventDefault();
      alert("O campo de justificativa deve ter, no máximo, 600 caracteres");
    }
    else
      $('.cedula_recusada').submit();
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

})(jQuery);

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
        async: false,
        cache: false,
        timeout: 60000,
        success: function(response) {
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
            $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false}).modal('show');
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
        if($('#' + campo + ' span.badge').length == 0){
            $('#' + campo).append('<span class="badge badge-warning ml-2">Justificado</span>');
            $('#' + campo + ' button.justificativaPreRegistro').html('<i class="fas fa-edit"></i>');
        }
    }else{
        $('#' + campo + ' span.badge').remove();
        $('#' + campo + ' button.justificativaPreRegistro').html('<i class="fas fa-times"></i>');
    }

    $('#' + campo + ' span.valorJustificativaPR').text(valor);
    console.log($('#' + campo).parent());
}

function verificaJustificados()
{
  // conferir anexos também para liberar botões
    $('#accordionPreRegistro div.card-body').each(function() {
        var menu = $(this).parentsUntil('#accordionPreRegistro').find('.menuPR');
        if($(this).find('.valorJustificativaPR').text().length > 0){
            $('button[value="aprovado"], #submitNegarPR button[value="negado"]').addClass('disabled').attr('type', 'button');
            $('button[value="correcao"]').removeClass('disabled').attr('type', 'submit');
            if(menu.find('span').length == 0)
              menu.append('<span class="badge badge-sm badge-warning ml-3">Justificado</span>');
        }else{
            menu.find('span').remove();
            $('button[value="aprovado"], #submitNegarPR button[value="negado"]').removeClass('disabled').attr('type', 'submit');
            $('button[value="correcao"]').addClass('disabled').attr('type', 'button');
        }
    });
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

// Fim da Funcionalidade Pre-Registro