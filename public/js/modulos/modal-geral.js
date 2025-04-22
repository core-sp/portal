function addTextCenter(){

    let temp = $("#msgGeral .modal-body");

    if(!temp.hasClass('mg-semTxtCenter'))
        temp.addClass('text-center');
}

function carregar(texto = ''){

    let temp = $("#msgGeral .modal-body");

    if(!temp.find('.mg-spinner'))
        temp.append('<div class="spinner-border text-info"></div>');

    temp.addClass('text-center').append(texto);
    $("#msgGeral .modal-header, #msgGeral .modal-footer").hide();
    $("#msgGeral").modal({backdrop: "static", keyboard: false, show: true});
}

function msgSomenteConteudo(conteudo, timeout){

    if(typeof conteudo !== "string")
        conteudo = '';

    if((timeout === undefined) || (timeout === null))
        timeout = 2250;
    timeout = parseInt(timeout);

    $("#msgGeral .modal-header, #msgGeral .modal-footer").hide();

    addTextCenter();
    
    $("#msgGeral .modal-body").html(conteudo);
    $("#msgGeral").modal({backdrop: "static", keyboard: false, show: true});

    setTimeout(function(){
        $("#msgGeral").modal('hide');
    }, timeout);
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
        layout(e.detail);
        carregar(e.detail);
    });

    $(document).on('MSG_GERAL_CARREGAR_CONTEUDO', function(e){
        layout(e.detail);
        carregar(e.detail.texto);
    });

    $(document).on('MSG_GERAL_CONTEUDO', function(e){
        layout(e.detail);
        msgSomenteConteudo(e.detail.texto, e.detail.timeout);
    });

    $(document).on('MSG_GERAL_CONT_TITULO', function(e){
        layout(e.detail);
        msgConteudoTitulo(e.detail.titulo, e.detail.texto);
    });

    $(document).on('MSG_GERAL_BTN_ACAO', function(e){
        layout(e.detail);
        msgConteudoTitulo(e.detail.titulo, e.detail.texto, e.detail.botao);
    });

    $(document).on('MSG_GERAL_VARIOS_BTN_ACAO', function(e){
        layout(e.detail);
        msgConteudoTitulo(e.detail.titulo, e.detail.texto, e.detail.botao.join(''));
    });

    $("#msgGeral").on('hide.bs.modal', function(){
        $(this).find('.modal-body, .modal-title, .modal-footer').html('').attr('style', "");
        $(this).attr('class', "modal");
        $(this).find('.modal-header').attr('class', "modal-header");
        $(this).find('.modal-body').attr('class', "modal-body");
    });
};