function editar(){

	$('#gerentiTipoContato').on('change', function(){
		$('.gerentiContato').prop("disabled", false).val('');
        $('.gerentiContato')[0].dispatchEvent(new CustomEvent("MASK", {
            detail: $(this).val()
        }));
	});

	if($('#gerentiTipoContato').is(':disabled')) {
		$('.gerentiContato')[0].dispatchEvent(new CustomEvent("MASK", {
            detail: $('#gerentiTipoContato option:selected').val()
        }));
	}

}

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
