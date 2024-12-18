function desabilitaHabilitaCampoAdd(valor){
    let desabilita = valor == '0';
    let obr = !desabilita;

    if(desabilita)
        $('select[name="campo_rotulo"] option[value=""]').prop('selected', true);

    $('select[name="campo_rotulo"], select[name="campo_required"]').prop('disabled', desabilita).prop('required', obr);
}

function editar(){

    if($('select[name="add_campo"]').length > 0)
        desabilitaHabilitaCampoAdd($('select[name="add_campo"]').val());
    
    $('select[name="add_campo"]').change(function(){
        desabilitaHabilitaCampoAdd($(this).val());
    });
    
};

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
