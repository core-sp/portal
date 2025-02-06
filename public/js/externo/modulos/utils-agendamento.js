const cor_desabilitado = '#e9ecef';
const cor_habilitado = '#FFFFFF';
let lotados = [];

// Função para adicionar feriados
function diasLotados(date) {
    for (let i = 0; i < lotados.length; i++) {
		if((date.getMonth() == lotados[i][0] - 1) && (date.getDate() == lotados[i][1])) {
			let habilita = lotados[i][2] === 'agendado';
			let texto = lotados[i][2] === 'agendado' ? 'Seu agendamento. Dia disponível, menos no(s) período(s) que está agendado.' : '';
			
			return [habilita, lotados[i][2], texto];
		}
	}

	return [true, ''];
}

// Função para feriados, fim-de-semana e dias lotados
function noWeekendsOrHolidays(date) {

	let noWeekend = $.datepicker.noWeekends(date);
	let lotado = diasLotados(date);

	return !noWeekend[0] ? noWeekend : lotado;
}

export function inicializa(){

	$('#datepicker').datepicker({
		dateFormat: 'dd/mm/yy',
		dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
		dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
		dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
		monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
		monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
		nextText: 'Próximo',
		prevText: 'Anterior',
		maxDate: '+1m',
		minDate: +1,
		beforeShowDay: noWeekendsOrHolidays
	});

	$(document).on('AGENDA_RESET', function(e){
		if(e.detail == 'datepicker')
			return;
		
		$('#datepicker').val('')
        .prop('disabled', true).prop('placeholder', 'dd/mm/aaaa')
        .css('background-color', cor_desabilitado);
	});

	$(document).on('AGENDA_ERRO', function(){
		$('#datepicker').val('')
        .prop('disabled', true).prop('placeholder', 'Falha ao recuperar calendário')
		.css('background-color', cor_desabilitado);
	});

	$(document).on('AGENDA_DTPICKER', function(e){
		lotados = e.detail.lotados;
		$('#datepicker').css('background-color', cor_habilitado)
		.prop('disabled', false).prop('placeholder', 'dd/mm/aaaa');
	});

	$(document).on('AGENDA_EMPTY_DTPICKER', function(){
		$('#datepicker').prop('disabled', true).prop('placeholder', 'Sem datas disponíveis').val('');
	});

	$(document).on('AGENDA_OPTIONS_DTPICKER', function(e){
		$('#datepicker').prop('placeholder', 'dd/mm/aaaa').datepicker('option', e.detail);
	});
}