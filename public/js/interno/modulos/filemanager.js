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

                // Preencher popover preview-lfm
                let final_id = target_preview.attr('id').replace("holder", "");
                let preview = $('#preview-lfm' + final_id);

                if(preview.length > 0)
                    preview[0].dataset.originalTitle = '<img src="' + target_preview.attr('src') + '" />';
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

    $('[id*="preview-lfm"]').popover({
        animated: true,
        placement: 'top',
        html: true,
        trigger: "hover",
        title: '<img src="" />',
    });

    $('[id*="preview-lfm"]').on('show.bs.popover', function(){
        let plfm = false;
        let largura = this.id.indexOf('-m-') > -1 ? "25%" : "45%";
        let final_id = this.id.replace("preview-lfm", "");

        $($(this).data("bs.popover").getTipElement()).css({"max-width": largura});

        $('[name="img' + final_id + '"], #img' + final_id).each(function(){
            if(($(this).length > 0) && ($(this).val().trim().length < 10))
                plfm = '<img src="" />';
        });

        if(plfm)
            this.dataset.originalTitle = plfm;
    });

    $('[id*="preview-lfm"]').each(function(){
        let final_id = this.id.replace("preview-lfm", "");
        let hol = $('#holder' + final_id);

        if(hol.length > 0)
            this.dataset.originalTitle = '<img src="' + hol.attr('src') + '" />';
    });
};
