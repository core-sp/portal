function aumentaDiminuiNovoValor(tag, aumenta = true, valor_final, valor_unit){

	let size = ($(tag).length > 0) && $(tag)[0].style.fontSize.length > 0 ? 
		$(tag)[0].style.fontSize.replace('px', '') : $(tag).css('font-size');

	valor_unit = parseFloat(valor_unit);
	valor_final = parseFloat(valor_final);
	size = parseFloat(size);

	return aumenta ? size + (valor_unit * valor_final) : size - valor_unit;
}

function aumentaOuDiminuiTags(){

	let cont = 0;

	$('#increase-font, #decrease-font').on('click AUMENTA', function(e){
		let aumenta_fonte = this.id == 'increase-font';
		let acao_click = e.detail._valor === undefined;
		let valor_final = !acao_click ? e.detail._valor : 1;
		let limite = aumenta_fonte ? cont >= 7 : cont == 0;

		cont = !acao_click ? e.detail._valor : cont;

		if(acao_click && limite)
			return;

		if(acao_click)
			aumenta_fonte ? cont++ : cont--;

		cont == 0 ? $('#increase-font, #decrease-font').removeClass('contraste-btn-acessibilidade') : 
			$('#increase-font, #decrease-font').addClass('contraste-btn-acessibilidade');

		let tamanho = aumentaDiminuiNovoValor('p', aumenta_fonte, valor_final, 1);
		$('p, .conteudo-txt p, .conteudo-txt ul li, .tableSimulador td, ol li').css('font-size', tamanho);
		$('.conteudo-txt-mini p').css('font-size', tamanho);

		tamanho = aumentaDiminuiNovoValor('.menu-principal .nav-item a', aumenta_fonte, valor_final, 1);
		$('.menu-principal .nav-item a').css('font-size', tamanho);

		for(let tag_header = 2, valor = 2; tag_header <= 6; tag_header++){
			if(tag_header == 3)
				continue;

			tamanho = aumentaDiminuiNovoValor('h' + tag_header, aumenta_fonte, valor_final, valor);
			$('h' + tag_header).css('font-size', tamanho);
		}

		sessionStorage.setItem("cont", cont);
    });

	if(parseInt(sessionStorage.getItem("cont")) > 0)
		$('#increase-font')[0].dispatchEvent(new CustomEvent("AUMENTA", {
			detail: {_valor: parseInt(sessionStorage.getItem("cont"))},
		}));
}

function mudaContraste(){

	$('#btn-contrast, #accesskeyContraste').on('click', function(){
		$([
			'body', '#espaco-representante', '#fixed-menu', '.home-title blockquote h4', '.branco-bg', '.cinza-claro-bg', '#eouv-calendario',
			'.preto', 'footer', '.btn', '.linha-branca', '.box', '.box-dois', '.pesquisaLicitacao', '.licitacao-grid', '.licitacao-grid-bottom', 
			'.edital-info .table', '.edital-download', '#pagina-cursos', '.curso-grid-content', '.nav-item', '.sidebar-header', '#sidebar', 
			'.dropdown-item', '.tableSimulador', '.menu-inteiro', '#header-principal', '.contatos-table', '.representante-content .list-group-item'
		]).toggleClass('contraste');

		$([
			'.linha-verde', '.linha-azul', '.linha-azul-escuro', '.saiba-mais-info i', '.consulta-linha hr', '.page-link', '.mr-item-selected a h6', 
			'#popup-campanha h4', 'a:contains("Termos de Uso")'
		]).toggleClass('contraste-branco');

		$([
			'.btn-atendimento', '.btn-buscaavancada', '.btn-voltar', '.btn-calendario', '.btn-como-foi', '.btn-edital', '.saiba-mais', '.btn-curso-grid',
            '.btn-curso-interna', '.btn-novo-core', '#dismiss', '.consulta-alert', 'label', '.menu-representante', '.representante-content', 
			'.representante-content .table-bordered'
		]).toggleClass('contraste-cinza');

		$([
			'p', 'h6', 'h5', 'h4', 'h3', 'h2', 'h1', 'li a'
		]).toggleClass('contraste-color');

		$([
			'.novo-core-box', '.beneficios-box', '.nav-link'
		]).toggleClass('contraste-filter');

		$([
			'.representante-content a'
		]).toggleClass('contraste-fundo-link');

		$([
			'.representante-content .text-success', '.representante-content .list-group-item .text-dark'
		]).toggleClass('contraste-fundo-branco');

		$('#btn-contrast').toggleClass('contraste-btn-acessibilidade');

		sessionStorage.setItem("contraste", $('body').hasClass('contraste'));
	});

	if(sessionStorage.getItem("contraste") == "true"){
		$('#btn-contrast').click();
	}
}

export function executar(local = 'interno'){
	aumentaOuDiminuiTags();
	mudaContraste();
}
