<?php

Route::get('/', 'SiteController@index');

Route::get('admin', function() {
  return view('admin.home');
});

/*
 * Rota de admin
 */
Route::prefix('admin')->group(function() {
  /*
   * Rotas de Login
   */
  Auth::routes();

  /*
   * Rotas de Configuração
   */
  Route::prefix('info')->group(function(){
    Route::get('/', 'UserController@infos')->name('admin.info');
    Route::get('/senha', 'UserController@senha');
    Route::put('/senha', 'UserController@changePassword');
  });
  
  /*
   * Rota para CRUD de páginas
   */
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
    Route::get('/categorias/mostra/{id}', 'PaginaCategoriaController@show');
    Route::get('/categorias/criar', 'PaginaCategoriaController@create');
    Route::post('/categorias/criar', 'PaginaCategoriaController@store');
    Route::get('/categorias/editar/{id}', 'PaginaCategoriaController@edit');
    Route::put('/categorias/editar/{id}', 'PaginaCategoriaController@update');
    Route::delete('/categorias/apagar/{id}', 'PaginaCategoriaController@destroy');
  });

  /*
   * Rota para mostrar regionais
   */
  Route::prefix('regionais')->group(function() {
    Route::get('/', 'RegionalController@index');
    Route::get('/busca', 'RegionalController@busca');
    Route::get('/mostra/{id}', 'RegionalController@show');
  });

  /*
   * Rota para CRUD de notícias
   */
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

  /*
   * Rota para CRUD de licitações
   */
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

  /*
   * Rota para CRUD de Usuários
   */
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
  });

  /*
   * Rota para CRUD de concursos
   */
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

  /*
   * Rota para CRUD de cursos
   */
  Route::prefix('cursos')->group(function(){
    Route::get('/', 'CursoController@index')->name('cursos.lista');
    Route::get('/busca', 'CursoController@busca');
    Route::get('/criar', 'CursoController@create');
    Route::post('/criar', 'CursoController@store');
    Route::get('/editar/{id}', 'CursoController@edit');
    Route::put('/editar/{id}', 'CursoController@update');
    Route::delete('/apagar/{id}', 'CursoController@destroy');
    Route::get('/lixeira', 'CursoController@lixeira');
    Route::get('/restore/{id}', 'CursoController@restore');
    // Lida com a parte de inscritos
    Route::get('/inscritos/{id}', 'CursoController@inscritos');
    Route::get('/adicionar-inscrito/{id}', 'CursoInscritoController@create');
    Route::post('/adicionar-inscrito/{id}', 'CursoInscritoController@store');
    Route::put('/cancelar-inscricao/{id}', 'CursoInscritoController@cancelarInscricao');
  });

  /*
   * Rota para CRUD do Balcão de Oportunidade
   */
  Route::prefix('bdo')->group(function(){
    Route::get('/', 'BdoOportunidadeController@index')->name('bdooportunidades.lista');
    Route::get('/busca', 'BdoOportunidadeController@busca');
    Route::get('/criar', 'BdoOportunidadeController@create');
    Route::post('/criar', 'BdoOportunidadeController@store');
    Route::get('/editar/{id}', 'BdoOportunidadeController@edit');
    Route::put('/editar/{id}', 'BdoOportunidadeController@update');
    // Lida com as empresas
    Route::get('/empresas', 'BdoEmpresaController@index')->name('bdoempresas.lista');
    Route::get('/empresas/busca', 'BdoEmpresaController@busca');
    Route::get('/empresas/criar', 'BdoEmpresaController@create');
    Route::post('/empresas/criar', 'BdoEmpresaController@store');
    Route::get('/empresas/editar/{id}', 'BdoEmpresaController@edit');
    Route::put('/empresas/editar/{id}', 'BdoEmpresaController@update');
    Route::delete('/empresas/apagar/{id}', 'BdoEmpresaController@destroy');
  });
});

Route::prefix('/')->group(function() {
  // Rotas de admin abertas
  Route::get('admin', 'AdminController@index')->name('admin');
  Route::get('admin/logout', 'Auth\LoginController@logout')->name('logout');
  // Rotas de conteúdo abertas
  Route::get('noticia/{slug}', 'NoticiaController@show');
  // Licitações
  Route::get('licitacao/{id}', 'LicitacaoSiteController@show');
  Route::get('licitacoes', 'LicitacaoSiteController@licitacoesView');
  Route::get('licitacoes/busca', 'LicitacaoSiteController@buscaLicitacoes');
  //Balcão de Oportunidades
  Route::get('balcao-de-oportunidades', 'BdoSite@index');

  Route::get('concurso/{id}', 'ConcursoController@show');
  Route::get('curso/{id}', 'CursoController@show');
  Route::get('{categoria}/{slug}', 'PaginaController@show');
  Route::get('{slug}', 'PaginaController@showSemCategoria');
  // Rota para inscrição em curso
  Route::get('curso/inscricao/{id}', 'CursoInscritoController@inscricaoView');
  Route::post('curso/inscricao/{id}', 'CursoInscritoController@inscricao');
});
