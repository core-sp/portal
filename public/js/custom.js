$(document).ready(function(){
  // Btns
  $('#lfm').filemanager('image');
  $('#edital').filemanager('file');
  // Máscaras gerais
  $('.nrlicitacaoInput').mask('999/9999');
  $('.nrprocessoInput').mask('999/9999');
  $('.cnpjInput').mask('99.999.999/9999-99');
  $('.telefoneInput').mask('(00) 0000-00009');
  $('.fixoInput').mask('(00) 0000-0000');
  $('.cepInput').mask('00000-000');
  $('.dataInput').mask('00/00/0000');
  // Máscaras para datas
  $('#dataTermino').mask('00/00/0000', {
    onComplete: function() {
      var dataInicio = $('#dataInicio').val();
      var dataTermino = $('#dataTermino').val();
      if(dataTermino < dataInicio) {
        alert('A data de término do curso não pode ser menor que a data de início.');
        $('#dataTermino').val('');
      }
    }
  });
  $('#dataInicio').mask('00/00/0000', {
    onComplete: function() {
      var dataInicio = $('#dataInicio').val();
      var dataTermino = $('#dataTermino').val();
      if(dataInicio > dataTermino) {
        alert('A data de início do curso não pode ser maior que a data de término.');
        $('#dataInicio').val('');
      }
    }
  });
  $('#horaTermino').mask('00:00', {
    onComplete: function() {
      var horaInicio = $('#horaInicio').val();
      var horaTermino = $('#horaTermino').val();
      if(horaTermino <= horaInicio) {
        alert('O horário de término do curso não pode ser menor ou igual ao horário de início do curso.');
        $('#horaTermino').val('');
      }
    }
  });
  $('#horaInicio').mask('00:00', {
    onComplete: function() {
      var horaInicio = $('#horaInicio').val();
      var horaTermino = $('#horaTermino').val();
      if(horaInicio > horaTermino) {
        alert('O horário de início do curso não pode ser maior que o horário de término do curso.');
        $('#horaInicio').val('');
      }
    }
  });
  // Máscara para horário bloqueio
  $('#horaTerminoBloqueio').change(function(){
    var horaTerminoBloqueio = $(this).val();
    var horaInicioBloqueio = $('#horaInicioBloqueio').val();
    if(horaTerminoBloqueio < horaInicioBloqueio) {
      alert('O horário de término do bloqueio não pode ser menor que o horário de início do bloqueio.');
      $(this).val($("#horaTerminoBloqueio option:first").val());
    }
  });
  $('#horaInicioBloqueio').change(function(){
    var horaInicioBloqueio = $(this).val();
    var horaTerminoBloqueio = $('#horaTerminoBloqueio').val();
    if(horaInicioBloqueio > horaTerminoBloqueio) {
      alert('O horário de início do bloqueio não pode ser maior que o horário de término do bloqueio.');
      $(this).val($("#horaInicioBloqueio option:first").val());
    }
  });
  $('.timeInput').mask('00:00');
  $('.vagasInput').mask('000');
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
})(jQuery);