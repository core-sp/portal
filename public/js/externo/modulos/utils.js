let tamanho_anterior = $(window).width();

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

export function executar(local = 'externo'){
    menuResponsivo();
    cookies();

    // vídeo popup
    if($('#popup-campanha').length > 0)
        $('#popup-campanha').modal('show');

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

    // Lightbox
    $(document).on('click', '[data-toggle="lightbox"]', function(e) {
        if($(window).width() > 767) {
            e.preventDefault();
            $(this).ekkoLightbox();
        }
    });

    // Logout Representante
    $("#logout-representante").click(function(){
        let token = $('meta[name="csrf-token"]').attr('content');
        let link = "/representante/logout";
        let form = $('<form action="' + link + '" method="POST"><input type="hidden" name="_token" value="' + token + '"></form>');

        $('body').append(form);
        $(form).submit();
    });
    
    lazyLoad();
    resizePagination();
    
    $(window).resize(function(){
        lazyLoad(true);
        resizePagination();
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
};

export let scripts_para_importar = {
    modulo: ['lazy-load-img', 'cep'], 
    local: ['modulos/', 'modulos/']
};