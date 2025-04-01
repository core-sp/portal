"use strict";

$(document).ready(function(){

    let elemento_init = $('#modulo-init');

    import(elemento_init.attr('src'))
    .then((init) => {
        let subarea = window.location.pathname.search('/representante/') > -1 ? 'restrita-rc' : null;

        init.default('externo', subarea);
        init.opcionais();
        console.log('[MÓDULOS] # Versão dos scripts: ' + elemento_init.attr('class'));
    })
    .catch((err) => {
        console.log(err);
        alert('Erro na página! Módulo não carregado! Tente novamente mais tarde!');
    });

});


/*
****************************************************************************************************************************
    Código pre-registro e usuario externo.
****************************************************************************************************************************
*/

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
	$("#logradouro_" + id).val(dados.logradouro);
	$("#bairro_" + id).val(dados.bairro);
	$("#cidade_" + id).val(dados.localidade);
	$("#uf_" + id).val(dados.uf);
}

function limpa_formulário_cep_by_class(id) {
	// Limpa valores do formulário de cep.
	$("#logradouro_" + id).val("");
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
				$("#logradouro_" + id).val("...");
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
// Funcionalidade Solicitação de Registro (Pré-registro)

// Campo file dinâmico

// Confere se o ultimo input file está vazio 
function arquivoVazio(nome){
	return $(nome + " .custom-file-input:last").val().length == 0;
}

function addArquivo(nome){
	if(nome == '')
		return false;
		
	var total = $(".Arquivo_" + nome).length + $(".ArquivoBD_" + nome).length;
	var total_files = 1;

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

	(total == 1) && (cloneBD.css("display") == "none") ? cloneBD.show() : $(".ArquivoBD_" + nome + ':last').after(cloneBD);
	limparFile(nome, totalFiles);
}
// ----------------------------------------------------------------------------------------------------------------------------

//	--------------------------------------------------------------------------------------------------------

const putDadosPR = new Object();

function popObjetoPutDados(objeto){
	var classesObjeto = objeto.attr("class");
	var contabil_editar_id = $('#contabil_editar_pr').length > 0 ? $('#contabil_editar_pr').val() : null;
	var link_post = '';
	var link_delete = '';
	var frmData = new FormData();

	putDadosPR.classe = classesObjeto.split(' ')[0];
	putDadosPR.campo = objeto.attr("name");
	putDadosPR.valor = putDadosPR.campo == 'path' ? objeto[0].files : objeto.val();
	putDadosPR.cT = putDadosPR.campo == 'path' ? false : 'application/x-www-form-urlencoded';
	putDadosPR.pD = putDadosPR.campo != 'path';
	putDadosPR.dados = null;
	
	if(putDadosPR.campo == 'path'){
		let retorno = verificarArquivos(putDadosPR.valor);
		let msg = 'Os anexos devem ter até 5MB de tamanho!<br>' + 
		' E somente deve anexar até 15 arquivos num anexo!';

		if(!retorno){
			$("#modalLoadingBody").html('<i class="icon fa fa-times text-danger"></i> ' + msg);
			$("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
			setTimeout(function() {
				$("#modalLoadingPreRegistro").modal('hide');
			}, 4000);
			return false;
		}

		for(var i = 0; i < putDadosPR.valor.length; i++)
			frmData.append("valor[]", putDadosPR.valor[i]);
		frmData.append('campo', putDadosPR.campo);
		frmData.append('classe', putDadosPR.classe);
	}

	if((putDadosPR.campo == "") || (putDadosPR.classe == ""))
		return;

	if(putDadosPR.classe == 'Socio-Excluir'){
		putDadosPR.classe = 'pessoaJuridica.socios';
		putDadosPR.campo = 'cpf_cnpj_socio';
	}

	if(putDadosPR.classe == 'Arquivo-Excluir')
		putDadosPR.dados = {
			'_method': 'delete',
			'id': putDadosPR.valor
		};
	else
		putDadosPR.dados = {
			'id_socio': putDadosPR.classe == 'pessoaJuridica.socios' ? $('#form_socio [name="id_socio"]').val() : null,
			'classe': putDadosPR.classe,
			'campo': putDadosPR.campo,
			'valor': putDadosPR.valor
		};

	link_post = contabil_editar_id != null ? '/externo/inserir-registro-ajax/' + contabil_editar_id : '/externo/inserir-registro-ajax';
	link_delete = (contabil_editar_id != null) && (putDadosPR.classe == 'Arquivo-Excluir') ? 
	'/externo/pre-registro-anexo/excluir/' + putDadosPR.dados.id + '/' + contabil_editar_id : '/externo/pre-registro-anexo/excluir/' + putDadosPR.dados.id;

	putDadosPR.data = putDadosPR.campo == 'path' ? frmData : putDadosPR.dados;
	putDadosPR.url = putDadosPR.classe == 'Arquivo-Excluir' ? link_delete : link_post;
}

function acoesAposSucesso(response){
	if(putDadosPR.campo == undefined)
		putDadosPR.campo = '';

	$("#modalLoadingPreRegistro").modal('hide');

	if(['cep', 'logradouro', 'numero', 'complemento', 'cidade', 'uf'].indexOf(putDadosPR.campo) != -1)
		confereEnderecoEmpresa(response['resultado']);
	if(putDadosPR.campo == 'cpf_rt')
		preencheRT(response['resultado']);
	if(putDadosPR.campo == 'cnpj_contabil')
		preencheContabil(response['resultado']);
	else if(putDadosPR.campo.includes('_contabil'))
		preencheCanalContabil(putDadosPR.campo);
	if(putDadosPR.campo == 'path')
		preencheFile(response['resultado']);
	if(putDadosPR.classe == 'Arquivo-Excluir')
		removeFile(response['resultado']);
	if(putDadosPR.classe == 'pessoaJuridica.socios'){
		removeSocio(response['resultado'], $('#form_socio [name="id_socio"]').val());
		preencheSocio(response['resultado'], putDadosPR.campo, putDadosPR.valor);
	}
}

function putDadosPreRegistro(objeto)
{
	let final = popObjetoPutDados(objeto);
	if(final === false)
		return;
		
	$("#modalLoadingBody").html('<i class="spinner-border text-info"></i> Salvando');
	$('#modalLoadingPreRegistro').modal({backdrop: "static", keyboard: false, show: true});

	$.ajax({
		method: 'POST',
		enctype: 'multipart/form-data',
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		data: putDadosPR.data,
		dataType: 'json',
		url: putDadosPR.url,
		processData: putDadosPR.pD,
        contentType: putDadosPR.cT,
		cache: false,
		timeout: 60000,
		success: function(response) {
			acoesAposSucesso(response);
			removerMsgErroServer(objeto);
			$('#atualizacaoPreRegistro').text(response['dt_atualizado']);
			valorPreRegistro = putDadosPR.valor;
			// confereObrigatorios();
		},
		error: function(request, status, error) {
			if(putDadosPR.campo == 'cpf_cnpj_socio')
				$('#mostrar_socios').click();

			var errorFunction = getErrorMsg(request);
			$("#modalLoadingBody").html('<i class="icon fa fa-times text-danger"></i> ' + errorFunction[0]);
			$("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
			setTimeout(function() {
				$("#modalLoadingPreRegistro").modal('hide');
			}, errorFunction[1]); 
			valorPreRegistro = null;
			// console.clear();
		}
	});
}

function removerMsgErroServer(objeto)
{
	var endEmpresa = '.erroPreRegistro[value="cep_empresa"], .erroPreRegistro[value="bairro_empresa"], ';
	endEmpresa += '.erroPreRegistro[value="logradouro_empresa"], .erroPreRegistro[value="numero_empresa"], ';
	endEmpresa += '.erroPreRegistro[value="complemento_empresa"], .erroPreRegistro[value="cidade_empresa"], .erroPreRegistro[value="uf_empresa"]';
	var dadosSocio = putDadosPR.campo.indexOf('_socio') >= 0 ? putDadosPR.dados : null;

	if((putDadosPR.campo.indexOf('_socio') >= 0) && (dadosSocio !== null)){
		if($('.erroPreRegistro[value="' + objeto.attr('name') + '_' + dadosSocio.id_socio + '"]').length > 0)
			$('.erroPreRegistro[value="' + objeto.attr('name') + '_' + dadosSocio.id_socio + '"]').parent().remove();
	}else{
		// remove mensagem de validação do servidor
		if(objeto.next().hasClass('invalid-feedback'))
			objeto.removeClass('is-invalid').next().remove();
		if($('.erroPreRegistro[value="' + objeto.attr('name') + '"]').length > 0)
			$('.erroPreRegistro[value="' + objeto.attr('name') + '"]').parent().remove();
	}

	if(($('.erroPreRegistro').length == 0) && ($('#erroPreRegistro').length == 1))
		$('#erroPreRegistro').remove();
	if(putDadosPR.campo == 'checkEndEmpresa')
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
	if((request.status == 401) || (request.status == 500)){
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

	$('#checkEndEmpresa').prop('checked', boolMesmoEndereco);
	$("#habilitarEndEmpresa").prop('disabled', boolMesmoEndereco);
	boolMesmoEndereco ? $("#habilitarEndEmpresa").hide() : $("#habilitarEndEmpresa").show();
}

function preencheContabil(dados)
{
	if(_.has(dados,"update")){
		var texto = "Somente pode trocar o CNPJ novamente dia: <br>" + dados.update;
		$("#modalLoadingBody").html('<i class="icon fa fa-times text-danger"></i> ' + texto);
		$("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
		setTimeout(function() {
			$("#modalLoadingPreRegistro").modal('hide');
			$('#inserirRegistro input[name="cnpj_contabil"]').val('');
			valorPreRegistro = null;
		}, 2500);
	}else{
		if($('#inserirRegistro input[name="cnpj_contabil"]').val() == ""){
			$('#inserirRegistro [name$="_contabil"]').each(function(){
				$(this).val('');
			});
			preencheCanalContabil();
			$('#campos_contabil').prop("disabled", true);
		}else{
			var desabilita = (dados.aceite != null) && (dados.ativo != null);
			$('#campos_contabil').prop("disabled", desabilita);
			$('#inserirRegistro [name$="_contabil"]').each(function(){
				var name = $(this).attr('name').slice(0, $(this).attr('name').indexOf('_contabil'));
				if(name != 'cnpj')
					$(this).val(dados[name]);
			});
			preencheCanalContabil('cnpj_contabil', dados);
		}
	}
}

function preencheCanalContabil(campo = null, dados = null)
{
	if($('#inserirRegistro input[name="cnpj_contabil"]').val() == ""){
		$('#inserirRegistro #contato-contabil-canal :input').each(function(){
			$(this).val('');
		});
		$('#inserirRegistro #contato-contabil-canal').hide();
		return;
	}

	if(campo == 'cnpj_contabil'){
		$('#inserirRegistro #contato-contabil-canal :input').each(function(){
			var name_id = $(this).attr('id').slice(0, $(this).attr('id').indexOf('-contabil-canal'));
			$(this).val(dados[name_id]);
		});
		$('#inserirRegistro #contato-contabil-canal').show();
		return;
	}

	if((campo != null) && (campo != 'cnpj_contabil')){
		$('#inserirRegistro #contato-contabil-canal :input').each(function(){
			var name_id = $(this).attr('id').slice(0, $(this).attr('id').indexOf('-contabil-canal'));
			if(campo == (name_id + '_contabil'))
				campo == 'email_contabil' ? 
				$(this).val($('#inserirRegistro input[name="' + campo + '"]').val()) : 
				$(this).val($('#inserirRegistro input[name="' + campo + '"]').val().toUpperCase());
		});
		return;
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
			$('#inserirRegistro input[name="cpf_rt"]').val('');
			valorPreRegistro = null;
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
	var dadosAntigos = [$("#logradouro_" + restoId).val(), $("#bairro_" + restoId).val(), $("#cidade_" + restoId).val(), $("#uf_" + restoId).val()];
	var array = [$("#logradouro_" + restoId), $("#bairro_" + restoId), $("#cidade_" + restoId), $("#uf_" + restoId)];
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
	var novoAtivado = ativado;

	if((tipo == 'voltarPreRegistro') && (ativado != 0))
		novoAtivado = ativado - 1;
	else if((tipo == 'avancarPreRegistro') && (ativado != (ordemMenu.length - 1)))
		novoAtivado = ativado + 1;
	
	if(novoAtivado != ativado)
		$('.menu-registro.nav-pills li:eq(' + novoAtivado + ') a').tab('show').focus();

	return novoAtivado;
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
		$('#inserirRegistro input[name="naturalidade_cidade"], #inserirRegistro select[name="naturalidade_estado"]').prop("disabled", (valor != 'Brasileira'));

	if((name == 'tipo_telefone') || (name == 'tipo_telefone_1'))
		$('#inserirRegistro #opcoesCelular' + name.replace('tipo_telefone', '')).prop("disabled", (valor != 'Celular'));
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

function verificarArquivos(arquivos){

	let pode_anexar = true;
	let total = 0;

	if(arquivos.length > 15)
		return false;

	for (const element of arquivos) {
		total += Math.round((element.size / 1024));
		if(total > 5120){
			pode_anexar = false;
			break;
		}
	}

	return pode_anexar;
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

	var ativoAntes = $(this).hasClass('nav-link') ? ordemMenu.indexOf($(this).text().trim()) : ordemMenu.indexOf($('.menu-registro .active').text().trim());
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
	var texto = $(this).parents("[class^='ArquivoBD_']").find('input').val();
	modalExcluirPR(id, texto, "Arquivo", "o anexo");
});

$('#modalExcluir #excluir-geral').click(function(e){
	putDadosPreRegistro($(this));
	$('#modalExcluir').modal('hide');
});

// gerencia os arquivos, cria os inputs, remove os inputs, controla as quantidades de inputs e files vindo do bd
const pre_registro_total_files = $('#totalFilesServer').length ? $('#totalFilesServer').val() : 0;

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
	var nomeClasse = $(this).parents("[class^='Arquivo_']").attr('class');
	var nome = nomeClasse.replace('Arquivo_', '');
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
	var btn = '#criar_socio';

	if(campo == 'checkRT_socio'){
		$('#mostrar_socios').click();
		id_socio = $(this).val().replace(/\D/g, '');
		if((id_socio != '') && ($(this).val().replace('_' + id_socio, '') != 'cpf_cnpj_socio'))
			$('#socio_' + id_socio + ' .acoes_socio button.editar_socio').click();
		if(id_socio != ''){
			campo = $(this).val().replace('_' + id_socio, '');
			btn = 'button[data-target="#socio_' + id_socio + '"]';
		}
	}

	teste = (campo == 'cpf_cnpj_socio') || (campo == 'checkRT_socio') ? btn : '[name="' + campo + '"]';
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

$('#acoes_socio').on('click', '#link-tab-rt', function(){
	$('.menu-registro.nav-pills a[href="#parte_contato_rt"]').tab('show');
});

$('#nacionalidade_socio').change(function(){
	desabilitaNatSocio();
});

$('#link-tab-contabil').click(function(){
	$('.menu-registro.nav-pills a[href="#parte_contabilidade"]').tab('show');
});

//	--------------------------------------------------------------------------------------------------------
// FIM da Funcionalidade Solicitação de Registro (Pré-registro)
