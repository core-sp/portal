const txt_descricao = '<h6 class="mb-2"><strong>Exemplo:</strong></h6>* Possuir carro;<br>* Possuir Empresa;<br>' + 
'* Preferencialmente ter experiência no segmento do produto / serviço;<br>* Conhecer a região que irá atuar;<br>' + 
'* Preferencialmente possuir carteira ativa de clientes;';
const txt_endereco = '<h6 class="mb-2">Busque pelo CEP e complete o endereço. <strong>Exemplo:</strong></h6>Av. Brigadeiro Luís Antônio, 613 - 5º andar - Centro - São Paulo - SP';
let cnpj_temp = '';

function validarCnpj(cnpj){
    let erro = '';

    if(cnpj.length != 18)
        erro = 'CNPJ com tamanho inválido';

    if(cnpj_temp === cnpj)
        erro = 'CNPJ já foi verificado';

    if(erro.length > 0){
        document.dispatchEvent(new CustomEvent("BDO_ERRO", {
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
            document.dispatchEvent(new CustomEvent("MSG_GERAL_CARREGAR"));
		},
		success: function(data){
            document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
            $('#avAlert').html('').hide();

            if(data.length == 0){
                document.dispatchEvent(new CustomEvent("BDO_SEM_EMPRESA", {
                    detail: 'Empresa não cadastrada. Favor informar os dados da empresa abaixo.',
                }));
                return;
            }

            document.dispatchEvent(new CustomEvent("BDO_COM_EMPRESA", {
                detail: {
                    empresa: JSON.parse(data.empresa),
                    class: data.class,
                    message: data.message
                }
            }));
		},
		error: function(erro){
            document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
            $('#avAlert').html('').hide();

            document.dispatchEvent(new CustomEvent("BDO_ERRO", {
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
        .removeClass('alert-warning alert-success').addClass('alert-info')
        .text(e.detail);
        $('#cep').val('');
    });

    $(document).on('BDO_COM_EMPRESA', function(e){
        $('.avHidden').hide();
		$('#av10').val(e.detail.empresa);
		$('#av01, #avEmail').val('');
		$('#titulice').focus();
		$('#avAlert').show()
        .removeClass('alert-info alert-warning alert-success').addClass(e.detail.class)
        .html(e.detail.message);
        $('#cep, [name="endereco"]').val('');
    });

    $(document).on('BDO_ERRO', function(e){
        $('#cep, [name="endereco"]').val('');
        document.dispatchEvent(new CustomEvent("MSG_GERAL_CONT_TITULO", {
            detail: {
                titulo: '<i class="fas fa-times text-danger"></i> Erro!', 
                texto: '<span class="text-danger">' + e.detail + '</span>'
            }
        }));
    });

    if(($('#cnpj').length > 0) && ($('#cnpj').val().length === 18)){
        let cnpj = $('#cnpj').val();

        let resultado = validarCnpj(cnpj);
        if(typeof resultado !== "boolean")
            getInfoEmpresa(cnpj);
    }

    $('#cnpj').on('input', function(e){
        let cnpj = $(this).masked($(this).val());
        let dado = e.originalEvent.data;

        if((dado !== null) && (dado.length == 1) && (dado.search(/[^0-9]/) > -1))
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

    $("#cep").on('CEP', function(e){
        if(e.detail == 'encontrado')
            $('[name="endereco"]')
            .val($('#rua').val() + ', nº ... - Complemento ... - ' + $('#bairro').val() + ' - ' + $('#cidade').val() + ' - ' + $('#uf').val());
    });
}

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
