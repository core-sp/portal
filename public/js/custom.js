$(document).ready(function(){
  // Btns
  $('#lfm').filemanager('image');
  $('#lfm-1').filemanager('image');
  $('#lfm-2').filemanager('image');
  $('#lfm-3').filemanager('image');
  $('#lfm-4').filemanager('image');
  $('#lfm-5').filemanager('image');
  $('#lfm-m-1').filemanager('image');
  $('#lfm-m-2').filemanager('image');
  $('#lfm-m-3').filemanager('image');
  $('#lfm-m-4').filemanager('image');
  $('#lfm-m-5').filemanager('image');
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
  $('#ageporhorario').mask('0');
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
          alert('O horário de término do curso não pode ser menor ou igual ao horário de início do curso.');
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
          alert('O horário de início do curso não pode ser maior que o horário de término do curso.');
          $('#horaInicio').val('');
        }
      }
    }
  });
  // Máscara para horário bloqueio
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
  $('.timeInput').mask('00:00');
  $('.vagasInput').mask('000');
  // Draggable
  $("#sortable").sortable();
  $( "#sortable" ).disableSelection();
  // Regra de data no filtro de agendamento
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
});

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
})(jQuery);