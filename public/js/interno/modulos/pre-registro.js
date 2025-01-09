const mimetype_aceito = ['application/pdf'];

async function validarArquivo(arquivo, mimeTypes = []){

    const link = $('#modulo-validar-arquivos').attr('src');

    try {
        const module = await import(link);
        console.log('[MÓDULOS] # Módulo validar-arquivos importado por opcional e carregado.');
        console.log('[MÓDULOS] # Local do módulo: ' + link);
        return module.validarUmArquivo(arquivo, mimeTypes);
    } catch (err) {
        console.log(err);
        alert('Erro na página! Módulo com erro! Tente novamente mais tarde!');
    }
}

function msgArquivo(retorno){
    
    if(typeof retorno === 'string'){
        $("#modalLoadingBody").html('<i class="icon fa fa-times text-danger"></i> ' + retorno);
        $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
        setTimeout(function() {
            $("#modalLoadingPreRegistro").modal('hide');
        }, 3000);
        return false;
    }
  
    return true;
}

function contJustificativaPR(obj){

    let total = 500 - obj.val().length;

    if(total == -1)
        return;

    $('#contChar').text(total);
}

function chamarModalJustificativa(campo = ''){

    let texto = campo == '' ? '' : $('#' + campo + ' span.valorJustificativaPR').text();
    let input = $('#modalJustificativaPreRegistro .modal-body textarea');
    let titleModal = texto.length > 0 ? ' Editar justificativa' : ' Adicionar justificativa';

    input.val(texto);
    contJustificativaPR(input);
    $('#submitJustificativaPreRegistro').val(campo);
    $('#submitJustificativaPreRegistro').show();
    $('#modalJustificativaPreRegistro .modal-title #titulo').text(titleModal);
    $('#modalJustificativaPreRegistro').modal({backdrop: "static", keyboard: false, show: true});
}

function confereAnexos(){

    let aprovado = $('.confirmaAnexoPreRegistro:checked').length == $('.confirmaAnexoPreRegistro').length;

    if(!aprovado){
        aprovado = true;
        $('.confirmaAnexoPreRegistro:not(:checked)').each(function() {
            if(!$(this).hasClass('opcional'))
                aprovado = false;
        });
    }

    return aprovado;
}

function habilitarBotoesStatus(justificado){

    justificado = justificado > 0;
    let tipo_btn = justificado ? 'submit' : 'button';

    $('#submitNegarPR, #submitAprovarPR').prop("disabled", justificado);
    $('#submitAprovarPR').attr('type', 'button');
    $('#submitCorrigirPR').prop('disabled', !justificado).attr('type', tipo_btn);
}

function verificaJustificados(){

    $('#accordionPreRegistro .tab-pane').each(function() {
        let menu = $('[href="#' + $(this).attr('id') + '"]').parent();
        let possui_just = $(this).find('.just').length > 0;

        if(possui_just && (menu.find('.justMenu').length == 0))
            menu.append('<i class="fas fa-circle text-warning justMenu"></i>');

        if(!possui_just)
            menu.find('.justMenu').remove();
    });

    let justificado = $('.menuPR .justMenu').length;
    let liberado = confereAnexos() && (justificado == 0);
    let btn = liberado ? 'submit' : 'button';

    habilitarBotoesStatus(justificado);
    $('#submitAprovarPR').prop('disabled', !liberado).attr('type', btn);
}

function htmlJustificativa(campo, valor){

    $('#' + campo + ' span.valorJustificativaPR').text(valor);

    let possui_just = (valor != "") || ($('#' + campo + ' .valorJustificativaPR').text().length > 0);
    let add_cor_btn = possui_just ? 'btn-outline-danger' : 'btn-outline-success';
    let remove_cor_btn = possui_just ? 'btn-outline-success' : 'btn-outline-danger';
    let icone_btn = possui_just ? '<i class="fas fa-edit"></i>' : '<i class="fas fa-user-edit"></i>';

    $('#' + campo + ' button.justificativaPreRegistro').removeClass(remove_cor_btn).addClass(add_cor_btn);
    $('#' + campo + ' button.justificativaPreRegistro').html(icone_btn);

    if(!possui_just)
        $('#' + campo + ' .just').remove();

    if(possui_just && $('#' + campo + ' .just').length == 0) 
        $('#' + campo + ' .justificativaPreRegistro').after('<span class="badge badge-warning just ml-2">Justificado</span>');
}

function validarAntesDeEnviar(id){

    let texto = "";
    let possui_reg_rt = ($('[name="registro"]').length > 0) && ($('[name="registro"]').val().length > 5);

    if((id == 'submitAprovarPR') && !possui_reg_rt)
        texto = "Falta salvar o registro do Responsável Técnico";

    if(texto.length > 0){
        $("#modalLoadingBody").html('<i class="icon fa fa-times text-danger"></i> ' + texto);
        $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
        setTimeout(function() {
            $("#modalLoadingPreRegistro").modal('hide');
        }, 3500);
    }

    return texto.length == 0;
}

function msgError(request){

    let time = 2000;
    let errorMessage = request.statusText;

    switch (request.status) {
        case 422:
            for(let nome of ['campo', 'valor']){
                let msg = request.responseJSON.errors[nome];
                if(msg != undefined)
                    errorMessage = msg[0];
            }
            break;
        case 401:
            errorMessage = request.responseJSON.message;
            break;
        case 419:
            errorMessage = "Sua sessão expirou! Recarregue a página";
            break;
        case 429:
            errorMessage = "Excedeu o limite de requisições por minuto.<br>Aguarde " + request.getResponseHeader('Retry-After') + " segundos";
            time = 2500;
            break;
        default:
            time = 5000;
            break;
    }

    $("#modalLoadingBody").html('<i class="icon fa fa-times text-danger"></i> Erro ' + request.status + ': ' + errorMessage);
    $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
    setTimeout(function() {
        $("#modalLoadingPreRegistro").modal('hide');
    }, time);
}

function requestJustHist(link, texto){
    $.ajax({
        method: 'GET',
        dataType: 'json',
        url: link,
        cache: false,
        timeout: 60000,
        success: function(response) {
            $("#modalLoadingPreRegistro").modal('hide');
            $('#modalJustificativaPreRegistro #submitJustificativaPreRegistro').hide();
            $('#modalJustificativaPreRegistro .modal-title #titulo')
            .html('Histórico da Justificativa <strong>' + texto + ' do dia ' + response.data_hora + '</strong>');
            $('#modalJustificativaPreRegistro .modal-body textarea').val(response.justificativa);
            contJustificativaPR($('#modalJustificativaPreRegistro .modal-body textarea'));
            $('#modalJustificativaPreRegistro').modal({backdrop: "static", keyboard: false, show: true});
        },
        error: function(request, status, error) {
            msgError(request);
        }
    });
}

function updatePreRegistro(campo, valor, acao){

    $('#modalJustificativaPreRegistro').modal('hide');
    $("#modalLoadingBody").html('<i class="spinner-border text-info"></i> Salvando');
	$('#modalLoadingPreRegistro').modal({backdrop: "static", keyboard: false, show: true});

    $.ajax({
        method: 'POST',
        data: {
            '_token': $('meta[name="csrf-token"]').attr('content'),
            'acao': acao,
            'campo': campo,
            'valor': valor
        },
        dataType: 'json',
        url: '/admin/pre-registros/update-ajax/' + $('[name="idPreRegistro"]').val(),
        cache: false,
        timeout: 60000,
        success: function(response) {
            $("#modalLoadingPreRegistro").modal('hide');
            if(campo == 'negado')
                acao = campo;

            document.dispatchEvent(new CustomEvent(acao.toUpperCase(), {
                detail: {
                    _campo: campo,
                    _valor: valor,
                }
            }));

            $('#userPreRegistro').text(response['user']);
            $('#atualizacaoPreRegistro').text(response['atualizacao']);
            verificaJustificados();
        },
        error: function(request, status, error) {
            msgError(request);
        }
    });
}

function editar(){

    $(document).on('NEGADO', function(){
        $('#submitNegarPR').parents('form').submit();
        $("#modalLoadingBody").html('<span class="spinner-border text-danger mr-3"></span> Enviando...');
        $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
    });

    $(document).on('JUSTIFICAR', function(e){
        htmlJustificativa(e.detail._campo, e.detail._valor);
    });

    $(document).on('EXCLUSAO_MASSA', function(e){
        e.detail._valor.forEach(function(value, index, array){
            htmlJustificativa(value, '');
        });
    });

    $(document).on('EDITAR', function(){
        $("#modalLoadingBody").html('<i class="icon fa fa-check text-success"></i> Salvo');
        $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
        setTimeout(function() {
            $("#modalLoadingPreRegistro").modal('hide');
        }, 1200); 
    });

    $("#modalJustificativaPreRegistro").on('show.bs.modal', function () {
        let btn = $(this).find('#submitJustificativaPreRegistro');
        let somente_leitura = btn.length == 0 ? true : window.getComputedStyle(btn[0]).display == 'none';

        $(this).find('textarea').prop('readonly', somente_leitura);
    });

    $('button.hide_menu').click(function(){
        let ativo = $(this).parent().find('.active');

        if(ativo.length > 0){
            $(ativo.attr('href')).removeClass('active');
            ativo.removeClass('active');
        }
    });

    if($('#accordionPreRegistro').length > 0)
        verificaJustificados();

    $('.link-tab-rt').click(function(){
        $('#accordionPreRegistro .nav-pills a[href="#parte_contato_rt"]').tab('show');
    });

    $('#doc_pre_registro').on('change',function(e){
        validarArquivo(e.target.files[0], mimetype_aceito)
        .then(function(retorno) {
            msgArquivo(retorno);
        });
    });

    $('#form-anexo-docs').click(function(){
        validarArquivo($('#doc_pre_registro')[0].files[0], mimetype_aceito)
        .then(function(retorno) {

            let pode_enviar = msgArquivo(retorno);
            let possui_tipo_doc = $('[name="tipo"]:checked').length > 0;

            if(!possui_tipo_doc)
                msgArquivo('Deve selecionar o tipo de documento!');

            if((pode_enviar === true) && possui_tipo_doc)
                $('#form-anexo-docs').parents('form').submit();
        });
    });

    $('#submitNegarPR').click(function() {
        chamarModalJustificativa('negado');
    });

    $('.justificativaPreRegistro').click(function() {
        chamarModalJustificativa(this.value);
    });

    $('#modalJustificativaPreRegistro .modal-body textarea').keyup(function() {
        contJustificativaPR($(this));
    });

    $('.addValorPreRegistro').click(function() {
        let valor = $(this).parent().find('input').val();
        updatePreRegistro(this.value, valor, 'editar');
    });

    $('.confirmaAnexoPreRegistro').change(function() {
        updatePreRegistro(this.name, this.value, 'conferir');
    });

    $('#submitJustificativaPreRegistro').click(function() {
        let valor = $(this).parents('#modalJustificativaPreRegistro').find('textarea').val().trim();

        if((valor.length == 0) && ($('#' + this.value + ' .just').length == 0))
            return false;

        if(valor == $('#' + this.value + ' .valorJustificativaPR').text())
            return false;

        updatePreRegistro(this.value, valor, 'justificar', this);
    });

    $('.remove_todas_justificativas').click(function() {
        const campos_array = [];

        $('#' + this.value + ' .justificativaPreRegistro').each(function(){
            if($(this).next().hasClass('just'))
                campos_array.push($(this).val());
        });
        
        if(campos_array.length > 0)
            updatePreRegistro('exclusao_massa', campos_array, 'exclusao_massa');
    });

    $('#submitAprovarPR, #submitCorrigirPR').click(function(e){
        let cor = this.id == 'submitAprovarPR' ? 'success' : 'warning';

        if(!validarAntesDeEnviar(this.id)){
            e.preventDefault();
            return false;
        }

        $("#modalLoadingBody").html('<span class="spinner-border text-' + cor + ' mr-3"></span> Enviando...');
        $("#modalLoadingPreRegistro").modal({backdrop: "static", keyboard: false, show: true});
    });

    $('.textoJustHist').click(function() {
        $('#modalJustificativaPreRegistro').modal('hide');
        $("#modalLoadingBody").html('<i class="spinner-border text-info"></i> Carregando');
        $('#modalLoadingPreRegistro').modal({backdrop: "static", keyboard: false, show: true});
        requestJustHist(this.value, this.innerText);
    });
};

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}

export let scripts_para_importar = {
    modulo: ['validar-arquivos'], 
    local: ['modulos/']
};
