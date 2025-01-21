const txt_descricao = '<h6 class="mb-2"><strong>Exemplo:</strong></h6>* Possuir carro;<br>* Possuir Empresa;<br>' + 
'* Preferencialmente ter experiência no segmento do produto / serviço;<br>* Conhecer a região que irá atuar;<br>' + 
'* Preferencialmente possuir carteira ativa de clientes;';
const txt_endereco = '<h6 class="mb-2"><strong>Exemplo:</strong></h6>Av. Brigadeiro Luís Antônio, 613 - 5º andar - Centro - São Paulo - SP';
let cnpj_temp = '';

function validarCnpj(cnpj){
    let erro = '';

    if(cnpj.length != 18)
        erro = 'CNPJ com tamanho inválido';

    if(cnpj_temp === cnpj)
        erro = 'CNPJ já foi verificado';

    if(erro.length > 0){
        $(document)[0].dispatchEvent(new CustomEvent("BDO_ERRO", {
            detail: erro
        }));
        return false;
    }

    cnpj_temp = cnpj;
    return cnpj;
}

function getInfoEmpresa(value){

	$.ajax({
		type: 'GET',
		url: '/info-empresa/' + encodeURIComponent(value.replace(/[^\d]+/g,'')),
		beforeSend: function(){
            $("#msgGeral .modal-header, #msgGeral .modal-footer").hide();
            $("#msgGeral .modal-body").addClass('text-center').html('<div class="spinner-grow text-info"></div>');
            $("#msgGeral").modal({backdrop: "static", keyboard: false, show: true});
		},
		success: function(data){
            $("#msgGeral").modal('hide');
            $('#avAlert').html('').hide();

            if(data.length == 0){
                $(document)[0].dispatchEvent(new CustomEvent("BDO_SEM_EMPRESA", {
                    detail: 'Empresa não cadastrada. Favor informar os dados da empresa abaixo.',
                }));
                return;
            }

            $(document)[0].dispatchEvent(new CustomEvent("BDO_COM_EMPRESA", {
                detail: {
                    empresa: JSON.parse(data.empresa),
                    class: data.class,
                    message: data.message
                }
            }));
		},
		error: function(erro){
            $("#msgGeral").modal('hide');
            $('#avAlert').html('').hide();

            $(document)[0].dispatchEvent(new CustomEvent("BDO_ERRO", {
                detail: erro.status == 422 ? erro.responseJSON.errors.cnpj : erro.responseJSON.message
            }));
		}
	});
}

function editar(){
    
    $(document).on('BDO_SEM_EMPRESA', function(e){
        $('.avHidden').css('display', 'flex');
        $('#av10').val('0');
        $('#av01').focus();
        $('#avAlert').show()
        .removeClass('alert-info alert-success').addClass('alert-info')
        .text(e.detail);
    });

    $(document).on('BDO_COM_EMPRESA', function(e){
        $('.avHidden').hide();
		$('#av10').val(e.detail.empresa);
		$('#av01, #avEmail').val('');
		$('#titulice').focus();
		$('#avAlert').show()
        .removeClass('alert-info alert-warning').addClass(e.detail.class)
        .html(e.detail.message);
    });

    $(document).on('BDO_ERRO', function(e){
        $("#msgGeral .modal-header").show();
        $("#msgGeral .modal-footer").hide();
        $("#msgGeral .modal-body").addClass('text-center text-danger').html(e.detail);
        $("#msgGeral").modal({backdrop: "static", keyboard: false, show: true});
    });

    if(($('#cnpj').length > 0) && ($('#cnpj').val().length === 18)){
        let cnpj = $('#cnpj').val();

        let resultado = validarCnpj(cnpj);
        if(typeof resultado !== "boolean")
            getInfoEmpresa(cnpj);
    }

    $('#cnpj').on('keyup', function(e){
        let cnpj = $(this).val();

        if((e.key !== undefined) && (e.key.search(/[^0-9]/) > -1))
            return;

        if(cnpj.length !== 18)
            return;

        let resultado = validarCnpj(cnpj);
        if(typeof resultado !== "boolean")
            getInfoEmpresa(cnpj);
	});

	$('#descricao-da-oportunidade, #endereco-da-empresa').on("mouseover", function() {
        let txt = this.id == 'descricao-da-oportunidade' ? txt_descricao : txt_endereco;

		$(this).tooltip({
			items: "#" + this.id,
			content: txt
		});
		$(this).tooltip("open");
	});

    $('#descricao-da-oportunidade, #endereco-da-empresa').on("mouseout", function() {
		$(this).tooltip("disable");
	});

	$('#avSegmentoOp').on('change', function(){
		$(this).val() == 'Outro' ? $('#outroSegmento').show() : $('#outroSegmento').hide();
	});
}

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
