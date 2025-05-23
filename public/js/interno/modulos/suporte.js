const url_logs = '/admin/suporte/logs';
const grafico = '.grafico-storage';

function exportarPDF(chart) {

    $(grafico).on('click', '.exportCustomPDF', function (e) {
        if (typeof window.jspdf !== 'object') {
            document.dispatchEvent(new CustomEvent("MSG_GERAL_CONT_TITULO", {
                detail: {
                    titulo: '<i class="fas fa-times text-danger"></i> Erro!',
                    texto: '<span class="text-danger">Não é possível exportar como PDF no momento!</span>'
                }
            }));
            return false;
        }

        // Exemplo ApexChart
        let dataURL = chart.dataURI()
            .then(({ imgURI, blob }) => {
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF();

                pdf.addImage(imgURI, 'PNG', 10, 10);
                pdf.save("pdf-chart.pdf");
            });
    });
}

function optionsChart(chart_, json){

    const exportPDF = '<div class="apexcharts-menu-item exportCustomPDF" title="Download PDF">Download PDF</div>';
    const opt_chart = {
        type: 'pie',
        height: '350px',
        toolbar: {
            show: true,
            tools: {
                download: '<i class="fas fa-download text-info"></i>',
                customIcons: [{
                    icon: '<i class="fas fa-sync btn-refresh-storage text-primary ml-2"></i>',
                    title: 'Atualizar',
                    class: 'custom-icon',
                    click: function (chart, options, e) {
                        $(grafico)[0].dispatchEvent(new CustomEvent("CHART_REFRESH", {
                            detail: chart
                        }));
                    }
                }]
            },
        },
        events: {
            legendClick: function (chartContext, seriesIndex, opts) {
                let cores = chartContext.w.config.legend.markers.fillColors == undefined ?
                    chartContext.w.globals.markers.colors : chartContext.w.config.legend.markers.fillColors;
                let temp = chartContext.w.globals.series;

                temp[seriesIndex] = chartContext.w.globals.series[seriesIndex] == 0 ? json.dados[seriesIndex] : 0;
                cores[seriesIndex] = temp[seriesIndex] == 0 ? 'rgb(255, 255, 255)' : json.cores[seriesIndex];

                chartContext.updateOptions({
                    legend: {
                        markers: {
                            fillColors: cores
                        }
                    }
                }, true);
                chartContext.updateSeries(temp, true);
            },
            updated: function (chartContext, config) {
                $('.apexcharts-menu').append(exportPDF);
            },
            mounted: function (chartContext, config) {
                $('.apexcharts-menu').append(exportPDF);
            },
        }
    };
    const opt_title = {
        text: 'Storage em ' + chart_.attr('id').replace('ambiente_', ''),
        align: 'center',
        floating: false,
        style: {
            fontSize: '16px',
            fontWeight: 'bolder',
            fontFamily: 'Arial, sans-serif',
            color: '#004587'
        }
    };
    const opt_subtitle = {
        text: 'Capacidade total - ' + json.total + ' MB',
        align: 'center',
        floating: false,
        offsetY: 22,
        style: {
            fontWeight: 'bold',
            fontFamily: 'Arial'
        }
    };
    const opt_dataLabels = {
        enabled: true,
        formatter: function (value, { seriesIndex, dataPointIndex, w }) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2
            }).format(w.config.series[seriesIndex]) + ' MB';
        },
        style: {
            fontSize: '13px',
            colors: ['#000']
        },
        dropShadow: {
            enabled: false,
        }
    };
    const opt_legend = {
        horizontalAlign: 'left',
        position: 'top',
    };
    const opt_tooltip = {
        enabled: false,
    };
    const opt_states = {
        hover: {
            filter: {
                type: 'none',
            }
        },
        active: {
            allowMultipleDataPointsSelection: false,
            filter: {
                type: 'none',
            }
        },
    };
    const opt_noData = {
        text: 'Sem dados',
    };

    return {
        elemento: chart_[0],
        opcoes: {
            series: json.dados, colors: json.cores, labels: json.labels,
            chart: opt_chart, title: opt_title, subtitle: opt_subtitle, dataLabels: opt_dataLabels, 
            legend: opt_legend, tooltip: opt_tooltip, states: opt_states, noData: opt_noData,
        }
    };
}

function graficoApex(obj) {

    let chart = new ApexCharts(obj.elemento, obj.opcoes);

    chart.render();
    exportarPDF(chart);
    $('.apexcharts-menu-icon').attr('title', 'Exportar');
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
    graficoApex(optionsChart(chart_, json));
}

function visualizar(){

    $('[data-toggle="popover"]').popover();

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

    $(grafico).on('CHART_REFRESH', function (e) {
        e.detail.destroy();
        sobreStorage();
    });
};

export function executar(funcao){
    if(funcao == 'visualizar')
        return visualizar();
}
