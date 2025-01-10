function mascarasGerais(){

    $('.cep').mask('00000-000');
    $('.cpfInput').mask('000.000.000-00');
    $('.cnpjInput').mask('99.999.999/9999-99');
    $('.nrlicitacaoInput').mask('99999/9999');
    $('.nrprocessoInput').mask('999/9999');
    $('.dataInput').mask('00/00/0000');
    $('#registro_core').mask('0000000/0000', {reverse: true});
    $('.celularInput').mask('(00) 0000-00009');

    $('.telefoneInput').mask('(00) 0000-00009').focusout(function (event) {  
        let target, phone, element;

        target = (event.currentTarget) ? event.currentTarget : event.srcElement;
        phone = target.value.replace(/\D/g, '');
        element = $(target);
        element.unmask();
        phone.length > 10 ? element.mask("(99) 99999-9999") : element.mask("(99) 9999-99999");  
    });

    // .cpfOuCnpj
    let options = {
        onKeyPress: function (cpf, ev, el, op) {
            let masks = ['000.000.000-000', '00.000.000/0000-00'];
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

            if(val.length > 4 && isNumeric)
                mask = 'AAA-0000';
            
            $(field).mask(mask, options);
        }
    });
}

function mascarasInternas(){
    
    mascarasGerais();

    $('.fixoInput').mask('(00) 0000-0000');
    $('.timeInput').mask('00:00');
    $('.vagasInput').mask('0000');
    $('.anoInput').mask('0000');
    
    $('#horaTermino').mask('00:00', {
        onComplete: function() {
            let horaInicio = $('#horaInicio').val();
            let horaTermino = $('#horaTermino').val();

            if(horaInicio && (horaTermino <= horaInicio)) {
                alert('O horário de término não pode ser menor ou igual ao horário de início.');
                $('#horaTermino').val('');
            }
        }
    });

    $('#horaInicio').mask('00:00', {
        onComplete: function() {
            let horaInicio = $('#horaInicio').val();
            let horaTermino = $('#horaTermino').val();

            if(horaTermino && (horaInicio > horaTermino)) {
                alert('O horário de início não pode ser maior que o horário de término.');
                $('#horaInicio').val('');
            }
        }
    });
}

function mascarasExternas(){
    
    mascarasGerais();

    $('#datepicker').mask("99/99/9999");
    $('.numeroInput').mask('99');
    $('.capitalSocial').mask('#.##0,00', {reverse: true});
    $('.codigo_certidao').mask('AAAAAAAA - AAAAAAAA - AAAAAAAA - AAAAAAAA');
    $('.numero').mask('ZZZZZZZZZZ', {
		translation: {
		    'Z': {pattern: /[0-9\-]/}
		}
	});
}

export function executar(local = 'interno'){
    if(local == 'interno')
        return mascarasInternas();
    if(local == 'externo')
        return mascarasExternas();
}
