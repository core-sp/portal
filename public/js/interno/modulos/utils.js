function menuAtivoDinamico(){
    // Função para tornar menu ativo dinâmico
    let url = window.location;

    $('ul.nav-sidebar a').filter(function() {
      return this.href == url;
    }).addClass('active');

    $('ul.nav-treeview a').filter(function() {
      return this.href == url;
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open')
    .prev('a').addClass('active');
}

function logout(){
    $("#logout-interno").click(function(){
        let token = $('meta[name="csrf-token"]').attr('content');
        let link = "/admin/logout";
        let form = $('<form action="' + link + '" method="POST"><input type="hidden" name="_token" value="' + token + '"></form>');
        $('body').append(form);
        $(form).submit();
    });
}

function sortable(){
    // Draggable
    $("#sortable").sortable();
    $("#sortable").disableSelection();
        let icons = {
        header: "fas fa-angle-right",
        activeHeader: "fas fa-angle-down"
    };

    $(".textosSortable").sortable({
        items: "> div > div > div.form-check",
    });
    $(".textosSortable").disableSelection();
}

function filtrar(){
    // Filtro dinâmico do bootstrap
    $("#myInput").on("keyup", function() {
        let value = $(this).val().toLowerCase();
        $("#myTable tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Filtro de data usado em filtro da listagem (visualizar) dos serviços
    $('#filtroDate').submit(function(e){
        let maxDataFiltro = $('#datemax').val();
        let minDataFiltro = $('#datemin').val();
        if(new Date(minDataFiltro) > new Date(maxDataFiltro)) {
            document.dispatchEvent(new CustomEvent("MSG_GERAL_CONT_TITULO", {
                detail: {
                    titulo: '<i class="fas fa-times text-danger"></i> Data inválida!', 
                    texto: '<span class="text-danger">A data inicial deve ser menor ou igual a data de término.</span>'
                }
            }));
            $('#datemin').focus();
            e.preventDefault();
        }
    });
}

function btnAcaoTabelaAdmin(){

    $('.acaoTabelaAdmin').click(function(){
        let conteudo = $(this).find('.txtTabelaAdmin');
        let cor = conteudo[0].classList[0].replace('cor-', '');

        document.dispatchEvent(new CustomEvent("MSG_GERAL_VARIOS_BTN_ACAO", {
            detail: {
                layout: {fade: true, header: 'bg-' + cor},
                titulo: $(this).find('button').text(), 
                texto: conteudo.val(),
                botao: ['<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Não</button>', 
                    '<button type="button" class="btn btn-' + cor + '" id="simTabelaAdmin" value="' + $(this).find('button').val() + '">Sim</button>']
            }
        }));
    });

    $('.modal-footer').on('click', '#simTabelaAdmin', function(){
        document.dispatchEvent(new CustomEvent("MSG_GERAL_CARREGAR"));
        let form = $('.acaoTabelaAdmin button[value="' + this.value + '"]').parents('.acaoTabelaAdmin');
        form.submit();
    });
}

export function executar(local = 'interno'){

    menuAtivoDinamico();
    logout();

    $(".custom-file-input").on("change", function(e) {
        let fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });

    $('.toast').toast({delay: 2000});
    $('.toast').toast('show');

    // Recusar formulários
    $('#recusar-trigger').on('click', function(){
        $('#recusar-form').toggle();
    });
    
    sortable();
    filtrar();

    $('.loadingPagina').on('click', function(){
		document.dispatchEvent(new CustomEvent("MSG_GERAL_CARREGAR"));
	});

    if($('.loadingPagina').length > 0)
        $('input, select, textarea').on("invalid", function(e){
            document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
        });
    
    btnAcaoTabelaAdmin();
};
