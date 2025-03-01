export function executar(local = 'interno'){

    // Botão standalone LFM
    $.fn.filemanager = function(type, options) {
        type = type || 'file';
        this.on('click', function(e) {
            // Define caminho para abrir o LFM
            let route_prefix = (options && options.prefix) ? options.prefix : '/laravel-filemanager';
            localStorage.setItem('target_input', $(this).data('input'));
            localStorage.setItem('target_preview', $(this).data('preview'));
            window.open(route_prefix + '?type=' + type, 'FileManager', 'width=900,height=600');
            window.SetUrl = function (url, file_path) {
                //set the value of the desired input to image url
                let target_input = $('#' + localStorage.getItem('target_input'));
                target_input.val(file_path).trigger('change');
                //set or change the preview image src
                let target_preview = $('#' + localStorage.getItem('target_preview'));
                target_preview.attr('src', url).trigger('change');
            };

            return false;
        });
    }
    
    $('#lfm').filemanager('image');

    if($('#total-bannerprincipal').length > 0)
        for(let i = 1; i <= $('#total-bannerprincipal').val(); ++i){
          $('#lfm-' + i).filemanager('image');
          $('#lfm-m-' + i).filemanager('image');
        }
    
    $('#edital').filemanager('file');
};
