var lotados = [];

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
