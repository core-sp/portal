let tamanho_anterior = $(window).width();

function buscarMunicipios(){

    if($('#bdo_tipo').length > 0){
        let rc = $('#bdo_tipo').val() != 'representantes';
        $('#buscar_municipios').prop('disabled', rc);
    }

    $('#bdo_tipo').change(function(){
        let rc = $(this).val() != 'representantes';
        $('#buscar_municipios').prop('disabled', rc);
    });
    
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
            $(inicio + '<button class="btn btn-link text-left btnMunicipio" type="button" value="' + x + '" style="font-size: 0.85rem;">' + icone + x + '</button>' + final)
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

        $("#lista_municipios").css({
            'border-style': "solid",
            'border-radius': '3%',
            'border-color': '#cbcfcf',
            'border-top': '0'
        });
    });
}

function adicionarMunicipio(){

    $('#lista_municipios').on('click', '.item-municipio button', function(){
        let municipio_escolhido = $(this).val();

        if($('#municipios_escolhidos').val() != municipio_escolhido)
            $('#municipios_escolhidos').val(municipio_escolhido);

        $('#lista_municipios').attr('style', '').html('');
        $("#buscar_municipios").val('');
    });
}

function removerMunicipio(){

    $('#btn_apagar_municipio').click(function(){
        $('#municipios_escolhidos').val('Qualquer');
    });
}

function menuResponsivo(){

    // Conteúdo do menu
    $('#sidebarContent, #append-menu').html($('#menu-principal').html());

	$('#sidebarBtn, .overlay, #dismiss').on('click', function(){
		$('#sidebar').toggleClass('leftando');
		$('.overlay').toggleClass('active');
	});

	$('.dropdown').on('show.bs.dropdown', function() {
		$(this).find('.dropdown-menu').first().stop(true, true).slideDown(200);
	});

	$('.dropdown').on('hide.bs.dropdown', function() {
		$(this).find('.dropdown-menu').first().stop(true, true).slideUp(200);
		$('.sub-dropdown').removeClass('menu-hoverable');
		$('.sub-dropdown-menu').hide();

		if($(window).width() < 768)
			$('.dropdown-item').removeClass('branco-azul');
	});

	// Segundo nível do menu
	$('.sub-dropdown').on('click', function(e){
		e.stopPropagation();

		$(this).toggleClass('menu-hoverable');
		$('.sub-dropdown').not($(this)).removeClass('menu-hoverable');
		$(this).children('.sub-dropdown-menu').toggle('slide', { direction: "left" }, 200);
		$('.sub-dropdown-menu').not($(this).children('.sub-dropdown-menu')).hide();

		if($(window).width() < 768) {
			$(this).children('.dropdown-item').toggleClass('branco-azul');
			$('.dropdown-item').not($(this).children('.dropdown-item')).removeClass('branco-azul');
		}
	});
}

function cookies(){
    
    if(!localStorage.pureJavaScriptCookies)
        $('.box-cookies').removeClass('hide');

    $('.btn-cookies').on('click', function(){
        $('.box-cookies').addClass('hide');
        localStorage.setItem("pureJavaScriptCookies", "accept");

        if(typeof window.clarity === 'function')
            window.clarity('consent');
	});
}

function importLazyLoadImg(elemento){
    const link = $('[data-modulo-id="lazy-load-img"]').attr('src');

    import(link)
    .then((module) => {
        document.dispatchEvent(new CustomEvent("LOG_SUCCESS_INIT", {
            detail: {tipo: 0, situacao: 4, nome: 'lazy-load-img', url: link}
        }));
        module.default(null, '0px', 0, elemento);
    })
    .catch((err) => {
        document.dispatchEvent(new CustomEvent("LOG_ERROR_INIT", {
            detail: {error: err}
        }));
    });
}

function lazyLoad(resize = false){

    let elemento = '.lazy-loaded-image.lazy';

    if($('.carousel-item').length > 0)
        elemento += $(window).width() > 576 ? ', .carousel-item .hide-576' : ', .carousel-item .show-576';

    if($(elemento).length == 0)
        return;
    
    if(!resize){
        importLazyLoadImg(elemento);
        return;
    }

    let mudou_tamanho = tamanho_anterior <= 576 ? $(window).width() > 576 : $(window).width() <= 576;

    if(mudou_tamanho){
        importLazyLoadImg(elemento);
        tamanho_anterior = $(window).width();
    }
}

function importCep(){
    const link = $('[data-modulo-id="cep"]').attr('src');

    import(link)
    .then((module) => {
        document.dispatchEvent(new CustomEvent("LOG_SUCCESS_INIT", {
            detail: {tipo: 0, situacao: 3, nome: 'cep', url: link}
        }));
        module.getCep();
    })
    .catch((err) => {
        document.dispatchEvent(new CustomEvent("LOG_ERROR_INIT", {
            detail: {error: err}
        }));
    });
}

function confereCep(retorno){

    if(retorno == 'encontrado'){
        document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
        return;
    }

    if(retorno == 'buscando')
        document.dispatchEvent(new CustomEvent("MSG_GERAL_CARREGAR"));

    if(retorno != 'buscando')
        document.dispatchEvent(new CustomEvent("MSG_GERAL_CONTEUDO", {
            detail: {texto: retorno}
        }));
}

function resizePagination(){

    if($('.pagination').length > 0)
        $(window).width() <= 576 ? $('.pagination').addClass('pagination-sm') : $('.pagination').removeClass('pagination-sm');
}

function galeriaLigthbox(obj){

    $(obj).ekkoLightbox({
        onHide: function(){
            if((this._$modal.length > 0) && (this._$modal[0].contains(document.activeElement)))
                document.activeElement.blur();
        }
    });
}

export function executar(local = 'externo'){

    lazyLoad();
    resizePagination();
    
    $(window).resize(function(){
        lazyLoad(true);
        resizePagination();
    });
    
    menuResponsivo();
    cookies();

    // vídeo popup
    if($('#popup-campanha').length > 0)
        $('#popup-campanha').modal('show');

    $("#popup-campanha").on('hide.bs.modal', function(){
        if(this.contains(document.activeElement))
            document.activeElement.blur();
    });

    // Menu principal fixo
	$(document).on('scroll', function(){
		if($(window).width() > 767)
			$(document).scrollTop() > 300 ? $('#fixed-menu').slideDown(150) : $('#fixed-menu').hide();

        if($(window).width() <= 767){
            $(document).scrollTop() > 300 ? 
                $('#menuResponsivo').addClass('fixed-top').next().find('.sidebar-header, #dismiss').addClass('invisible') : 
                $('#menuResponsivo').removeClass('fixed-top').next().find('.sidebar-header, #dismiss').removeClass('invisible');
        }
        
	});

    if($('[data-toggle="lightbox"]').length > 0)
        $(document).on('click', '[data-toggle="lightbox"]', function(e) {
            e.preventDefault();
            document.dispatchEvent(new CustomEvent("LIB_GALERIA", {
                detail: {funcao: galeriaLigthbox, propriedade: this}
            }));
        });

    // Logout Representante
    $("#logout-representante").click(function(){
        let token = $('meta[name="csrf-token"]').attr('content');
        let link = "/representante/logout";
        let form = $('<form action="' + link + '" method="POST"><input type="hidden" name="_token" value="' + token + '"></form>');

        $('body').append(form);
        $(form).submit();
    });

    // Texto do link com quantidade de caracteres que ultrapassam a largura do conteúdo
    $('.conteudo-txt').find('a').each(function(){
        if($('.conteudo-txt').width() < $(this).width())
            $(this).addClass('text-break');
    });
        
    $(".custom-file-input").on("change", function(e) {
        let fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });

    if($("#cep").length > 0){
        importCep();
        $("#cep").on('CEP CEP_ERRO', function(e){
            confereCep(e.detail);
        });
    }

    $('.saiba-mais').on('click', function(){
        let saibamais = $(this);
        let info = saibamais.prev('.saiba-mais-info');

        info.slideToggle(function(){
            let texto = info.is(':visible') ? 'Menos' : 'Mais';

            saibamais.html(saibamais.html().replace(/Menos|Mais/, texto))
            .children()
            .toggleClass('fa-angle-double-up').toggleClass('fa-angle-double-down');
        });
    });

    $('.loadingPagina').on('click', function(){
		document.dispatchEvent(new CustomEvent("MSG_GERAL_CARREGAR"));
	});

    if($('.loadingPagina').length > 0)
        $('input, select, textarea').on("invalid", function(e){
            document.dispatchEvent(new CustomEvent("MSG_GERAL_FECHAR"));
        });

    // Temporário utils -> restrita-rc também
    buscarMunicipios();
    adicionarMunicipio();
    removerMunicipio();
};

export let scripts_para_importar = {
    modulo: ['lazy-load-img', 'cep'], 
    local: ['modulos/', 'modulos/']
};