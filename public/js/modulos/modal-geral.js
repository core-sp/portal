function carregar(cor){

    const cores = ['info', 'danger', 'warning', 'primary', 'success', 'secondary', 'dark'];

    if((cor === undefined) || (cor === null) || (cores.indexOf(cor) == -1))
        cor = 'info';

    $("#msgGeral .modal-header, #msgGeral .modal-footer").hide();
    $("#msgGeral .modal-body").addClass('text-center').html('<div class="spinner-border text-' + cor + '"></div>');
    $("#msgGeral").modal({backdrop: "static", keyboard: false, show: true});
}

function msgSomenteConteudo(conteudo, timeout){

    if(typeof conteudo !== "string")
        conteudo = '';

    if((timeout === undefined) || (timeout === null))
        timeout = 2250;
    timeout = parseInt(timeout);

    $("#msgGeral .modal-header, #msgGeral .modal-footer").hide();
    $("#msgGeral .modal-body").addClass('text-center').html(conteudo);
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
    $("#msgGeral .modal-body").addClass('text-center').html(conteudo);
    $("#msgGeral").modal({backdrop: "static", keyboard: false, show: true});
}

export function executar(local){

    $(document).on('MSG_GERAL_FECHAR', function(e){
        $("#msgGeral").modal('hide');
    });

    $(document).on('MSG_GERAL_CARREGAR', function(e){
        carregar(e.detail);
    });

    $(document).on('MSG_GERAL_CONTEUDO', function(e){
        msgSomenteConteudo(e.detail.texto, e.detail.timeout);
    });

    $(document).on('MSG_GERAL_CONT_TITULO', function(e){
        msgConteudoTitulo(e.detail.titulo, e.detail.texto);
    });

    $(document).on('MSG_GERAL_BTN_ACAO', function(e){
        msgConteudoTitulo(e.detail.titulo, e.detail.texto, e.detail.botao);
    });

    $(document).on('MSG_GERAL_VARIOS_BTN_ACAO', function(e){
        msgConteudoTitulo(e.detail.titulo, e.detail.texto, e.detail.botao.join(''));
    });

    $("#msgGeral").on('hide.bs.modal', function(){
        $(this).find('.modal-body, .modal-title, .modal-footer').html('');
    });
};