$(document).ready(function(){
	new WOW().init();
	
	$('#btn-contrast').on('click', function(){
		$('body').toggleClass('contraste');
		$('#espaco-representante').toggleClass('contraste');
	});
});