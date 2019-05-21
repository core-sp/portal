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
    Route::get('/inscritos/editar/{id}', 'CursoInscritoController@edit');
    Route::put('/inscritos/editar/{id}', 'CursoInscritoController@update');
    Route::get('/adicionar-inscrito/{id}', 'CursoInscritoController@create');
    Route::post('/adicionar-inscrito/{id}', 'CursoInscritoController@store');
    Route::delete('/cancelar-inscricao/{id}', 'CursoInscritoController@destroy');
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
    Route::get('/editar/{id}', 'AgendamentoController@edit');
    Route::put('/editar/{id}', 'AgendamentoController@update');
    Route::post('/reenviar-email/{id}', 'AgendamentoController@reenviarEmail');
    // Lida com bloqueios
    Route::get('/bloqueios', 'AgendamentoBloqueioController@index')->name('agendamentobloqueios.lista');
    Route::get('/bloqueios/criar', 'AgendamentoBloqueioController@create');
    Route::post('/bloqueios/criar', 'AgendamentoBloqueioController@store');
    Route::get('/bloqueios/editar/{id}', 'AgendamentoBloqueioController@edit');
    Route::put('/bloqueios/editar/{id}', 'AgendamentoBloqueioController@update');
    Route::delete('/bloqueios/apagar/{id}', 'AgendamentoBloqueioController@destroy');
  });
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
  Route::post('agendamento', 'AgendamentoSiteController@store');
  Route::post('/checa-horarios', 'AgendamentoSiteController@checaHorarios');
  Route::get('agendamento-consulta', 'AgendamentoSiteController@consultaView');
  Route::get('agendamento-consulta/busca', 'AgendamentoSiteController@consulta');
  Route::put('agendamento-consulta/busca', 'AgendamentoSiteController@cancelamento');

  // Newsletter
  Route::post('newsletter', 'NewsletterController@store');

  // Feiras
  Route::get('feiras', 'SiteController@feiras');

  // Páginas (deve ser inserido no final do arquivo de rotas)
  Route::get('{slug}', 'PaginaSiteController@show');
  Route::get('{categoria}/{slug}', 'PaginaSiteController@showCategoria');
});
