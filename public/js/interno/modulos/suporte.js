const url_logs = '/admin/suporte/logs';
const grafico = '.grafico-storage';

function exportarPDF(dataUrl) {

    if (typeof window.jspdf !== 'object') {
        document.dispatchEvent(new CustomEvent("MSG_GERAL_CONT_TITULO", {
            detail: {
                titulo: '<i class="fas fa-times text-danger"></i> Erro!',
                texto: '<span class="text-danger">Não é possível exportar como PDF no momento!</span>'
            }
        }));
        return false;
    }

    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF();

    pdf.addImage(dataUrl, 'PNG', 10, 10);
    pdf.save("pdf-grafico.pdf");
}

function options(json){

    return bb.generate({
        bindto: grafico,
        data: {
            type: 'pie',
            columns: json.labels.map((val, index) => [val, json.dados[index]]),
        },
        title: {
            text: 'Storage em ' + $(grafico).attr('id').replace('ambiente_', '') + 
                '\n\nCapacidade total - ' + json.total + ' MB',
            position: 'center',
            padding: {
                right: 10,
                bottom: 40,
                left: 10
            },
        },
        tooltip: {
            show: false
        },
        pie: {
            expand: false,
            label: {
                format: function(value, ratio, id) {
                    return d3.formatLocale({thousands: ".", decimal: ","}).format(".2f")(value) + ' MB';
                },
                ratio: 1.35
            }
        },
        svg: {
            classname: "billboard_svg_class"
        },
    });
}

function graficoBillboard(chart){

    $('.bb-title').addClass('font-weight-bold');

    $('.export').click(function(){
        let extensao = this.value;
        let obj_mime = extensao == 'pdf' ? {} : {mimeType: "image/" + extensao};

        chart.export(obj_mime, function(dataUrl) {
            if(extensao == 'pdf'){
                exportarPDF(dataUrl);
                return;
            }

            let link = document.createElement("a");
            link.download = 'grafico.' + extensao;
            link.href = dataUrl;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });

    $('.btn-refresh-storage').click(function () {
        if(chart !== undefined)
            chart.destroy();
        sobreStorage();
    });
}

async function sobreStorage() {

    const chart_ = $('div' + grafico);
    const spinner = 'spinner-grow spinner-grow-sm text-primary';

    if (!chart_.hasClass('spinner-grow'))
        chart_.addClass(spinner);

    const dados = await fetch('/admin/suporte/sobre-storage', {
        method: 'GET', 
        headers: {
            'Content-Type': 'application/json;charset=utf-8',
        }
    });

    const json = await dados.json();

    if(!dados.ok){
        let msg = '<i class="fas fa-exclamation-triangle text-danger"></i><br>' + 
            '<span class="text-danger mt-2"><b>Código: </b></span>' + dados.status + 
            '<br><span class="text-danger"><b>Mensagem: </b></span> ' + 
            json.message.replace('{', '<span class="text-danger"><code>').replace('}', '</code></span>');

        chart_.removeClass(spinner);
        chart_.parents('.card-body').html(msg);

        return false;
    }

    chart_.removeClass(spinner);
    graficoBillboard(options(json));
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
    if($(grafico).length > 0){
        sobreStorage();
    }
};

export function executar(funcao){
    if(funcao == 'visualizar')
        return visualizar();
}
