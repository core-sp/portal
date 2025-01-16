let tamanho_anterior = window.innerWidth;

function menuResponsivo(){

    // Conteúdo do menu
    $('#sidebarContent').html($('#menu-principal').html());

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
        window.clarity('consent');
	});
}

function importLazyLoadImg(elemento){
    const link = $('#modulo-lazy-load-img').attr('src');

    import(link)
    .then((module) => {
        console.log('Módulo lazy-load-img importado por principal e carregado.');
        console.log('Local do módulo: ' + link);
        module.default(null, '0px', 0, elemento);
    })
    .catch((err) => {
        console.log(err);
        alert('Erro na página! Módulo não carregado! Tente novamente mais tarde!');
    });
}

function lazyLoad(resize = false){

    let elemento = '.lazy-loaded-image.lazy';

    if($('.carousel-item').length > 0)
        elemento += window.innerWidth > 576 ? ', .carousel-item .hide-576' : ', .carousel-item .show-576';

    if($(elemento).length == 0)
        return;
    
    if(!resize){
        importLazyLoadImg(elemento);
        return;
    }

    let mudou_tamanho = tamanho_anterior <= 576 ? window.innerWidth > 576 : window.innerWidth <= 576;

    if(mudou_tamanho){
        importLazyLoadImg(elemento);
        tamanho_anterior = window.innerWidth;
    }
}

function importCep(){
    const link = $('#modulo-cep').attr('src');

    import(link)
    .then((module) => {
        console.log('Módulo cep importado por principal e carregado.');
        console.log('Local do módulo: ' + link);
        module.getCep();
    })
    .catch((err) => {
        console.log(err);
        alert('Erro na página! Módulo não carregado! Tente novamente mais tarde!');
    });
}

function confereCep(retorno){

    if(retorno == 'encontrado'){
        $("#msgGeral").modal('hide');
        return;
    }

    let texto = retorno == 'buscando' ? '<div class="spinner-grow text-info"></div>' : retorno;

    $("#msgGeral .modal-header, #msgGeral .modal-footer").hide();
    $("#msgGeral .modal-body").addClass('text-center').html(texto);
    $("#msgGeral").modal({backdrop: "static", keyboard: false, show: true});

    setTimeout(function(){
        $("#msgGeral").modal('hide');
    }, 2250);
}

export function executar(local = 'externo'){
    menuResponsivo();
    cookies();

    // vídeo popup
    if($('#popup-campanha').length > 0)
        $('#popup-campanha').modal('show');

    // Menu principal fixo se maior que 767
	$(window).scroll(function(){
		if($(window).width() > 767)
			$(document).scrollTop() > 300 ? $('#fixed-menu').slideDown(150) : $('#fixed-menu').hide();
	});

    // Lightbox
    $(document).on('click', '[data-toggle="lightbox"]', function(e) {
        if($(window).width() > 767) {
            e.preventDefault();
            $(this).ekkoLightbox();
        }
    });

    lazyLoad();

    $(window).resize(function(){
        lazyLoad(true);
    });

    $(".custom-file-input").on("change", function(e) {
        let fileName = e.target.files[0].name;
        $(this).next('.custom-file-label').html(fileName);
    });

    $("#msgGeral").on('hide.bs.modal', function(){
        $(this).find('.modal-body, .modal-title, .modal-footer').html('');
    });

    if($("#cep").length > 0){
        importCep();
        $("#cep").on('CEP CEP_ERRO', function(e){
            confereCep(e.detail);
        });
    }
};

export let scripts_para_importar = {
    modulo: ['lazy-load-img', 'cep'], 
    local: ['modulos/', 'modulos/']
};