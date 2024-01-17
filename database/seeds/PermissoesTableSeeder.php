<?php

use Illuminate\Database\Seeder;
use App\Permissao;

class PermissoesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permissao::insert([
            [
                'controller' => 'UserController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'RegionalController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ],[
                'controller' => 'PaginaController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'PaginaController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'PaginaController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'PaginaController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ], [
                'controller' => 'CursoController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'CursoController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'CursoController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'CursoController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ], [
                'controller' => 'CursoInscritoController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'CursoInscritoController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'CursoInscritoController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'CursoInscritoController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ], [
                'controller' => 'BdoEmpresaController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'BdoEmpresaController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'BdoEmpresaController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'BdoEmpresaController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ], [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'BdoOportunidadeController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ], [
                'controller' => 'AgendamentoController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'AgendamentoController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'AgendamentoBloqueioController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'AgendamentoBloqueioController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'AgendamentoBloqueioController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'AgendamentoBloqueioController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ], [
                'controller' => 'LicitacaoController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'LicitacaoController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'LicitacaoController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'LicitacaoController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ], [
                'controller' => 'ConcursoController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'ConcursoController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'ConcursoController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'ConcursoController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ], [
                'controller' => 'NewsletterController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'HomeImagemController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'PostsController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'PostsController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'RepresentanteEnderecoController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'RepresentanteEnderecoController',
                'metodo' => 'show',
                'perfis' => '1,'
            ], [
                'controller' => 'RepresentanteController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'PostsController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'PostsController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ], [
                'controller' => 'FiscalizacaoController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'FiscalizacaoController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'FiscalizacaoController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'CompromissoController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'CompromissoController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'CompromissoController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'CompromissoController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ], [
                'controller' => 'AvisoController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'AvisoController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'SolicitaCedulaController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'SolicitaCedulaController',
                'metodo' => 'show',
                'perfis' => '1,'
            ], [
                'controller' => 'PlantaoJuridicoController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'PlantaoJuridicoController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'PlantaoJuridicoBloqueioController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'PlantaoJuridicoBloqueioController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'PlantaoJuridicoBloqueioController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'PlantaoJuridicoBloqueioController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ], [
                'controller' => 'SalaReuniaoController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'SalaReuniaoController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'SuspensaoExcecaoController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'SuspensaoExcecaoController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'SuspensaoExcecaoController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'AgendamentoController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'CartaServicos',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'CartaServicos',
                'metodo' => 'edit',
                'perfis' => '1,'
            ],
        ]);
    }
}
