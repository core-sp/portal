$(document).ready(function(){
	// Máscaras
	$('#datepicker').mask("99/99/9999");
	$('.cpfInput').mask('999.999.999-99');
	$('.celularInput').mask('(99) 99999-9999');
	$('.nrlicitacaoInput').mask('99999/9999');
	$('.nrprocessoInput').mask('999/9999');
	$('.dataInput').mask('00/00/0000');
	$('.cnpjInput').mask('99.999.999/9999-99');
	$('.capitalSocial').mask('#.##0,00', {reverse: true});
	$('#registro_core').mask('0000000/0000', {reverse: true});
	$('.numeroInput').mask('99');
	$('.cep').mask('00000-000');
	$('.codigo_certidao').mask('AAAAAAAA - AAAAAAAA - AAAAAAAA - AAAAAAAA');
	$('.cartao_credit').mask('0000  0000  0000  0999  999');
	$('.cvv').mask('0009');
	$('.expiracao').mask("99/99");
  	$('.horaInput').mask('00:00:00');
	$('.numero').mask('ZZZZZZZZZZ', {
		translation: {
			'Z': {
			  pattern: /[0-9\-]/
			}
		}
	});
	// $('.protocoloInput').mask('ZZZZZZ', {
	//   translation: {
	// 	  'Z': {
	// 		pattern: /[A-Za-z0-9]/
	// 	  }
	//   }
	// });
	$('.telefoneInput').mask('(00) 0000-00009').focusout(function (event) {  
		var target, phone, element;
		target = (event.currentTarget) ? event.currentTarget : event.srcElement;
		phone = target.value.replace(/\D/g, '');
		element = $(target);
		element.unmask();
		if(phone.length > 10) {
			element.mask("(99) 99999-9999");  
		} else {  
			element.mask("(99) 9999-99999");  
		}  
	});
	var options = {
		onKeyPress: function (cpf, ev, el, op) {
			var masks = ['000.000.000-000', '00.000.000/0000-00'];
			$('.cpfOuCnpj').mask((cpf.length > 14) ? masks[1] : masks[0], op);
		}
	}
	$('.cpfOuCnpj').index() > -1 && $('.cpfOuCnpj').val().length > 11 ? 
	$('.cpfOuCnpj').mask('00.000.000/0000-00', options) : 
	$('.cpfOuCnpj').mask('000.000.000-00#', options);

	// copiado
	$('.placaVeiculo').mask('AAA 0U00', {
		translation: {
			'A': {
				pattern: /[A-Za-z]/
			},
			'U': {
				pattern: /[A-Za-z0-9]/
			},
		},
		onKeyPress: function (value, e, field, options) {
			// Convert to uppercase
			e.currentTarget.value = value.toUpperCase();
	
			// Get only valid characters
			let val = value.replace(/[^\w]/g, '');
	
			// Detect plate format
			let isNumeric = !isNaN(parseFloat(val[4])) && isFinite(val[4]);
			let mask = 'AAA 0U00';
			if(val.length > 4 && isNumeric) {
				mask = 'AAA-0000';
			}
			$(field).mask(mask, options);
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
		$('.sub-dropdown').removeClass('menu-hoverable');
		$('.sub-dropdown-menu').hide();
		if($(window).width() < 768) {
			$('.dropdown-item').removeClass('branco-azul');
		}
	});
	// Segundo nível do menu
	$('.sub-dropdown').on('click', function(e){
		e.stopPropagation();
		$(this).toggleClass('menu-hoverable');
		$('.sub-dropdown').not($(this)).removeClass('menu-hoverable');
		$(this).children('.sub-dropdown-menu').toggle('slide', { direction: "left" }, 200);
		$('.sub-dropdown-menu').not($(this).children('.sub-dropdown-menu')).hide();
		if($(window).width() < 768) {
			$(this).children('.dropdown-item').toggleClass('branco-azul');
			$('.dropdown-item').not($(this).children('.dropdown-item')).removeClass('branco-azul');
		}
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

	// CEP Correios
	function limpa_formulário_cep() {
		// Limpa valores do formulário de cep.
		$("#rua").val("");
		$("#bairro").val("");
		$("#cidade").val("");
		$("#uf").val("");
		$("#ibge").val("");
	}
	
	//Quando o campo cep perde o foco.
	$("#cep").on('keyup', function() {
		// Nova variável "cep" somente com dígitos.
		if($(this).val().length === 9) {
			var cep = $(this).val().replace(/\D/g, '');
			// Verifica se campo cep possui valor informado.
			if (cep != "") {
				//Expressão regular para validar o CEP.
				var validacep = /^[0-9]{8}$/;
				//Valida o formato do CEP.
				if(validacep.test(cep)) {
					//Preenche os campos com "..." enquanto consulta webservice.
					$("#rua").val("...");
					$("#bairro").val("...");
					$("#cidade").val("...");
					$("#uf").val("...");
					$("#ibge").val("...");
					//Consulta o webservice viacep.com.br/
					$.getJSON("https://viacep.com.br/ws/"+ cep +"/json/?callback=?", function(dados) {
						if (!("erro" in dados)) {
							//Atualiza os campos com os valores da consulta.
							$("#rua").val(dados.logradouro);
							$("#bairro").val(dados.bairro);
							$("#cidade").val(dados.localidade);
							$("#uf").val(dados.uf);
							$("#ibge").val(dados.ibge);
						} //end if.
						else {
							//CEP pesquisado não foi encontrado.
							limpa_formulário_cep();
							alert("CEP não encontrado.");
						}
					});
				} //end if.
				else {
					//cep é inválido.
					limpa_formulário_cep();
					alert("Formato de CEP inválido.");
				}
			} //end if.
			else {
				//cep sem valor, limpa formulário.
				limpa_formulário_cep();
			}
		}
	});
	// Scroll fixed menu
	$(window).scroll(function(){
		if($(window).width() > 767) {
			if ($(document).scrollTop() > 300) {
				$('#fixed-menu').slideDown(150);
			} else {
				$('#fixed-menu').hide();
			}
		}
	});
	// Interrogação (Descricão da Oportunidade)
	$('#descricao-da-oportunidade').on({
		"mouseover": function() {
			$(this).tooltip({
				items: "#descricao-da-oportunidade",
				content: "<h6 class='mb-2'><strong>Exemplo:</strong></h6>* Possuir carro;<br>* Possuir Empresa;<br>* Preferencialmente ter experiência no segmento do produto / serviço;<br>* Conhecer a região que irá atuar;<br>* Preferencialmente possuir carteira ativa de clientes;"
			});
			$(this).tooltip("open");
		},
		"mouseout": function() {
			$(this).tooltip("disable");   
		}
	});
	// Interrogação (Endereço da empresa)
	$('#endereco-da-empresa').on({
		"mouseover": function() {
			$(this).tooltip({
				items: "#endereco-da-empresa",
				content: "<h6 class='mb-2'><strong>Exemplo:</strong></h6>Av. Brigadeiro Luís Antônio, 613 - 5º andar - Centro - São Paulo - SP"
			});
			$(this).tooltip("open");
		},
		"mouseout": function() {
			$(this).tooltip("disable");   
		}
	});
	if($('#cnpj').length != 0) {
		if($('#cnpj').val().length == 18) {
			var value = $('#cnpj').val();
			getInfoEmpresa(value);
		}
	}
	// // Popup Campanha
	// // carrega um video, mas no modal já faz isso
	// var campanha = localStorage.getItem('campanha');
	// if (campanha == null) {
	// 	localStorage.setItem('campanha', 1);
	// 	$(window).on('load', function(){
	// 		$('#popup-campanha').modal('show');
	// 	});
	// }

	$(window).on('load', function(){
		if($('#popup-campanha').length > 0)
			$('#popup-campanha').modal('show');
	});
	$('#popup-campanha').on('hidden.bs.modal', function(){
		var youtube = $('#video-campanha').attr('src');
		$('#video-campanha').attr('src', youtube);
	});
	// $('#video-campanha').on('ended', function(){
	// 	$('#popup-campanha').modal('hide');
	// });
});

// Lightbox
$(document).on('click', '[data-toggle="lightbox"]', function(event) {
	if($(window).width() > 767) {
		event.preventDefault();
		$(this).ekkoLightbox();
	}
});

// Scroll das redes sociais em posts do blog
if(window.location.href.indexOf("/blog/") > -1) {
	var prenderTop = $('#prender').offset().top;
	$(window).scroll(function() {
		var margin = 15;
		var topson = $(document).scrollTop() + $('#fixed-menu').height() + margin;
		var topsonPrender = $(document).scrollTop() + $('#fixed-menu').height() + margin + $('#prender').height();
		var botson = $('.sociais-post').offset().top + $('.sociais-post').height();
		if(topson >= prenderTop) {
			$('#prender').addClass('prender').css('top', $('#fixed-menu').height() + margin);
		} else {
			$('#prender').removeClass('prender');
		}
		if(topsonPrender >= botson) {
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
// natDays = [
// 	[6, 20, 'br'],
// 	[6, 21, 'br'],
// 	[11, 15, 'br'],
// 	[11, 20, 'br'],
// 	[12, 23, 'br'],
// 	[12, 24, 'br'],
// 	[12, 25, 'br'],
// 	[12, 26, 'br'],
// 	[12, 27, 'br'],
// 	[12, 30, 'br'],
// 	[12, 31, 'br'],
// ];
var lotados = [];

// Função para adicionar feriados
// function nationalDays(date) {
//     for (i = 0; i < natDays.length; i++) {
//       if (date.getMonth() == natDays[i][0] - 1
//           && date.getDate() == natDays[i][1]) {
//         return [false, natDays[i][2] + '_day'];
//       }
// 	}	
// 	return [true, ''];
// }

// Função para adicionar feriados
function diasLotados(date) {
    for (i = 0; i < lotados.length; i++) {
      if (date.getMonth() == lotados[i][0] - 1 && date.getDate() == lotados[i][1]) {
		var habilita = lotados[i][2] === 'agendado';
		var texto = lotados[i][2] === 'agendado' ? 'Seu agendamento. Dia disponível, menos no(s) período(s) que está agendado.' : '';
        return [habilita, lotados[i][2], texto];
      }
	}	
	return [true, ''];
}

// Função para feriados, fim-de-semana e dias lotados
function noWeekendsOrHolidays(date) {
	var noWeekend = $.datepicker.noWeekends(date);
	//var feriado = nationalDays(date);
	var lotado = diasLotados(date);

	// if(!feriado[0]) {
	// 	return feriado;
	// }
	if (!noWeekend[0]) {
		return noWeekend;
	}
	else {
		return lotado;
	}
}

// Funcionalidade Mapa Fiscalização ++++++++++++++++++++++++++++++++++++++++
$('.mapa_regional').on({
	"click": function() {
		$(".dado-regional").addClass('d-none');
		$(".mapa_regional").removeClass("regional-selecionada");
		$("#instrucao-mapa").addClass('dado-oculto');

		$("#dado-" + $(this).attr('id')).removeClass('d-none');
		$("#" + $(this).attr('id')).addClass("regional-selecionada");
	},
});

$('#ano-mapa').on({
	"change": function() {
		window.location.href = "/mapa-fiscalizacao/" + $(this).val();
	},
});
// Fim da Funcionalidade Mapa Fiscalização ++++++++++++++++++++++++++++++++++++++++

(function($){
	$(function(){

	// Funcionalidade Agendamentos ++++++++++++++++++++++++++++++++++++++++

		function validarDatasPlantaoJuridico(datas)
		{
			var datasNovas = [];

			if((datas[0] != null) && (datas[1] != null))
			{
				datasNovas[0] = new Date(datas[0] + " 00:00:00");
				datasNovas[1] = new Date(datas[1] + " 00:00:00");
				return datasNovas;
			}

			if((datas[0] == null) && (datas[1] != null))
			{
				datasNovas[0] = '+1';
				datasNovas[1] = new Date(datas[1] + " 00:00:00");
				return datasNovas;
			}

			return null;
		}
		
		function errorAjaxAgendamento()
		{
			$('#agendamentoStore #datepicker')
				.val('')
				.prop('disabled', true)
				.prop('placeholder', 'Falha ao recuperar calendário');

			$('#agendamentoStore #horarios')
				.find('option')
				.remove()
				.end()
				.append('<option value="" disabled selected>Falha ao recuperar os dados para o agendamento</option>');

			$("#dialog_agendamento")
				.empty()
				.append("Falha ao recuperar calendário. <br> Por favor verifique se o uso de cookies está habilitado e recarregue a página ou tente mais tarde.");
						
			$("#dialog_agendamento").dialog({
				draggable: false,
				buttons: [{
					text: "Recarregar",
					click: function() {
						location.reload(true);
					}
				}]	
			});
		}

		function getRegionaisPlantaoJuridico()
		{
			$.ajax({
				method: "GET",
				dataType: 'json',
				url: "/regionais-plantao-juridico",
				beforeSend: function(){
					$('#agendamentoStore #loadCalendario').show();
				},
				complete: function(){
					$('#agendamentoStore #loadCalendario').hide();
				},
				success: function(response) {
					regionaisAtivas = response;
					$('#agendamentoStore #idregional option').each(function(){
						var valor = parseInt($(this).val())
						jQuery.inArray(valor, regionaisAtivas) != -1 ? $(this).show() : $(this).hide();
					});
				},
				error: function() {
					errorAjaxAgendamento();
				}
			});
		}

		function getDatasPorRegionalPlantaoJuridico()
		{
			$.ajax({
				method: "GET",
				data: {
					"idregional": $('#idregional').val(),
				},
				dataType: 'json',
				url: "/dias-horas",
				beforeSend: function(){
					$('#agendamentoStore #loadCalendario').show();
				},
				complete: function(){
					$('#agendamentoStore #loadCalendario').hide();
				},
				success: function(response) {
					datas = response;
					datasNovas = validarDatasPlantaoJuridico(datas);
					if(datasNovas == null)
						$('#agendamentoStore #datepicker')
						.prop('disabled', true)
						.prop('placeholder', 'Sem datas disponíveis')
						.val('');
					else
						$('#agendamentoStore #datepicker').prop('placeholder', 'dd/mm/aaaa').datepicker('option', {
							minDate: datasNovas[0],
							maxDate: datasNovas[1]
						});
				},
				error: function() {
					errorAjaxAgendamento();
				}
			});
		}

		function getDatasAgendamento()
		{
			if($("#agendamentoStore #selectServicos option:selected").val() == "Plantão Jurídico")
				getDatasPorRegionalPlantaoJuridico();
			else
				$('#agendamentoStore #datepicker').datepicker('option', {
					maxDate: '+1m',
					minDate: +1,
				});

			$('#agendamentoStore #idregional option[value=""]').hide();

			$.ajax({
				method: "GET",
				data: {
					"idregional": $('#idregional').val(),
					"servico": $('#selectServicos').val()
				},
				dataType: 'json',
				url: "/dias-horas",
				beforeSend: function(){
					$('#agendamentoStore #loadCalendario').show();
				},
				complete: function(){
					$('#agendamentoStore #loadCalendario').hide();
				},
				success: function(response) {
					lotados = response;
					if($('#agendamentoStore #datepicker').prop('placeholder') != 'Sem datas disponíveis')
						$('#agendamentoStore #datepicker')
						.prop('disabled', false)
						.prop('placeholder', 'dd/mm/aaaa');
				},
				error: function() {
					errorAjaxAgendamento();
				}
			});
		}

		function getHorariosAgendamento()
		{
			$.ajax({
				method: "GET",
				data: {
					"idregional": $('#idregional').val(),
					"dia": $('#datepicker').val(),
					"servico": $('#selectServicos').val()
				},
				dataType: 'json',
				url: "/dias-horas",
				beforeSend: function(){
					$('#agendamentoStore #loadHorario').show();
				},
				complete: function(){
					$('#agendamentoStore #loadHorario').hide();
				},
				success: function(response) {
					response;
					if (!jQuery.isEmptyObject(response)) {
						$('#agendamentoStore #horarios').empty();
						$.each(response, function(i, horario) {
							$('#agendamentoStore #horarios').append($('<option>', { 
								value: horario,
								text : horario 
							}));
						});
						$('#agendamentoStore #horarios').prop('disabled', false);
						$('#agendamentoStore #datepicker').css('background-color','#FFFFFF');
					} 
					else 
						$('#agendamentoStore #horarios')
							.find('option')
							.remove()
							.end()
							.append('<option value="" disabled selected>Nenhum horário disponível</option>');
				},
				error: function() {
					errorAjaxAgendamento();
				}
			});
		}

		function limpaDiasHorariosAgendamento()
		{
			$('#agendamentoStore #datepicker')
				.val('')
				.prop('disabled', true)
				.prop('placeholder', 'dd/mm/aaaa')
				.css('background-color','#e9ecef');
			$('#agendamentoStore #horarios')
				.find('option')
				.remove()
				.end()
				.append('<option value="" disabled selected>Selecione o dia do atendimento</option>');
			$('#agendamentoStore #horarios').prop('disabled', true);
		}

		function openAviso()
		{
			$('#tipo-outros').modal({backdrop: 'static', keyboard: false, show: true});
			$('#tipo-outros .modal-body')
			.html('Você é <strong>Representante Comercial</strong>?');
			$('#tipo-outros .modal-footer').show();
		}

		$('#notRC-agendamento').click(function(){
			$('#tipo-outros .modal-body')
			.html('Você está no Portal dos Representantes Comerciais, este agendamento é para uso exclusivo à categoria de Representante Comercial');
			$('#tipo-outros .modal-footer').hide();
		});

		// Datepicker Agendamentos
		$('#agendamentoStore #datepicker, #agendamentoSala #datepicker').datepicker({
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

		// Para quando houver Plantão Jurídico ou selecionar 'Outros'
		$('#agendamentoStore #selectServicos').change(function(){
			$("#idregional").val("");
			limpaDiasHorariosAgendamento();
			if($(this).val() == "Plantão Jurídico")
				getRegionaisPlantaoJuridico();
			else if($(this).val() == "Outros")
				openAviso();
			else
				$('#agendamentoStore #idregional option').show();
		});	

		if($("#agendamentoStore #selectServicos option:selected").val() == "Plantão Jurídico")
			getRegionaisPlantaoJuridico();
		else
			$('#agendamentoStore #idregional option').show();
		
		if($("#agendamentoStore #idregional option:selected").val() > 0)
			getDatasAgendamento();

		// Zera o valor do dia, ao selecionar a regional
		$('#agendamentoStore #idregional').change(function(){
			limpaDiasHorariosAgendamento();
			// if($('#agendamentoStore #idregional').val() == 14){
			// 	var texto = 'Para realização de cédula de habilitação Profissional do Representante Comercial (Carteirinha), realizar agendamento somente em nossa sede: ';
			// 	texto += 'Av. Brigadeiro Luís Antônio, 613, Térreo, CEP: 01317-000, São Paulo/SP.';
			// 	$("#textoCarteirinha")
			// 	.html(texto);
			// 	$("#avisoCarteirinha").modal();
			// }
			if(($('#agendamentoStore #idregional').val() == 6) || ($('#agendamentoStore #idregional').val() == 8)){
				var texto = 'As cidades de Araraquara e São José do Rio Preto  instituíram, por meio dos Decretos n° 12.892/2022 e n° 19.213/2022, respectivamente,';
				texto += ' a volta da obrigatoriedade do uso de máscaras de proteção em locais fechados devido à alta nos números de infecções pelo coronavírus. ';
				texto += '<br>Assim, o Core-SP solicita a todos os visitantes dos Escritórios Seccionais nessas localidades, usem o acessório e não se esqueçam de agendar,';
				texto += ' neste espaço, o horário em que pretendem comparecer.';
				$("#textoCarteirinha")
				.html(texto);
				$("#avisoCarteirinha").modal();
			}
			getDatasAgendamento();
		});

		// Ajax após change no datepicker
		$('#agendamentoStore #datepicker').change(function(){
			getHorariosAgendamento();
		});

		$("#agendamentoStore").submit(function(e){
			var today = new Date();
			var dd = String(today.getDate()).padStart(2, '0');
			var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
			var yyyy = today.getFullYear();
			today = dd + '/' + mm + '/' + yyyy;
			$dataNula = $("#agendamentoStore #datepicker").val() == "";
			$dataAntiga = $.datepicker.parseDate("dd/mm/yy", $("#agendamentoStore #datepicker").val()) <= $.datepicker.parseDate("dd/mm/yy", today);
			$semData = $('#agendamentoStore #datepicker').prop('placeholder') == 'Sem datas disponíveis';
			
			if($semData)
			{
				$("#agendamentoStore #idregional").focus();
				e.preventDefault();
				return;
			}
			if(!$semData && ($dataNula || $dataAntiga))
			{
				$("#agendamentoStore #datepicker").focus();
				e.preventDefault();
				return;
			}

			var valor = $("#agendamentoStore #horarios").val();
			if(valor == "")
			{
				$("#agendamentoStore #horarios").focus();
				e.preventDefault();
				return;
			}

			if(valor.length < 5)
			{
				$("#agendamentoStore #horarios").focus();
				e.preventDefault();
				return;
			}
		});

	// FIM Funcionalidade Agendamentos ++++++++++++++++++++++++++++++++++++++++

	// Funcionalidade Agendamentos Salas Area Restrita RC ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	function formatSalas(response){
		regionaisAtivas = response;
		$('#agendamentoSala #sala_reuniao_id option').each(function(){
			var valor = parseInt($(this).val())
			jQuery.inArray(valor, regionaisAtivas) != -1 ? $(this).show() : $(this).hide();
		});
	}

	function formatDias(response){
		lotados = response;
		$('#agendamentoSala #datepicker')
			.prop('disabled', false)
			.prop('placeholder', 'dd/mm/aaaa');
	}

	function formatPeriodos(response, tipo){
		if(!jQuery.isEmptyObject(response['horarios'])) {
			$('#agendamentoSala #periodo').empty();
			$.each(response['horarios'], function(i, periodo) {
				var periodo_texto = periodo.replace(' - ', ' até ');
				periodo_texto = (i == 'manha') || (i == 'tarde') ? 'Período todo: ' + periodo_texto : periodo_texto;
				
				$('#agendamentoSala #periodo').append($('<option>', { 
					value: periodo,
					text : periodo_texto
				}));
			});
			var itens = '';
			$.each(response.itens, function(i, valor) {
				itens += i == 0 ? valor : '&nbsp;&nbsp;&nbsp;<strong>|</strong>&nbsp;&nbsp;&nbsp;' + valor;
			});
			$('#itensShow').html(itens).parent().show();
			$('#agendamentoSala #periodo').prop('disabled', false);
			$('#agendamentoSala #datepicker').css('background-color','#FFFFFF');
			if(tipo == 'reuniao'){
				$(".participante:gt(0)").remove();
				var cont = $('.participante').length;
				if(cont < response.total)
					for (let i = cont; i < response.total; i++)
						$('#area_participantes').append($('.participante:last').clone());
				$('.participante :input[name="participantes_cpf[]"]').val('').unmask().mask('999.999.999-99');
				$('.participante :input[name="participantes_nome[]"]').val('');
				$('#area_participantes').show();
			}
		}else
			$('#agendamentoSala #periodo')
			.prop('disabled', true)
			.find('option')
			.remove()
			.end()
			.append('<option value="" disabled selected>Nenhum período disponível</option>');
	}

	function limpaDiasHorariosAgendamentoSala(error = false){
		if(error){
			$('#agendamentoSala #datepicker')
				.val('')
				.prop('disabled', true)
				.prop('placeholder', 'Falha ao recuperar calendário');
				$('#agendamentoSala #periodo')
					.find('option')
					.remove()
					.end()
					.append('<option value="" disabled selected>Falha ao recuperar os dados para o agendamento</option>');
				$("#dialog_agendamento")
					.empty()
					.append("Falha ao recuperar calendário. <br> Por favor verifique se o uso de cookies está habilitado e recarregue a página ou tente mais tarde.");
				$("#dialog_agendamento").dialog({
					draggable: false,
					buttons: [{
						text: "Recarregar",
						click: function() {
							location.reload(true);
						}
					}]	
				});
			return;
		}
		$('#agendamentoSala #datepicker')
			.val('')
			.prop('disabled', true)
			.prop('placeholder', 'dd/mm/aaaa')
			.css('background-color','#e9ecef');
		$('#agendamentoSala #periodo')
			.find('option')
			.remove()
			.end()
			.append('<option value="" disabled selected>Selecione o dia da reserva de sala</option>');
		$('#agendamentoSala #periodo').prop('disabled', true);
	}
	
	function getDadosSalas(acao, tipo, sala_id = '', dia = ''){
		if((tipo != 'reuniao') && (tipo != 'coworking'))
			return false;

		var dados_url = acao == 'getSalas' ? 
		"/admin/salas-reunioes/regionais-salas-ativas/" + tipo : 
		'/admin/salas-reunioes/sala-dias-horas/' + tipo;
		var dados_data = acao == 'getSalas' ? '' : 'sala_id=' + sala_id + '&dia=' + dia;

		$.ajax({
			method: "GET",
			dataType: 'json',
			url: dados_url,
			data: dados_data,
			beforeSend: function(){
				dia == '' ? $('#agendamentoSala #loadCalendario').show() : $('#agendamentoSala #loadHorario').show();
			},
			complete: function(){
				dia == '' ? $('#agendamentoSala #loadCalendario').hide() : $('#agendamentoSala #loadHorario').hide();
			},
			success: function(response) {
				$('#agendamentoSala #itensShow').html('').parent().hide();
				$('#agendamentoSala #area_participantes').hide();
				if(acao == 'getSalas')
					formatSalas(response);
				else if((acao == 'getDias') && (dia == ''))
					formatDias(response);
				else if((acao == 'getDias') && (dia != ''))
					formatPeriodos(response, tipo);
				else 
					$('#agendamentoSala #periodo')
					.find('option')
					.remove()
					.end()
					.append('<option value="" disabled selected>Nenhum período disponível</option>');				
			},
			error: function() {
				limpaDiasHorariosAgendamentoSala(true);
			}
		});
	}

	$('#agendamentoSala #tipo_sala').change(function(){
		$("#sala_reuniao_id").val("");
		if(this.value == "")
			return false;
		limpaDiasHorariosAgendamentoSala();
		getDadosSalas('getSalas', $("#tipo_sala").val());
	});	

	$('#agendamentoSala #sala_reuniao_id').change(function(){
		if($("#tipo_sala").val() == "")
			return false;
		limpaDiasHorariosAgendamentoSala();
		$('#agendamentoSala #sala_reuniao_id option[value=""]').hide();
		getDadosSalas('getDias', $("#tipo_sala").val(), $("#sala_reuniao_id").val());
	});	

	if($("#agendamentoSala #tipo_sala option:selected").val() != "")
		getDadosSalas('getSalas', $("#tipo_sala").val());
	
	if($("#agendamentoSala #sala_reuniao_id option:selected").val() != "")
		getDadosSalas('getDias', $("#tipo_sala").val(), $("#sala_reuniao_id").val());

	$('#agendamentoSala #datepicker').change(function(){
		getDadosSalas('getDias', $("#tipo_sala").val(), $("#sala_reuniao_id").val(), $('#datepicker').val());
	});

	// FIM Funcionalidade Agendamentos Salas Area Restrita RC++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

		// Agenda Institucional
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
	
		// Switch para máscaras de contato Gerenti
		function switchMascaras(conteudo, id)
		{
			conteudo.attr('type', 'text');
			switch (id) {
				case '1':
				case '4':
				case '6':
				case '7':
				case '8':
					conteudo.mask('(99) 9999-9999');
				break;
				case '2':
					conteudo.mask('(99) 99999-9999');
				break;
				case '3':
					conteudo.unmask();
					conteudo.attr('type', 'email');
				break;
				case '5':
					conteudo.unmask();
				break;
				default:
					conteudo.mask('9');
				break;
			}
		}
		// Gerenti Inserir Contato
		$('#gerentiTipoContato').on('change', function(){
			var conteudo = $('#gerentiInserirContato');
			conteudo.prop("disabled", false).val('');
			switchMascaras(conteudo, $(this).val());
		});
		if($('#gerentiTipoContato').is(':disabled')) {
			conteudo = $('#gerentiInserirContato');
			var id = $('#gerentiTipoContato option:selected').val();
			switchMascaras(conteudo, id);
		}
		// Auto-preenche empresa Balcão de Oportunidades (anúncio)
		$('#cnpj').on('keyup', function(){
			var value = $(this).val();
			if(value.length == 18) {
				getInfoEmpresa(value);
			}
		});
		// Após modal
		$('#avInfo').on('hidden.bs.modal', function () {
			$('#titulice').focus();
		});
		$('#avNull').on('hidden.bs.modal', function () {
			$('#av01').focus();
		});
		// Abre campo para Outro Segmento
		$('#avSegmentoOp').on('change', function(){
			if($(this).val() == 'Outro') {
				$('#outroSegmento').show();
			} else {
				$('#outroSegmento').hide();
			}
		});
		// Menu mobile representante
		$('#bars-representante').on('click', function(){
			$('#mobile-menu-representante').slideToggle();
		});

		$('#agenda-institucional').change(function(){
			window.location.href = "/agenda-institucional/" + $(this).val();
		});
	});
})(jQuery);

// Get informação empresa
function getInfoEmpresa(value)
{
	return $.ajax({
		type: 'GET',
		url: '/info-empresa/' + encodeURIComponent(value.replace(/[^\d]+/g,'')),
		beforeSend: function() {
			$('#avLoading').show();	
		},
		success: function(data)
		{
			var json = $.parseJSON(data.empresa);
			$('.avHidden').hide();
			$('#av10').val(json.idempresa);
			$('#av01, #avEmail').val('');
			$('#titulice').focus();
			$('#avLoading').hide();
			// Mostra o alert
			$('#avAlert').show().removeClass('alert-info alert-warning').addClass(data.class).html(data.message);
		},
		error: function()
		{
			$('.avHidden').css('display', 'flex');
			$('#av10').val('0');
			$('#av01').focus();
			$('#avLoading').hide();
			$('#avAlert').show().removeClass('alert-info alert-success').addClass('alert-info').text('Empresa não cadastrada. Favor informar os dados da empresa abaixo.');
		}
	});
}

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
	$('#submitSimulador, #anoVigenteButton').on('click', function(){
		$('#loadingSimulador').css('display', 'inline-block');
	});

	// Calendário Simulador
	var hoje = $('#dataInicio').attr('max');

	if($('#tipoPessoa').val() != '1') {
		$('#dataInicio').prop('readonly', true);
	} else {
		$('#dataInicio').prop('readonly', false);
	}

	// Mudanças on change tipo de Pessoa
	$(document).on('change', '#tipoPessoa', function(){
		$('#simuladorTxt').hide();
		if ($('#tipoPessoa').val() == 1) {
			$('#simuladorAddons').show();
			$('#simuladorAddons').css('display','flex');
			$('#dataInicio').prop('readonly', false);
		} else {
			$('#simuladorAddons').hide();
			$('#filial').prop('disabled', 'disabled').val('');
			$('#dataInicio').val(hoje).prop('readonly', true);
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

	// Filename comprovante de residência
	$('#comprovante-residencia, #comprovante-residencia-dois, #comprovante-justificativa').on('change',function(e){
		var fileName = e.target.files[0].name;
		$(this).next('.custom-file-label').html(fileName);
	})

	// Mostra Crimage Dois
	$('#linkShowCrimageDois').on('click', function(){
		$('#showCrimageDois').hide();
		$('#divCrimageDois').show();
	});

	$(document).on('change', ".nParcela", function() {
		var id = $(this).attr('id');
		var nParcela = parseFloat($('option:selected',this).attr('value'));
		var total = parseFloat($('#total' + id).attr('value'));
		var valorParcelado =  (total/nParcela).toFixed(2);

		$('#parcelamento' + id).attr('value', valorParcelado);
		$('#parcelamento' + id).html('R$ ' + valorParcelado.replace('.', ','));
	});

})(jQuery);

// Lazy-load
$(function() {
	$('.lazy').Lazy();
});

function clickBoleto(descricao)
{
	$.get('/representante/evento-boleto', {
		'descricao': descricao,
	});
}

function showLoading()
{
	$('#rc-main').hide();
	$('#loading').show();
}

$('.emitirCertidaoBtn').on('click', function(){
	$('.emitirCertidaoBtn').hide();
	$('.baixarCertidaoBtn').hide();
});

// Cuida do comportamento da mensagem do cookie e armazena na maquina do cliente a opção, e somente apos a limpeza que volta a pedir

(() => {
	if (!localStorage.pureJavaScriptCookies) {
	  document.querySelector(".box-cookies").classList.remove('hide');
	}
	
	const acceptCookies = () => {
	  document.querySelector(".box-cookies").classList.add('hide');
	  localStorage.setItem("pureJavaScriptCookies", "accept");
	  window.clarity('consent');
	};
	
	const btnCookies = document.querySelector(".btn-cookies");
	
	btnCookies.addEventListener('click', acceptCookies);
  })();

  $('#cedula').submit(function() {
	  var rg = $('#rg').val().replace(/[^a-zA-Z0-9]/g,'');
	  var cpf = $('#cpf').val().replace(/\D/g,'');
	  $('#rg').val(rg);
	  $('#cpf').val(cpf);
  })

  // Para quantos dígitos forem necessários, sendo o dígito verificador sempre unitário
  function mascaraRG(rg){
	  var dv = '-' + rg.slice(rg.length - 1, rg.length);
	  var rgSemDV = rg.slice(0, rg.length - 1);
	  var rgFinal = dv;
	  while(rgSemDV.length > 3)
	  {
		  rgFinal = '.' + rgSemDV.slice(rgSemDV.length - 3, rgSemDV.length) + rgFinal;
		  rgSemDV = rgSemDV.slice(0, rgSemDV.length - 3);
	  }
	  rgFinal = rgSemDV + rgFinal;
	  return rgFinal;
  }

// Máscara para o RG ao digitar
$(".rgInput").keyup(function() {
	// Remove qualquer caracter que não seja número ou letra e somente a máscara insere os pontos e traço
	var texto = $(this).val().replace(/[^a-zA-Z0-9]/g,'');
	if(texto.length > 3)
		$(this).val(mascaraRG(texto));
});

// Carrega a máscara quando já possui um rg
$('#cedula').ready(function() {
	if($(".rgInput").index($('#rg')) > -1){
		var texto = $('#rg').val().replace(/[^a-zA-Z0-9]/g,'');
		if(texto.length > 3)
			$('#rg').val(mascaraRG(texto));
	}
});

// Logout Representante
$("#logout-representante").click(function(){
	var token = $('meta[name="csrf-token"]').attr('content');
	var link = "/representante/logout";
	var form = $('<form action="' + link + '" method="POST"><input type="hidden" name="_token" value="' + token + '"></form>');
	$('body').append(form);
	$(form).submit();
});

// +++++++++++++++++++++++ Página pagamento ++++++++++++++++++++++

function disabledPagamento(){
	$('select[name="parcelas_1"]').val('1').attr('disabled', true);
	$('#dados_combinado, #valor_combinado').hide();
	$('#dados_combinado input, #dados_combinado select, #valor_combinado input').attr('required', false);
}

function enabledPagamento(credito){
	var habilita = credito == 'combined' ? true : false;
	habilita ? $('#dados_combinado, #valor_combinado').show() : $('#dados_combinado, #valor_combinado').hide();
	$('#dados_combinado input, #dados_combinado select, #valor_combinado input').attr('required', habilita);
}

$('select[name="tipo_pag"]').change(function() {
	if((this.value == 'debit_3ds') || (this.value == '')){
		disabledPagamento();
		return;
	}
	$('select[name="parcelas_1"]').attr('disabled', false);
	enabledPagamento(this.value);
});

$('input[name="amount"]').ready(function() {
	if($('select[name="tipo_pag"]').val() == "")
		disabledPagamento();
});

$('input[name="amount_1"], input[name="amount_2"]').keyup(function(e) {
	if($('select[name="tipo_pag"]').val() == 'combined'){
		var campo_digitado = this.name;
		var campo_resto = campo_digitado == 'amount_1' ? 'amount_2' : 'amount_1';
		var total = Number($('input[name="amount"]').val().replace(/[^0-9]/g,''));
		var temp = total - Number($('input[name="' + campo_digitado + '"]').val().replace(/[^0-9]/g,''));
		var last = temp.toString().slice(-2);
		var start = temp.toString().slice(0, temp.toString().length - 2);
		temp = new Intl.NumberFormat('pt-BR').format(start);
		$('input[name="' + campo_resto + '"]').val(temp + ',' + last);
	}
});

function hideModalPagamentoSubmit(elemento){
	elemento.addEventListener('invalid', (e) => {
		$('#modalPagamento').modal('hide');
	});
}

const inputs = document.getElementsByClassName('pagamento');
for(elemento of inputs){
	hideModalPagamentoSubmit(elemento);
}

function showModelPagamento3ds(msg)
{
	var texto = '<h5 class="text-break"><i class="fas fa-times text-danger"></i> ';
	texto += msg + '</h5><br><a class="btn btn-secondary" href="' + window.location.href + '">Fechar</a>';

	$('#modalPagamento').modal('hide');
	$('#modalPagamento .modal-body').html(texto);
	$('#modalPagamento').modal({backdrop: 'static', keyboard: false, show: true});
}

function liberarSubmit3ds(brand, number_eci, version)
{
	var master = false;
	var outros = false;

	if(version.indexOf('2.') == 0){
		master = (brand == 'Mastercard') && ((number_eci == '02') || (number_eci == '01'));
		outros = (brand != 'Mastercard') && ((number_eci == '05') || (number_eci == '06'));
	} else{
		master = (brand == 'Mastercard') && (number_eci == '02');
		outros = (brand != 'Mastercard') && (number_eci == '05');
	}

	return master || outros;
}

function tresDsIniciado(response2)
{
	if((response2 != null) && (response2.status >= 200) && (response2.status <= 299)) { 
		switch(response2.data[0].status){
			case 'AUTHENTICATION_SUCCESSFUL':
				var brand = $('input[name="brand"]').val();
				var dados = response2.data[0];
				var number_eci = dados.consumerAuthenticationInformation.eci == undefined ? '' : dados.consumerAuthenticationInformation.eci;
				var number_xid = dados.consumerAuthenticationInformation.xid == undefined ? '' : dados.consumerAuthenticationInformation.xid;
				var version = dados.consumerAuthenticationInformation.specificationVersion;
				var number = '<input type="hidden" name="number_token" value="' + dados.card.numberToken + '"/>';
				var eci = '<input type="hidden" name="eci" value="' + dados.consumerAuthenticationInformation.eci + '"/>';
				var ucaf = '<input type="hidden" name="ucaf" value="' + dados.consumerAuthenticationInformation.ucaf + '"/>';
				var xid = '<input type="hidden" name="xid" value="' + number_xid + '"/>';
				var tdsver = '<input type="hidden" name="tdsver" value="' + version + '"/>';
				var tdsdsxid = '<input type="hidden" name="tdsdsxid" value="' + dados.consumerAuthenticationInformation.directoryServerTransactionId + '"/>';
				var authorization = '<input type="hidden" name="authorization" value="' + $('.gn3ds_merchantBackEndTokenOauth').val() + '"/>';
				if(!liberarSubmit3ds(brand, number_eci, version))
					showModelPagamento3ds('Retorno da autenticação não permitida no momento.');
				else{
					$('#card_number_1').removeAttr('name');
					$('#btnApiPag').before(number, eci, ucaf, xid, tdsver, tdsdsxid, authorization);
					$('#formPagamento').submit();
				}
				break;
			case 'AUTHENTICATION_FAILED':
				showModelPagamento3ds('Erro durante a autenticação! Retorno da autenticação falhou.');
				break;
		}
	} else
		showModelPagamento3ds('Erro durante a autenticação! Autenticação falhou. Código de erro da prestadora: ' + response2.status);
}

function enrollment() 
{ 
	GN3DS.init(function(response) { 
		console.log(response);
		if((response != null) && (response.status >= 200) && (response.status <= 299)) { 
			GN3DS.authentication(function(response2) { 
				tresDsIniciado(response2); 
			}); 
		} else { 
			showModelPagamento3ds('Erro no processo de inicialização.');
		} 
	}); 
}

// condição se for 3DS
$('#btnApiPag').click(function(e) {
	if(($('#tipo_pag').val().indexOf('_3ds') != -1) && (this.type == 'button')){
		tresDS($('[name="cobranca"]').val(), $('#card_number_1').val());
	}
});

function tresDS(cobranca, card)
{
	var bin = card.replace(/[^0-9]/g,'').slice(0,6);
	$.ajax({
		method: "GET",
		dataType: 'json',
		url: "/cardsBrand/" + cobranca + '/' + bin,
		success: function(response, textStatus, xhr) {
			var token = xhr.getResponseHeader('authorization');
			var tokenPrincipal = xhr.getResponseHeader('authorization_principal');
			preencheCampos3ds(response, token, tokenPrincipal);
			enrollment();
		},
		error: function(xhr, ajaxOptions, thrownError) {
			var msg = xhr.responseJSON.message;
			$('#modalPagamento .modal-body')
			.html('<h5><i class="fas fa-times text-danger"></i> ' + msg + '</h5><br><a class="btn btn-secondary" href="' + window.location.href + '">Fechar</a>');
			$('#modalPagamento').modal({backdrop: 'static', keyboard: false, show: true});
		}
	});
}

function preencheCampos3ds(response, token, tokenPrincipal)
{
	var metodoPag = $('#tipo_pag').val() == 'debit_3ds' ? '03' : '02';
	$('#gn3ds_merchantBackEndTokenBasic').val(tokenPrincipal);
	$('#gn3ds_merchantBackEndTokenOauth').val(token);

	$('#gn3ds_cardType').val(response['card_type']);
	$('#gn3ds_totalAmount').val($('#amount').val().replace(/[^0-9]/g,''));
	$('#gn3ds_cardExpirationMonth').val($('#expiration_1').val().slice(0,2));
	$('#gn3ds_cardExpirationYear').val($('#expiration_1').val().slice(3));
	$('#gn3ds_cardNumber').val($('#card_number_1').val().replace(/[^0-9]/g,''));
	$('#gn3ds_cardHolderName').val($('#cardholder_name_1').val());
	$('#gn3ds_overridePaymentMethod').val(metodoPag);
	$('#gn3ds_installmentTotalCount').val($('#parcelas_1').val());
	$('#btnApiPag').before('<input type="hidden" name="brand" value="' + response['brand'] + '" />');
}

// Para o Checkout Iframe
$('.pay-button-getnet').click(function(){
	verificaCheckoutIframe();
});

function listenerCheckoutIframe(){
	// Funções compatíveis com IE e outros navegadores
	var eventMethod = (window.addEventListener ? 'addEventListener' : 'attachEvent');
	var eventer = window[eventMethod];
	var messageEvent = (eventMethod === 'attachEvent') ? 'onmessage' : 'message';

	// Ouvindo o evento do loader
	eventer(messageEvent, function iframeMessage(e) {
		var data = e.data || '';

		switch (data.status || data) {
			case 'success':
				var endpoint = window.location.protocol + '//' + window.location.hostname;
				window.location.replace(endpoint + '/checkout/sucesso/' + $('[name="cobranca"]').val());
			break;
			case 'close':
				window.location.replace($('#callbackURL').val());
				break; 
			default:
			break;
		}
	}, false);
}

function verificaCheckoutIframe(){
	$.ajax({
		method: "POST",
		dataType: 'json',
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		url: '/checkout/verify/' + $('[name="cobranca"]').val(),
		success: function(response) {
			listenerCheckoutIframe();
		},
		error: function(xhr, ajaxOptions, thrownError) {
			if(xhr.status == 419){
				if(($('#getnet-checkout').length > 0) && ($('#getnet-loader').length > 0)){
					$('#getnet-checkout').attr('style', 'z-index: -1').remove();
					$('#getnet-loader').remove();
					var msg = xhr.responseJSON.message;
					$('#modalPagamento .modal-body')
					.html('<h5><i class="fas fa-times text-danger"></i> ' + msg + '</h5><br><a class="btn btn-secondary" href="' + $('#callbackURL').val() + '">Fechar</a>');
					$('#modalPagamento').modal({backdrop: 'static', keyboard: false, show: true});
					return;
				}
			}
			window.location.replace($('#callbackURL').val());
		}
	});
}

// +++++++++++++++++++++++ ++++++++++++++++++++++++++++++ ++++++++++++++++++++++

$('#btnPrintSimulador').click(function(){
	var myWindow = window.open();
	var data = $('#dataInicio').val();
	data = data.slice(8,10) + '/' + data.slice(5,7) + '/' + data.slice(0,4);
	data = '<b>Data início das atividades:</b> ' + data + '</br>';
	var selectTipoPessoa = $('select[name="tipoPessoa"] option:selected').text();
	var tipoPessoa = '<b>Tipo Pessoa:</b> ' + selectTipoPessoa + '</br>';
	var capital = '<b>Capital social:</b> ' + $('#capitalSocial').val() + '</br>';
	var filial = $('#filialCheck:checked').length > 0 ? 'Com filial | ' + $('select[name="filial"] option:selected').text() + '</br>' : null;
	var empresa = $('#empresaIndividual:checked').length > 0 ? 'Empresa individual</br>' : null;
	var titulo = '<h4>RESULTADO DO SIMULADOR DE VALORES</h4><hr>';
	var final = titulo + tipoPessoa + data;
	if(selectTipoPessoa == 'Jurídica'){
		final = final + capital;
		if(filial != null)
			final = final + filial;
		if(empresa != null)
			final = final + empresa;
	}
	myWindow.document.write(final + $('#simuladorTxt').html());
	myWindow.print();
});

// Carta-serviços ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
$('#textosSumario').change(function(){
	var id = $('#textosSumario').val();
	var link = "/carta-de-servicos-ao-usuario/" + id;
	window.location.replace(window.location.protocol + "//" + window.location.host + link);
});

if($('#corpoTexto').length > 0)
	$('#corpoTexto').focus();

// FIM Carta-serviços ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
