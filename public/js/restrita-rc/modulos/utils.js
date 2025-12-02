function buscarMunicipios(){

    let municipios = document.getElementById('municipiosJSON');

    if((municipios === null) || (municipios === undefined))
        return false;

    municipios = JSON.parse(municipios.textContent);
    let inicio = '<p class="item-municipio font-weight-normal p-0 m-0">';
    let final = '</p>';

    $("#buscar_municipios").on("input", function() {
        let value = $(this).val().toUpperCase();
        let municipios_letra = [];
        const icone = '<i class="fas fa-map-marker-alt mr-3"></i>';

        // remove acentuação do caracter
        value = value.normalize("NFD").replace(/[\u0300-\u036f]/g, "");

        if(value.length == 0){
            $("#lista_municipios").attr('style', '').html('');
            return false;
        }

        if(value.length == 1){
            $("#lista_municipios").html('');

            if(!municipios.hasOwnProperty(value))
                return false;
            municipios_letra = municipios[value];
        }

        const inputs_municipios = municipios_letra.flatMap(x => [
            $(inicio + '<button class="btn btn-link" type="button" value="' + x + '" style="font-size: 0.85rem;">' + icone + x + '</button>' + final)
        ]);
    
        $("#lista_municipios").append(inputs_municipios);

        if(inputs_municipios.length > 7)
            $("#lista_municipios").height('200');

        $("#lista_municipios .item-municipio").filter(function() {
            let texto = $(this).text();
            
            // remove acentuação do caracter
            texto = texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "");

            $(this).toggle(texto.toUpperCase().indexOf(value) > -1);
        });

        if($("#lista_municipios .item-municipio:visible").length < 8)
            $("#lista_municipios").attr('style', '');
    });
}

function btnRemoverMunicipio(municipio_escolhido){

    const icone_remover = '<i class="fas fa-times-circle text-danger ml-2"></i>';

    let botao = '<button type="button" class="btn btn-sm btn-outline-danger font-weight-normal mr-2 mb-2" ';
    botao += 'style="font-size: 0.75rem;" value="' + municipio_escolhido + '">';
    botao += municipio_escolhido + icone_remover + '</button>';

    let input = '<input type="hidden" name="regioes.municipios[]" value="' + municipio_escolhido + '" />';

    return {'botao': botao, 'input': input};
}

function adicionarMunicipio(){

    $('#lista_municipios').on('click', '.item-municipio button', function(){
        if($('#municipios_escolhidos button').length >= 20)
            return false;

        let municipio_escolhido = $(this).val();
        const resp = btnRemoverMunicipio(municipio_escolhido);

        if($('#municipios_escolhidos button[value="' + municipio_escolhido + '"]').length == 0)
            $('#municipios_escolhidos').append($(resp.botao + resp.input));
    });
}

function removerMunicipio(){

    $('#municipios_escolhidos').on('click', 'button', function(){
        $(this).remove();
        $('input[type="hidden"][value="' + $(this).val() + '"]').remove();
    });
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

export function executar(){

    // Menu mobile representante
	$('#bars-representante').on('click', function(){
		$('#mobile-menu-representante').slideToggle();
	});

    $('[data-descricao]').on('click', function(){
        $.get('/representante/evento-boleto', {
            'descricao': $(this).attr('data-descricao'),
        });
    });

    $('.showLoading').on('click', function(){
        $('#rc-main').hide();
        $('#loading').show();
    });

    $('#linkShowCrimageDois').on('click', function(){
		$('#showCrimageDois').hide();
		$('#divCrimageDois').show();
	});

    buscarMunicipios();
    adicionarMunicipio();
    removerMunicipio();
    carregarMunicipios();
    removerTodosMunicipios();
}