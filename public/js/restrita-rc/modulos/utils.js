export function executar(){

    // Logout Representante
    $("#logout-representante").click(function(){
        let token = $('meta[name="csrf-token"]').attr('content');
        let link = "/representante/logout";
        let form = $('<form action="' + link + '" method="POST"><input type="hidden" name="_token" value="' + token + '"></form>');

        $('body').append(form);
        $(form).submit();
    });

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