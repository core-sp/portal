function editar(){

    $('[id^="bene-"]').change(function() {
        if((this.id == 'bene-0') && this.checked)
            $('[id^="bene-"]').each(function(){
                $(this).prop("checked", true);
            });
    });
}

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
