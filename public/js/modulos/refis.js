function visualizar(){

    $(document).on('change', ".nParcela", function() {

        let id = $(this).attr('id');
        let nParcela = parseFloat($('option:selected',this).attr('value'));
        let total = parseFloat($('#total' + id).attr('value'));
        let valorParcelado =  (total/nParcela).toFixed(2);

        $('#parcelamento' + id).attr('value', valorParcelado);
        $('#parcelamento' + id).html('R$ ' + valorParcelado.replace('.', ','));
    });
    
};

export function executar(funcao){
    if(funcao == 'visualizar')
        return visualizar();
}
