// biblioteca desenvolvida pelo dropbox: zxcvbn.js em init.js

function isGood(passed) {

	let baseText = 'rounded progress-bar';
    let strength = "";
	
	switch (passed) {
	  case 0:
	  case 1:
		strength = "<div class='" + baseText + " bg-danger' style='width: 40%'><strong>Fraca</strong></div>";
		break;
	  case 2:
	  case 3:
		strength = "<div class='" + baseText + " bg-warning' style='width: 60%'><strong>Média</strong></div>";
		break;
	  case 4:
		strength = "<div class='" + baseText + " bg-success' style='width: 100%'><strong>Forte</strong></div>";
		break;
	  default:
		strength = "";
	}

	$("#password-text").html(strength);
}

function visualizar(){

    $('#password, #password_login').on("keyup", function(){
        let login = this.id == 'password_login' ? $('#' + this.id) : $('#login');
        let cpfCnpj = $('#cpfCnpj').length > 0 ? $('#cpfCnpj') : $('#cpf_cnpj');
        let userEntrada = login.length > 0 ? login.val() : cpfCnpj.val();
        let verificacao = zxcvbn(this.value, [userEntrada]);
	    let senha = this.value == '' ? null : verificacao.score;
	
        isGood(senha);
    });
}

export function executar(funcao){
    if(funcao == 'visualizar')
        return visualizar();
}
