<?php

Route::middleware(['block_ip'])->group(function () {

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

    // Regionais
    Route::prefix('regionais')->group(function() {
      Route::get('/', 'RegionalController@index')->name('regionais.index');
      Route::get('/busca', 'RegionalController@busca')->name('regionais.busca');
      Route::get('/editar/{id}', 'RegionalController@edit')->name('regionais.edit');
      Route::patch('/editar/{id}', 'RegionalController@update')->name('regionais.update');
    });
    
    // Rotas de páginas
    require('admin/paginas.php');

    // Rotas de notícias
    Route::prefix('noticias')->group(function(){
      Route::get('/', 'NoticiaController@index')->name('noticias.index');
      Route::get('/create', 'NoticiaController@create')->name('noticias.create');
      Route::post('/', 'NoticiaController@store')->name('noticias.store');
      Route::get('/{id}/edit', 'NoticiaController@edit')->name('noticias.edit');
      Route::patch('/{noticia}', 'NoticiaController@update')->name('noticias.update');
      Route::delete('/{id}', 'NoticiaController@destroy')->name('noticias.destroy');
      Route::get('/{id}/restore', 'NoticiaController@restore')->name('noticias.restore');
      Route::get('/busca', 'NoticiaController@busca')->name('noticias.busca');
      Route::get('/lixeira', 'NoticiaController@lixeira')->name('noticias.trashed');
    });

    // Rotas de licitações
    Route::prefix('licitacoes')->group(function(){
      Route::get('/', 'LicitacaoController@index')->name('licitacoes.index');
      Route::get('/create', 'LicitacaoController@create')->name('licitacoes.create');
      Route::post('/', 'LicitacaoController@store')->name('licitacoes.store');
      Route::get('/{id}/edit', 'LicitacaoController@edit')->name('licitacoes.edit');
      Route::patch('/{id}', 'LicitacaoController@update')->name('licitacoes.update');
      Route::delete('/{id}', 'LicitacaoController@destroy')->name('licitacoes.destroy');
      Route::get('/{id}/restore', 'LicitacaoController@restore')->name('licitacoes.restore');
      Route::get('/busca', 'LicitacaoController@busca')->name('licitacoes.busca');
      Route::get('/lixeira', 'LicitacaoController@lixeira')->name('licitacoes.trashed');
    });

    // Rotas de concursos
    require('admin/concursos.php');
    
    // Rotas para cursos
    Route::prefix('cursos')->group(function(){
      Route::get('/', 'CursoController@index')->name('cursos.index');
      Route::get('/busca', 'CursoController@busca')->name('cursos.busca');
      Route::get('/create', 'CursoController@create')->name('cursos.create');
      Route::post('/', 'CursoController@store')->name('cursos.store');
      Route::get('/{id}/edit', 'CursoController@edit')->name('cursos.edit');
      Route::patch('/{id}', 'CursoController@update')->name('cursos.update');
      Route::delete('/{id}', 'CursoController@destroy')->name('cursos.destroy');
      Route::get('/lixeira', 'CursoController@lixeira')->name('cursos.lixeira');
      Route::get('/{id}/restore', 'CursoController@restore')->name('cursos.restore');
      // Lida com a parte de inscritos
      Route::get('/inscritos/{idcurso}', 'CursoInscritoController@index')->name('inscritos.index');
      Route::get('/inscritos/{idcurso}/busca', 'CursoInscritoController@busca')->name('inscritos.busca');
      Route::get('/inscritos/editar/{id}', 'CursoInscritoController@edit')->name('inscritos.edit');
      Route::put('/inscritos/editar/{id}', 'CursoInscritoController@update')->name('inscritos.update');
      Route::put('/inscritos/atualizar-presenca/{id}', 'CursoInscritoController@updatePresenca')->name('inscritos.update.presenca');
      Route::get('/adicionar-inscrito/{idcurso}', 'CursoInscritoController@create')->name('inscritos.create');
      Route::post('/adicionar-inscrito/{idcurso}', 'CursoInscritoController@store')->name('inscritos.store');
      Route::delete('/cancelar-inscricao/{id}', 'CursoInscritoController@destroy')->name('inscritos.destroy');
      Route::get('/inscritos/download/{id}', 'CursoInscritoController@download')->name('inscritos.download');
  });
    
    // Rotas do mapa de fiscalização (possivelmente será removido pois os dados virão do GERENTI)
    Route::prefix('fiscalizacao')->group(function() {
      Route::get('/', 'FiscalizacaoController@index')->name('fiscalizacao.index');
      Route::get('/createPeriodo', 'FiscalizacaoController@createPeriodo')->name('fiscalizacao.createperiodo');
      Route::post('/createPeriodo', 'FiscalizacaoController@storePeriodo')->name('fiscalizacao.storeperiodo');
      Route::put('/updateStatus/{id}', 'FiscalizacaoController@updateStatus')->name('fiscalizacao.updatestatus');
      Route::get('/busca', 'FiscalizacaoController@busca')->name('fiscalizacao.busca');
      Route::get('/editPeriodo/{id}', 'FiscalizacaoController@editPeriodo')->name('fiscalizacao.editperiodo');
      Route::put('/editPeriodo/{id}', 'FiscalizacaoController@updatePeriodo')->name('fiscalizacao.updateperiodo');
    });
  
    require('admin/compromissos.php');

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
      Route::prefix('bloqueios')->group(function(){
        Route::get('/', 'AgendamentoBloqueioController@index')->name('agendamentobloqueios.lista');
        Route::get('/busca', 'AgendamentoBloqueioController@busca')->name('agendamentobloqueios.busca');
        Route::get('/criar', 'AgendamentoBloqueioController@create')->name('agendamentobloqueios.criar');
        Route::post('/criar', 'AgendamentoBloqueioController@store')->name('agendamentobloqueios.store');
        Route::get('/editar/{id}', 'AgendamentoBloqueioController@edit')->name('agendamentobloqueios.edit');
        Route::put('/editar/{id}', 'AgendamentoBloqueioController@update')->name('agendamentobloqueios.update');
        Route::delete('/apagar/{id}', 'AgendamentoBloqueioController@destroy')->name('agendamentobloqueios.delete');
        Route::get('/dados-ajax', 'AgendamentoBloqueioController@getDadosAjax')->name('agendamentobloqueios.dadosAjax');
      });
    });

    // Rota para Newsletter
    Route::prefix('newsletter')->group(function(){
      Route::get('/download', 'NewsletterController@download');
    });

    // Rota para Home Imagens
    Route::prefix('imagens')->group(function(){
      Route::get('/banner', 'HomeImagemController@editBanner')->name('imagens.banner');
      Route::put('/banner', 'HomeImagemController@updateBanner')->name('imagens.banner.put');
      Route::get('/itens-home', 'HomeImagemController@editItensHome')->name('imagens.itens.home');
      Route::patch('/itens-home', 'HomeImagemController@updateItensHome')->name('imagens.itens.home.update');
      Route::get('/itens-home/armazenamento/{folder?}', 'HomeImagemController@storageItensHome')->where('folder', 'img')->name('imagens.itens.home.storage');
      Route::post('/itens-home/armazenamento', 'HomeImagemController@storageItensHome')->name('imagens.itens.home.storage.post');
      Route::delete('/itens-home/armazenamento/delete-file/{file}', 'HomeImagemController@destroyFile')->name('imagens.itens.home.storage.delete');
      Route::get('/itens-home/armazenamento/download/{folder}/{arquivo}', 'HomeImagemController@downloadStorageItensHome')->where('folder', 'itens-home|img')->name('imagens.itens.home.storage.download');
    });

    // Rotas para Blog Posts
    Route::resource('/posts', 'PostsController')->except(['show']);
    Route::get('/posts/busca', 'PostsController@busca')->name('admin.posts.busca');

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

    // Solicita cedula
    Route::prefix('solicita-cedula')->group(function(){
      Route::get('/', 'SolicitaCedulaController@index')->name('solicita-cedula.index');
      Route::get('/filtro', 'SolicitaCedulaController@index')->name('solicita-cedula.filtro');
      Route::get('/visualizar/{id}', 'SolicitaCedulaController@show')->name('solicita-cedula.show');
      Route::get('/pdf/{id}', 'SolicitaCedulaController@gerarPdf')->name('solicita-cedula.pdf');
      Route::get('/busca', 'SolicitaCedulaController@busca')->name('solicita-cedula.busca');
      Route::put('/update/{id}', 'SolicitaCedulaController@updateStatus')->name('solicita-cedula.update');
    });
    
    
    // Termo de Consentimento, baixar CSV
    Route::get('/termo-consentimento/download', 'TermoConsentimentoController@download')->name('termo.consentimento.download');

    // Avisos
    Route::prefix('avisos')->group(function(){
      Route::get('/', 'AvisoController@index')->name('avisos.index');
      Route::get('/{id}', 'AvisoController@show')->name('avisos.show');
      Route::get('/editar/{id}', 'AvisoController@edit')->name('avisos.editar.view');
      Route::put('/editar/{id}', 'AvisoController@update')->name('avisos.editar');
      Route::put('/status/{id}', 'AvisoController@updateStatus')->name('avisos.editar.status');
    });

    // Suporte
    Route::prefix('suporte')->group(function(){
      // Logs
      Route::get('/logs', 'SuporteController@logExternoIndex')->name('suporte.log.externo.index');
      Route::get('/logs/hoje/{tipo}', 'SuporteController@viewLogExternoDoDia')->name('suporte.log.externo.hoje.view');
      Route::get('/logs/busca', 'SuporteController@buscaLogExterno')->name('suporte.log.externo.busca');
      Route::get('/logs/log/{data}/{tipo}', 'SuporteController@viewLogExterno')->name('suporte.log.externo.view');
      Route::get('/logs/log/download/{data}/{tipo}', 'SuporteController@downloadLogExterno')->name('suporte.log.externo.download');
      Route::get('/logs/verifica-integridade/{data}/{tipo}', 'SuporteController@verificaIntegridade')->name('suporte.log.externo.integridade');
      Route::get('/logs/relatorios', 'SuporteController@relatorios')->name('suporte.log.externo.relatorios');
      Route::get('/logs/relatorios/{relat}/{acao}', 'SuporteController@relatoriosAcoes')->where('acao', 'visualizar|remover|exportar-csv')->name('suporte.log.externo.relatorios.acoes');
      Route::get('/logs/relatorios/final', 'SuporteController@relatorioFinal')->name('suporte.log.externo.relatorios.final');
      // Sobre os erros
      Route::get('/erros', 'SuporteController@errosIndex')->name('suporte.erros.index');
      Route::post('/erros/file', 'SuporteController@uploadFileErros')->name('suporte.erros.file.post');
      Route::get('/erros/file', 'SuporteController@getErrosFile')->name('suporte.erros.file.get');
      // Desbloqueio de IP
      Route::get('/ips', 'SuporteController@ipsView')->name('suporte.ips.view');
      Route::delete('/ips/excluir/{ip}', 'SuporteController@ipsExcluir')->name('suporte.ips.excluir');
      Route::get('/sobre-storage', 'SuporteController@sobreStorage')->name('suporte.sobre-storage');
    });

    // Plantão Jurídico
    Route::prefix('plantao-juridico')->group(function(){
      Route::get('/', 'PlantaoJuridicoController@index')->name('plantao.juridico.index');
      Route::get('/editar/{id}', 'PlantaoJuridicoController@edit')->name('plantao.juridico.editar.view');
      Route::put('/editar/{id}', 'PlantaoJuridicoController@update')->name('plantao.juridico.editar');
      // Lida com bloqueios
      Route::prefix('bloqueios')->group(function(){
        Route::get('/', 'PlantaoJuridicoBloqueioController@index')->name('plantao.juridico.bloqueios.index');
        Route::get('/criar', 'PlantaoJuridicoBloqueioController@create')->name('plantao.juridico.bloqueios.criar.view');
        Route::post('/criar', 'PlantaoJuridicoBloqueioController@store')->name('plantao.juridico.bloqueios.criar');
        Route::get('/editar/{id}', 'PlantaoJuridicoBloqueioController@edit')->name('plantao.juridico.bloqueios.editar.view');
        Route::put('/editar/{id}', 'PlantaoJuridicoBloqueioController@update')->name('plantao.juridico.bloqueios.editar');
        Route::delete('/apagar/{id}', 'PlantaoJuridicoBloqueioController@destroy')->name('plantao.juridico.bloqueios.excluir');
      });
      Route::get('/ajax', 'PlantaoJuridicoBloqueioController@getPlantaoAjax')->name('plantao.juridico.bloqueios.ajax');
    });

    // Salas de Reuniões
    Route::prefix('salas-reunioes')->group(function(){
      Route::name('sala.reuniao.')->group(function () {
        Route::get('/', 'SalaReuniaoController@index')->name('index');
        Route::get('/editar/{id}', 'SalaReuniaoController@edit')->name('editar.view');
        Route::put('/editar/{id}', 'SalaReuniaoController@update')->name('editar');
        Route::get('/regionais-salas-ativas/{tipo}', 'SalaReuniaoController@getRegionaisAtivas')->name('regionais.ativas');
        Route::get('/sala-dias-horas/{tipo}', 'SalaReuniaoController@getDiasHoras')->name('dias.horas');
        Route::post('/sala-horario-formatado/{id}', 'SalaReuniaoController@getHorarioFormatado')->name('horario.formatado');

        Route::prefix('agendados')->group(function(){
          Route::name('agendados.')->group(function () {
            Route::get('/', 'AgendamentoController@index')->name('index');
            Route::get('/criar', 'AgendamentoController@create')->name('create');
            Route::post('/verifica', 'AgendamentoController@verificar')->name('verifica.criar');
            Route::post('/criar', 'AgendamentoController@store')->name('store');
            Route::get('/visualizar/{id}/{anexo?}', 'AgendamentoController@view')->name('view');
            Route::put('/{id}/{acao}', 'AgendamentoController@updateStatus')->where('acao', 'confirma|aceito|recusa')->name('update');
            Route::get('/filtro', 'AgendamentoController@index')->name('filtro');
            Route::get('/busca', 'AgendamentoController@busca')->name('busca');
          });
        });

        Route::prefix('bloqueios')->group(function(){
          Route::name('bloqueio.')->group(function () {
            Route::get('/', 'AgendamentoBloqueioController@index')->name('lista');
            Route::get('/busca', 'AgendamentoBloqueioController@busca')->name('busca');
            Route::get('/criar', 'AgendamentoBloqueioController@create')->name('criar');
            Route::post('/criar', 'AgendamentoBloqueioController@store')->name('store');
            Route::get('/editar/{id}', 'AgendamentoBloqueioController@edit')->name('edit');
            Route::put('/editar/{id}', 'AgendamentoBloqueioController@update')->name('update');
            Route::delete('/apagar/{id}', 'AgendamentoBloqueioController@destroy')->name('delete');
            Route::get('/horarios-ajax', 'AgendamentoBloqueioController@getDadosAjax')->name('horariosAjax');
          });
        });

        Route::prefix('suspensoes-excecoes')->group(function(){
          Route::name('suspensao.')->group(function () {
            Route::get('/', 'SuspensaoExcecaoController@index')->name('lista');
            Route::get('/visualizar/{id}/', 'SuspensaoExcecaoController@view')->name('view');
            Route::get('/editar/{id}/{situacao}', 'SuspensaoExcecaoController@edit')->where('situacao', 'suspensao|excecao')->name('edit');
            Route::put('/editar/{id}/{situacao}', 'SuspensaoExcecaoController@update')->where('situacao', 'suspensao|excecao')->name('update');
            Route::get('/criar', 'SuspensaoExcecaoController@create')->name('criar');
            Route::post('/criar', 'SuspensaoExcecaoController@store')->name('store');
            Route::get('/busca', 'SuspensaoExcecaoController@busca')->name('busca');
            Route::delete('/apagar/{id}', 'SuspensaoExcecaoController@destroy')->name('delete');
          });
        });
      });
    });

    Route::post('/termo-de-consentimento/upload/{tipo_servico}', 'TermoConsentimentoController@uploadTermo')
    ->where('tipo_servico', 'sala-reuniao')->name('termo.consentimento.upload')->middleware('auth');

    // Rota para Gerar Textos
    Route::prefix('textos')->group(function(){
      Route::get('/{tipo_doc}/{id?}', 'GerarTextoController@view')->where('tipo_doc', 'carta-servicos')->name('textos.view');
      Route::post('/{tipo_doc}', 'GerarTextoController@create')->where('tipo_doc', 'carta-servicos')->name('textos.create');
      Route::post('/{tipo_doc}/{id}', 'GerarTextoController@updateCampos')->where('tipo_doc', 'carta-servicos')->name('textos.update.campos');
      Route::post('/publicar/{tipo_doc}', 'GerarTextoController@publicar')->where('tipo_doc', 'carta-servicos')->name('textos.publicar');
      Route::delete('/{tipo_doc}/excluir', 'GerarTextoController@delete')->where('tipo_doc', 'carta-servicos')->name('textos.delete');
      Route::put('/{tipo_doc}', 'GerarTextoController@update')->where('tipo_doc', 'carta-servicos')->name('textos.update.indice');
    });
    
  });

  /*
  * ROTAS ABERTAS
  */
  Route::prefix('/')->group(function() {
    // Rotas de admin abertas
    Route::get('admin', 'AdminController@index')->name('admin');

    // Regionais
    Route::get('seccionais', 'RegionalController@siteGrid')->name('regionais.siteGrid');
    Route::get('seccionais/{id}', 'RegionalController@show')->name('regionais.show');

    // Notícias
    Route::get('/noticias', 'NoticiaController@siteGrid')->name('noticias.siteGrid');
    Route::get('/noticias/{slug}', 'NoticiaController@show')->name('noticias.show');
    // Redirects
    Route::get('/noticia/{slug}', function($slug){
        return redirect(route('noticias.show', $slug), 301);
    });

    // Licitações
    Route::get('/licitacoes/busca', 'LicitacaoController@siteBusca')->name('licitacoes.siteBusca');
    Route::get('/licitacoes/{id}', 'LicitacaoController@show')->name('licitacoes.show');
    Route::get('/licitacoes', 'LicitacaoController@siteGrid')->name('licitacoes.siteGrid');

    // Concursos
    require('site/concursos.php');

    // Cursos
    Route::get('cursos', 'CursoController@cursosView')->name('cursos.index.website');
    Route::get('cursos/{id}', 'CursoController@show')->name('cursos.show');
    Route::get('cursos/{idcurso}/inscricao', 'CursoInscritoController@inscricaoView')->name('cursos.inscricao.website');
    Route::post('cursos/{idcurso}/inscricao', 'CursoInscritoController@inscricao')->name('cursos.inscricao');
    Route::get('cursos-anteriores', 'CursoController@cursosAnterioresView')->name('cursos.previous.website');
    // Redirects
    Route::get('/curso/{id}', function($id){
        return redirect(route('cursos.show', $id), 301);
    });

    // Representantes
    require('site/representantes.php');
    
    //Balcão de Oportunidades
    Route::get('balcao-de-oportunidades', 'BdoSiteController@index')->name('bdosite.index');
    Route::get('balcao-de-oportunidades/busca', 'BdoSiteController@buscaOportunidades')->name('bdosite.buscaOportunidades');
    Route::get('anunciar-vaga', 'BdoSiteController@anunciarVagaView')->name('bdosite.anunciarVagaView');
    Route::post('anunciar-vaga', 'BdoSiteController@anunciarVaga')->name('bdosite.anunciarVaga');
    Route::get('/info-empresa/{cnpj}', 'BdoEmpresaController@apiGetEmpresa')->name('bdosite.apiGetEmpresa');
    
    // Busca geral
    Route::get('/busca', 'SiteController@busca')->name('site.busca');

    // Agendamentos
    Route::get('agendamento', 'AgendamentoSiteController@formView')->name('agendamentosite.formview');
    Route::post('agendamento', 'AgendamentoSiteController@store')->name('agendamentosite.store');
    Route::get('dias-horas', 'AgendamentoSiteController@getDiasHorasAjax')->name('agendamentosite.diasHorasAjax');
    Route::get('agendamento-consulta', 'AgendamentoSiteController@consultaView')->name('agendamentosite.consultaView');
    Route::get('agendamento-consulta/busca', 'AgendamentoSiteController@consulta')->name('agendamentosite.consulta');
    Route::put('agendamento-consulta/busca', 'AgendamentoSiteController@cancelamento')->name('agendamentosite.cancelamento');
    Route::get('regionais-plantao-juridico', 'AgendamentoSiteController@regionaisPlantaoJuridico')->name('agendamentosite.regionaisPlantaoJuridico');

    // Newsletter
    Route::post('newsletter', 'NewsletterController@store');

    // Feiras
    Route::get('feiras', 'SiteController@feiras')->name('site.feiras');

    // Fiscalização
    Route::get('acoes-da-fiscalizacao', 'SiteController@acoesFiscalizacao')->name('fiscalizacao.acoesfiscalizacao');
    // Rotas para o SIG (Sistema de Informação Geográfico)
    Route::get('/mapa-fiscalizacao', 'FiscalizacaoController@mostrarMapa')->name('fiscalizacao.mapa');
    Route::get('/mapa-fiscalizacao/{id}', 'FiscalizacaoController@mostrarMapaPeriodo')->name('fiscalizacao.mapaperiodo');
    Route::get('espaco-do-contador', 'SiteController@espacoContador')->name('fiscalizacao.espacoContador');

    // Simulador
    Route::get('simulador', 'SimuladorController@view');
    Route::post('simulador', 'SimuladorController@extrato');

    // Consulta de Situação
    Route::get('consulta-de-situacao', 'ConsultaSituacaoController@consultaView');
    // Route::post('consulta-de-situacao', 'ConsultaSituacaoController@consulta');

    // Blog
    Route::get('blog', 'PostsController@blogPage')->name('site.blog');
    Route::get('blog/{slug}', 'PostsController@show')->name('site.blog.post');

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
    Route::get('/termo-consentimento-pdf/{tipo_servico?}', 'TermoConsentimentoController@termoConsentimentoPdf')
    ->where('tipo_servico', 'sala-reuniao')->name('termo.consentimento.pdf');

    Route::get('/carta-de-servicos-ao-usuario/buscar', 'GerarTextoController@buscar')->name('carta-servicos-buscar');
    Route::get('/carta-de-servicos-ao-usuario/{id?}', 'GerarTextoController@show')->name('carta-servicos');

    // Páginas (deve ser inserido no final do arquivo de rotas)
    Route::get('{slug}', 'PaginaController@show')->name('paginas.site');
  });
  
});