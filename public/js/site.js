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
	$('.registro_core_format').mask('0000000/0000', {reverse: true});
	$('.numeroInput').mask('99');
	$('.cep').mask('00000-000');
	$('.codigo_certidao').mask('AAAAAAAA - AAAAAAAA - AAAAAAAA - AAAAAAAA');
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

	// mascara de telefone com mudança de formato enquanto digita
	var SPMaskBehavior = function (val) {
		return val.replace(/\D/g, '').length === 10 ? '(00) 0000-00009' : '(00) 00000-0009';
	},
	optionsTel = {
		onKeyPress: function (val, e, field, options) {
			field.mask(SPMaskBehavior.apply({}, arguments), options);
		}
	};
	$('.telefone2Input').mask(SPMaskBehavior, optionsTel);
	// ****************************************************************
	
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

	$('[data-toggle="popover"]').popover({html: true});
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

// Logout Externo
$("#logout-externo").click(function(){
	var token = $('meta[name="csrf-token"]').attr('content');
	var link = "/externo/logout";
	var form = $('<form action="' + link + '" method="POST"><input type="hidden" name="_token" value="' + token + '"></form>');
	$('body').append(form);
	$(form).submit();
});

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

// Logout Externo
$('[name="tipo_conta"]').change(function(){
	var valor = $(this).val();
	valor == 'contabil' ? $('label[for="cpf_cnpj"]').text('CNPJ') : $('label[for="cpf_cnpj"]').text('CPF ou CNPJ');
	valor == 'contabil' ? $('input[name="cpf_cnpj"]').attr('placeholder', 'CNPJ') : $('input[name="cpf_cnpj"]').attr('placeholder', 'CPF ou CNPJ');
});

// ----------------------------------------------------------------------------------------------------------------------------
// Busca endereço

function preenche_formulario_cep(id, dados)
{
	$("#rua_" + id).val(dados.logradouro);
	$("#bairro_" + id).val(dados.bairro);
	$("#cidade_" + id).val(dados.localidade);
	$("#uf_" + id).val(dados.uf);
}

function limpa_formulário_cep_by_class(id) {
	// Limpa valores do formulário de cep.
	$("#rua_" + id).val("");
	$("#bairro_" + id).val("");
	$("#cidade_" + id).val("");
	$("#uf_" + id)[0].selectedIndex = 0;
	$("#ibge_" + id).val("");
}

// Para formulários com varios endereços
async function getEndereco(id)
{
	var objeto = $("#cep_" + id);
	if(objeto.val().length === 9) {
		var cep = objeto.val().replace(/\D/g, '');
		if (cep != "") {
			var validacep = /^[0-9]{8}$/;
			if(validacep.test(cep)) {
				$("#rua_" + id).val("...");
				$("#bairro_" + id).val("...");
				$("#cidade_" + id).val("...");
				$("#uf_" + id).val("...");
				//Consulta o webservice viacep.com.br/
				const dados = await $.getJSON("https://viacep.com.br/ws/"+ cep +"/json/?callback=?", function(dados){
					if ("erro" in dados) {
						alert("CEP não encontrado.");
						limpa_formulário_cep_by_class(id);
					}
				});
				return dados;
			} 
			else 
				alert("Formato de CEP inválido.");
		} 
		limpa_formulário_cep_by_class(id);
	}
}
// ----------------------------------------------------------------------------------------------------------------------------

// ----------------------------------------------------------------------------------------------------------------------------
// Campo file dinâmico

// Confere se o ultimo input file está vazio 
function arquivoVazio(nome){
	if($(nome + " .custom-file-input:last").val().length == 0)
		return true;
}

function addArquivo(nome){
	if(nome == '')
		return false;
		
	var total = $(".Arquivo_" + nome).length + $(".ArquivoBD_" + nome).length;
	var total_files = 1 ;

	if(($(".ArquivoBD_" + nome).length < total_files) && ($(".Arquivo_" + nome).css("display") == "none")){ //quando usa o hide
		$(".Arquivo_" + nome).show();
	} else if((total < total_files) && (!arquivoVazio(".Arquivo_" + nome))){
		var novoInput = $(".Arquivo_" + nome + ":last");
		novoInput.after(novoInput.clone());
		$(".Arquivo_" + nome + " .custom-file-input:last").val("");
		$(".Arquivo_" + nome + " .custom-file-input:last")
		.siblings(".custom-file-label")
		.removeClass("selected")
		.html('<span class="text-secondary">Escolher arquivo</span>');
		$(".Arquivo_" + nome + " .invalid-feedback:last").remove();
	}
}

function limparFile(nomeBD, totalFiles)
{
	var todoArquivo = $('.Arquivo_' + nomeBD + ':last');
	var classe = todoArquivo.attr('class');
	if($('.' + classe).length > 1)
		todoArquivo.remove();
	else if($(".ArquivoBD_" + nomeBD).length < totalFiles){
		$('.' + classe + ' .custom-file-input:last').val("");
		$('.' + classe + ' .custom-file-input:last').siblings(".custom-file-label")
		.removeClass("selected")
		.html('<span class="text-secondary">Escolher arquivo</span>');
		$('.' + classe + ' .custom-file-input:last').removeClass('is-invalid');
		$('.' + classe + " .invalid-feedback:last").remove();
	}else
		todoArquivo.hide();
}

function limparFileBD(nome, dados, totalFiles)
{
	var total = $('.ArquivoBD_' + nome).length;
	$('.ArquivoBD_' + nome).each(function(){
		if($(this).find("button").val() == dados){
			total == 1 ? $(this).hide() : $(this).remove();
			if(total == totalFiles)
				$('.Arquivo_' + nome).show().parent().find("label").text("Escolher Arquivo");
		}
	});
}

function appendArquivoBD(finalLink, nome, valor, id, totalFiles)
{
	var total = $(".ArquivoBD_" + nome).length;
	var link = window.location.protocol + '//' + window.location.hostname + '/' + finalLink + '/';
	var cloneBD = null;

	if((total == 1) && ($(".ArquivoBD_" + nome).css("display") == "none"))
		cloneBD = $(".ArquivoBD_" + nome);
	
	if((total >= 1) && (total < totalFiles) && !($(".ArquivoBD_" + nome).css("display") == "none"))
		cloneBD = $(".ArquivoBD_" + nome + ":last").clone(true);

	cloneBD.find("input").val(valor);
	$('#contabil_editar_pr').length > 0 ? cloneBD.find(".Arquivo-Download").attr("href", link + 'download/' + id + '/' + $('#contabil_editar_pr').val()) : 
	cloneBD.find(".Arquivo-Download").attr("href", link + 'download/' + id);
	cloneBD.find(".modalExcluir").val(id);

	if((total == 1) && (cloneBD.css("display") == "none"))
		cloneBD.show();
	else
		$(".ArquivoBD_" + nome + ':last').after(cloneBD);

	limparFile(nome, totalFiles);
}
// ----------------------------------------------------------------------------------------------------------------------------

//	--------------------------------------------------------------------------------------------------------
// Funcionalidade Solicitação de Registro (Pré-registro)

function putDadosPreRegistro(objeto)
{
	var classesObjeto = objeto.attr("class");
	var classe = classesObjeto.split(' ')[0];
	var campo = objeto.attr("name");
	var valor = campo == 'path' ? objeto[0].files : objeto.val();
	var cT = campo == 'path' ? false : 'application/x-www-form-urlencoded';
	var pD = campo == 'path' ? false : true;
	var frmData = new FormData();
	var dados = null;
	var arrayEndereco = ['cep', 'logradouro', 'numero', 'complemento', 'cidade', 'uf'];
	var contabil_editar_id = $('#contabil_editar_pr').length > 0 ? $('#contabil_editar_pr').val() : null;
	var link_post = '';
	var link_delete = '';

	if(campo == 'path'){
		for(var i = 0; i < valor.length; i++)
			frmData.append("valor[]", valor[i]);
		frmData.append('campo', campo);
		frmData.append('classe', classe);
	}

	if((campo == "") || (classe == ""))
		return;

	if(classe == 'Socio-Excluir'){
		classe = 'pessoaJuridica.socios';
		campo = 'cpf_cnpj_socio';
	}

	if(classe == 'Arquivo-Excluir')
		dados = {
			'_method': 'delete',
			'id': valor
		};
	else
		dados = {
			'id_socio': classe == 'pessoaJuridica.socios' ? $('#form_socio [name="id_socio"]').val() : null,
			'classe': classe,
			'campo': campo,
			'valor': valor
		};

	link_post = contabil_editar_id != null ? '/externo/inserir-registro-ajax/' + contabil_editar_id : '/externo/inserir-registro-ajax';
	link_delete = (contabil_editar_id != null) && (classe == 'Arquivo-Excluir') ? 
	'/externo/pre-registro-anexo/excluir/' + dados.id + '/' + contabil_editar_id : '/externo/pre-registro-anexo/excluir/' + dados.id;

	$("#modalLoadingBody").html('<i class="spinner-border text-info"></i> Salvando');
	$('#modalLoadingPreRegistro').modal({backdrop: "static", keyboard: false, show: true});

	$.ajax({
		method: 'POST',
		enctype: 'multipart/form-data',
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		data: campo == 'path' ? frmData : dados,
		dataType: 'json',
		url: classe == 'Arquivo-Excluir' ? link_delete : link_post,
		processData: pD,
        contentType: cT,
		cache: false,
		timeout: 60000,
		success: function(response) {
			$("#modalLoadingPreRegistro").modal('hide');
			if(arrayEndereco.indexOf(campo) != -1)
				confereEnderecoEmpresa(response['resultado']);
			if(campo == 'cpf_rt')
				preencheRT(response['resultado']);
			if(campo == 'cnpj_contabil')
				preencheContabil(response['resultado']);
			if(campo == 'path')
				preencheFile(response['resultado']);
			if(classe == 'Arquivo-Excluir')
				removeFile(response['resultado']);
			if(classe == 'pessoaJuridica.socios'){
				removeSocio(response['resultado'], $('#form_socio [name="id_socio"]').val());
				preencheSocio(response['resultado'], campo, valor);
			}
			removerMsgErroServer(objeto, campo, campo.indexOf('_socio') >= 0 ? dados : null);
			$('#atualizacaoPreRegistro').text(response['dt_atualizado']);
			valorPreRegistro = valor;
			// confereObrigatorios();
		},
		error: function(request, status, error) {
			if(campo == 'cpf_cnpj_socio')
				$('#mostrar_socios').click();

			var errorFunction = getErrorMsg(request);
			$("#modalLoadingBody").html('<i class="icon fa fa-times text-danger"></i> ' + errorFunction[0]);
			$("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
			setTimeout(function() {
				$("#modalLoadingPreRegistro").modal('hide');
			}, errorFunction[1]); 
			valorPreRegistro = null;
			console.clear();
		}
	});
}

function removerMsgErroServer(objeto, campo, dadosSocio = null)
{
	var endEmpresa = '.erroPreRegistro[value="cep_empresa"], .erroPreRegistro[value="bairro_empresa"], ';
	endEmpresa += '.erroPreRegistro[value="logradouro_empresa"], .erroPreRegistro[value="numero_empresa"], ';
	endEmpresa += '.erroPreRegistro[value="complemento_empresa"], .erroPreRegistro[value="cidade_empresa"], .erroPreRegistro[value="uf_empresa"]';

	if((campo.indexOf('_socio') >= 0) && (dadosSocio !== null)){
		if($('.erroPreRegistro[value="' + campo + '_' + dadosSocio.id_socio + '"]').length > 0)
			$('.erroPreRegistro[value="' + campo + '_' + dadosSocio.id_socio + '"]').parent().remove();
	}else{
		// remove mensagem de validação do servidor
		if(objeto.next().hasClass('invalid-feedback'))
			objeto.removeClass('is-invalid').next().remove();
		if($('.erroPreRegistro[value="' + campo + '"]').length > 0)
			$('.erroPreRegistro[value="' + campo + '"]').parent().remove();
	}

	if(($('.erroPreRegistro').length == 0) && ($('#erroPreRegistro').length == 1))
		$('#erroPreRegistro').remove();
	if(campo == 'checkEndEmpresa')
		$(endEmpresa).parent().remove();
}

function getErrorMsg(request)
{
	var time = 5000;
	var errorMessage = request.status + ': ' + request.statusText;
	var nomesCampo = ['classe', 'campo', 'valor'];
	if(request.status == 422){
		for(var nome of nomesCampo){
			var erroNome = _.has(request.responseJSON.errors,"nome");
			var msg = erroNome ? request.responseJSON.errors[nome] : Object.values(request.responseJSON.errors)[0];
			if(msg != undefined)
				errorMessage = msg[0];
		}
		time = 3000;
	}
	if(request.status == 401){
		errorMessage = request.responseJSON.message;
		time = 3000;
	}
	if(request.status == 419){
		errorMessage = "Sua sessão expirou! Recarregue a página";
		time = 2000;
	}
	if(request.status == 429){
		var aguarde = request.getResponseHeader('Retry-After');
		errorMessage = "Excedeu o limite de requisições por minuto.<br>Aguarde " + aguarde + " segundos";
		time = 2500;
	}
	return [errorMessage, time];
}

function confereEnderecoEmpresa(boolMesmoEndereco)
{
	if(boolMesmoEndereco === null)
		return;
	if(boolMesmoEndereco){
		$('#checkEndEmpresa').prop('checked', true);
		$("#habilitarEndEmpresa").prop('disabled', true).hide();
	}else{
		$('#checkEndEmpresa').prop('checked', false);
		$("#habilitarEndEmpresa").prop('disabled', false).show();
	}
}

function preencheContabil(dados)
{
	if(_.has(dados,"update")){
		var texto = "Somente pode trocar o CNPJ novamente dia: <br>" + dados.update;
		$("#modalLoadingBody").html('<i class="icon fa fa-times text-danger"></i> ' + texto);
		$("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
		setTimeout(function() {
			$("#modalLoadingPreRegistro").modal('hide');
		}, 2500);
	}else{
		if($('#inserirRegistro input[name="cnpj_contabil"]').val() == ""){
			$('#inserirRegistro [name$="_contabil"]').each(function(){
				$(this).val('');
			});
			$('#campos_contabil').prop("disabled", true);
		}else{
			var desabilita = (dados.aceite != null) && (dados.ativo != null);
			$('#campos_contabil').prop("disabled", desabilita);
			$('#inserirRegistro [name$="_contabil"]').each(function(){
				var name = $(this).attr('name').slice(0, $(this).attr('name').indexOf('_contabil'));
				if(name != 'cnpj')
					$(this).val(dados[name]);
			});
		}
	}
}

function preencheRT(dados)
{
	var sem_cpf = $('#inserirRegistro input[name="cpf_rt"]').val() == "";

	if(_.has(dados,"update")){
		var texto = "Somente pode trocar o CPF novamente dia: <br>" + dados.update;
		$("#modalLoadingBody").html('<i class="icon fa fa-times text-danger"></i> ' + texto);
		$("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
		setTimeout(function() {
			$("#modalLoadingPreRegistro").modal('hide');
		}, 2500);
	}else{
		if(sem_cpf){
			$('#campos_rt').prop("disabled", true);
			$('#inserirRegistro #registro_preRegistro').val('');
			$('#inserirRegistro [name$="_rt"]').each(function(){
				$(this).val('');
			});
		}else{
			$('#campos_rt').prop("disabled", false);
			$('#inserirRegistro #registro_preRegistro').val(dados.registro);
			$('#inserirRegistro [name$="_rt"]').each(function(){
				var name = $(this).attr('name').slice(0, $(this).attr('name').indexOf('_rt'));
				if(name != 'cpf')
					$(this).val(dados[name]);
			});
			if(_.has(dados,"tab") && _.has(dados,"id_socio")){
				removeSocio('remover', dados.id_socio);
				preencheSocio(dados, null, null);
				$('#checkRT_socio').prop('checked', true);
			}
		}

		// remove a tab do sócio e desmarca e desabilita o checkbox
		$('#checkRT_socio').prop('disabled', sem_cpf);
		if(sem_cpf && $('#checkRT_socio')[0].checked){
			var id = $('#acoes_socio button > span.badge').parent().attr('data-target').replace('#socio_', '');
			$('#checkRT_socio').prop('checked', false);
			removeSocio('remover', id);
		}
	}
}

function preencheFile(dados)
{
	if(_.has(dados,"id")){
		if(dados.id && dados.nome_original){
			appendArquivoBD('externo/pre-registro-anexo', "anexo", dados.nome_original, dados.id, pre_registro_total_files);
			$('#fileObrigatorio').val('existeAnexo');
		}
	}
}

function removeFile(dados)
{
	if(dados != null){
		limparFileBD('anexo', dados, pre_registro_total_files);
		if(($('.ArquivoBD_anexo').length == 1) && ($('.ArquivoBD_anexo').attr('style') == "display: none;"))
			$('#fileObrigatorio').val('');
	}
}

function atualizaOrdemSocios(){
	if($('#acoes_socio .ordem-socio').length > 0)
		$('#acoes_socio .ordem-socio').each(function(index){
			var count = index + 1;
			$(this).text(count);
		});
}

function desabilitaBtnAcoesSocio(){
	if($('#analiseCorrecao').length > 0){
		if($('#acoes_socio .editar_socio').length > 0)
			$('#acoes_socio .editar_socio').prop('disabled', true);
		if($('#acoes_socio .excluir_socio').length > 0)
			$('#acoes_socio .excluir_socio').prop('disabled', true);
	}
}

function removeSocio(dados, id)
{
	if(dados == 'remover'){
		$('#acoes_socio #socio_' + id + '_box').remove();
		atualizaOrdemSocios();
		if(($('#acoes_socio button > span.badge').length == 0) && $('#checkRT_socio')[0].checked)
			$('#checkRT_socio').prop('checked', false);
	}

	var limite = $('#acoes_socio .ordem-socio').length >= parseInt($('#limite-socios').text());
	$('#criar_socio').prop('disabled', limite);
}

function preencheSocio(dados, campo, valor)
{
	if(_.has(dados,"update") || _.has(dados,"existente") || _.has(dados,"limite")){
		if((campo == 'checkRT_socio') && (valor == 'on'))
			$('#checkRT_socio').prop('checked', false);

		var texto = _.has(dados,"update") ? "Somente pode trocar o CPF/CNPJ novamente dia: <br>" + dados.update : dados.existente;
		texto = _.has(dados,"limite") ? dados.limite : texto;

		$("#modalLoadingBody").html('<i class="icon fa fa-times text-danger"></i> ' + texto);
		$("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
		$('#mostrar_socios').click();
		setTimeout(function() {
			$("#modalLoadingPreRegistro").modal('hide');
		}, 3000);
	}else if(_.has(dados,"tab")){
		$('#inserirRegistro input[name="cpf_cnpj_socio"]').prop('disabled', true);
		$('#acoes_socio').append(dados.tab);
		_.has(dados,"rt") ? habDesabCamposSocio('rt') : habDesabCamposSocio($('#form_socio input[name="cpf_cnpj_socio"]').length > 14 ? 'cnpj' : 'cpf');
		_.has(dados,"id_socio") ? null : $('#acoes_socio .editar_socio:last').click();
	}else if(_.has(dados,"atualizado")){
		removeSocio('remover', dados.id);
		$('#acoes_socio').append(dados.atualizado);
	}

	atualizaOrdemSocios();

	var limite = $('#acoes_socio .ordem-socio').length >= parseInt($('#limite-socios').text());
	$('#criar_socio').prop('disabled', limite);
}

function habDesabCamposSocio(tipo){
	$('.esconder-rt-socio').show();
	$('.esconder-campo-socio').show();

	switch (tipo) {
		case 'rt':
			$('.esconder-rt-socio').hide();
			break;
		case 'cnpj':
			$('.esconder-campo-socio').hide();
			break;
		default:
			break;
	}
}

async function callbackEnderecoPreRegistro(restoId)
{
	var dadosAntigos = [$("#rua_" + restoId).val(), $("#bairro_" + restoId).val(), $("#cidade_" + restoId).val(), $("#uf_" + restoId).val()];
	var array = [$("#rua_" + restoId), $("#bairro_" + restoId), $("#cidade_" + restoId), $("#uf_" + restoId)];
	var dados = await getEndereco(restoId);
	preenche_formulario_cep(restoId, dados);
	for (let i = 0; i < array.length; i++) {
		if(dadosAntigos[i] != array[i].val())
			putDadosPreRegistro(array[i]); 
	}
	putDadosPreRegistro($("#cep_" + restoId));
}

function avancarVoltarDisabled(ativado, ordemMenu)
{	
	if(ativado == 0){
		$('#voltarPreRegistro').attr("disabled", true);
		$('#avancarPreRegistro').attr("disabled", false);
	}else if(ativado == (ordemMenu.length - 1)){
		$('#voltarPreRegistro').attr("disabled", false);
		$('#avancarPreRegistro').attr("disabled", true);
	}else{
		$('#voltarPreRegistro').attr("disabled", false);
		$('#avancarPreRegistro').attr("disabled", false);
	}
}

function avancarVoltarPreRegistro(tipo, ativado, ordemMenu)
{	
	if(tipo == 'voltarPreRegistro')
		if(ativado != 0){
			var novoAtivado = ativado - 1;
			$('.menu-registro.nav-pills li:eq(' + novoAtivado + ') a').tab('show').focus();
			return novoAtivado;
		}
	if(tipo == 'avancarPreRegistro')
		if(ativado != (ordemMenu.length - 1)){
			var novoAtivado = ativado + 1;
			$('.menu-registro.nav-pills li:eq(' + novoAtivado + ') a').tab('show').focus();
			return novoAtivado;
		}
	
	return ativado;
}

// function confereObrigatorios()
// {
// 	var obrigatorios = $('.obrigatorio:enabled');
// 	var total = obrigatorios.length;
	
// 	obrigatorios.each(function(){
// 		if($(this).val() != "")
// 			total--;
// 	});

// 	if(total == 0)
// 		$('#btnVerificaPend').prop('disabled', false);
// 	else
// 		$('#btnVerificaPend').prop('disabled', true);
// }

function disabledOptionsSelect(name, valor)
{
	if(name == 'nacionalidade')
		valor != 'Brasileira' ? 
		$('#inserirRegistro input[name="naturalidade_cidade"], #inserirRegistro select[name="naturalidade_estado"]').prop("disabled", true) : 
		$('#inserirRegistro input[name="naturalidade_cidade"], #inserirRegistro select[name="naturalidade_estado"]').prop("disabled", false);

	if(name == 'tipo_telefone')
		valor != 'Celular' ? $('#inserirRegistro #opcoesCelular').prop("disabled", true) : 
		$('#inserirRegistro #opcoesCelular').prop("disabled", false);

	if(name == 'tipo_telefone_1')
		valor != 'Celular' ? $('#inserirRegistro #opcoesCelular_1').prop("disabled", true) : 
		$('#inserirRegistro #opcoesCelular_1').prop("disabled", false);
}

function desabilitaNatSocio(){
	var desabilita = ($('#nacionalidade_socio').val() != 'Brasileira') && ($('#nacionalidade_socio').val() != '');
	$('select[name="naturalidade_estado_socio"]').prop("disabled", desabilita);
}

function changeLabelIdentidade(objeto)
{
	if((objeto.attr('name') == 'tipo_identidade') || (objeto.attr('name') == 'tipo_identidade_rt')){
		if(objeto.attr('name') == 'tipo_identidade'){
			$('[name="tipo_identidade"]').val() == '' ? $('label[for="identidade"]').text('N° do documento') : 
			$('label[for="identidade"]').text('N° do(a) ' + $('[name="tipo_identidade"] option:selected').text());
			$('<span class="text-danger"> *</span>').appendTo('label[for="identidade"]');
		}else{
			$('[name="tipo_identidade_rt"]').val() == '' ? $('label[for="identidade_rt"]').text('N° do documento') : 
			$('label[for="identidade_rt"]').text('N° do(a) ' + $('[name="tipo_identidade_rt"] option:selected').text());
			$('<span class="text-danger"> *</span>').appendTo('label[for="identidade_rt"]');
		}
	}
}

function getFullNameFile(item) {
	return [item.name] + ', ';
}

function limparFormSocio(){
	$('#form_socio [name$="_socio"]').each(function(){
		this.tagName != "SELECT" ? this.value = "" : this.selectedIndex = 0;
	});
}

function criarFormSocio(){
	$('#cpf_cnpj_socio').prop("disabled", false);
	$('#campos_socio').prop("disabled", true);
	habDesabCamposSocio('cpf');
}

function editarFormSocio(objeto){
	$('#cpf_cnpj_socio').prop("disabled", true);
	$('#campos_socio').prop("disabled", false);
	objeto.find('.editar_dado').each(function(){
		var nome = '#form_socio [name="' + this.classList[0] + '"]';
		var texto = $(this).text();

		if((texto != '-----') && ($(nome).length > 0)){
			if(['text', 'date', 'hidden'].indexOf($(nome).attr('type')) >= 0)
				$(nome).val(texto);
			else
				['naturalidade_estado_socio', 'uf_socio'].indexOf(this.classList[0]) >= 0 ? $(nome + ' option[value="' + texto + '"]').prop('selected', true) : 
				$(nome + ' option:contains("' + texto + '"):first').prop('selected', true);
		}
	});

	desabilitaNatSocio();
	$('#form_socio [name="cpf_cnpj_socio"]').val().length > 14 ? habDesabCamposSocio('cnpj') : habDesabCamposSocio('cpf');
	if($('#form_socio [name="cpf_cnpj_socio"]').val() == $('#inserirRegistro [name="cpf_rt"]').val())
		habDesabCamposSocio('rt');
}

function modalExcluirPR(id, conteudo, titulo_exclusao, texto_tipo_exclusao){
	var novo = titulo_exclusao == "Sócio" ? "Socio-Excluir" : "Arquivo-Excluir";
	var trocar = titulo_exclusao == "Sócio" ? "Arquivo-Excluir" : "Socio-Excluir";
	$('#modalExcluir #completa-texto-excluir').text(texto_tipo_exclusao);
	$('#modalExcluir #completa-titulo-excluir').text(titulo_exclusao);
	$('#modalExcluir #excluir-geral').val(id);
	$('#modalExcluir #textoExcluir').text(conteudo);
	const classes = $('#modalExcluir #excluir-geral')[0].classList;
	classes.replace(trocar, novo);
}

$('#inserirPreRegistro').ready(function(){
	// confereObrigatorios();
	if($('[name="tipo_telefone"]').length)
		disabledOptionsSelect("tipo_telefone", $('[name="tipo_telefone"]').val());
	if($('[name="tipo_telefone_1"]').length)
		disabledOptionsSelect("tipo_telefone_1", $('[name="tipo_telefone_1"]').val());
	if($('[name="tipo_identidade"]').length)
		changeLabelIdentidade($('[name="tipo_identidade"]'));
	if($('[name="tipo_identidade_rt"]').length)
		changeLabelIdentidade($('[name="tipo_identidade_rt"]'));
})

$('#voltarPreRegistro, #avancarPreRegistro, .menu-registro .nav-link').click(function() {
	var ordemMenu = [];
	$('.menu-registro .nav-link').each(function(){
		ordemMenu.push($(this).text().trim());
	});
	var ativoAntes = 0;
	if($(this).hasClass('nav-link'))
		ativoAntes = ordemMenu.indexOf($(this).text().trim());
	else
		ativoAntes = ordemMenu.indexOf($('.menu-registro .active').text().trim());
	var ativoDepois = avancarVoltarPreRegistro(this.id, ativoAntes, ordemMenu);
	avancarVoltarDisabled(ativoDepois, ordemMenu);
	
});

// Habilitar Endereço da Empresa no Registro
$("#checkEndEmpresa:checked").length == 1 ? $("#habilitarEndEmpresa").prop('disabled', true).hide() : $("#habilitarEndEmpresa").prop('disabled', false).show();

$("#checkEndEmpresa").change(function(){
	this.checked ? $("#habilitarEndEmpresa").prop('disabled', true).hide() : $("#habilitarEndEmpresa").prop('disabled', false).show();
});

$("#checkRT_socio").change(function(){
	!this.checked ? $(this).val('off') : $(this).val('on');
	if($(this).val() == 'off'){
		var id = $('#acoes_socio button > span.badge').parent().attr('data-target').replace('#socio_', '');
		$('#form_socio [name="id_socio"]').val(id);
	}
});

$('#inserirRegistro .modalExcluir').click(function(){
	var id = $(this).val();
	var texto = $(this).parent().parent().find("input").val();
	modalExcluirPR(id, texto, "Arquivo", "o anexo");
});

$('#modalExcluir #excluir-geral').click(function(e){
	putDadosPreRegistro($(this));
	$('#modalExcluir').modal('hide');
});

// gerencia os arquivos, cria os inputs, remove os inputs, controla as quantidades de inputs e files vindo do bd
var pre_registro_total_files = $('#totalFilesServer').length ? $('#totalFilesServer').val() : 0;

// ao carregar a pagina, verifica se possui o limite maximo de arquivos permitidos, caso sim, ele impede de adicionar mais
$('form #inserirRegistro').ready(function(){
	atualizaOrdemSocios();
	desabilitaBtnAcoesSocio();
	if($(".ArquivoBD_anexo").length == pre_registro_total_files)
		$(".Arquivo_anexo").hide();
}); 

// Faz aparecer o nome do arquivo na máscara do input estilizado, remove as mensagens de erro
//  e adiciona, caso seja possível, um novo input
$("#inserirRegistro .files").on("change", function() {

	// procedimento usado no bootstrap 4 para usar um input file customizado
	var files = Array.from(this.files);
	var fileName = files.map(getFullNameFile);
	$(this).siblings(".custom-file-label").addClass("selected").html(fileName);
	// fim do procedimento do input customizado do bootstrap 4

	// limpa o input caso esteja com erro de validação
	$(this).removeClass("is-invalid");
	$(this).parent().remove("div .invalid-feedback");

	// procedimento para recuperar a classe e adicionar o final do nome para o método de add input
	var nomeClasse = $(this).parent().parent().parent().parent().attr('class');
	var nome = nomeClasse.slice(nomeClasse.indexOf('_') + 1);
	addArquivo(nome);
});

// remove a div com input file ou limpa o campo se for 1 input
$("#inserirRegistro .limparFile").click(function(){
	limparFile('anexo', pre_registro_total_files);
});

$('#inserirRegistro input[id^="cep_"]').on('keyup', function(){
	var indice = this.id.indexOf("_");
	var restoId = this.id.slice(indice + 1, this.id.length);
	var diferente = valorPreRegistro != $(this).val();
	var valorLength = $(this).val().length == 9;
	if(valorLength && diferente){
		// keyup dispara multiplos eventos quando cola via teclado e ainda dispara ao final a mascara
		// com a variável preenchida abaixo, entra na lógica somente uma vez independente da quantidade de disparos simultaneos
		valorPreRegistro = $(this).val();
		callbackEnderecoPreRegistro(restoId);
	}
});

var valorPreRegistro = null;

$('#inserirRegistro input:not(:checkbox,:file)').focus(function(){
	valorPreRegistro = $(this).val();
});

$('#inserirRegistro input[name="cpf_rt"], #inserirRegistro input[name="cnpj_contabil"], #inserirRegistro input[name="cpf_cnpj_socio"]').on('keyup', function(){
	var objeto = $(this);
	var vazio = (objeto.attr('name') != 'cpf_cnpj_socio') && (objeto.val() == "");
	var validaCpf = (['cpf_rt'].indexOf(objeto.attr('name')) >= 0) && (objeto.val().length == 14);
	var validaCnpj = (['cnpj_contabil', 'cpf_cnpj_socio'].indexOf(objeto.attr('name')) >= 0) && (objeto.val().length == 18);
	var diferente = valorPreRegistro != $(this).val();

	if(diferente && (validaCpf || validaCnpj || vazio)){
		// keyup dispara multiplos eventos quando cola via teclado e ainda dispara ao final a mascara
		// com a variável preenchida abaixo, entra na lógica somente uma vez independente da quantidade de disparos simultaneos
		valorPreRegistro = objeto.val();
		putDadosPreRegistro(objeto);
	}
});

$('#inserirRegistro input:not(:checkbox,:file,[name="cpf_rt"],[name="cnpj_contabil"])').blur(function(){
	var name = $(this).attr('name');

	if((name == 'cpf_cnpj_socio') && ($(this).val().length < 14))
		return;

	if(valorPreRegistro != $(this).val())
		if((name.includes('cep_') && ($(this).val() == '')) || !name.includes('cep_')){
			putDadosPreRegistro($(this));
			valorPreRegistro = null;
		}
});

$('#inserirRegistro select, #inserirRegistro input[type="file"]').change(function(){
	disabledOptionsSelect($(this).attr('name'), $(this).val());
	($(this).attr('type') == 'file') && ($(this).val() == "") ? null : putDadosPreRegistro($(this));
	changeLabelIdentidade($(this));
});

$('#inserirRegistro input:checkbox').change(function(){
	var checkMesmoEndereco = $(this).attr('name') == 'checkEndEmpresa';
	if((this.checked && checkMesmoEndereco) || !checkMesmoEndereco)
		putDadosPreRegistro($(this));
});

// --------------------------------------------------------------------------------------
// 2 métodos em Jquery para focar no campo que está o erro pelo link na tabela de erros
// No primeiro click vai direto para o input, no segundo necessita do método abaixo:
// $('.nav-pills a').on('shown.bs.tab', function(){
var teste;
$('.erroPreRegistro').click(function(){
	var campo = $(this).val().indexOf('_socio_') >= 0 ? 'checkRT_socio' : $(this).val();
	var hrefMenu = $('[name="' + campo + '"]').parents('.tab-pane').attr('id');
	var id_socio = null;

	if(campo == 'checkRT_socio'){
		$('#mostrar_socios').click();
		id_socio = $(this).val().replace(/\D/g, '');
		if($(this).val().replace('_' + id_socio, '') != 'cpf_cnpj_socio')
			$('#socio_' + id_socio + ' .acoes_socio button.editar_socio').click();
		campo = $(this).val().replace('_' + id_socio, '');
	}

	teste = campo == 'cpf_cnpj_socio' ? 'button[data-target="#socio_' + id_socio + '"]' : '[name="' + campo + '"]';
	$('.menu-registro.nav-pills [href="#' + hrefMenu + '"]').hasClass('active') ? 
	$(teste).focus() : $('.menu-registro.nav-pills [href="#' + hrefMenu + '"]').tab('show');
});

$('.menu-registro.nav-pills a').on('shown.bs.tab', function(){
    if($('.erroPreRegistro').length > 0)
		$(teste).focus();
});
// --------------------------------------------------------------------------------------

$(window).on('load', function() {
	if($('#modalSubmitPreRegistro').hasClass('show'))
		$('#modalSubmitPreRegistro').modal({backdrop: "static", keyboard: false}).modal('show');
});

$('#submitPreRegistro').click(function(){
	if($('#modalSubmitPreRegistro').hasClass('show'))
		$('#modalSubmitPreRegistro').modal('hide');
		
	$("#modalLoadingBody").html('<i class="spinner-border text-info"></i> Enviando...');
	$('#modalLoadingPreRegistro').modal({backdrop: "static", keyboard: false, show: true});
	$('#campos_contabil').attr('disabled', false);
	$('#inserirRegistro').submit();
});

$('#btnVerificaPend').click(function(){
	$('#campos_contabil').attr('disabled', false);
});

// carrega texto da justificativa
$('.textoJust').click(function(e) {
	e.preventDefault();
	$('#modalJustificativaPreRegistro').modal('hide');
	$('#modalJustificativaPreRegistro .modal-body textarea').val('');
	$("#modalLoadingBody").html('<i class="spinner-border text-info"></i> Carregando');
	$('#modalLoadingPreRegistro').modal({backdrop: "static", keyboard: false, show: true});
  
	var item = this.innerText;
	$.ajax({
	  method: 'GET',
	  dataType: 'json',
	  url: this.value,
	  cache: false,
	  timeout: 60000,
	  success: function(response) {
		$("#modalLoadingPreRegistro").modal('hide');
		$('#modalJustificativaPreRegistro .modal-title').html('<span class="text-danger">Justificativa </span>' + item);
		$('#modalJustificativaPreRegistro .modal-body textarea').val(response.justificativa);
		$('#modalJustificativaPreRegistro').modal({backdrop: "static", keyboard: false, show: true});
	  },
	  error: function(request, status, error) {
		  var errorFunction = getErrorMsg(request);
		  $("#modalLoadingBody").html('<i class="icon fa fa-times text-danger"></i> ' + errorFunction[0]);
		  $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
		  setTimeout(function() {
			$("#modalLoadingPreRegistro").modal('hide');
		  }, errorFunction[1]); 
		  console.clear();
	  }
	});
});

$('#mostrar_socios').click(function() {
	$("#acoes_socio .collapse").each(function(){
		$(this).collapse('hide');
	});
	$('#form_socio').hide();
	$('#acoes_socio').show();
});

$('#acoes_socio').on('click', '#criar_socio', function() {
	$('#form_socio').show();
	limparFormSocio();
	$('#acoes_socio').hide();
	criarFormSocio();
});

$('#acoes_socio').on('click', '.editar_socio', function() {
	$('#form_socio').show();
	limparFormSocio();
	$('#acoes_socio').hide();
	editarFormSocio($(this).parents('.dados_socio'));
});

$('#acoes_socio').on('click', '.excluir_socio', function() {
	var id = $(this).parents('.dados_socio').find('.id_socio').text();
	var texto = $(this).parents('.dados_socio').find('.cpf_cnpj_socio').text();
	$('#form_socio [name="id_socio"]').val(id);
	$('#form_socio [name="cpf_cnpj_socio"]').val('');
	modalExcluirPR(null, texto, "Sócio", "o sócio");
});

$('#nacionalidade_socio').change(function(){
	desabilitaNatSocio();
});

//	--------------------------------------------------------------------------------------------------------
// FIM da Funcionalidade Solicitação de Registro (Pré-registro)