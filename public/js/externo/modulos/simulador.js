const _tipo_pessoa = $('#tipoPessoa option:selected').val();
const _dt_inicio = $('#dataInicio').val();
const _capital_social = $('#capitalSocial').val();
const _filial_check = $('#filialCheck:checked').length;
const _filial = $('#filial option:selected').val();
const _empresa_ind = $('#empresaIndividual:checked').length;

function confereSeSimulou(){

    if(_tipo_pessoa != $('#tipoPessoa option:selected').val())
        return false;
        
    if(_dt_inicio != $('#dataInicio').val())
        return false;
        
    if(_capital_social != $('#capitalSocial').val())
        return false;
        
    if(_filial_check != $('#filialCheck:checked').length)
        return false;
        
    let temp_filial = $('#filial option:selected').val();

    if((_filial != temp_filial) && (_filial !== undefined) && (temp_filial !== undefined))
        return false;
        
    if(_empresa_ind != $('#empresaIndividual:checked').length)
        return false;        

    return true;
}

function criarHTMLPrint(){

    let data = $('#dataInicio').val();

    data = data.slice(8,10) + '/' + data.slice(5,7) + '/' + data.slice(0,4);
    data = '<b>Data início das atividades:</b> ' + data + '</br>';

    let selectTipoPessoa = $('select[name="tipoPessoa"] option:selected').text();
    let capital = '<b>Capital social:</b> ' + $('#capitalSocial').val() + '</br>';
    let filial = $('#filialCheck:checked').length > 0 ? 'Com filial | ' + $('select[name="filial"] option:selected').text() + '</br>' : '';
    let empresa = $('#empresaIndividual:checked').length > 0 ? 'Empresa individual</br>' : '';
    let titulo = '<h4>RESULTADO DO SIMULADOR DE VALORES</h4><hr>';
    let final = titulo + data;

    if(selectTipoPessoa == 'Jurídica')
        final = final + capital + filial + empresa;

    let style = '<style>#separadorAviso { margin-top: 30px; } img, .btn_print { display: block; margin-left: auto; margin-right: auto; } ' + 
        'img { width: 15%; } table, th, td { border: 1px solid black; border-collapse: collapse; } th, td { padding: 10px; } ' + 
        '.btn_print { background-color: #008CBA; color: white; padding: 10px 10px; font-size: 16px; } ' + 
        '@media print{ img { width: 40% !important; } .btn_print { display: none !important; } }</style>';
    
    return style + '<div><br><hr />' + final + '</div>' + $('#simuladorTxt').html();
}

function visualizar(){

    let dt_inicio_readonly = $('#tipoPessoa').val() != '1';

    $('#dataInicio').prop('readonly', dt_inicio_readonly);

	$('#tipoPessoa').on('change', function(){
        dt_inicio_readonly = $(this).val() != '1';
		$('#simuladorTxt').hide();
        $('#dataInicio').prop('readonly', dt_inicio_readonly);
        !dt_inicio_readonly ? $('#simuladorAddons').css('display','flex').show() : $('#simuladorAddons').hide();

		if (dt_inicio_readonly) {
			$('#filial').prop('disabled', true).val('');
			$('#dataInicio').val($('#dataInicio').attr('max'));
		}
	});

	$("#filialCheck").on('change', function() {
        let filial = $('#filial');

        filial.prop('disabled', !this.checked);
		if(!this.checked)
			filial.val('');
	});

    $('.btnPrintSimulador').click(function(){

        if(!confereSeSimulou()){
            document.dispatchEvent(new CustomEvent("MSG_GERAL_CONT_TITULO", {
                detail: {
                    titulo: '<i class="fas fa-exclamation-triangle text-primary mr-2"></i>Atenção!', 
                    texto: '<span>Deve simular novamente após alterar os valores.</span>'
                }
            }));
            return false;
        }
            
        let myWindow = window.open();

        // evitar remover o node na página do simulador
        let img = myWindow.document.importNode($('.logo_print')[0]);
        let btn_print = myWindow.document.importNode($('.btn_print')[0]);
        const att = document.createAttribute("onclick");

        att.value = "window.print()";
        btn_print.innerHTML = 'Imprimir';
        btn_print.setAttributeNode(att);

        myWindow.document.body.innerHTML = criarHTMLPrint();
        myWindow.document.body.appendChild(btn_print);
        $(myWindow.document.body).prepend(img);
        $(myWindow.document.body.getElementsByClassName('blank-row')).each(function(){
            this.remove();
        });
        
        myWindow.print();

        // evitar reload na página do simulador
        return false;
    });
}

export function executar(funcao){
    if(funcao == 'visualizar')
        return visualizar();
}
