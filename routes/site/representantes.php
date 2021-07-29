<?php

Route::prefix('representante')->group(function(){
    Route::get('/home', 'RepresentanteSiteController@index')->name('representante.dashboard');
    Route::get('/dados-gerais', 'RepresentanteSiteController@dadosGeraisView')->name('representante.dados-gerais');
    Route::get('/contatos', 'RepresentanteSiteController@contatosView')->name('representante.contatos.view');
    Route::get('/enderecos', 'RepresentanteSiteController@enderecosView')->name('representante.enderecos.view');
    Route::get('/inserir-contato', 'RepresentanteSiteController@inserirContatoView')->name('representante.inserir-ou-alterar-contato.view');
    Route::post('/inserir-contato', 'RepresentanteSiteController@inserirContato')->name('representante.inserir-ou-alterar-contato');
    Route::post('/deletar-contato', 'RepresentanteSiteController@deletarContato')->name('representante.deletar-contato');
    Route::get('/inserir-endereco', 'RepresentanteSiteController@inserirEnderecoView')->name('representante.inserir-endereco.view');
    Route::post('/inserir-endereco', 'RepresentanteSiteController@inserirEndereco')->name('representante.inserir-endereco');
    Route::get('/situacao-financeira', 'RepresentanteSiteController@listaCobrancas')->name('representante.lista-cobrancas');
    Route::get('/verifica-email/{token}', 'RepresentanteSiteController@verificaEmail')->name('representante.verifica-email');
    Route::get('/evento-boleto', 'RepresentanteSiteController@eventoBoleto')->name('representante.evento-boleto');
    // Login e Cadastro
    Route::get('/login', 'Auth\RepresentanteLoginController@showLoginForm')->name('representante.login');
    Route::post('/login', 'Auth\RepresentanteLoginController@login')->name('representante.login.submit');
    Route::get('/logout', 'Auth\RepresentanteLoginController@logout')->name('representante.logout');
    Route::get('/cadastro', 'RepresentanteSiteController@cadastroView')->name('representante.cadastro');
    Route::post('/cadastro', 'RepresentanteSiteController@cadastro')->name('representante.cadastro.submit');
    // Reset password routes
    Route::get('/password/reset', 'Auth\RepresentanteForgotPasswordController@showLinkRequestForm')->name('representante.password.request');
    Route::post('/password/email', 'Auth\RepresentanteForgotPasswordController@sendResetLinkEmail')->name('representante.password.email');
    Route::get('/password/reset/{token}', 'Auth\RepresentanteResetPasswordController@showResetForm')->name('representante.password.reset');
    Route::post('/password/reset', 'Auth\RepresentanteResetPasswordController@reset')->name('representante.password.update');
    // Reset email routes
    Route::get('/email/reset', 'Auth\RepresentanteForgotEmailController@resetEmailView')->name('representante.email.reset.view');
    Route::post('/email/reset', 'Auth\RepresentanteForgotEmailController@resetEmail')->name('representante.email.reset');

    // Rotas para emissão de Certidão
    Route::get('/emitir-certidao', 'RepresentanteSiteController@emitirCertidaoView')->name('representante.emitirCertidaoView');
    Route::post('/emitir-certidao', 'RepresentanteSiteController@emitirCertidao')->name('representante.emitirCertidao');
    Route::get('/baixar-certidao', 'RepresentanteSiteController@baixarCertidao')->name('representante.baixarCertidao');
    

    // SIMULADOR_REFIS - Rota para simulador Refis
    //Route::get('/simulador-refis', 'RepresentanteSiteController@simuladorRefis')->name('representante.simuladorRefis');

    // Solicitar cédula
    Route::get('/cedulas', 'RepresentanteSiteController@cedulasView')->name('representante.solicitarCedulaView');
    Route::get('/inserir-solicita-cedula', 'RepresentanteSiteController@inserirsolicitarCedulaView')->name('representante.inserirSolicitarCedulaView');
    Route::post('/inserir-solicita-cedula', 'RepresentanteSiteController@inserirsolicitarCedula')->name('representante.inserirSolicitarCedula');
});