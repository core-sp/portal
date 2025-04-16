const cpf_text = '000.000.000-00';
const cnpj_text = '00.000.000/0000-00';
const dt_text = '00/00/0000';
const hora_text = '00:00';
const fone_4_text = '(00) 0000-0000';
const fone_5_text = '(00) 00000-0000';

function gerentiContato(conteudo, id){

	conteudo.attr('type', 'text');
	switch (id) {
		case '1':
		case '4':
		case '6':
		case '7':
		case '8':
			conteudo.mask(fone_4_text);
			break;
		case '2':
			conteudo.mask(fone_5_text);
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

function mascaraRG(rg){

    rg = rg.replace(/[^a-zA-Z0-9]/g, '');
    
    let dv = '-' + rg.slice(rg.length - 1, rg.length);
    let rgSemDV = rg.slice(0, rg.length - 1);
    let rgFinal = dv;

    while(rgSemDV.length > 3){
        rgFinal = '.' + rgSemDV.slice(rgSemDV.length - 3, rgSemDV.length) + rgFinal;
        rgSemDV = rgSemDV.slice(0, rgSemDV.length - 3);
    }

    return rgSemDV + rgFinal;
}

function mascarasGerais(){

    $('.cep').mask('00000-000');
    $('.cpfInput').mask(cpf_text);
    $('.cnpjInput').mask(cnpj_text);
    $('.nrlicitacaoInput').mask('99999/9999');
    $('.nrprocessoInput').mask('999/9999');
    $('.dataInput').mask(dt_text);
    $('#registro_core').mask('0000000/0000', {reverse: true});
    $('.celularInput').mask(fone_4_text + '9');

    let SPMaskBehavior = function(val) {
        return val.replace(/\D/g, '').length === 11 ? fone_5_text : fone_4_text + '9';
    }
    $('.telefoneInput').mask(SPMaskBehavior, {
        onKeyPress: function(val, e, field, options) {
            field.mask(SPMaskBehavior.apply({}, arguments), options);
        }
    });

    // .cpfOuCnpj
    let options_cpf_cnpj = {
        onKeyPress: function (cpf, ev, el, op) {
            let masks = [cpf_text + '0', cnpj_text];
            $('.cpfOuCnpj').mask((cpf.length > 14) ? masks[1] : masks[0], op);
        }
    }
    $('.cpfOuCnpj').index() > -1 && $('.cpfOuCnpj').val().length > 11 ? 
	$('.cpfOuCnpj').mask(cnpj_text, options_cpf_cnpj) : 
	$('.cpfOuCnpj').mask(cpf_text + '#', options_cpf_cnpj);

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

            if(val.length > 4 && isNumeric)
                mask = 'AAA-0000';
            
            $(field).mask(mask, options);
        }
    });

    if(($(".rgInput").length > 0) && ($(".rgInput").val().length > 3))
        $(".rgInput").val(mascaraRG($(".rgInput").val()));

    $(".rgInput").on('input', function() {
        let texto = $(this).val();
    
        if(texto.length > 3)
            $(this).val(mascaraRG(texto));
    });

    $('input[name="participantes_cpf[]"]').on('MASK', function(){
        $(':input[name="participantes_cpf[]"]').val('').unmask().mask(cpf_text);
    });
}

function mascarasInternas(){
    
    mascarasGerais();

    $('.fixoInput').mask(fone_4_text);
    $('.timeInput').mask(hora_text);
    $('.vagasInput').mask('0000');
    $('.anoInput').mask('0000');
    
    $('#horaTermino').mask(hora_text, {
        onComplete: function() {
            let horaInicio = $('#horaInicio').val();
            let horaTermino = $('#horaTermino').val();

            if(horaInicio && (horaTermino <= horaInicio)) {
                document.dispatchEvent(new CustomEvent("MSG_GERAL_CONT_TITULO", {
                    detail: {
                        titulo: '<i class="fas fa-times text-danger"></i> Erro!', 
                        texto: '<span class="text-danger">O horário de término não pode ser menor ou igual ao horário de início.</span>'
                    }
                }));
                $('#horaTermino').val('');
            }
        }
    });

    $('#horaInicio').mask(hora_text, {
        onComplete: function() {
            let horaInicio = $('#horaInicio').val();
            let horaTermino = $('#horaTermino').val();

            if(horaTermino && (horaInicio > horaTermino)) {
                document.dispatchEvent(new CustomEvent("MSG_GERAL_CONT_TITULO", {
                    detail: {
                        titulo: '<i class="fas fa-times text-danger"></i> Erro!', 
                        texto: '<span class="text-danger">O horário de início não pode ser maior que o horário de término.</span>'
                    }
                }));
                $('#horaInicio').val('');
            }
        }
    });
}

function mascarasExternas(){
    
    mascarasGerais();

    $('#datepicker').mask(dt_text);
    $('.numeroInput').mask('99');
    $('.capitalSocial').mask('#.##0,00', {reverse: true});
    $('.codigo_certidao').mask('AAAAAAAA - AAAAAAAA - AAAAAAAA - AAAAAAAA');
    $('.numero').mask('ZZZZZZZZZZ', {
		translation: {
		    'Z': {pattern: /[0-9\-]/}
		}
	});

    $('.gerentiContato').on('MASK', function(e){
        gerentiContato($(this), e.detail);
    });
}

export function executar(local = 'interno'){
    if(local == 'interno')
        return mascarasInternas();
    if(local == 'externo')
        return mascarasExternas();
}
