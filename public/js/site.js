$(document).ready(function(){
	$('#btn-contrast').on('click', function(){
		$('body').toggleClass('contraste');
		$('#espaco-representante').toggleClass('contraste');
	});
});