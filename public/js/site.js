$(document).ready(function(){	
	$('.saiba-mais').on('click', function(){
		var saibamais = $(this);
		var bdoinfo = saibamais.prev('.bdo-info');
		bdoinfo.slideToggle(function(){
			if(bdoinfo.is(':visible')) {
				saibamais.html('<i class="fas fa-angle-double-up"></i>&nbsp;&nbsp;Menos Detalhes');
			} else {
				saibamais.html('<i class="fas fa-angle-double-down"></i>&nbsp;&nbsp;Mais Detalhes');
			}
		});
	});
});

$(window).scroll(function(){
	if ($(document).scrollTop() > 300) {
		$('#fixed-menu').slideDown(150);
	} else {
		$('#fixed-menu').hide();
	}
});

var primeira = document.getElementById('menu-principal');
var segunda = document.getElementById('append-menu');

segunda.innerHTML = primeira.innerHTML;

(function($){
	$(function(){
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
			minDate: 0,
			beforeShowDay: $.datepicker.noWeekends,
			defaultDate: +1
		});
	});
})(jQuery);