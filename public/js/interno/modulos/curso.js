function desabilitaHabilitaCampoAdd(valor){
    let desabilita = valor == '0';
    let obr = !desabilita;

    if(desabilita)
        $('select[name="campo_rotulo"] option[value=""]').prop('selected', true);

    $('select[name="campo_rotulo"], select[name="campo_required"]').prop('disabled', desabilita).prop('required', obr);
}

function adicionarCidade(){

    $('#nova_cidade').click(function(){
        document.dispatchEvent(new CustomEvent("MSG_GERAL_VARIOS_BTN_ACAO", {
            detail: {
                layout: {sem_txt_center: true, fade: true},
                titulo: '<i class="fas fa-map-marked-alt mr-2"></i>Adicionar nova cidade <br><small class="form-text" style="font-size: 0.8rem;"><em>* A nova cidade só será salva após confirmar a alteração / criação do curso</em></small>', 
                texto: '<div class="form-group"><label for="nome_cidade">Nome da nova cidade: </label><input type="text" id="nome_cidade" class="form-control" /></div>',
                botao: ['<button type="button" class="btn btn-success btn-sm" id="salvar_cidade">Salvar</button>', 
                    '<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>']
            }
        }));
    });

    let cidade_temp = '';
    $('.modal-footer').on('click', '#salvar_cidade', function(){
        $('#idregional').append('<option class="text-success" value="' + cidade_temp + '">' + cidade_temp + '</option>');
        document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));

        $('#idregional option[value="' + cidade_temp + '"]').attr('selected', true);
        cidade_temp = '';
    });
    
    $('.modal').on('input', '#nome_cidade', function(){
        cidade_temp = this.value;
    });
}

function editar(){

    if($('select[name="add_campo"]').length > 0)
        desabilitaHabilitaCampoAdd($('select[name="add_campo"]').val());
    
    $('select[name="add_campo"]').change(function(){
        desabilitaHabilitaCampoAdd($(this).val());
    });

    adicionarCidade();

    $('#curso_adm').submit(function(){
        if($('#idregional').val().match(/\D/g) !== null)
            $('#idregional').attr('name', 'cidade');
    });
};

export function executar(funcao){
    if(funcao == 'editar')
        return editar();
}
