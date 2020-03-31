<?php

Route::get('/', 'SiteController@index')->name('site.home');

Route::get('admin', function() {
  return view('admin.home');
});

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
  
  // Rotas para CRUD de páginas
  Route::prefix('paginas')->group(function() {
    Route::get('/', 'PaginaController@index')->name('paginas.lista');
    Route::get('/busca', 'PaginaController@busca');
    Route::get('/criar', 'PaginaController@create');
    Route::post('/criar', 'PaginaController@store');
    Route::get('/editar/{id}', 'PaginaController@edit');
    Route::put('/editar/{id}', 'PaginaController@update');
    Route::delete('/apagar/{id}', 'PaginaController@destroy');
    Route::get('/lixeira', 'PaginaController@lixeira');
    Route::get('/restore/{id}', 'PaginaController@restore');
    // Rotas para categorias de páginas
    Route::get('/categorias', 'PaginaCategoriaController@index');
    Route::get('/categorias/busca', 'PaginaCategoriaController@busca');
    Route::get('/categorias/mostra/{id}', 'PaginaCategoriaController@show');
    Route::get('/categorias/criar', 'PaginaCategoriaController@create');
    Route::post('/categorias/criar', 'PaginaCategoriaController@store');
    Route::get('/categorias/editar/{id}', 'PaginaCategoriaController@edit');
    Route::put('/categorias/editar/{id}', 'PaginaCategoriaController@update');
    Route::delete('/categorias/apagar/{id}', 'PaginaCategoriaController@destroy');
  });

  // Rotas de regionais
  Route::prefix('regionais')->group(function() {
    Route::get('/', 'RegionalController@index');
    Route::get('/busca', 'RegionalController@busca');
    Route::get('/editar/{id}', 'RegionalController@edit');
    Route::put('/editar/{id}', 'RegionalController@update');
  });

  // Rotas de notícias
  Route::prefix('noticias')->group(function(){
    Route::get('/', 'NoticiaController@index');
    Route::get('/busca', 'NoticiaController@busca');
    Route::get('/criar', 'NoticiaController@create');
    Route::post('/criar', 'NoticiaController@store');
    Route::get('/editar/{id}', 'NoticiaController@edit');
    Route::put('/editar/{id}', 'NoticiaController@update');
    Route::delete('/apagar/{id}', 'NoticiaController@destroy');
    Route::get('/lixeira', 'NoticiaController@lixeira');
    Route::get('/restore/{id}', 'NoticiaController@restore');
  });

  // Rotas de licitações
  Route::prefix('licitacoes')->group(function(){
    Route::get('/', 'LicitacaoController@index')->name('licitacoes.lista');
    Route::get('/busca', 'LicitacaoController@busca');
    Route::get('/criar', 'LicitacaoController@create');
    Route::post('/criar', 'LicitacaoController@store');
    Route::get('/editar/{id}', 'LicitacaoController@edit');
    Route::put('/editar/{id}', 'LicitacaoController@update');
    Route::delete('apagar/{id}', 'LicitacaoController@destroy');
    Route::get('/lixeira', 'LicitacaoController@lixeira');
    Route::get('/restore/{id}', 'LicitacaoController@restore');
  });

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

  // Rotas para concursos
  Route::prefix('concursos')->group(function(){
    Route::get('/', 'ConcursoController@index')->name('concursos.lista');
    Route::get('/busca', 'ConcursoController@busca');
    Route::get('/criar', 'ConcursoController@create');
    Route::post('/criar', 'ConcursoController@store');
    Route::get('/editar/{id}', 'ConcursoController@edit');
    Route::put('/editar/{id}', 'ConcursoController@update');
    Route::delete('/apagar/{id}', 'ConcursoController@destroy');
    Route::get('/lixeira', 'ConcursoController@lixeira');
    Route::get('/restore/{id}', 'ConcursoController@restore');
  });

  // Rotas para cursos
  Route::prefix('cursos')->group(function(){
    Route::get('/', 'CursoController@index')->name('cursos.lista');
    Route::get('/busca', 'CursoController@busca');
    Route::get('/criar', 'CursoController@create');
    Route::post('/criar', 'CursoController@store');
    Route::get('/editar/{id}', 'CursoController@edit');
    Route::put('/editar/{id}', 'CursoController@update');
    Route::delete('/cancelar/{id}', 'CursoController@destroy');
    Route::get('/lixeira', 'CursoController@lixeira');
    Route::get('/restore/{id}', 'CursoController@restore');
    // Lida com a parte de inscritos
    Route::get('/inscritos/{id}', 'CursoController@inscritos')->name('inscritos.lista');
    Route::get('/inscritos/{id}/busca', 'CursoInscritoController@busca');
    Route::get('/inscritos/editar/{id}', 'CursoInscritoController@edit');
    Route::put('/inscritos/editar/{id}', 'CursoInscritoController@update');
    Route::put('/inscritos/confirmar-presenca/{id}', 'CursoInscritoController@confirmarPresenca');
    Route::put('/inscritos/confirmar-falta/{id}', 'CursoInscritoController@confirmarFalta');
    Route::get('/adicionar-inscrito/{id}', 'CursoInscritoController@create');
    Route::post('/adicionar-inscrito/{id}', 'CursoInscritoController@store');
    Route::delete('/cancelar-inscricao/{id}', 'CursoInscritoController@destroy');
    Route::get('/inscritos/download/{id}', 'CursoInscritoController@download');
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
  Route::resource('/posts', 'PostsController')->except(['show']);
  Route::get('/posts/busca', 'PostsController@busca');

  // Rotas para Representantes
  Route::get('/representantes', 'RepresentanteController@index');
  Route::get('/representantes/busca', 'RepresentanteController@busca');
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

  // Notícias
  Route::get('noticias', 'NoticiaSiteController@noticiasView');
  Route::get('noticia/{slug}', 'NoticiaSiteController@show');
  
  // Licitações
  Route::get('licitacao/{id}', 'LicitacaoSiteController@show');
  Route::get('licitacoes', 'LicitacaoSiteController@licitacoesView');
  Route::get('licitacoes/busca', 'LicitacaoSiteController@buscaLicitacoes');
  
  //Balcão de Oportunidades
  Route::get('balcao-de-oportunidades', 'BdoSiteController@index');
  Route::get('balcao-de-oportunidades/busca', 'BdoSiteController@buscaOportunidades');
  Route::get('anunciar-vaga', 'BdoSiteController@anunciarVagaView');
  Route::post('anunciar-vaga', 'BdoSiteController@anunciarVaga');
  Route::get('/info-empresa/{cnpj}', 'BdoEmpresaController@apiGetEmpresa');
  
  // Cursos
  Route::get('cursos', 'CursoSiteController@cursosView');
  Route::get('curso/{id}', 'CursoSiteController@cursoView');
  Route::get('curso/inscricao/{id}', 'CursoInscritoController@inscricaoView');
  Route::post('curso/inscricao/{id}', 'CursoInscritoController@inscricao');
  Route::get('cursos-anteriores', 'CursoSiteController@cursosAnterioresView');

  // Concursos
  Route::get('concursos', 'ConcursoSiteController@concursosView');
  Route::get('concursos/busca', 'ConcursoSiteController@buscaConcursos');
  Route::get('concurso/{id}', 'ConcursoSiteController@show');
  
  // Busca geral
  Route::get('/busca', 'SiteController@busca');

  // Seccionais
  Route::get('seccionais', 'RegionalSiteController@regionaisView');
  Route::get('seccional/{id}', 'RegionalSiteController@show');

  // Agendamentos
  Route::get('agendamento', 'AgendamentoSiteController@formView');
  // Route::post('agendamento', 'AgendamentoSiteController@store');
  // Route::post('/checa-horarios', 'AgendamentoSiteController@checaHorarios');
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

  // Páginas (deve ser inserido no final do arquivo de rotas)
  Route::get('{slug}', 'PaginaSiteController@show');
  Route::get('{categoria}/{slug}', 'PaginaSiteController@showCategoria');
});
