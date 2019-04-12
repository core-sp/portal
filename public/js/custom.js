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
  // Filtro Agendamento
  $('#filtroAgendamento').on('submit', function(){
    var url = "/admin/agendamentos";
    var regional = $('#filtroAgendamentoRegional').val();
    var status = $('#filtroAgendamentoStatus').val();
    $.ajax({
      method: "GET",
      success: function() {
        if(status === null) {
          window.location.href = '?filtro=sim&regional='+regional;
        } else {
          window.location.href = '?filtro=sim&regional='+regional+'&status='+status;
        }
      }
    });
  });
  // Btns
  $('#lfm').filemanager('image');
  $('#edital').filemanager('file');
  // Máscaras
  $('.nrlicitacaoInput').mask('999/9999');
  $('.nrprocessoInput').mask('999/9999');
  $('.cnpjInput').mask('99.999.999/9999-99');
  $('.telefoneInput').mask('(00) 0000-00009');
})(jQuery);