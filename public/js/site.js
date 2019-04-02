$(document).ready(function(){	
	$('#btn-contrast').on('click', function(){
		$('body').toggleClass('contraste');
		$('#espaco-representante').toggleClass('contraste');
	});
	$('.saiba-mais').on('click', function(){
		var saibamais = $(this);
		var bdoinfo = saibamais.prev('.bdo-info');
		bdoinfo.slideToggle(function(){
			if(bdoinfo.is(':visible')) {
				saibamais.html('<i class="fas fa-angle-double-up"></i>&nbsp;&nbsp;Menos Detalhes');
			} else {
				saibamais.html('<i class="fas fa-angle-double-down"></i>&nbsp;&nbsp;Mais Detalhes');
			}
		});
	});
});

$(window).scroll(function(){
	if ($(document).scrollTop() > 300) {
		$('#fixed-menu').slideDown(150);
	} else {
		$('#fixed-menu').hide();
	}
});

var primeira = document.getElementById('menu-principal');
var segunda = document.getElementById('append-menu');

segunda.innerHTML = primeira.innerHTML;