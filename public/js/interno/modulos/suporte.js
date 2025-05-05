const url_logs = '/admin/suporte/logs';

async function sobreStorage(){

    const spinner = 'spinner-grow spinner-grow-sm text-primary';
    const chart_ = $('.grafico-storage');
    const dados = await fetch('/admin/suporte/sobre-storage', {
        method: 'GET', 
        headers: {
            'Content-Type': 'application/json;charset=utf-8',
        }
    });

    if(!dados.ok){
        let msg = '<i class="fas fa-exclamation-triangle text-danger"></i><br>' + 
            '<span class="text-danger mt-2">' + dados.status + ", <b>Mensagem:</b> " + dados.statusText + 
            '</span><br><b>Atualize a página e tente novamente!</b>';
        chart_.removeClass(spinner);
        chart_.parents('.card-body').html(msg);

        return false;
    }

    const json = await dados.json();

    chart_.removeClass(spinner);

    new Chart(chart_[0], {
        type: 'doughnut',
        data: {
            labels: json.labels,
            datasets: [{
                label: json.label,
                data: json.dados,
                backgroundColor: json.cores,
                hoverOffset: 4
            }]
        }
    });
}

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

    // Storage
    if($('.grafico-storage').length > 0){
        sobreStorage();
    }
};

export function executar(funcao){
    if(funcao == 'visualizar')
        return visualizar();
}
