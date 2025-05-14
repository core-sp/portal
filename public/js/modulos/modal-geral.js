let timeout_id = null;

function limpar(msg){
    
    msg.find('.modal-body, .modal-title, .modal-footer').html('').attr('style', "");
    msg.attr('class', "modal");
    msg.find('.modal-header').attr('class', "modal-header");
    msg.find('.modal-body').attr('class', "modal-body");
}

function addTextCenter(){

    let temp = $("#msgGeral .modal-body");

    if(!temp.hasClass('mg-semTxtCenter'))
        temp.addClass('text-center');
}

function addTimeout(timeout){

    if(typeof timeout !== "number")
        return;

    timeout = parseInt(timeout);

    timeout_id = setTimeout(function(){
        $("#msgGeral").modal('hide');
    }, timeout);
}

function carregar(texto = ''){

    let temp = $("#msgGeral .modal-body");

    if(temp.find('.mg-spinner').length == 0)
        temp.append('<div class="spinner-border text-info"></div>');

    temp.addClass('text-center').append(texto);
    $("#msgGeral .modal-header, #msgGeral .modal-footer").hide();
    $("#msgGeral").modal({backdrop: "static", keyboard: false, show: true});
}

function msgSomenteConteudo(conteudo){

    if(typeof conteudo !== "string")
        conteudo = '';

    $("#msgGeral .modal-header, #msgGeral .modal-footer").hide();

    addTextCenter();
    
    $("#msgGeral .modal-body").html(conteudo);
    $("#msgGeral").modal({backdrop: "static", keyboard: false, show: true});
}

function msgConteudoTitulo(titulo, conteudo, botao = ''){

    if(typeof titulo !== "string")
        titulo = '';

    if(typeof conteudo !== "string")
        conteudo = '';

    (typeof botao === "string") && (botao.length > 0) ? 
        $("#msgGeral .modal-footer").append($(botao)).show() : $("#msgGeral .modal-footer").html('').hide();
    $("#msgGeral .modal-header .modal-title").html(titulo);
    $("#msgGeral .modal-header").show();

    addTextCenter();

    $("#msgGeral .modal-body").html(conteudo);
    $("#msgGeral").modal({backdrop: "static", keyboard: false, show: true});
}

function layout(detail){

    if((detail === null) || (detail.layout === undefined))
        return;

    const cores = ['info', 'danger', 'warning', 'primary', 'success', 'secondary', 'dark'];

    if(detail.layout.fade === true)
        $("#msgGeral").addClass('fade');

    if(detail.layout.header !== undefined)
        $("#msgGeral .modal-header").addClass(detail.layout.header);

    if(detail.layout.sem_txt_center === true)
        $("#msgGeral .modal-body").addClass('mg-semTxtCenter');

    if(detail.layout.load_cor !== undefined)
        $("#msgGeral .modal-body").html('<div class="mg-spinner spinner-border text-' + cores[detail.layout.load_cor] + '"></div>');
}

export function executar(local){

    $(document).on('MSG_GERAL_FECHAR', function(e){
        $("#msgGeral").modal('hide');
    });

    $(document).on('MSG_GERAL_CARREGAR', function(e){
        limpar($("#msgGeral"));
        layout(e.detail);
        carregar(e.detail);
    });

    $(document).on('MSG_GERAL_CARREGAR_CONTEUDO', function(e){
        limpar($("#msgGeral"));
        layout(e.detail);
        carregar(e.detail.texto);
    });

    $(document).on('MSG_GERAL_CONTEUDO', function(e){
        limpar($("#msgGeral"));
        layout(e.detail);
        msgSomenteConteudo(e.detail.texto);
        typeof e.detail.timeout !== "number" ? addTimeout(2250) : addTimeout(e.detail.timeout);
    });

    $(document).on('MSG_GERAL_CONT_TITULO', function(e){
        limpar($("#msgGeral"));
        layout(e.detail);
        msgConteudoTitulo(e.detail.titulo, e.detail.texto);
        addTimeout(e.detail.timeout);
    });

    $(document).on('MSG_GERAL_BTN_ACAO', function(e){
        limpar($("#msgGeral"));
        layout(e.detail);
        msgConteudoTitulo(e.detail.titulo, e.detail.texto, e.detail.botao);
    });

    $(document).on('MSG_GERAL_VARIOS_BTN_ACAO', function(e){
        limpar($("#msgGeral"));
        layout(e.detail);
        msgConteudoTitulo(e.detail.titulo, e.detail.texto, e.detail.botao.join(''));
    });

    $("#msgGeral").on('hide.bs.modal', function(){
        if(this.contains(document.activeElement))
            document.activeElement.blur();

        if(timeout_id !== null)
            clearTimeout(timeout_id);
        limpar($(this));
    });
};