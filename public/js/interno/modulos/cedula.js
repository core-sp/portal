function editar(){

    if($('.cedula_recusada').length > 0)
        $('[name="justificativa"]').val('');

};

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
