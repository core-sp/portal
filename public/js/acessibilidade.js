$(document).ready(function(){

    $('#btn-contrast, #accesskeyContraste').on('click', function(){
		var list = [
			'body',
			'#espaco-representante',
			'#fixed-menu',
			'.home-title blockquote h4',
			'.branco-bg',
			'.cinza-claro-bg',
			'#eouv-calendario',
			'.preto',
			'footer',
			'.btn',
			'.linha-branca',
			'.box',
			'.box-dois',
			'.pesquisaLicitacao',
            '.pesquisaLicitacao label',
            '.inscricaoCurso label',
			'.licitacao-grid',
			'.licitacao-grid-bottom',
			'.edital-info .table',
			'.edital-download',
			'#pagina-cursos',
			'.curso-grid-content',
			'.nav-item',
			'.sidebar-header',
			'#sidebar',
			'.dropdown-item',
			'.tableSimulador',
			'label'
		];
		$(list).toggleClass('contraste');

		var listBranco = [
			'.linha-verde',
			'.linha-azul',
			'.linha-azul-escuro',
			'.bdo-info i'
		];
		$(listBranco).toggleClass('contraste-branco');

		var listCinza = [
			'.btn-atendimento',
			'.btn-buscaavancada',
			'.btn-voltar',
			'.btn-calendario',
			'.btn-como-foi',
			'.btn-edital',
			'.saiba-mais',
			'.btn-curso-grid',
            '.btn-curso-interna',
			'.btn-novo-core',
			'#dismiss'
		];
		$(listCinza).toggleClass('contraste-cinza');

		var justColor = [
			'p',
			'h6',
			'h5',
			'h4',
			'h3',
			'h2',
			'h1'
		];
		$(justColor).toggleClass('contraste-color');

		var bgFilter = [
			'.novo-core-box',
			'.beneficios-box',
			'.nav-link'
		];
		$(bgFilter).toggleClass('contraste-filter');

		var str = 'logo-core';
		var str2 = 'logo-brancore';
		var logo = $('#logo-header').attr('src').toString();
		if(logo.includes(str)) {
			$('#logo-header').attr('src',$('#logo-header').attr('src').replace(str,str2));
		} else {
			$('#logo-header').attr('src',$('#logo-header').attr('src').replace(str2,str));
		}
	});

	$('#increase-font').on('click', function(){
		// Change P
		newFontP = parseInt($('p').css('font-size')) + 1;
		maxP = 20;
		if(newFontP <= maxP) {
			$('p, .home-title h5, .conteudo-txt p, .conteudo-txt ul li, .tableSimulador td, ol li').css('font-size', newFontP);
		}
		// Change P Mini
		newFontPMini = parseInt($('p').css('font-size')) + 1;
		maxPMini = 19;
		if(newFontPMini <= maxPMini) {
			$('.conteudo-txt-mini p').css('font-size', newFontPMini);
		}
		// Change H5
		newFontH5 = parseInt($('h5').css('font-size')) + 1;
		maxH5 = 22;
		if(newFontH5 <= maxH5) {
			$('h5').css('font-size', newFontH5);
		}
		// Change H4
		newFontH4 = parseInt($('h4').css('font-size')) + 1;
		maxH4 = 26;
		if(newFontH4 <= maxH4) {
			$('h4').css('font-size', newFontH4);
		}
		// Change h2
		newFontH2 = parseInt($('h2').css('font-size')) + 1;
		maxH2 = 39;
		if(newFontH2 <= maxH2) {
			$('h2').css('font-size', newFontH2);
		}
	});

	$('#decrease-font').on('click', function(){
		// Change P
		newMinFontP = parseInt($('p').css('font-size')) - 1;
		minP = 12;
		if(newMinFontP > minP) {
			$('p, .home-title h5, .conteudo-txt p, .conteudo-txt ul li, .tableSimulador td, ol li').css('font-size', newMinFontP);
		}
		// Change P Mini
		newMinFontPMini = parseInt($('p').css('font-size')) - 1;
		minPMini = 11;
		if(newMinFontPMini <= minPMini) {
			$('.conteudo-txt-mini p').css('font-size', newMinFontPMini);
		}
		// Change H5
		newMinFontH5 = parseInt($('h5').css('font-size')) - 1;
		minH5 = 14;
		if(newMinFontH5 > minH5) {
			$('h5').css('font-size', newMinFontH5);
		}
		// Change H4
		newMinFontH4 = parseInt($('h4').css('font-size')) - 1;
		minH4 = 18;
		if(newMinFontH4 > minH4) {
			$('h4').css('font-size', newMinFontH4);
		}
		// Change h2
		newMinFontH2 = parseInt($('h2').css('font-size')) - 1;
		minH2 = 32;
		if(newMinFontH2 > minH2) {
			$('h2').css('font-size', newMinFontH2);
		}
	});
});