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

    let horas_selecionadas = $('#' + id + ' option:selected');

    if(horas_selecionadas.length <= 0)
        return;

    const selectedValues = Array.from(horas_selecionadas)
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
            document.dispatchEvent(new CustomEvent("MSG_GERAL_CONT_TITULO", {
                detail: {
                    titulo: '<i class="fas fa-times text-danger"></i> Erro!', 
                    texto: '<span class="text-danger">Erro ao carregar os horários formatados. Recarregue a página.</span>'
                }
            }));
        }
    });
}

function editar(){

    $("#itens_reuniao, #itens_coworking").on('dblclick', 'option', function(){

        let txt = this.text;
        let valor = this.value;
        let numero = txt.replace(/[^0-9_,]/ig, '');
    
        if(numero.trim().length <= 0)
            return;

        document.dispatchEvent(new CustomEvent("MSG_GERAL_VARIOS_BTN_ACAO", {
            detail: {
                layout: {sem_txt_center: true, fade: true},
                titulo: 'Editar unidade do item<span class="ml-2 mt-2 font-italic" style="font-size:0.6em">(usar vírgula para decimal)</span>', 
                texto: txt.substr(0, txt.search(numero)) + ' <input type="text" id="'+ valor +'">' + 
                    txt.substr(txt.search(numero) + numero.length, txt.length),
                botao: ['<button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>', 
                    '<button type="button" class="btn btn-success ' + $(this).parent().attr('id') + '" id="editar_item">Inserir</button>']
            }
        }));
    });

    $('.modal-footer').on('click', '#editar_item', function(){

        let tipo = $(this).hasClass('itens_reuniao') ? 'itens_reuniao' : 'itens_coworking';
        let body = $(this).parents('.modal-content').find('.modal-body input');
        let id = body.attr('id');
        let valor = body.val();
        let texto = $('#' + tipo + ' option[value="' + id + '"]').text();
        let numero = texto.replace(/[^0-9_,]/ig, '');
    
        if((valor.trim().length == 0) || (valor.replace(/[0-9,]/ig, '').length > 0)){
            document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
            return;
        }

        texto = texto.replace(numero, valor);
        body.remove();
        $('#' + tipo + ' option[value="' + id + '"]').val(texto).text(texto);
        document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
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
        this.selectedIndex >= 0 ? ajaxHorariosViewSala(this.id) : $('#' + this.id + '_rep').html('');
    });

};

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
