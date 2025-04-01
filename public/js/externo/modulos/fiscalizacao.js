function visualizar(){

    $('.mapa_regional').on("click", function() {
		$(".dado-regional").addClass('d-none');
		$(".mapa_regional").removeClass("regional-selecionada");
		$("#instrucao-mapa").addClass('dado-oculto');

		$("#dado-" + $(this).attr('id')).removeClass('d-none');
		$("#" + $(this).attr('id')).addClass("regional-selecionada");
    });

    $('#ano-mapa').on("change", function() {
        window.location.href = "/mapa-fiscalizacao/" + $(this).val();
    });
}

export function executar(funcao){
    if(funcao == 'visualizar')
        return visualizar();
}
