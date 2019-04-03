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
			'.pesquisaLicitacao',
            '.pesquisaLicitacao label',
            '.inscricaoCurso label',
			'.licitacao-grid',
			'.licitacao-grid-bottom',
			'.edital-info .table',
			'.edital-download',
			'#pagina-cursos',
			'.curso-grid-content',
			'.nav-item'
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
			'.btn-edital',
			'.saiba-mais',
			'.btn-curso-grid',
            '.btn-curso-interna',
            '.btn-novo-core'
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
	});

});