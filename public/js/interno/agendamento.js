function selectAtendenteByStatus(valor)
{
    $('#idusuarioAgendamento option').show();
    $('#idusuarioAgendamento option').each(function(){
        let idUser = $(this).val();
        if((valor == '') && (idUser != ''))
            $(this).hide();
        if((valor == 'Compareceu') && (idUser == ''))
            $(this).hide();
    });
    
    if(valor != 'Compareceu')
        $('#idusuarioAgendamento')[0].selectedIndex = 0;
}

function editar(){

    $('#statusAgendamentoAdmin').change(function(){
        let valor = $('#statusAgendamentoAdmin').val();
        selectAtendenteByStatus(valor);
    });
      
    $('#statusAgendamentoAdmin').ready(function(){
        if($('#statusAgendamentoAdmin').length > 0){
            let valor = $('#statusAgendamentoAdmin').val();
            selectAtendenteByStatus(valor);
        }
    });

};

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
