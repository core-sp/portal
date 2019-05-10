<?php

use Illuminate\Database\Seeder;
use App\Permissao;

class PermissaoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissao = new Permissao();
        $permissao->controller = 'UserController';
        $permissao->metodo = 'index';
        $permissao->perfis = '10,2,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'RegionalController';
        $permissao->metodo = 'edit';
        $permissao->perfis = '2,3,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'PaginaController';
        $permissao->metodo = 'index';
        $permissao->perfis = '10,2,3,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'PaginaController';
        $permissao->metodo = 'create';
        $permissao->perfis = '2,3,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'PaginaController';
        $permissao->metodo = 'edit';
        $permissao->perfis = '2,3,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'PaginaController';
        $permissao->metodo = 'destroy';
        $permissao->perfis = '2,3,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'NoticiaController';
        $permissao->metodo = 'index';
        $permissao->perfis = '10,6,2,3,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'NoticiaController';
        $permissao->metodo = 'create';
        $permissao->perfis = '2,1,3,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'NoticiaController';
        $permissao->metodo = 'edit';
        $permissao->perfis = '2,1,3,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'NoticiaController';
        $permissao->metodo = 'destroy';
        $permissao->perfis = '2,1,3,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'CursoController';
        $permissao->metodo = 'index';
        $permissao->perfis = '10,2,3,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'CursoController';
        $permissao->metodo = 'create';
        $permissao->perfis = '2,3,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'CursoController';
        $permissao->metodo = 'edit';
        $permissao->perfis = '2,3,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'CursoController';
        $permissao->metodo = 'destroy';
        $permissao->perfis = '2,3,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'CursoInscritoController';
        $permissao->metodo = 'index';
        $permissao->perfis = '10,2,1,3,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'CursoInscritoController';
        $permissao->metodo = 'create';
        $permissao->perfis = '2,3,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'CursoInscritoController';
        $permissao->metodo = 'edit';
        $permissao->perfis = '2,3,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'CursoInscritoController';
        $permissao->metodo = 'destroy';
        $permissao->perfis = '2,3,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'BdoEmpresaController';
        $permissao->metodo = 'index';
        $permissao->perfis = '10,5,1';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'BdoEmpresaController';
        $permissao->metodo = 'create';
        $permissao->perfis = '5,1';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'BdoEmpresaController';
        $permissao->metodo = 'edit';
        $permissao->perfis = '5,1';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'BdoEmpresaController';
        $permissao->metodo = 'destroy';
        $permissao->perfis = '5,1';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'BdoOportunidadeController';
        $permissao->metodo = 'index';
        $permissao->perfis = '10,5,1';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'BdoOportunidadeController';
        $permissao->metodo = 'create';
        $permissao->perfis = '5,1';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'BdoOportunidadeController';
        $permissao->metodo = 'edit';
        $permissao->perfis = '5,1';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'BdoOportunidadeController';
        $permissao->metodo = 'destroy';
        $permissao->perfis = '5,1';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'AgendamentoController';
        $permissao->metodo = 'index';
        $permissao->perfis = '10,8,6,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'AgendamentoController';
        $permissao->metodo = 'edit';
        $permissao->perfis = '6,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'AgendamentoBloqueioController';
        $permissao->metodo = 'index';
        $permissao->perfis = '10,6,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'AgendamentoBloqueioController';
        $permissao->metodo = 'create';
        $permissao->perfis = '6,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'AgendamentoBloqueioController';
        $permissao->metodo = 'edit';
        $permissao->perfis = '6,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'AgendamentoBloqueioController';
        $permissao->metodo = 'destroy';
        $permissao->perfis = '6,1,';
        $permissao->save();
        
        $permissao = new Permissao();
        $permissao->controller = 'LicitacaoController';
        $permissao->metodo = 'index';
        $permissao->perfis = '10,4,2,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'LicitacaoController';
        $permissao->metodo = 'create';
        $permissao->perfis = '4,2,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'LicitacaoController';
        $permissao->metodo = 'edit';
        $permissao->perfis = '4,2,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'LicitacaoController';
        $permissao->metodo = 'destroy';
        $permissao->perfis = '4,2,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'ConcursoController';
        $permissao->metodo = 'index';
        $permissao->perfis = '10,6,2,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'ConcursoController';
        $permissao->metodo = 'create';
        $permissao->perfis = '6,2,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'ConcursoController';
        $permissao->metodo = 'edit';
        $permissao->perfis = '6,2,1,';
        $permissao->save();

        $permissao = new Permissao();
        $permissao->controller = 'ConcursoController';
        $permissao->metodo = 'destroy';
        $permissao->perfis = '6,2,1,';
        $permissao->save();
    }
}
