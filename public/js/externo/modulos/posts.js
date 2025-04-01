const tamanho = {
    fb_share: [450, 550],
    twitter_share: [273, 450],
    linkedin_share: [700, 450],
};

function resizeContainer(){
    $(window).width() < 768 ? $('#pagina-noticias .container').attr('style', 'max-width: ' + ($(window).width() - 30) + 'px') : 
        $('#pagina-noticias .container').attr('style', '');
}

function visualizar(){

    $('.fb-share, .twitter-share, .linkedin-share').click(function(e) {
        let h_w = tamanho[$(this).attr('class').replace('-', '_')];

        e.preventDefault();
        window.open(
            $(this).attr('href'), 
            'fbShareWindow', 
            'height=' + h_w[0] + ', width=' + h_w[1] + ', top=' + 
            ($(window).height() / 2 - 275) + 
            ', left=' + ($(window).width() / 2 - 225) + 
            ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0'
        );
    });

    resizeContainer();

    $(window).resize(function(){
        resizeContainer();
    });

    // Scroll fixo das redes sociais
	let prenderTop = $('#prender').offset().top;

	$(window).on('scroll', function(){
        const id_top = $(window).width() < 768 ? 'menuResponsivo' : 'fixed-menu';
		let fixed_margin = $('#' + id_top).height() + 15;
		let topson = $(document).scrollTop() + fixed_margin;
		let topsonPrender = topson + $('#prender').height();
		let botson = $('.sociais-post').offset().top + $('.sociais-post').height();

		topson >= prenderTop ? $('#prender').addClass('prender').css('top', fixed_margin) : 
            $('#prender').removeClass('prender');
            
		topsonPrender >= botson ? $('#prender').addClass('prenderBot') : $('#prender').removeClass('prenderBot');
	});
}

export function executar(funcao){
    if(funcao == 'visualizar')
        return visualizar();
}
