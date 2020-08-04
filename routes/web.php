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
    Route::get('/busca', 'BdoOportunidadeController@busca');
    Route::get('/criar', 'BdoOportunidadeController@create');
    Route::post('/criar', 'BdoOportunidadeController@store');
    Route::get('/editar/{id}', 'BdoOportunidadeController@edit');
    Route::put('/editar/{id}', 'BdoOportunidadeController@update');
    Route::delete('/apagar/{id}', 'BdoOportunidadeController@destroy');
    // Lida com as empresas
    Route::get('/empresas', 'BdoEmpresaController@index')->name('bdoempresas.lista');
    Route::get('/empresas/busca', 'BdoEmpresaController@busca');
    Route::get('/empresas/criar', 'BdoEmpresaController@create');
    Route::post('/empresas/criar', 'BdoEmpresaController@store');
    Route::get('/empresas/editar/{id}', 'BdoEmpresaController@edit');
    Route::put('/empresas/editar/{id}', 'BdoEmpresaController@update');
    Route::delete('/empresas/apagar/{id}', 'BdoEmpresaController@destroy');
  });

  // Rota para Agendamentos
  Route::prefix('agendamentos')->group(function(){
    Route::get('/', 'AgendamentoController@index')->name('agendamentos.lista');
    Route::get('/busca', 'AgendamentoController@busca');
    Route::put('/status', 'AgendamentoController@updateStatus');
    Route::get('/filtro', 'AgendamentoController@index');
    Route::get('/pendentes', 'AgendamentoController@pendentes');
    Route::get('/editar/{id}', 'AgendamentoController@edit');
    Route::put('/editar/{id}', 'AgendamentoController@update');
    Route::post('/reenviar-email/{id}', 'AgendamentoController@reenviarEmail');
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
  Route::post('/representantes/buscaGerenti', 'RepresentanteController@buscaGerenti');
  Route::post('/representantes/info', 'RepresentanteController@representanteInfo');
  Route::get('/representante-enderecos', 'RepresentanteEnderecoController@index');
  Route::get('/representante-enderecos/{id}', 'RepresentanteEnderecoController@show')->name('admin.representante-endereco.show');
  Route::post('/representante-enderecos/inserir', 'RepresentanteEnderecoController@inserirEnderecoGerenti')->name('admin.representante-endereco.post');
  Route::post('/representante-enderecos/recusar', 'RepresentanteEnderecoController@recusarEndereco')->name('admin.representante-endereco-recusado.post');
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
  
  //Balcão de Oportunidades
  Route::get('balcao-de-oportunidades', 'BdoSiteController@index');
  Route::get('balcao-de-oportunidades/busca', 'BdoSiteController@buscaOportunidades');
  Route::get('anunciar-vaga', 'BdoSiteController@anunciarVagaView');
  Route::post('anunciar-vaga', 'BdoSiteController@anunciarVaga');
  Route::get('/info-empresa/{cnpj}', 'BdoEmpresaController@apiGetEmpresa');
  
  // Busca geral
  Route::get('/busca', 'SiteController@busca');

  // Agendamentos
  Route::get('agendamento', 'AgendamentoSiteController@formView');
  Route::post('agendamento', 'AgendamentoSiteController@store');
  Route::post('/checa-horarios', 'AgendamentoSiteController@checaHorarios');
  Route::get('agendamento-consulta', 'AgendamentoSiteController@consultaView');
  Route::get('agendamento-consulta/busca', 'AgendamentoSiteController@consulta');
  Route::put('agendamento-consulta/busca', 'AgendamentoSiteController@cancelamento');

  // Newsletter
  Route::post('newsletter', 'NewsletterController@store');

  // Feiras
  Route::get('feiras', 'SiteController@feiras');

  // Fiscalização
  Route::get('acoes-da-fiscalizacao', 'SiteController@acoesFiscalizacao');

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

  // Representantes
  Route::prefix('representante')->group(function(){
    Route::get('/home', 'RepresentanteSiteController@index')->name('representante.dashboard');
    Route::get('/dados-gerais', 'RepresentanteSiteController@dadosGeraisView')->name('representante.dados-gerais');
    Route::get('/contatos', 'RepresentanteSiteController@contatosView')->name('representante.contatos.view');
    Route::get('/enderecos', 'RepresentanteSiteController@enderecosView')->name('representante.enderecos.view');
    Route::get('/dados-gerais', 'RepresentanteSiteController@dadosGeraisView')->name('representante.dados-gerais');
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
  });

  Route::get('/chat', function(){
    return view('site.chat');
  });

  // Páginas (deve ser inserido no final do arquivo de rotas)
  Route::get('{slug}', 'PaginaController@show')->name('paginas.site');
});
