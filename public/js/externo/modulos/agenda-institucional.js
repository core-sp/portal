function visualizar(){

	$("#agenda-institucional").datepicker({
		dateFormat: 'dd-mm-yy',
		todayHighlight: false,
		dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
		dayNamesMin: ['D','S','T','Q','Q','S','S'],
		dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'],
		monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
		monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez']
	})
	.datepicker('setDate', $('#data').text());

    $('#agenda-institucional').change(function(){
        window.location.href = "/agenda-institucional/" + $(this).val();
    });
}

export function executar(funcao){
    if(funcao == 'visualizar')
        return visualizar();
}
