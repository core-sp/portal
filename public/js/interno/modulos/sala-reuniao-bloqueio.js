function setCampoHorariosSala(sala){

    $('#horariosBloqueio option').show();
    $('#horariosBloqueio option').each(function(){
        jQuery.inArray($(this).val(), sala) != -1 ? $(this).show() : $(this).hide();
    });
}

function ajaxSalaBloqueio(valor){

    $.ajax({
        method: "GET",
        data: {
            "id": valor,
        },
        dataType: 'json',
        url: "/admin/salas-reunioes/bloqueios/horarios-ajax",
        success: function(response) {
            let sala = response;
            setCampoHorariosSala(sala);
        },
        error: function() {
            document.dispatchEvent(new CustomEvent("MSG_GERAL_CONT_TITULO", {
                detail: {
                    titulo: '<i class="fas fa-times text-danger"></i> Erro!', 
                    texto: '<span class="text-danger">Erro ao carregar os horários. Recarregue a página.</span>'
                }
            }));
        }
    });
}

function chamarAjax(){

    let valor = $('#salaBloqueio').val();

    if(valor > 0)
        ajaxSalaBloqueio(valor);
}

function editar(){

    if($('#salaBloqueio').length > 0)
        chamarAjax();

    $('#salaBloqueio').change(function(){
        chamarAjax();
    });

};

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
