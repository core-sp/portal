const pasta_img_principal = 'img/';
const pasta_img_opcional = "/admin/imagens/itens-home/armazenamento";
let openStorage_id = '';

function lazyLoadImg(){

    const link = $('#modulo-lazy-load-img').attr('src');

    import(link)
    .then((module) => {
        console.log('[MÓDULOS] # Módulo lazy-load-img importado por opcional e carregado.');
        console.log('[MÓDULOS] # Local do módulo: ' + link);

        let root = document.querySelector('#armazenamento .modal-dialog .modal-content');
        module.default(root);
    })
    .catch((err) => {
        console.log(err);
        alert('Erro na página! Módulo com erro! Tente novamente mais tarde!');
    });
}

async function validarArquivo(arquivo, mimeTypes = []){

    const link = $('#modulo-validar-arquivos').attr('src');

    try {
        const module = await import(link);
        console.log('[MÓDULOS] # Módulo validar-arquivos importado por opcional e carregado.');
        console.log('[MÓDULOS] # Local do módulo: ' + link);
        return module.validarUmArquivo(arquivo, mimeTypes);
    } catch (err) {
        console.log(err);
        alert('Erro na página! Módulo com erro! Tente novamente mais tarde!');
    }
}

function limparTabelaStorage(){
    
    $('#armazenamento .card-columns .card').remove();
}

function limparHeaderFundo(texto = 'Cor selecionada'){

    if($('#header_fundo_default').prop('checked'))
        texto = 'Imagem padrão escolhida';

    $('#header_fundo').val('');
    $('#header_fundo').attr('placeholder', texto);
}

function msgGerenciarArquivo(texto = '', sucesso = true){

    let add_alerta = sucesso ? 'success' : 'danger';
    let remove_alerta = sucesso ? 'danger' : 'success';

    $('#armazenamento #msgStorage').removeClass('alert-' + remove_alerta).addClass('alert-' + add_alerta).html(texto).show();
}

function preencheTabelaPath(caminho, folder_name, value) {

    let href_path = caminho + value;
    let final_texto = caminho == pasta_img_principal ? '</div>' : '<button class="btn btn-sm btn-danger deleteFileStorage" type="button" value="' + value 
    + '"><i class="fas fa-trash"></i></button></div>';

    let texto_html = '<div class="card-body text-center pt-0 pl-0 pr-0"><div class="card-img-top"><a href="/' + href_path 
    + '" target="_blank" rel="noopener" data-toggle="lightbox" data-gallery="itens_home_storage"><img data-src="/' + href_path 
    + '" class="lazy-loaded-image lazy"></a></div><br><button class="btn btn-link text-break storagePath" value="' + href_path + '">' + value 
    + '</button><br><hr><a href="' + pasta_img_opcional + '/download/' + folder_name + '/' + value 
    + '" class="btn btn-sm btn-primary mr-2"><i class="fas fa-download"></i></a>' + final_texto;
    
    $('#armazenamento #cards').append('<div class="card storageFile w-100 border border-primary"></div>');
    $('#armazenamento #cards .storageFile:last').append(texto_html);
}

function receberArquivos(id, pasta = null){

    $.ajax({
        method: "GET",
        data: {},
        dataType: 'json',
        url: pasta == null ? pasta_img_opcional : pasta_img_opcional + "/" + pasta,
        success: function(response) {
            limparTabelaStorage();
            let caminho = response.caminho;
            let folder_name = response.folder;

            response.path.forEach(function(value, index, array) {
                preencheTabelaPath(caminho, folder_name, value);
            });
            document.dispatchEvent(new CustomEvent("ITENS_SELECIONAR", {
                detail: id,
            }));
            document.dispatchEvent(new CustomEvent("ITENS_EXCLUIR", {
                detail: caminho,
            }));
            lazyLoadImg();
        },
        error: function() {
            document.dispatchEvent(new CustomEvent("MSG_GERAL_CONT_TITULO", {
                detail: {
                    titulo: '<i class="fas fa-times text-danger"></i> Erro!', 
                    texto: '<span class="text-danger">Erro ao carregar os arquivos. Recarregue a página.</span>'
                }
            }));
        }
    });
}

function ajaxAdicionaArquivo(form){

    $.ajax({
        method: "POST",
        data: form,
        contentType : false,
        processData : false,
        url: pasta_img_opcional,
        success: function(response) {
            if(response.novo_arquivo != null){
                let texto = 'Arquivo <strong><i>"' + response.novo_arquivo + '"</i></strong> foi adicionado da pasta!';
                
                $('#armazenamento .custom-file-label').text('Selecionar arquivo...');
                receberArquivos(openStorage_id);
                msgGerenciarArquivo(texto);
                $('.openStoragePasta[value=""]').attr('disabled', true);
                $('.openStoragePasta[value!=""]').attr('disabled', false);
            }
        },
        error: function(xhr) {
            let txt = xhr.status == 422 ? xhr.responseJSON.errors.file_itens_home[0] : 'Erro ao adicionar o arquivo. Recarregue a página.';

            msgGerenciarArquivo(txt, false);
            $('#armazenamento .custom-file-label').text('Selecionar arquivo...');
        }
    });
}

function ajaxRemoveArquivo(arquivo){

    $.ajax({
        method: "POST",
        data: {
            _method: "DELETE",
            _token: $('meta[name="csrf-token"]').attr('content'),
        },
        dataType: 'json',
        url: pasta_img_opcional + "/delete-file/" + arquivo,
        success: function(response) {
            let txt = 'Arquivo <strong><i>"' + arquivo + '"</i></strong> ';
            let sucesso = response != 'Não foi removido.';

            response != 'Não foi removido.' ? $('.deleteFileStorage[value="' + arquivo + '"]').parents('.card.storageFile').remove() : txt += 'NÃO ';
            msgGerenciarArquivo(txt + 'foi removido da pasta!', sucesso);
            $('#armazenamento .modal-body').scrollTop(0);
        },
        error: function() {
            let texto = 'Erro ao excluir o arquivo <strong><i>"' + arquivo + '"</i></strong>. Recarregue a página.';

            msgGerenciarArquivo(texto, false);
            $('#armazenamento .modal-body').scrollTop(0);
        }
    });
}

function editar(){

    $("#filtrarFile").on("keyup", function(){
        let value = $(this).val().toLowerCase();

        $("#cards .card .storagePath").filter(function() {
            $(this).parents('.card.storageFile').toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $('.openStorage').click(function(){
        openStorage_id = $(this).parents('.input-group').find('input').attr('id');
        receberArquivos(openStorage_id);
    });

    $('.openStoragePasta').click(function(){
        let pasta = this.value == "" ? null : this.value;

        $('.openStoragePasta').attr('disabled', false);
        $(this).attr('disabled', true);
        receberArquivos(openStorage_id, pasta);
    });

    $("#armazenamento").on('shown.bs.modal', function(){
        $('.openStoragePasta[value=""]').attr('disabled', true);
        $('.openStoragePasta[value!=""]').attr('disabled', false);
    });

    $("#armazenamento").on('hidden.bs.modal', function(){
        $('#armazenamento #msgStorage').hide();
        $("#filtrarFile").val('');
        limparTabelaStorage();
        $('#armazenamento .custom-file-label').text('Selecionar arquivo...');
    });

    $("#header_fundo_cor").change(function(){
        limparHeaderFundo();
    });

    $("#header_fundo_default").change(function(){
        limparHeaderFundo('');
    });

    $('#popup_video_vazio, #popup_video_default').change(function(){
        if(this.checked)
            $('#popup_video_novo').val('');
    });

    $('#armazenamento #file_itens_home').change(function(e){
        if($(this).val() == '')
            return;
    
        validarArquivo(e.target.files[0], ['image/png', 'image/jpeg'])
        .then(function(value) {
            if(typeof value === 'string'){
                msgGerenciarArquivo(value, false);
                return;
            }

            let form = new FormData();
            form.append('_method', "POST");
            form.append('_token', $('meta[name="csrf-token"]').attr('content'));
            form.append('file_itens_home', e.target.files[0]);
        
            ajaxAdicionaArquivo(form);
        });
    });

    $('.modal-footer').on('click', '#deleteFileStorage', function(){
        let arquivo = this.value;
    
        document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
        ajaxRemoveArquivo(arquivo);
    });

    $(document).on('ITENS_SELECIONAR', function(e){
        $('.storagePath').on('click', function(){
            $('#' + e.detail).val(this.value);
            $("#armazenamento").modal("hide");
        });
    });

    $(document).on('ITENS_EXCLUIR', function(e){
        if(e.detail == pasta_img_principal)
            return;
        
        $('.deleteFileStorage').on('click', function(){
            document.dispatchEvent(new CustomEvent("MSG_GERAL_VARIOS_BTN_ACAO", {
                detail: {
                    layout: {header: 'bg-warning'},
                    titulo: '<i class="fas fa-trash"></i> Excluir arquivo', 
                    texto: 'Tem certeza que deseja excluir o arquivo "<b><span class="font-italic">' + this.value + '</span></b>" da pasta?',
                    botao: ['<button type="button" class="btn btn-danger" id="deleteFileStorage" value="' + this.value + '">Sim</button>', 
                        '<button type="button" class="btn btn-secondary" data-dismiss="modal">Não</button>']
                }
            }));

        });
    });
};

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}

export let scripts_para_importar = {
    modulo: ['lazy-load-img', 'validar-arquivos'], 
    local: ['modulos/', 'modulos/']
};
