function ajaxAgendamentoBloqueio(valor)
{
    $.ajax({
        method: "GET",
        data: {
            "idregional": valor,
        },
        dataType: 'json',
        url: "/admin/agendamentos/bloqueios/dados-ajax",
        success: function(response) {
            let horas_atendentes = response;
            setCamposAgeBloqueio(horas_atendentes);
        },
        error: function() {
            alert('Erro ao carregar os horários. Recarregue a página.');
        }
    });
}

function optionTodas(valor)
{
    if(valor == 'Todas'){
        $('#horarios').prop("disabled", true);
        $('#qtd_atendentes').val(0);
        $('#qtd_atendentes').text("0");
        return;
    }
        
    $('#horarios').prop("disabled", false);
}

function setCamposAgeBloqueio(horas_atendentes)
{
    $('#horarios option').show();
    $('#horarios option').each(function(){
        let valor = $(this).val();
        jQuery.inArray(valor, horas_atendentes['horarios']) != -1 ? $(this).show() : $(this).hide();
    });

    $('#totalAtendentes').text(horas_atendentes['atendentes']);
}

function formatarSelect(){

    let valor = $('#idregionalBloqueio').val();
    optionTodas(valor);
    if(valor > 0)
        ajaxAgendamentoBloqueio(valor);
}

function editar(){

    if($('#idregionalBloqueio').length > 0)
        formatarSelect();

    $('#idregionalBloqueio').change(function(){
        formatarSelect();
    });

};

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
