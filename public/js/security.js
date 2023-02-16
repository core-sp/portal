// biblioteca desenvolvida pelo dropbox: zxcvbn

var login = document.getElementById("login") == null ? undefined : document.getElementById("login").value;
var cpfCnpj = document.getElementById("cpfCnpj") == null ? undefined : document.getElementById("cpfCnpj").value;
var input = document.getElementById("password");

input.addEventListener("keyup", getTexto);

function getTexto(){
	var userEntrada = login != undefined ? login : cpfCnpj;
	var verificacao = zxcvbn(input.value, [userEntrada]);
	var senha = input.value == '' ? null : verificacao.score;
	isGood(senha);
}

function isGood(passed) {
	var password_strength = document.getElementById("password-text");
	var baseText = 'rounded progress-bar';

	//Display status.
	var strength = "";
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
	password_strength.innerHTML = strength;
}