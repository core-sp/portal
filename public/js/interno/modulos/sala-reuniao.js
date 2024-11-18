function mesaIgualParticipantesReuniao(){

    let input = $('input[name="participantes_reuniao"]');
    let com_itens = "Mesa com " + input.val() + " cadeira(s)";

    let adicionado = $('#itens_reuniao option[value^="Mesa com "]');
    if(adicionado.length > 0)
        adicionado.val(com_itens).text(com_itens);
}

function hideShowHorasLimitesSala(){

    let hide_proxima_hora = false;

    $('#horarios_reuniao option, #horarios_coworking option').each(function(){

        if(this.value == $('#hora_limite_final_manha').val() || hide_proxima_hora){
            $(this).hide().prop('selected', false);
            hide_proxima_hora = hide_proxima_hora ? false : true;

        }else if(this.value >= $('#hora_limite_final_tarde').val())
            $(this).hide().prop('selected', false);
        else
            $(this).show();
    });
}

function ajaxHorariosViewSala(id){

    const selectedValues = Array.from($('#' + id + ' option:selected'))
    .map(
        option => option.value,
    );

    $.ajax({
        type: "POST",
        data: {
            _method: "POST",
            _token: $('meta[name="csrf-token"]').attr('content'),
            horarios: selectedValues,
            hora_limite_final_manha: $('#hora_limite_final_manha').val(),
            hora_limite_final_tarde: $('#hora_limite_final_tarde').val(),
        },
        dataType: 'json',
        url: "/admin/salas-reunioes/sala-horario-formatado/" + $('#valor_id').val(),
        success: function(response) {
            $('#' + id + '_rep').html(response);
        },
        error: function() {
            alert('Erro ao carregar os horários formatados. Recarregue a página.');
        }
    });
}

function editar(){

    $("#itens_reuniao, #itens_coworking").on('dblclick', 'option', function(){

        let texto = this.text;
        let valor = this.value;
        let numero = texto.replace(/[^0-9_,]/ig, '');
    
        if(numero.trim().length > 0){
            $('#sala_reuniao_itens').modal({backdrop: 'static', keyboard: false, show: true});
            $('#sala_reuniao_itens')
                .removeClass('itens_reuniao')
                .removeClass('itens_coworking')
                .addClass($(this).parent().attr('id'));
            $('#sala_reuniao_itens .modal-body')
                .html(texto.substr(0, texto.search(numero)) + ' <input type="text" id="'+ valor +'">' + texto.substr(texto.search(numero) + numero.length, texto.length));
        }
    
    });

    $('#editar_item').click(function(){

        let tipo = $('#sala_reuniao_itens').hasClass('itens_reuniao') ? 'itens_reuniao' : 'itens_coworking';
        let id = $('#sala_reuniao_itens .modal-body input').attr('id');
        let valor = $('#sala_reuniao_itens .modal-body input').val();
        let texto = $('#' + tipo + ' option[value="' + id + '"]').text();
        let numero = texto.replace(/[^0-9_,]/ig, '');
    
        texto = texto.replace(numero, valor);
        $('#sala_reuniao_itens .modal-body input').remove();
        $('#' + tipo + ' option[value="' + id + '"]').val(texto).text(texto);
        $('#sala_reuniao_itens').modal('hide');
    });
    
    $('button.addItem, button.removeItem').click(function(){
    
        let tipo = (this.id == 'btnAddReuniao') || (this.id == 'btnRemoveReuniao') ? 'reuniao' : 'coworking';
        let itens = $(this).hasClass('addItem') ? $('#todos_itens_' + tipo + ' option:selected') : $('#itens_' + tipo + ' option:selected');
        let opcao = $(this).hasClass('addItem') ? 'add' : 'remove';
    
        itens.each(function() {
            if(opcao == 'add')
                $('#itens_' + tipo).append('<option value="' + this.value + '">' + this.text +'</option>');
            else{
                let texto = this.text;
                let numero = texto.replace(/[^0-9_,]/ig, '');
                
                texto = numero.trim().length > 0 ? texto.replace(numero, '_') : texto;
                $('#todos_itens_' + tipo).append('<option value="' + texto + '">' + texto +'</option>');
            }
    
            $(this).remove();
        });
    
        mesaIgualParticipantesReuniao();
    });

    if($('#form_salaReuniao #hora_limite_final_manha, #form_salaReuniao #hora_limite_final_tarde').length > 1)
        hideShowHorasLimitesSala();
    
    $('#form_salaReuniao input[name="participantes_reuniao"]').change(function(){
        mesaIgualParticipantesReuniao();
    });
    
    $('#form_salaReuniao button[type="submit"]').click(function(){
        $('#itens_reuniao option').prop('selected', true);
        $('#itens_coworking option').prop('selected', true);
    });
    
    $('#form_salaReuniao #hora_limite_final_manha, #form_salaReuniao #hora_limite_final_tarde').change(function(){
        hideShowHorasLimitesSala();
        ajaxHorariosViewSala('horarios_reuniao');
        ajaxHorariosViewSala('horarios_coworking');
    });
    
    $('#form_salaReuniao #horarios_reuniao, #form_salaReuniao #horarios_coworking').change(function(){
        ajaxHorariosViewSala(this.id);
    });

};

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
