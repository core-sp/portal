export function executar(){

    // Menu mobile representante
	$('#bars-representante').on('click', function(){
		$('#mobile-menu-representante').slideToggle();
	});

    $('[data-descricao]').on('click', function(){
        $.get('/representante/evento-boleto', {
            'descricao': $(this).attr('data-descricao'),
        });
    });

    $('.showLoading').on('click', function(){
        $('#rc-main').hide();
        $('#loading').show();
    });

    $('#linkShowCrimageDois').on('click', function(){
		$('#showCrimageDois').hide();
		$('#divCrimageDois').show();
	});
}