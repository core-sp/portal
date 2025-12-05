function removerAcento(palavra){

    return palavra.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

function limparBoxMunicipios(manterStyle = false, manterHtml = false){

    if(!manterStyle) 
    $('#lista_municipios').attr('style', '');

    if(!manterHtml) 
        $('#lista_municipios').html('');
}

function btnRemoverMunicipio(municipio_escolhido){

    const icone_remover = '<i class="fas fa-times-circle text-danger ml-2"></i>';

    let botao = '<button type="button" class="btn btn-sm btn-outline-danger font-weight-normal mr-2 mb-2" ';
    botao += 'style="font-size: 0.75rem;" value="' + municipio_escolhido + '">';
    botao += municipio_escolhido + icone_remover + '</button>';

    let input = '<input type="hidden" name="regioes.municipios[]" value="' + municipio_escolhido + '" />';

    return {'botao': botao, 'input': input};
}

function carregarMunicipios(){

    if($('#municipios_carregados span').length == 0)
        return false;

    $('#municipios_carregados span').each(function(){
        let municipio_escolhido = $(this).text();
        const resp = btnRemoverMunicipio(municipio_escolhido);

        if($('#municipios_escolhidos button[value="' + municipio_escolhido + '"]').length == 0){
            $('#municipios_escolhidos').append($(resp.botao + resp.input));
            $(this).remove();
        }
    });
}

function removerTodosMunicipios(){

    $('#remover_todos_municipios').click(function(){
        if($('#municipios_escolhidos button').length == 0)
            return false;
        
        $('#municipios_escolhidos').html('');
    });    
}

function removerMunicipioDIV(){

    $('#municipios_escolhidos').on('click', 'button', function(){
        $(this).remove();
        $('input[type="hidden"][value="' + $(this).val() + '"]').remove();
    });
}

function removerMunicipioINPUT(){

    $('#btn_apagar_municipio').click(function(){
        $('#municipios_escolhidos').val('Qualquer');
    });
}

function adicionarMunicipioDIV(){

    $('#lista_municipios').on('click', '.item-municipio button', function(){
        if($('#municipios_escolhidos button').length >= 20)
            return false;

        let municipio_escolhido = $(this).val();
        const resp = btnRemoverMunicipio(municipio_escolhido);

        if($('#municipios_escolhidos button[value="' + municipio_escolhido + '"]').length == 0)
            $('#municipios_escolhidos').append($(resp.botao + resp.input));

        limparBoxMunicipios();
        $("#buscar_municipios").val('');
    });
}

function adicionarMunicipioINPUT(){

    $('#lista_municipios').on('click', '.item-municipio button', function(){
        let municipio_escolhido = $(this).val();

        if($('#municipios_escolhidos').val() != municipio_escolhido)
            $('#municipios_escolhidos').val(municipio_escolhido);

        limparBoxMunicipios();
        $("#buscar_municipios").val('');
    });
}

function onOffMunicipiosINPUT(){

    const tipos = ['representantes'];

    if($('#on_off_municipios').length > 0){
        let off = !tipos.includes($('#on_off_municipios').val());
        $('#buscar_municipios').prop('disabled', off);
    }

    $('#on_off_municipios').change(function(){
        let off = !tipos.includes($('#on_off_municipios').val());
        $('#buscar_municipios').prop('disabled', off);
    });
}

function buscarMunicipios(){

    onOffMunicipiosINPUT();

    let municipios = document.getElementById('municipiosJSON');

    if((municipios === null) || (municipios === undefined))
        return false;

    municipios = JSON.parse(municipios.textContent);

    $("#buscar_municipios").on("input", function() {
        let value = removerAcento($(this).val().toUpperCase());
        let municipios_letra = [];

        const inicio = '<p class="item-municipio font-weight-normal p-0 m-0">';
        const final = '</p>';
        const btnPropMun = 'class="btn btn-link text-left btnMunicipio" type="button" style="font-size: 0.85rem;"';
        const icone = '<i class="fas fa-map-marker-alt mr-3"></i>';

        if(value.length == 0){
            limparBoxMunicipios();
            return false;
        }

        if(value.length == 1){
            limparBoxMunicipios(true);

            if(!municipios.hasOwnProperty(value))
                return false;
            municipios_letra = municipios[value];
        }

        const inputs_municipios = municipios_letra.flatMap(mun => [
            $(inicio + '<button ' + btnPropMun + ' value="' + mun + '">' + icone + mun + '</button>' + final)
        ]);
    
        $("#lista_municipios").append(inputs_municipios);

        if(inputs_municipios.length > 7)
            $("#lista_municipios").height('200');

        $("#lista_municipios .item-municipio").filter(function() {
            let texto = removerAcento($(this).text());
            
            $(this).toggle(texto.toUpperCase().indexOf(value) > -1);
        });

        if($("#lista_municipios .item-municipio:visible").length < 8)
            limparBoxMunicipios(false, true);

        layoutListaMunicipios();
    });
}

function layoutListaMunicipios(){

    $("#lista_municipios").css({
        'border-style': "solid",
        'border-radius': '3%',
        'border-color': '#cbcfcf',
        'border-top': '0'
    });
}

function visualizar(){

    buscarMunicipios();
    carregarMunicipios();
    removerTodosMunicipios();

    $('#municipios_escolhidos')[0].tagName == 'INPUT' ? removerMunicipioINPUT() : removerMunicipioDIV();
    $('#municipios_escolhidos')[0].tagName == 'INPUT' ? adicionarMunicipioINPUT() : adicionarMunicipioDIV();
}

export function executar(funcao){
    if(funcao == 'visualizar')
        return visualizar();
}