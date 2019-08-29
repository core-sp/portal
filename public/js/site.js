$(document).ready(function(){
	// Máscaras
	$('#datepicker').mask("99/99/9999");
	$('.cpfInput').mask('999.999.999-99');
	$('.celularInput').mask('(99) 99999-9999');
	$('.nrlicitacaoInput').mask('999/9999');
	$('.nrprocessoInput').mask('999/9999');
	$('.dataInput').mask('00/00/0000');
	$('.capitalSocial').mask('#.##0,00', {reverse: true});
	$('.protocoloInput').mask('ZZZZZZ', {
	  translation: {
		  'Z': {
			pattern: /[A-Za-z0-9]/
		  }
	  }
	});
	var options = {
		onKeyPress: function (cpf, ev, el, op) {
			var masks = ['000.000.000-000', '00.000.000/0000-00'];
			$('.cpfOuCnpj').mask((cpf.length > 14) ? masks[1] : masks[0], op);
		}
	}
	$('.cpfOuCnpj').length > 11 ? $('.cpfOuCnpj').mask('00.000.000/0000-00', options) : $('.cpfOuCnpj').mask('000.000.000-00#', options);
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
	// Facebook Sharer
	$('.fb-share').click(function(e) {
        e.preventDefault();
        window.open($(this).attr('href'), 'fbShareWindow', 'height=450, width=550, top=' + ($(window).height() / 2 - 275) + ', left=' + ($(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
        return false;
    });
	// Twitter Sharer
	$('.twitter-share').click(function(e) {
        e.preventDefault();
        window.open($(this).attr('href'), 'fbShareWindow', 'height=273, width=450, top=' + ($(window).height() / 2 - 275) + ', left=' + ($(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
        return false;
	});
	// Facebook Sharer
	$('.linkedin-share').click(function(e) {
        e.preventDefault();
        window.open($(this).attr('href'), 'fbShareWindow', 'height=700, width=450, top=' + ($(window).height() / 2 - 275) + ', left=' + ($(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
        return false;
    });
});
// Scroll das redes sociais em posts do blog
if(window.location.href.indexOf("/blog/") > -1) {
	// Variaveis
	var margin = 15;
	var prenderTopInicial = $('#prender').offset().top - margin;
	var limiteBotInicial = $('.limite-sociais').offset().top + $('.limite-sociais').outerHeight(true);
	if($(window).resize()) {
		var prenderTopInicial = $('#prender').offset().top - margin;
		var limiteBotInicial = $('.limite-sociais').offset().top + $('.limite-sociais').outerHeight(true);
	}
	// Mostra menu superior ao rolar a tela
	$(window).scroll(function(){
		if($(window).width() > 767) {
			if ($(document).scrollTop() > 300) {
				$('#fixed-menu').slideDown(150);
			} else {
				$('#fixed-menu').hide();
			}
		}
		var windowTop  = $(document).scrollTop() + $('#fixed-menu').height() + margin;
		if(windowTop >= prenderTopInicial) {
			$('#prender').addClass('prender').css('top', $('#fixed-menu').height() + margin);
		} else {
			$('#prender').removeClass('prender');
		}
		if(windowTop >= limiteBotInicial - ($('#prender').height() + margin)) {
			$('#prender').addClass('prenderBot');
		} else {
			$('#prender').removeClass('prenderBot');
		}
	});
}

// Replica o conteúdo do menu no menu superior
var primeira = document.getElementById('menu-principal');
var segunda = document.getElementById('append-menu');
segunda.innerHTML = primeira.innerHTML;
// Feriados para desablitar calendário
natDays = [
	[6, 20, 'br'],
	[6, 21, 'br'],
	[7, 8, 'br'],
	[7, 9, 'br'],
	[11, 15, 'br'],
	[11, 20, 'br'],
	[12, 23, 'br'],
	[12, 24, 'br'],
	[12, 25, 'br'],
	[12, 26, 'br'],
	[12, 27, 'br'],
	[12, 30, 'br'],
	[12, 31, 'br'],
];
// Função para adicionar feriados
function nationalDays(date) {
    for (i = 0; i < natDays.length; i++) {
      if (date.getMonth() == natDays[i][0] - 1
          && date.getDate() == natDays[i][1]) {
        return [false, natDays[i][2] + '_day'];
      }
	}	
	return [true, ''];
}
// Função para feriados e fim-de-semana
function noWeekendsOrHolidays(date) {
    var noWeekend = $.datepicker.noWeekends(date);
    if (noWeekend[0]) {
        return nationalDays(date);
    } else {
        return noWeekend;
    }
}

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
			beforeShowDay: noWeekendsOrHolidays
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
					if (response !== "[]") {
						$('#horarios').html(response);
					} else {
						$('#horarios')
							.find('option')
							.remove()
							.end()
							.append('<option value="" disabled selected>Nenhum horário disponível</option>');
					}
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

// Get now date
function getDate() {
	var today = new Date();

	var dd = today.getDate();
	var mm = today.getMonth()+1; 
	var yyyy = today.getFullYear();

	if(dd<10) 
	{
		dd='0'+dd;
	} 

	if(mm<10) 
	{
		mm='0'+mm;
	} 

	return dd+'/'+mm+'/'+yyyy;
}

// Simulador
(function($){
	// Opções Datepicker
	var options = {
		dateFormat: 'dd/mm/yy',
		dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
		dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
		dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
		monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
		monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
		nextText: 'Próximo',
		prevText: 'Anterior',
		maxDate: '0',
		minDate: '-256m',
	}
	// Mostra loading button
	$('#submitSimulador').on('click', function(){
		$('#loadingSimulador').css('display', 'inline-block');
	});
	// Calendário Simulador
	$('#dataInicio').datepicker(options);
	// Datepicker options
	if($('#tipoPessoa').val() != '1') {
		$('#dataInicio').datepicker("destroy");
	}
	// Mudanças on change tipo de Pessoa
	$(document).on('change', '#tipoPessoa', function(){
		if ($('#tipoPessoa').val() == 1) {
			$('#simuladorAddons').show();
			$('#simuladorAddons').css('display','flex');
			$('#dataInicio').val('').datepicker(options);
		} else {
			$('#simuladorAddons').hide();
			$('#filial').prop('disabled', 'disabled').val('');
			$('#dataInicio').val(getDate()).datepicker("destroy");
		}
	});
	// Filial
	$(document).on('change', "#filialCheck", function() {
		if(this.checked) {
			$('#filial').prop('disabled', false);
		} else {
			$('#filial').prop('disabled', 'disabled').val('');
		}
	});
})(jQuery);

// Lazy-load
$(function() {
	$('.lazy').Lazy();
});