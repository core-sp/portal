function criarJson(nome_campo){

    let final = '';

    switch(nome_campo) {
        case "cpf_cnpj":
            final = '"' + nome_campo + '":' + '"' + $('input[name="cpf_cnpj"]').val() + '"';
            break;
        case "sala_reuniao_id":
            final = '"' + nome_campo + '":' + '"' + $('select[name="sala_reuniao_id"]').val();
            final = final + '", "tipo_sala":"' + $('select[name="tipo_sala"]').val() + '"';
            break;
        default:
            let cpfs = [$('input[name="cpf_cnpj"]').val()];

            $(':input[name="participantes_cpf[]"]').each(function() {
                if($(this).val().length == 14)
                    cpfs.push(this.value);
            });

            final = '"' + nome_campo + '":' + JSON.stringify(cpfs);
    }
  
    return JSON.parse('{"_method":"POST", "_token":"' + $('meta[name="csrf-token"]').attr('content') + '", ' + final + '}');
}

function caseCpfCnpj(response){

    let resultado = response.situacaoGerenti;
    let situacao = resultado != null ? resultado.substring(0, resultado.indexOf(',')) : 'Ativo';

    $.each(response, function(i, valor) {
        $('#' + i).text(valor);
    });

    $('#area_gerenti').show();
    $('#cpfResponsavel').val($('input[name="cpf_cnpj"]').val());
    $('#nomeResponsavel').val(response['nomeGerenti']);

    if((response.registroGerenti == null) || (situacao != 'Ativo')){

        let texto = situacao != 'Ativo' ? 'Sem registro Ativo no Gerenti!' : 'Sem registro no Gerenti!';
        controlarModal('<strong>' + texto + ' Não pode criar o agendamento.</strong>');
    }
}

function controlarModal(texto = '', footerHide = true){

    $('#modal-criar_agenda').modal({backdrop: 'static', keyboard: false, show: true});
    footerHide ? $('.modal-footer').hide() : $('.modal-footer').show();
    $('#modal-criar_agenda .modal-body').html(texto);
}

function caseSalaReuniaoId(response){

    $(".participante:gt(0)").remove();

    if(response.total_participantes <= 0){
        let texto = 'A regional não está com a Sala de ' + $('select[name="tipo_sala"] option:selected').text() + ' habilitada! Não pode criar o agendamento.';

        $('#area_participantes').hide();
        controlarModal('<strong>' + texto + '</strong>');
        return;
    }

    if((response.total_participantes > 0) && ($('select[name="tipo_sala"]').val() == 'reuniao')){
        for (let i = 1; i < response.total_participantes; i++)
            $('#area_participantes').append($('.participante:last').clone());

        $('.participante input[name="participantes_cpf[]"]')[0].dispatchEvent(new CustomEvent("MASK"));
        $('.participante :input[name="participantes_nome[]"]').val('');
        $('#area_participantes').show();
    }
}

function caseDefault(response){

    if(response.suspenso == ''){
        $('#criarAgendaSala').submit();
        return;
    }

    let texto = response.suspenso + '<br><br><strong>Confirmar esse agendamento?</strong>';
    controlarModal(texto, false);
}

function verificarDadosCriarAgendaSala(nome_campo){
  
    let json = criarJson(nome_campo);
    
    $.ajax({
        method: "POST",
        dataType: 'json',
        url: '/admin/salas-reunioes/agendados/verifica',
        data: json,
        beforeSend: function(){
            if(nome_campo == "cpf_cnpj")
                $('#modal-load-criar_agenda').modal({backdrop: 'static', keyboard: false, show: true});
        },
        complete: function(){
            $('#modal-load-criar_agenda').modal('hide');
        },
        success: function(response) {
            $('#modal-load-criar_agenda').modal('hide');

            switch(nome_campo) {
            case "cpf_cnpj":
                caseCpfCnpj(response);
                break;
            case "sala_reuniao_id":
                caseSalaReuniaoId(response);
                break;
            default:
                caseDefault(response);
            }
        },
        error: function() {
            let texto = '<span class="text-danger">Deu erro! Recarregue a página.</span>';
            controlarModal(texto);
        }
    });
}

function criar(){
  
    if($('#criarAgendaSala').length <= 0)
        return;

    let tam = $('#criarAgendaSala input[name="cpf_cnpj"]').val().length;
    if((tam == 14) || (tam == 18))
        verificarDadosCriarAgendaSala("cpf_cnpj");
  
    $('input[name="cpf_cnpj"]').change(function(){
        let tamanho = $('#criarAgendaSala input[name="cpf_cnpj"]').val().length;

        if((tamanho == 14) || (tamanho == 18))
            verificarDadosCriarAgendaSala("cpf_cnpj");
    });
    
    $('select[name="sala_reuniao_id"]').change(function(){
        if(this.value == "")
            $(".participante:gt(0)").remove();

        verificarDadosCriarAgendaSala("sala_reuniao_id");
    });
  
    $('select[name="tipo_sala"]').change(function(){
        if(this.value != 'reuniao')
            $('#area_participantes').hide();

        verificarDadosCriarAgendaSala("sala_reuniao_id");
    });
  
    $('#verificaSuspensos').click(function(){
        verificarDadosCriarAgendaSala("participantes_cpf[]");
    });
  
    $('select[name="periodo_entrada"]').change(function(){
        let valor = this.value;
        let indice = 0;

        if(valor != '')
            $('select[name="periodo_saida"] option').each(function(i) {
                $(this).val() <= valor ? $(this).hide() : $(this).show();
                indice = $(this).val() == valor ? i + 1 : indice;
            });

        $('select[name="periodo_saida"] option:eq(' + indice + ')').prop('selected', true);
    });
  
    $('#enviarCriarAgenda').click(function(){
        $('#criarAgendaSala').submit();
    });

};

export function executar(funcao){
    if(funcao == 'criar')
        return criar();
}
