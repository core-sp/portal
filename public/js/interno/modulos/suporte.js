const url_logs = '/admin/suporte/logs';

function visualizar(){

    $(document).on('keydown', function(e) {
        if((e.keyCode == 27) && (window.location.href.indexOf(url_logs))){
            document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
        }
    });

    $('[name="relat_opcoes"]').change(function(){
        let somente_rc = $('[name="relat_opcoes"] option[value="' + $(this).val() + '"]').text().search('do RC') > -1;

        if(somente_rc){
            $('[name="relat_tipo"] option[value="externo"]').prop('selected', true);
            $('[name="relat_tipo"] option[value="interno"]').hide();
            return;
        }

        $('[name="relat_tipo"] option[value="interno"]').show();
    });

    $('[type="radio"]').change(function(){
        let id = $(this).parents('.input-group').attr('id');
        let periodo = this.value;
        let outro_periodo = periodo == 'mes' ? 'ano' : 'mes';
        let nome = id == 'relat-buscar-' + periodo ? 'relat_' + periodo : periodo;
        let outro = id == 'relat-buscar-' + periodo ? 'relat_' + outro_periodo : outro_periodo;

        if(this.checked){
            $('[name="' + outro + '"]').prop('disabled', true);
            $('[name="' + nome + '"]').prop('disabled', false);
        }
    });

};

export function executar(funcao){
    if(funcao == 'visualizar')
        return visualizar();
}
