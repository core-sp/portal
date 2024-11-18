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
        selectAtendenteByStatus($(this).val());
    });
      
    if($('#statusAgendamentoAdmin').length > 0)
        selectAtendenteByStatus($('#statusAgendamentoAdmin').val());

};

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
