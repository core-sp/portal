
var enrollment = function() { 
	// Invocar no botão da compra e guardar o retorno. Obs: Só invocar com sucesso do preenchimento dos campos obrigatórios no front-end. 
	//Inserir regra de negócio se tiver. 
	GN3DS.init(function(response) { 
		console.log(response);
		//Inicia o processo 3ds2.1, realiza um request para o endpoint GenerateToken e cria uma sessão no front-end (fingerprint) do navaegador. 
		//Inserir regra de negócio se tiver. 
		if(response != null && response.status >= 200 && response.status <= 299) { 
			GN3DS.authentication(function(response2) { 
				//Inicia a authenticação do 3ds2.1, realiza um request ao endpoint authentications verifica se existe ou desafio, havendo um desafio (não silencioso) a executa um request ao (authentication-results) 
				//Inserir regra de negócio se tiver. 
				if(response2 != null && response2.status >= 200 && response2.status <= 299) { 
					//Tratar o sucesso. 
				} else { //Tratar o erro. 
				} 
			}); 
		} else { 
			alert("Erro ao inciar a autenticação 3ds2.1"); 
		} 
	}); 
}