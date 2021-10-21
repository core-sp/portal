<?php

Route::get('/', 'SiteController@index')->name('site.home');

/*
 * ROTAS FECHADAS
 */
Route::prefix('admin')->group(function() {
  // Rotas de login
  Auth::routes();

  // Rotas de Configuração
  Route::prefix('perfil')->group(function(){
    Route::get('/', 'UserController@infos')->name('admin.info');
    Route::get('/senha', 'UserController@senha');
    Route::put('/senha', 'UserController@changePassword');
  });

  // Rotas para chamados
  Route::prefix('chamados')->group(function(){
    Route::get('/', 'ChamadoController@index')->name('chamados.lista');
    Route::get('/busca', 'ChamadoController@busca');
    Route::get('/criar', 'ChamadoController@create');
    Route::post('/criar', 'ChamadoController@store');
    Route::get('/editar/{id}', 'ChamadoController@edit');
    Route::put('/editar/{id}', 'ChamadoController@update');
    Route::put('/resposta/{id}', 'ChamadoController@resposta');
    Route::get('/ver/{id}', 'ChamadoController@show');
    Route::delete('/apagar/{id}', 'ChamadoController@destroy');
    Route::get('/concluidos', 'ChamadoController@lixeira');
    Route::get('/restore/{id}', 'ChamadoController@restore');
  });
  
  // Rotas de páginas
  require('admin/paginas.php');
  // Rotas de regionais
  require('admin/regionais.php');
  // Rotas de notícias
  require('admin/noticias.php');
  // Rotas de licitações
  require('admin/licitacoes.php');
  // Rotas de concursos
  require('admin/concursos.php');
  // Rotas de concursos
  require('admin/cursos.php');
  // Rotas do mapa de fiscalização (possivelmente será removido pois os dados virão do GERENTI)
  require('admin/fiscalizacao.php');
 
  require('admin/compromissos.php');


  //require('admin/fiscalizacao.php');

  // Rotas para usuários
  Route::prefix('usuarios')->group(function(){
    Route::get('/', 'UserController@index')->name('usuarios.lista');
    Route::get('/busca', 'UserController@busca');
    Route::get('/criar', 'UserController@create');
    Route::post('/criar', 'UserController@store');
    Route::get('/editar/{id}', 'UserController@edit');
    Route::put('/editar/{id}', 'UserController@update');
    Route::delete('/apagar/{id}', 'UserController@destroy');
    Route::get('/lixeira', 'UserController@lixeira');
    Route::get('/restore/{id}', 'UserController@restore');
    // Lida com a parte de Perfis
    Route::get('/perfis', 'PerfilController@index')->name('perfis.lista');
    Route::get('/perfis/criar', 'PerfilController@create');
    Route::post('/perfis/criar', 'PerfilController@store');
    Route::get('/perfis/editar/{id}', 'PerfilController@edit');
    Route::put('/perfis/editar/{id}', 'PerfilController@update');
    Route::delete('/perfis/apagar/{id}', 'PerfilController@destroy');
  });

  

  // Rota para Balcão de Oportunidades
  Route::prefix('bdo')->group(function(){
    Route::get('/', 'BdoOportunidadeController@index')->name('bdooportunidades.lista');
    Route::get('/busca', 'BdoOportunidadeController@busca')->name('bdooportunidades.busca');
    Route::get('/criar/{id}', 'BdoOportunidadeController@create')->name('bdooportunidades.create');
    Route::post('/criar', 'BdoOportunidadeController@store')->name('bdooportunidades.store');
    Route::get('/editar/{id}', 'BdoOportunidadeController@edit')->name('bdooportunidades.edit');
    Route::put('/editar/{id}', 'BdoOportunidadeController@update')->name('bdooportunidades.update');
    Route::delete('/apagar/{id}', 'BdoOportunidadeController@destroy')->name('bdooportunidades.destroy');
    // Lida com as empresas
    Route::get('/empresas', 'BdoEmpresaController@index')->name('bdoempresas.lista');
    Route::get('/empresas/busca', 'BdoEmpresaController@busca')->name('bdoempresas.busca');
    Route::get('/empresas/criar', 'BdoEmpresaController@create')->name('bdoempresas.create');
    Route::post('/empresas/criar', 'BdoEmpresaController@store')->name('bdoempresas.store');
    Route::get('/empresas/editar/{id}', 'BdoEmpresaController@edit')->name('bdoempresas.edit');
    Route::put('/empresas/editar/{id}', 'BdoEmpresaController@update')->name('bdoempresas.update');
    Route::delete('/empresas/apagar/{id}', 'BdoEmpresaController@destroy')->name('bdoempresas.destroy');
  });

  // Rota para Agendamentos
  Route::prefix('agendamentos')->group(function(){
    Route::get('/', 'AgendamentoController@index')->name('agendamentos.lista');
    Route::get('/busca', 'AgendamentoController@busca')->name('agendamentos.busca');
    Route::put('/status', 'AgendamentoController@updateStatus')->name('agendamentos.updateStatus');
    Route::get('/filtro', 'AgendamentoController@index')->name('agendamentos.filtro');
    Route::get('/pendentes', 'AgendamentoController@pendentes')->name('agendamentos.pendentes');
    Route::get('/editar/{id}', 'AgendamentoController@edit')->name('agendamentos.edit');
    Route::put('/editar/{id}', 'AgendamentoController@update')->name('agendamentos.update');
    Route::post('/reenviar-email/{id}', 'AgendamentoController@reenviarEmail')->name('agendamentos.reenviarEmail');
    // Lida com bloqueios
    Route::get('/bloqueios', 'AgendamentoBloqueioController@index')->name('agendamentobloqueios.lista');
    Route::get('/bloqueios/busca', 'AgendamentoBloqueioController@busca');
    Route::get('/bloqueios/criar', 'AgendamentoBloqueioController@create');
    Route::post('/bloqueios/criar', 'AgendamentoBloqueioController@store');
    Route::get('/bloqueios/editar/{id}', 'AgendamentoBloqueioController@edit');
    Route::put('/bloqueios/editar/{id}', 'AgendamentoBloqueioController@update');
    Route::delete('/bloqueios/apagar/{id}', 'AgendamentoBloqueioController@destroy');
  });

  // Rota para Newsletter
  Route::prefix('newsletter')->group(function(){
    Route::get('/download', 'NewsletterController@download');
  });

  // Rota para Home Imagens
  Route::prefix('imagens')->group(function(){
    Route::get('/bannerprincipal', 'HomeImagemController@editBannerPrincipal');
    Route::put('/bannerprincipal', 'HomeImagemController@updateBannerPrincipal');
  });

  // Rotas para Blog Posts
  require('admin/posts.php');

  // Rotas para Representantes
  Route::get('/representantes', 'RepresentanteController@index');
  Route::get('/representantes/busca', 'RepresentanteController@busca');
  Route::get('/representantes/buscaGerenti', 'RepresentanteController@buscaGerentiView');
  Route::get('/representantes/buscaGerenti/resultado', 'RepresentanteController@buscaGerenti')->name('admin.representante.buscaGerenti');
  Route::get('/representantes/info', 'RepresentanteController@representanteInfo');
  Route::get('/representantes/baixar-certidao', 'RepresentanteController@baixarCertidao')->name('admin.representante.baixarCertidao');

  // Mudança de endereço
  Route::get('/representante-enderecos', 'RepresentanteEnderecoController@index');
  Route::get('/representante-enderecos/busca', 'RepresentanteEnderecoController@busca')->name('representante-endereco.busca');
  Route::get('/representante-enderecos/{id}', 'RepresentanteEnderecoController@show')->name('admin.representante-endereco.show');
  Route::post('/representante-enderecos/inserir', 'RepresentanteEnderecoController@inserirEnderecoGerenti')->name('admin.representante-endereco.post');
  Route::post('/representante-enderecos/recusar', 'RepresentanteEnderecoController@recusarEndereco')->name('admin.representante-endereco-recusado.post');
  Route::get('/representante-enderecos-visualizar', 'RepresentanteEnderecoController@visualizarComprovante')->name('representante-endereco.visualizar');
  Route::get('/representante-enderecos-baixar', 'RepresentanteEnderecoController@baixarComprovante')->name('representante-endereco.baixar');

  // Termo de Consentimento, baixar CSV
  Route::get('/termo-consentimento/download', 'TermoConsentimentoController@download')->name('termo.consentimento.download');

});

/*
 * ROTAS ABERTAS
 */
Route::prefix('/')->group(function() {
  // Rotas de admin abertas
  Route::get('admin', 'AdminController@index')->name('admin');
  Route::get('admin/logout', 'Auth\LoginController@logout')->name('logout');

  // Regionais
  require('site/regionais.php');
  // Notícias
  require('site/noticias.php');  
  // Licitações
  require('site/licitacoes.php');
  // Concursos
  require('site/concursos.php');
  // Cursos
  require('site/cursos.php');
  // Representantes
  require('site/representantes.php');

  Route::prefix('pre-representante')->group(function(){
    // Login e Cadastro
    Route::get('/login', 'Auth\PreRepresentanteLoginController@showLoginForm')->name('prerepresentante.login');
    Route::post('/login', 'Auth\PreRepresentanteLoginController@login')->name('prerepresentante.login.submit');
    Route::post('/logout', 'Auth\PreRepresentanteLoginController@logout')->name('prerepresentante.logout');
    Route::get('/cadastro', 'PreRepresentanteSiteController@cadastroView')->name('prerepresentante.cadastro');
    Route::post('/cadastro', 'PreRepresentanteSiteController@cadastro')->name('prerepresentante.cadastro.submit');
    Route::get('/verifica-email/{token}', 'PreRepresentanteSiteController@verificaEmail')->name('prerepresentante.verifica-email');
    // Reset password routes
    Route::get('/password/reset', 'Auth\PreRepresentanteForgotPasswordController@showLinkRequestForm')->name('prerepresentante.password.request');
    Route::post('/password/email', 'Auth\PreRepresentanteForgotPasswordController@sendResetLinkEmail')->name('prerepresentante.password.email');
    Route::get('/password/reset/{token}', 'Auth\PreRepresentanteResetPasswordController@showResetForm')->name('prerepresentante.password.reset');
    Route::post('/password/reset', 'Auth\PreRepresentanteResetPasswordController@reset')->name('prerepresentante.password.update');
  });
  
  //Balcão de Oportunidades
  Route::get('balcao-de-oportunidades', 'BdoSiteController@index')->name('bdosite.index');
  Route::get('balcao-de-oportunidades/busca', 'BdoSiteController@buscaOportunidades')->name('bdosite.buscaOportunidades');
  Route::get('anunciar-vaga', 'BdoSiteController@anunciarVagaView')->name('bdosite.anunciarVagaView');
  Route::post('anunciar-vaga', 'BdoSiteController@anunciarVaga')->name('bdosite.anunciarVaga');
  Route::get('/info-empresa/{cnpj}', 'BdoEmpresaController@apiGetEmpresa')->name('bdosite.apiGetEmpresa');
  
  // Busca geral
  Route::get('/busca', 'SiteController@busca');

  // Agendamentos
  Route::get('agendamento', 'AgendamentoSiteController@formView')->name('agendamentosite.formview');
  Route::post('agendamento', 'AgendamentoSiteController@store')->name('agendamentosite.store');
  Route::get('checa-horarios', 'AgendamentoSiteController@checaHorarios')->name('agendamentosite.checaHorarios');
  Route::get('checa-mes', 'AgendamentoSiteController@checaMes')->name('agendamentosite.checaMes');
  Route::get('agendamento-consulta', 'AgendamentoSiteController@consultaView')->name('agendamentosite.consultaView');
  Route::get('agendamento-consulta/busca', 'AgendamentoSiteController@consulta')->name('agendamentosite.consulta');
  Route::put('agendamento-consulta/busca', 'AgendamentoSiteController@cancelamento')->name('agendamentosite.cancelamento');

  // Newsletter
  Route::post('newsletter', 'NewsletterController@store');

  // Feiras
  Route::get('feiras', 'SiteController@feiras');

  // Fiscalização
  Route::get('acoes-da-fiscalizacao', 'SiteController@acoesFiscalizacao')->name('fiscalizacao.acoesfiscalizacao');
  // Rotas para o SIG (Sistema de Informação Geográfico)
  Route::get('/mapa-fiscalizacao', 'FiscalizacaoController@mostrarMapa')->name('fiscalizacao.mapa');
  Route::get('/mapa-fiscalizacao/{id}', 'FiscalizacaoController@mostrarMapaPeriodo')->name('fiscalizacao.mapaperiodo');

  // Fiscalização
  Route::get('espaco-do-contador', 'SiteController@espacoContador')->name('fiscalizacao.espacoContador');

  // Simulador
  Route::get('simulador', 'SimuladorController@view');
  Route::post('simulador', 'SimuladorController@extrato');

  // Consulta de Situação
  Route::get('consulta-de-situacao', 'ConsultaSituacaoController@consultaView');
  Route::post('consulta-de-situacao', 'ConsultaSituacaoController@consulta');

  // Blog
  Route::get('blog', 'PostsController@blogPage');
  Route::get('blog/{slug}', 'PostsController@show');

  // Anuidade ano vigente
  Route::get('/anuidade-ano-vigente', 'AnoVigenteSiteController@anoVigenteView')->name('anuidade-ano-vigente');
  Route::post('/anuidade-ano-vigente', 'AnoVigenteSiteController@anoVigente');

  Route::get('/chat', function(){
    return view('site.chat');
  });

  Route::get('/agenda-institucional', 'SiteController@agendaInstitucional')->name('agenda-institucional');
  Route::get('/agenda-institucional/{data}', 'SiteController@agendaInstitucionalByData')->name('agenda-institucional-data');

  // Página do termo de consentimento com o acesso via email
  Route::get('/termo-de-consentimento', 'TermoConsentimentoController@termoConsentimentoView')->name('termo.consentimento.view');
  Route::post('/termo-de-consentimento', 'TermoConsentimentoController@termoConsentimento')->name('termo.consentimento.post');
  Route::get('/termo-consentimento-pdf', 'TermoConsentimentoController@termoConsentimentoPdf')->name('termo.consentimento.pdf');

  // Páginas (deve ser inserido no final do arquivo de rotas)
  Route::get('{slug}', 'PaginaController@show')->name('paginas.site');
});