function visualizar(){

    if($('#corpoTexto').length > 0)
        $('#corpoTexto').focus();

    $('#textosSumario').change(function(){
        let link = "/carta-de-servicos-ao-usuario/" + $('#textosSumario').val();
        window.location.replace(window.location.protocol + "//" + window.location.host + link);
    });
}

export function executar(funcao){
    if(funcao == 'visualizar')
        return visualizar();
}
