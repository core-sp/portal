const inicio_url = '/admin/textos/';
let token = $('meta[name="csrf-token"]').attr('content');

function montarAjax(acao, valor){

    let url = inicio_url + $('#tipo_doc').val() + '/';

    switch(acao) {
        case 'carregar':
            return {textoLoading: 'Carregando texto...', link: url + valor, metodo: 'GET', dados: {}};
        case 'atualizar':
            return {textoLoading: 'Atualizando campos...', link: url + valor, metodo: 'POST', dados: {
              _token: token,
              tipo: $('#tipo').val(),
              texto_tipo: $('#texto_tipo').val(),
              com_numeracao: $('#com_numeracao').val(),
              nivel: $('#nivel').val(),
              conteudo: tinymce.get('conteudo').getContent(),
            }};
        case 'excluir_varios':
            return {textoLoading: 'Excluindo textos...', link: url + 'excluir', metodo: 'DELETE', dados: {
              _token: token,
              excluir_ids: valor
            }};
    }
}

function crudGerarTexto(acao, valor){

    let objeto = montarAjax(acao, valor);
  
    $.ajax({
        url: objeto.link,
        method: objeto.metodo,
        dataType: "json",
        data: objeto.dados,
        beforeSend: function(){
            document.dispatchEvent(new CustomEvent("MSG_GERAL_CARREGAR_CONTEUDO", {
                detail: {
                    layout: {load_cor: 3},
                    texto: '&nbsp;&nbsp;' + objeto.textoLoading,
                }
            }));
        },
        success: function(response) {
            atualizarViewTexto(acao, valor, response);
        },
        error: function(erro, textStatus, errorThrown) {
            let resposta = erro.status == 422 ? JSON.stringify(erro.responseJSON.errors) : 
            'Código: ' + erro.status + ' | Mensagem: ' + erro.responseJSON.message;
            gerarTextoAvisosCrud('erro', resposta);
        }
    });
} 
  
function textoAoCarregar(resultado, titulo = true){

    let cor = titulo ? 'warning' : 'dark';
    let upper = titulo ? 'text-uppercase' : '';
    let conteudo = resultado.conteudo !== null ? resultado.conteudo : '';
    let indice = resultado.indice !== null ? resultado.indice : '';

    $('#span-tipo').attr('class', 'text-' + cor).text(resultado.tipo);
    $('#span-nivel').text(resultado.nivel);
    $('#span-texto_tipo').attr('class', upper).text(indice + ' - ' + resultado.texto_tipo);
    $('#texto_tipo').val(resultado.texto_tipo);
    $('#tipo option[value="' + resultado.tipo + '"]').prop('selected', true);
    $('#com_numeracao option[value="' + resultado.com_numeracao + '"]').prop('selected', true);

    try {
        tinymce.activeEditor.setContent(conteudo);
        document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
    } catch (error) {
        console.log(error);
        gerarTextoAvisosCrud('erro', '<strong>Clique novamente no texto.</strong>');
    } finally{
        hideShowOptions(resultado.nivel);
    }
}

function textoAoAtualizar(resultado, titulo = true){
  
    let cor = titulo ? 'warning' : 'dark';
    let upper = titulo ? 'text-uppercase' : '';

    $('#span-tipo').attr('class', 'text-' + cor).text(resultado.tipo);
    $('#span-nivel').text(resultado.nivel);
    $('#span-texto_tipo').attr('class', upper).text(resultado.texto_tipo);
    $('button[value="' + resultado.id + '"] .indice-texto').text(resultado.texto_tipo);
}

function atualizarViewTexto(acao, valor, response){

    $('.updateCampos').val(valor);
    $('.deleteTexto').val(valor);

    if(response.length == 0){
        gerarTextoAvisosCrud('erro', '<strong>Texto não existe! Por favor, atualize a página!</strong>');
        return;
    }

    let resultado = acao == 'carregar' ? response[0] : response;
    let titulo = resultado.tipo == 'Título';

    switch (acao) {
        case 'atualizar':
            textoAoAtualizar(resultado, titulo);
            break;
        case 'carregar':
            textoAoCarregar(resultado, titulo);
            break;
        default:
            break;
    }

    gerarTextoAvisosCrud(acao, valor, response);
}

function msgExcluir(response, valor, title = '', texto = ''){

    if(response !== null){
        response.forEach(function(id, i){
            $('button[value="' + id + '"]').parents('.form-check').remove();
        });

        $('#lista').hide();
        selecionarTodos();
        msgRetorno('excluir_varios', title, texto);
        return;
    }

    let textos_ids = '';
    let valor_final = JSON.parse(valor);
    let text = valor_final.length > 1 ? 'todos estes textos selecionados' : 'este texto';
  
    valor_final.forEach(function(id, i){
        textos_ids += '<strong>Texto: </strong><i>' + $('button[value="' + id + '"]').text() + '</i><br>';
    });

    document.dispatchEvent(new CustomEvent("MSG_GERAL_VARIOS_BTN_ACAO", {
        detail: {
            layout: {sem_txt_center: true},
            titulo: '<i class="fas fa-trash" style="color: #dc0909;"></i> Excluir', 
            texto: textos_ids + 'Tem certeza que deseja excluir ' + text + '?<br>Esta ação não é reversível!',
            botao: ['<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>', 
                '<button type="button" class="btn btn-danger" id="excluirTexto" value="' + valor_final + '">Sim</button>']
        }
    }));
}
  
function msgRetorno(acao, title, txt){

    document.dispatchEvent(new CustomEvent("MSG_GERAL_CONT_TITULO", {
        detail: {
            layout: {sem_txt_center: true},
            titulo: title, 
            texto: txt,
            timeout: acao != 'erro' ? 2500 : null,
        }
    }));
}

function gerarTextoAvisosCrud(acao, valor, response = null){

    let title = acao == 'erro' ? '<i class="fas fa-times" style="color: #e70d0d;"></i> Erro!' : 
    '<i class="fas fa-check-circle" style="color: #40c011;"></i> Sucesso!';
  
    switch (acao) {
        case 'excluir_varios':
            msgExcluir(response, valor, title, 'Exclusão realizada com sucesso!<br>Se algum item permanecer, por favor atualize a página.');
            break;
        case 'atualizar':
            msgRetorno(acao, title, 'Campos do texto foram atualizados!');
            break;
        case 'erro':
            msgRetorno(acao, title, valor);
            break;
    }
}

function hideShowOptions(nivel = 1){

    let titulo = $(".textoTipo").val() == 'Título';

    titulo ? $('#nivel option').hide() : $('#nivel option').show();
        
    $('#nivel option').each(function(){
        if($(this).val() == '0')
            titulo ? $(this).show() : $(this).hide();
    });

    $('#nivel')[0].selectedIndex = titulo ? 0 : nivel;
    $('#com_numeracao option').show();

    if(!titulo)
        $('#com_numeracao option').each(function(){
            if($(this).val() == '0')
                $(this).hide();
        });

    if(titulo && !$('#texto_tipo').hasClass('text-uppercase'))
        $('#texto_tipo').addClass('text-uppercase');

    if(!titulo && $('#texto_tipo').hasClass('text-uppercase'))
        $('#texto_tipo').removeClass('text-uppercase');
}

function selecionarTodos(inverso = false){

    let nao_check = $('[name="excluir_ids"]:checked').length <= 0;
    let texto = nao_check ? 'Selecionar Todos' : 'Limpar Seleção';
    let quadrado = nao_check ? '<i class="fas fa-check-square"></i>' : '<i class="fas fa-square"></i>';
  
    if(inverso){
        texto = nao_check ? 'Limpar Seleção' : 'Selecionar Todos';
        quadrado = nao_check ? '<i class="fas fa-square"></i>' : '<i class="fas fa-check-square"></i>';
    }
    
    $('.selecionarTextos').html(quadrado + '&nbsp;&nbsp;' + texto);
}

function editar(){

    $(".criarTexto, #publicarTexto").click(function(){
        let link = this.id == 'publicarTexto' ? inicio_url + 'publicar/' + $('#tipo_doc').val() : inicio_url + $('#tipo_doc').val();
        let form = '<form action="' + link + '" method="POST"><input type="hidden" name="_token" value="' + token + '">';

        if(this.id == 'publicarTexto')
            form += '<input type="hidden" name="publicar" value="' + $(this).val() + '">';

        form = $(form + '</form>');
        $('body').append(form);
        $(form).submit();
    });

    $(".updateCampos").click(function(){
        crudGerarTexto('atualizar', $(this).val());
    });

    $(".deleteTexto").click(function(){
        if($(".deleteTexto").length > 0)
            gerarTextoAvisosCrud('excluir_varios', JSON.stringify([$(this).val()]));
    });

    $('.modal-footer').on('click', '#excluirTexto', function(){
        crudGerarTexto('excluir_varios', $(this).val());
    });

    $(".excluirTextos").click(function(){
        let excluirIds = [];

        if($('[name="excluir_ids"]:checked').length > 0){
            $('[name="excluir_ids"]:checked').each(function(){
                excluirIds.push($(this).val());
            });

            gerarTextoAvisosCrud('excluir_varios', JSON.stringify(excluirIds));
        }
    });

    $(".textoTipo").change(function(){
        hideShowOptions();
    });

    $("#updateIndice").click(function(){
        document.dispatchEvent(new CustomEvent("MSG_GERAL_CARREGAR_CONTEUDO", {
            detail: {
                layout: {load_cor: 3},
                texto: '&nbsp;&nbsp;Atualizando a índice...',
            }
        }));
    });

    // link no sumário para abrir e ir no texto
    $('button.abrir').click(function(){
        crudGerarTexto('carregar', $(this).val());
        $('#lista').hide();
        $('#lista').show();
        $('#tipo').focus();
    });

    if($('button.abrir .badge').length > 0)
        $('button.abrir .badge').click();
  
    $('[name="excluir_ids"]').change(function(){
        selecionarTodos();
    });
  
    $('.selecionarTextos').click(function(){
        selecionarTodos(true);
        let selecionados = $('[name="excluir_ids"]:checked').length <= 0;

        $('[name="excluir_ids"]').prop('checked', selecionados);
        $('[name="excluir_ids"]:first').prop('checked', false);
    });

};

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
