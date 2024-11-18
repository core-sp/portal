"use strict";

$(document).ready(function(){

    import($('#modulo-init').attr('src'))
    .then((init) => {
        init.default();
        init.opcionais();
    })
    .catch((err) => {
        console.log(err);
        alert('Erro na página! Módulo não carregado! Tente novamente mais tarde!');
    });

});

// Funcionalidade Home Imagem / Itens Home

var caminho = '';
var openStorage_id = '';
var folder_name = '';
const PastaItensHomePrincipal = 'img/';

function preencheTabelaPath(value, index, array) {
  var href_path = caminho + value;
  var texto_html = '<div class="card-body text-center pt-0 pl-0 pr-0"><div class="card-img-top"><a href="/' + href_path + '" target="_blank" rel="noopener" data-toggle="lightbox" data-gallery="itens_home_storage"><img src="/' + href_path + '"></a></div><br>';
  texto_html += '<button class="btn btn-link text-break storagePath" value="' + href_path + '">' + value + '</button><br>';
  texto_html += '<hr><a href="/admin/imagens/itens-home/armazenamento/download/' + folder_name + '/' + value + '" class="btn btn-sm btn-primary mr-2"><i class="fas fa-download"></i></a>';
  texto_html += caminho == PastaItensHomePrincipal ? '</div>' : '<button class="btn btn-sm btn-danger deleteFileStorage" type="button" value="' + value + '"><i class="fas fa-trash"></i></button></div>';
  $('#armazenamento #cards').append('<div class="card storageFile w-100 border border-primary"></div>');
  $('#armazenamento #cards .storageFile:last').append(texto_html);
}

$(document).ready(function(){
  $("#filtrarFile").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#cards .card .storagePath").filter(function() {
      $(this).parent().parent().toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});

$('.openStorage').click(function(){
  openStorage_id = $(this).parent().parent().find('input').attr('id');
  receberArquivos(openStorage_id, null);
});

$('.openStoragePasta').click(function(){
  var pasta = this.value == "" ? null : this.value;
  $('.openStoragePasta').attr('disabled', false);
  $(this).attr('disabled', true);
  receberArquivos(openStorage_id, pasta);
});

$("#armazenamento").on('hidden.bs.modal', function(){
  $('#armazenamento #msgStorage').hide();
  $("#filtrarFile").val('');
  cleanTabelaStorage();
});

$("#armazenamento").on('shown.bs.modal', function () {
  $('.openStoragePasta[value=""]').attr('disabled', true);
  $('.openStoragePasta[value!=""]').attr('disabled', false);
});

$("#header_fundo_cor").change(function() {
  $('#header_fundo').val('');
  $('#header_fundo').attr('placeholder', 'Cor selecionada');
});

$("#header_fundo_cor").ready(function() {
  if($("#header_fundo_default").prop('checked') || ($("#header_fundo").val() != ""))
    return;
  $('#header_fundo').val('');
  $('#header_fundo').attr('placeholder', 'Cor selecionada');
});

$("#header_fundo_default").change(function() {
  if(!this.checked){
    $('#header_fundo').attr('placeholder', '');
  }else{
    $('#header_fundo').val('');
    $('#header_fundo').attr('placeholder', 'Imagem padrão escolhida');
  }
});

$('#popup_video_vazio, #popup_video_default').change(function() {
  if(this.checked)
    $('#popup_video_novo').val('');
});

$('#armazenamento #file_itens_home').change(function(e){
  if($(this).val() == '')
    return;

  var form = new FormData();
  form.append('_method', "POST");
  form.append('_token', $('meta[name="csrf-token"]').attr('content'));
  form.append('file_itens_home', e.target.files[0]);

  $.ajax({
    method: "POST",
    data: form,
    contentType : false,
		processData : false,
    url: "/admin/imagens/itens-home/armazenamento",
    success: function(response) {
      if(response.novo_arquivo != null){
        $('#armazenamento .custom-file-label').text('Selecionar arquivo...');
        receberArquivos(openStorage_id, null);
        $('#armazenamento #msgStorage').removeClass('alert-danger')
        .addClass('alert-success').html('Arquivo <strong><i>"' + response.novo_arquivo + '"</i></strong> foi adicionado da pasta!').show();
        $('.openStoragePasta[value=""]').attr('disabled', true);
        $('.openStoragePasta[value!=""]').attr('disabled', false);
      }
    },
    error: function(xhr) {
      var txt = xhr.status == 422 ? xhr.responseJSON.errors.file_itens_home[0] : 'Erro ao adicionar o arquivo. Recarregue a página.';
      $('#armazenamento #msgStorage').removeClass('alert-success')
      .addClass('alert-danger').html(txt).show();
      $('#armazenamento .custom-file-label').text('Selecionar arquivo...');
    }
  });
});

function cleanTabelaStorage()
{
  $('#armazenamento .card-columns .card').remove();
}

function receberArquivos(id, pasta){
  $.ajax({
    method: "GET",
    data: {},
    dataType: 'json',
    url: pasta == null ? "/admin/imagens/itens-home/armazenamento" : "/admin/imagens/itens-home/armazenamento/" + pasta,
    success: function(response) {
      cleanTabelaStorage();
      caminho = response.caminho;
      folder_name = response.folder;
      response.path.forEach(preencheTabelaPath);
      eventClickSelecionar(id);
      eventClickExcluir();
    },
    error: function() {
      alert('Erro ao carregar os arquivos. Recarregue a página.');
    }
  });
}

function eventClickSelecionar(id){
  $('.storagePath').on('click', function(){
    var linha = this.value;
    $('#' + id).val(linha);
    $("#armazenamento").modal("hide");
  });
}

function eventClickExcluir(){
  if(caminho == PastaItensHomePrincipal)
    return;
  $('.deleteFileStorage').on('click', function(){
    $('#confirmDelete #confirmFile').text(this.value);
    $('#confirmDelete #deleteFileStorage').val(this.value);
    $('#confirmDelete').modal({backdrop: 'static', keyboard: false, show: true});
  });
}

$('#deleteFileStorage').on('click', function(){
  var arquivo = this.value;
  $('#confirmDelete').modal('hide');
  $.ajax({
    method: "POST",
    data: {
      _method: "DELETE",
      _token: $('meta[name="csrf-token"]').attr('content'),
    },
    dataType: 'json',
    url: "/admin/imagens/itens-home/armazenamento/delete-file/" + arquivo,
    success: function(response) {
      if(response != 'Não foi removido.'){
        $('.deleteFileStorage[value="' + arquivo + '"]').parent().parent().remove();
        $('#armazenamento #msgStorage').removeClass('alert-danger')
        .addClass('alert-success').html('Arquivo <strong><i>"' + arquivo + '"</i></strong> foi removido da pasta!').show();
      }else{
        $('#armazenamento #msgStorage').removeClass('alert-success')
        .addClass('alert-danger').html('Arquivo <strong><i>"' + arquivo + '"</i></strong> NÃO foi removido da pasta!').show();
      }
      $('#armazenamento .modal-body').scrollTop(0);
    },
    error: function() {
      $('#armazenamento #msgStorage').removeClass('alert-success')
        .addClass('alert-danger').html('Erro ao excluir o arquivo <strong><i>"' + arquivo + '"</i></strong>. Recarregue a página.').show();
        $('#armazenamento .modal-body').scrollTop(0);
    }
  });
});

// FIM Funcionalidade Home Imagem / Itens Home

// Funcionalidade GerarTexto ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

function crudGerarTexto(acao, objeto){

  $('#avisoTextos').modal('hide');
  var token = $('meta[name="csrf-token"]').attr('content');
  var id = $(objeto).val();
  var textoLoading = '';
  var link = '';
  var metodo = '';
  var dados = '';

  switch(acao) {
    case 'carregar':
      textoLoading = '<div class="spinner-border text-primary"></div>&nbsp;&nbsp;Carregando texto...';
      link = '/admin/textos/' + $('#tipo_doc').val() + '/' + id;
      metodo = 'GET';
      dados = {};
      break;
    case 'atualizar':
      textoLoading = '<div class="spinner-border text-primary"></div>&nbsp;&nbsp;Atualizando campos...';
      link = '/admin/textos/' + $('#tipo_doc').val() + '/' + id;
      metodo = 'POST';
      dados = {
        _token: token,
        tipo: $('#tipo').val(),
        texto_tipo: $('#texto_tipo').val(),
        com_numeracao: $('#com_numeracao').val(),
        nivel: $('#nivel').val(),
        conteudo: tinymce.get('conteudo').getContent(),
      };
      break;
    case 'excluir_varios':
      textoLoading = '<div class="spinner-border text-primary"></div>&nbsp;&nbsp;Excluindo textos...';
      link = '/admin/textos/' + $('#tipo_doc').val() + '/excluir';
      metodo = 'DELETE';
      dados = {
        _token: token,
        excluir_ids: objeto.val()
      };
      break;
  }

	$.ajax({
      url: link,
      method: metodo,
      dataType: "json",
      data: dados,
      beforeSend: function(){
        $('#loadingIndice').modal({backdrop: 'static', keyboard: false, show: true});
        $('#loadingIndice .modal-body').html(textoLoading);
      },
      complete: function(){
        $('#loadingIndice').modal('hide');
      },
      success: function(response) {
        $('.updateCampos').val(id);
        $('.deleteTexto').val(id);
        atualizarTextoCrud(acao, response);
        gerarTextoAvisosCrud(acao, response, id);
      },
      error: function(erro, textStatus, errorThrown) {
        var resposta = erro.status == 422 ? JSON.stringify(erro.responseJSON.errors) : 
        'Código: ' + erro.status + ' | Mensagem: ' + erro.responseJSON.message;
        gerarTextoAvisosCrud('erro', resposta, null);
      }
  });
}

function atualizarTextoCrud(acao, response){
  var texto_campo = acao == 'carregar' ? response[0].tipo : response.tipo;
  var cor = texto_campo == 'Título' ? 'warning' : 'dark';
  var upper = texto_campo == 'Título' ? 'text-uppercase' : '';

  if(acao == 'carregar'){
    var resultado = response[0];
    var indice = resultado.indice != null ? resultado.indice : '';
    $('#span-tipo').attr('class', 'text-' + cor).text(texto_campo);
    $('#span-nivel').text(resultado.nivel);
    $('#span-texto_tipo').attr('class', upper).text(indice + ' - ' + resultado.texto_tipo);
    $('#texto_tipo').val(resultado.texto_tipo);
    $('#tipo option[value="' + resultado.tipo + '"]').prop('selected', true);
    $('#com_numeracao option[value="' + resultado.com_numeracao + '"]').prop('selected', true);
    $('#nivel option[value="' + resultado.nivel + '"]').prop('selected', true);
    resultado.conteudo != null ? tinymce.activeEditor.setContent(resultado.conteudo) : tinymce.activeEditor.setContent('');
    hideShowOptions();
  }

  if(acao == 'atualizar'){
    $('#span-tipo').attr('class', 'text-' + cor).text(texto_campo);
    $('#span-nivel').text(response.nivel);
    $('#span-texto_tipo').attr('class', upper).text(response.texto_tipo);
    $('button[value="' + response.id + '"] .indice-texto').text(response.texto_tipo);
  }
  return;
}

function gerarTextoAvisosCrud(acao, response, valor){
  var texto = '';
  var title = acao == 'erro' ? '<i class="fas fa-times" style="color: #e70d0d;"></i> Erro!' : 
  '<i class="fas fa-check-circle" style="color: #40c011;"></i> Sucesso!';

  switch (acao) {
    case 'excluir_varios':
      texto = 'Exclusão realizada com sucesso!';
      break;
    case 'atualizar':
      texto = 'Campos do texto foram atualizados!';
      response = 1;
      break;
    case 'erro':
      texto = response;
      break;
  }

  if((acao == 'excluir_varios') && (response != null) && (typeof response == 'object')){
    response.forEach(function(id, i){
      $('button[value="' + id + '"]').parents('.form-check').remove();
    });
    $('#lista').hide();
    selecionarTodos(false);
    response = 1;
  }

  if((acao == 'erro') || (response == 1)){
    $('#avisoTextos').modal({backdrop: 'static', keyboard: false, show: true});
    $('#avisoTextos .modal-title').html(title);
		$('#avisoTextos .modal-body').html(texto);
		$('#avisoTextos .modal-footer').hide();
    if(response == 1)
      setTimeout(function(){
        $('#avisoTextos').modal('hide');
      }, 1500);
    return;
  }

  if((acao == 'excluir_varios') && (response == null)){
    var textos_ids = '';
    var valor_final = JSON.parse(valor);
    var total = valor_final.length;
    var text = total > 1 ? 'todos estes textos selecionados' : 'este texto';

    valor_final.forEach(function(id, i){
      textos_ids += '<strong>Texto: </strong><i>' + $('button[value="' + id + '"]').text() + '</i><br>';
    });

    $('#avisoTextos').modal({backdrop: 'static', keyboard: false, show: true});
    $('#avisoTextos .modal-title')
      .html('<i class="fas fa-trash" style="color: #dc0909;"></i> Excluir');
		$('#avisoTextos .modal-body')
			.html(textos_ids + 'Tem certeza que deseja excluir ' + text + '?<br>Esta ação não é reversível!');
    $('#avisoTextos .modal-footer #excluirTexto').val(valor_final);
		$('#avisoTextos .modal-footer').show();
    return;
  }
}

function hideShowOptions(){
  if($(".textoTipo").val() == 'Título'){
    $('#nivel option').hide();
    $('#nivel option').each(function(){
      if($(this).val() == '0')
        $(this).show();
    });
    $('#nivel')[0].selectedIndex = 0;
    $('#com_numeracao option').hide();
    $('#com_numeracao option').each(function(){
        $(this).show();
    });
    if(!$('#texto_tipo').hasClass('text-uppercase'))
      $('#texto_tipo').addClass('text-uppercase');
  }else{
    $('#nivel option').show();
    $('#nivel option').each(function(){
      if($(this).val() == '0')
        $(this).hide();
    });
    $('#nivel')[0].selectedIndex = 1;
    $('#com_numeracao option').each(function(){
      if($(this).val() == '0')
        $(this).hide();
    });
    $('#com_numeracao')[0].selectedIndex = 0;
    if($('#texto_tipo').hasClass('text-uppercase'))
      $('#texto_tipo').removeClass('text-uppercase');
  }
}

function selecionarTodos(inverso){
  var texto, quadrado;

  if(!inverso){
    texto = $('[name="excluir_ids"]:checked').length == 0 ? 'Selecionar Todos' : 'Limpar Seleção';
    quadrado = $('[name="excluir_ids"]:checked').length == 0 ? '<i class="fas fa-check-square"></i>' : '<i class="fas fa-square"></i>';
  }else{
    texto = $('[name="excluir_ids"]:checked').length == 0 ? 'Limpar Seleção' : 'Selecionar Todos';
    quadrado = $('[name="excluir_ids"]:checked').length == 0 ? '<i class="fas fa-square"></i>' : '<i class="fas fa-check-square"></i>';
  }
  
  $('.selecionarTextos').html(quadrado + '&nbsp;&nbsp;' + texto);
}

$(".criarTexto").click(function(){
	var token = $('meta[name="csrf-token"]').attr('content');
	var link = '/admin/textos/' + $('#tipo_doc').val();
	var form = $('<form action="' + link + '" method="POST"><input type="hidden" name="_token" value="' + token + '"></form>');
	$('body').append(form);
	$(form).submit();
});

$("#publicarTexto").click(function(){
  var token = $('meta[name="csrf-token"]').attr('content');
	var link = '/admin/textos/publicar/' + $('#tipo_doc').val();
	var form = $('<form action="' + link + '" method="POST"><input type="hidden" name="_token" value="' + token + '"><input type="hidden" name="publicar" value="' + $(this).val() + '"></form>');
	$('body').append(form);
	$(form).submit();
});

$(".updateCampos").click(function(){
	crudGerarTexto('atualizar', $(this));
});

$(".deleteTexto").click(function(){
  if($(".deleteTexto").length > 0)
    gerarTextoAvisosCrud('excluir_varios', null, JSON.stringify([$(this).val()]));
});

$(".excluirTextos").click(function(){
  var excluirIds = [];
  if($('[name="excluir_ids"]:checked').length > 0){
    $('[name="excluir_ids"]:checked').each(function(){
      excluirIds.push($(this).val());
    });
    gerarTextoAvisosCrud('excluir_varios', null, JSON.stringify(excluirIds));
  }
});

$("#excluirTexto").click(function(){
  crudGerarTexto('excluir_varios', $(this));
});

$(".textoTipo").change(function(){
  hideShowOptions();
});

$("#updateIndice").click(function(){
  $('#loadingIndice').modal({backdrop: 'static', keyboard: false, show: true});
  $('#loadingIndice .modal-body').html('<div class="spinner-border text-primary"></div>&nbsp;&nbsp;Atualizando a índice...');
});

// link no sumário para abrir e ir no texto
$('button.abrir').click(function(){
  crudGerarTexto('carregar', $(this));
  $('#lista').hide();
  $('#lista').show();
  $('#tipo').focus();
});

$('#formGerarTexto').ready(function(){
  if($('button.abrir .badge').length > 0)
    $('button.abrir .badge').click();
});

$('[name="excluir_ids"]').change(function(){
  selecionarTodos(false);
});

$('.selecionarTextos').click(function(){
  selecionarTodos(true);
  var selecionados = $('[name="excluir_ids"]:checked').length > 0 ? false : true;
  $('[name="excluir_ids"]').prop('checked', selecionados);
  $('[name="excluir_ids"]:first').prop('checked', false);
});

// FIM da Funcionalidade GerarTexto ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++