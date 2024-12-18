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

    // $(".textosSortable").sortable({
    //     items: "> div > div > div.form-check",
    // });
    // $(".textosSortable").disableSelection();
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
            alert('Data inválida. A data inicial deve ser menor ou igual a data de término.');
            $('#datemin').focus();
            e.preventDefault();
        }
    });
}

export function executar(local = 'interno'){

    menuAtivoDinamico();
    logout();

    $(".custom-file-input").on("change", function(e) {
        let fileName = e.target.files[0].name;
        $(this).next('.custom-file-label').html(fileName);
    });

    $('.toast').toast({delay: 2000});
    $('.toast').toast('show');

    // Recusar formulários
    $('#recusar-trigger').on('click', function(){
        $('#recusar-form').toggle();
    });
    
    sortable();
    filtrar();
};
