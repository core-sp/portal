function editar(){

    $('#cedula').submit(function() {
        let rg = $('#rg').val().replace(/[^a-zA-Z0-9]/g, '');
        let cpf = $('#cpf').val().replace(/\D/g, '');

        $('#rg').val(rg);
        $('#cpf').val(cpf);
    });
}

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
