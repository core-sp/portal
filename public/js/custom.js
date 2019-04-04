$(document).ready(function() {

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


  // Função para botão stand-alone do LFM
  (function( $ ){

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

  $('#lfm').filemanager('image');
  $('#lfm-noticia').filemanager('image', {
    working_dir: 'teste'
  });
  $('#edital').filemanager('file');

});