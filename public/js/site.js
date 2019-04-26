$(document).ready(function(){	
	// Máscaras
	$('#datepicker').mask("99/99/9999");
	$('.cpfInput').mask('999.999.999-99');
	$('.celularInput').mask('(99) 99999-9999');
	$('.nrlicitacaoInput').mask('999/9999');
	$('.nrprocessoInput').mask('999/9999');
	$('.dataInput').mask('00/00/0000');
	$('.protocoloInput').mask('ZZZZZZ', {
	  translation: {
		  'Z': {
			pattern: /[A-Za-z0-9]/
		  }
	  }
	});
	// Menu responsivo
	var first = document.getElementById('menu-principal');
	var second = document.getElementById('sidebarContent');
	second.innerHTML = first.innerHTML;
	$('#sidebarBtn').on('click', function(){
		$('#sidebar').toggleClass('leftando');
		$('.overlay').toggleClass('active');
	});
	$('.overlay, #dismiss').on('click', function(){
		$('.overlay').toggleClass('active');
		$('#sidebar').toggleClass('leftando');
	});
	$('.dropdown').on('show.bs.dropdown', function() {
		$(this).find('.dropdown-menu').first().stop(true, true).slideDown(200);
	});
	$('.dropdown').on('hide.bs.dropdown', function() {
		$(this).find('.dropdown-menu').first().stop(true, true).slideUp(200);
	  });
});

$(window).scroll(function(){
	if($(window).width() > 767) {
		if ($(document).scrollTop() > 300) {
			$('#fixed-menu').slideDown(150);
		} else {
			$('#fixed-menu').hide();
		}
	}
});

var primeira = document.getElementById('menu-principal');
var segunda = document.getElementById('append-menu');

segunda.innerHTML = primeira.innerHTML;

(function($){
	$(function(){
		// Botão Saiba Mais do Banco de Oportunidades
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
		// Datepicker Agendamentos
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
			beforeShowDay: $.datepicker.noWeekends
		});
		// Zera o valor do dia, ao selecionar a regional
		$('#idregional').change(function(){
			$('#datepicker').val('');
			$('#horarios')
				.find('option')
				.remove()
				.end()
				.append('<option value="" disabled selected>Selecione o dia do atendimento</option>');
		});
		// Ajax após change no datepicker
		$('#datepicker').change(function(){
			$.ajax({
				method: "POST",
				data: {
					"_token": $('#token').val(),
					"idregional": $('#idregional').val(),
					"dia": $('#datepicker').val()
				},
				dataType: 'HTML',
				url: "/checa-horarios",
				beforeSend: function(){
					$('#loadImage').show();
				},
				complete: function(){
					$('#loadImage').hide();
				},
				success: function(response) {
					$('#horarios').html(response);
				}
			});
		});
		$('#datepicker').blur(function(){
			if(!$(this).val()){
				$(this).css('background-color','#FFFFFF');
			}
		});
		// Muda Status agendamento no ADMIN
		$('#btnSubmit').on('click', function(e){
			e.preventDefault();
			e.stopImmediatePropagation();
			$.ajax({
				url: $(this).attr('action'),
				method: "POST",
				data: {
					"_method": $('#method').val(),
					"_token": $('#tokenStatusAgendamento').val(),
					"idagendamento": $('#idagendamento').val(),
					"status": $('#status').val()
				},
				dataType: "html",
				success: function(response) {
					console.log(response);
				},
				error: function (jXHR, textStatus, errorThrown) {
					alert(errorThrown);
				}
			});
		});
	});
})(jQuery);