function setCamposDatas(plantao, tipo)
{
    if(tipo == 'change'){
        $("#dataInicialBloqueio").val('');
        $("#dataFinalBloqueio").val('');
    }

    $("#dataInicialBloqueio").prop('min', plantao['datas'][0]).prop('max', plantao['datas'][1]);
    $("#dataFinalBloqueio").prop('min', plantao['datas'][0]).prop('max', plantao['datas'][1]);

    let inicial = new Date(plantao['datas'][0] + ' 00:00:00');
    let final = new Date(plantao['datas'][1] + ' 00:00:00');
    let inicialFormatada = inicial.getDate() + '/' + (inicial.getMonth() + 1) + '/' + inicial.getFullYear(); 
    let finalFormatada = final.getDate() + '/' + (final.getMonth() + 1) + '/' + final.getFullYear(); 

    $("#bloqueioPeriodoPlantao").text(inicialFormatada + ' - ' + finalFormatada);
}

function setCampoHorarios(plantao)
{
    $('#horariosBloqueio option').show();
    $('#horariosBloqueio option').each(function(){
        let valor = $(this).val();
        jQuery.inArray(valor, plantao['horarios']) != -1 ? $(this).show() : $(this).hide();
    });
}

function setCampoAgendados(plantao)
{
    if(plantao['link-agendados'] != null)
    {
        $('#textoAgendados').prop('class', 'mb-3');
        $('#linkAgendadosPlantao').prop('href', plantao['link-agendados']);
        return;
    }

    $('#textoAgendados').prop('class', 'text-hide');
}

function ajaxPlantaoJuridico(valor, e)
{
    $.ajax({
        method: "GET",
        data: {
            "id": valor,
        },
        dataType: 'json',
        url: "/admin/plantao-juridico/ajax",
        success: function(response) {
            let plantao = response;
            setCampoAgendados(plantao);
            setCamposDatas(plantao, e.type);
            setCampoHorarios(plantao);
        },
        error: function() {
            alert('Erro ao carregar as datas e/ou os horários. Recarregue a página.');
        }
    });
}

function chamarAjax(e){
    
    let valor = $('#plantaoBloqueio').val();

    if(valor > 0)
        ajaxPlantaoJuridico(valor, e);
}

function editar(){

    $('#plantaoBloqueio').ready(function(e){
        chamarAjax(e);
    });

    $('#plantaoBloqueio').change(function(e){
        chamarAjax(e);
    });
};

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
