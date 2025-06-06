function desabilitaHabilitaCampoAdd(valor){
    let desabilita = valor == '0';
    let obr = !desabilita;

    if(desabilita)
        $('select[name="campo_rotulo"] option[value=""]').prop('selected', true);

    $('select[name="campo_rotulo"], select[name="campo_required"]').prop('disabled', desabilita).prop('required', obr);
}

function abrirModalCidade(detail){

    document.dispatchEvent(new CustomEvent("MSG_GERAL_VARIOS_BTN_ACAO", {
        detail: {
            layout: {sem_txt_center: true, fade: true},
            titulo: '<i class="fas fa-map-marked-alt mr-2"></i>' + detail.titulo + '<br><small class="form-text" style="font-size: 0.8rem;"><em>* ' + detail.sobre + '</em></small>', 
            texto: '<div class="form-group"><label for="nome_cidade">' + detail.label + '</label><input type="text" id="nome_cidade" class="form-control" /></div>',
            botao: ['<button type="button" class="btn btn-success btn-sm" id="salvar_cidade">Salvar</button>', 
                '<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>'],
            focus: '#nome_cidade',
        }
    }));
}

function adicionarCidade(){

    let cidade_temp = $('#nome_cidade').val();
    
    if(cidade_temp.length > 2){
        $('#idregional').append('<option class="text-success" value="' + cidade_temp + '">' + cidade_temp + '</option>');
        $('#idregional option[value="' + cidade_temp + '"]').attr('selected', true);
        $('#idregional').change();
    }

    document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
}

function editarCidade(){

    $.ajax({
        url: "/admin/cursos/cidade/edit",
        method: "POST",
        dataType: "json",
        data: {
            _method: "PATCH",
            _token: $('meta[name="csrf-token"]').attr('content'),
            cidade: $('#idregional option:selected').val(),
            alterar_cidade: $('#nome_cidade').val()
        },
        beforeSend: function(){
            document.dispatchEvent(new CustomEvent("MSG_GERAL_CARREGAR"));
        },
        success: function(response) {
            $('#idregional option:selected').html(response.alterar_cidade).val(response.alterar_cidade);
            document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
        },
        error: function(erro, textStatus, errorThrown) {
            let resposta = erro.status == 422 ? 
                JSON.stringify(erro.responseJSON.errors).split(':')[1].replaceAll(/[\{\}\[\]\"]/g, '') : 
                'Código: ' + erro.status + ' | Mensagem: ' + erro.responseJSON.message;

            document.dispatchEvent(new CustomEvent("MSG_GERAL_CONT_TITULO", {
                detail: {titulo: 'Erro!', texto: resposta}
            }));
        }
    });
}

function gerenciarCidade(){

    let acao = null;

    $('#nova_cidade').click(function(){
        this.dispatchEvent(new CustomEvent("CURSO_ADD_CIDADE", {
            detail: {
                titulo: 'Adicionar nova cidade ',
                sobre: 'A nova cidade só será criada após confirmar a alteração / criação do curso',
                label: 'Nome da nova cidade: ',
            }
        }));
    });

    $('#editar_cidade').click(function(){
        let opcao = $('#idregional option:selected').val();

        if(opcao.match(/\D/g) === null)
            return false;

        this.dispatchEvent(new CustomEvent("CURSO_EDIT_CIDADE", {
            detail: {
                titulo: 'Editar cidade já criada',
                sobre: 'Somente cidades já criadas podem ser editadas',
                label: 'Alterar nome da cidade <span class="text-primary font-italic" id="cidade_selecionada">' + opcao + '</span>: ',
            }
        }));
    });

    $('#nova_cidade').on('CURSO_ADD_CIDADE', function(e){
        abrirModalCidade(e.detail);
        acao = adicionarCidade;
    });

    $('#editar_cidade').on('CURSO_EDIT_CIDADE', function(e){
        abrirModalCidade(e.detail);
        acao = editarCidade;
    });

    $('.modal-footer').on('click', '#salvar_cidade', function(){
        acao();
        acao = null;
    });
    
    $('#idregional').change(function(){
        let desabilitar = (this.value.match(/\D/g) === null) || $(this.selectedOptions[0]).hasClass('text-success');
        $('#editar_cidade').prop('disabled', desabilitar);
    });
    
    $('.modal').on('input keyup', '#nome_cidade', function(e){
        if((e.type == 'keyup') && (e.keyCode == 13))
            $('.modal-footer #salvar_cidade').click();
    });
}

function editar(){

    $('#curso_adm [data-toggle="popover"]').popover({
        content: '<b>Clique em <i class="fas fa-plus text-success"></i> para adicionar uma nova cidade (em verde), mas ainda não será criada.</b><br>' + 
            '<hr /><b>Clique em <i class="far fa-edit text-info"></i> para editar uma cidade já criada (em azul) e os cursos com esta cidade serão alterados.</b>',
        html: true,
    });

    if($('select[name="add_campo"]').length > 0)
        desabilitaHabilitaCampoAdd($('select[name="add_campo"]').val());
    
    $('select[name="add_campo"]').change(function(){
        desabilitaHabilitaCampoAdd($(this).val());
    });

    if($('#curso_adm').length > 0)
        gerenciarCidade();

    $('#curso_adm').submit(function(){
        if($('#idregional').val().match(/\D/g) !== null)
            $('#idregional').attr('name', 'cidade');
    });
};

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
