<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Perfil;
use App\Permissao;
use App\Services\PerfilService;

class PerfilTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** 
     * =======================================================================================================
     * TESTES PERFIL E PERMISSAO MODEL
     * =======================================================================================================
     */

    /** @test */
    public function soft_delete()
    {        
        $perfil = factory('App\Perfil')->create();

        $this->assertDatabaseHas('perfis', ['idperfil' => 1, 'deleted_at' => null]);

        $perfil->delete();

        $this->assertSoftDeleted('perfis', ['idperfil' => 1]);
    }

    /** @test */
    public function permissao_possui_relacionamento_perfis()
    {        
        $user = factory('App\User')->create();
        $perfil = factory('App\Perfil')->create();
        $users = factory('App\User', 5)->create([
            'idperfil' => $perfil->idperfil
        ]);
        factory('App\Perfil')->create();

        $this->relacionarPerfil($user->perfil);
        $this->relacionarPerfilPermissao($perfil, 'RegionalController', 'edit');

        $this->assertEquals(1, Permissao::find(1)->perfis()->count());
        $this->assertEquals(2, Permissao::find(2)->perfis()->count());
        $this->assertEquals(1, Permissao::find(3)->perfis()->count());
    }

    /** @test */
    public function possui_relacionamento_users()
    {        
        $user = factory('App\User')->create();
        $perfil = factory('App\Perfil')->create();
        $users = factory('App\User', 5)->create([
            'idperfil' => $perfil->idperfil
        ]);
        factory('App\Perfil')->create();

        $this->assertEquals(1, Perfil::find(1)->user->count());
        $this->assertEquals(5, Perfil::find(2)->user->count());
        $this->assertEquals(0, Perfil::find(3)->user->count());
    }

    /** @test */
    public function possui_relacionamento_permissoes()
    {        
        $user = factory('App\User')->create();
        $perfil = factory('App\Perfil')->create();
        $users = factory('App\User', 5)->create([
            'idperfil' => $perfil->idperfil
        ]);
        factory('App\Perfil')->create();

        $this->relacionarPerfil($user->perfil);
        $this->relacionarPerfilPermissao($perfil, 'RegionalController', 'edit');

        $this->assertEquals(74, Perfil::find(1)->permissoes->count());
        $this->assertEquals(1, Perfil::find(2)->permissoes->count());
        $this->assertEquals(0, Perfil::find(3)->permissoes->count());
    }

    /** @test */
    public function tem_permissao()
    {        
        $user = factory('App\User')->create();
        $perfil = factory('App\Perfil')->create();
        $users = factory('App\User', 5)->create([
            'idperfil' => $perfil->idperfil
        ]);
        factory('App\Perfil')->create();

        $this->relacionarPerfil($user->perfil);
        $this->relacionarPerfilPermissao($perfil, 'RegionalController', 'edit');

        $this->assertTrue(Perfil::find(1)->temPermissao('UserController', 'index'));
        $this->assertTrue(Perfil::find(2)->temPermissao('RegionalController', 'edit'));

        $this->assertFalse(Perfil::find(2)->temPermissao('UserController', 'index'));
        $this->assertFalse(Perfil::find(3)->temPermissao('RegionalController', 'edit'));
        $this->assertFalse(Perfil::find(3)->temPermissao('UserController', 'index'));
    }

    /** @test */
    public function pode_acessar_menu_conteudo()
    {        
        $user = factory('App\User')->create();
        $perfil = factory('App\Perfil')->create();
        $users = factory('App\User', 5)->create([
            'idperfil' => $perfil->idperfil
        ]);
        factory('App\Perfil')->create();

        $this->relacionarPerfil($user->perfil);

        foreach(Permissao::where('grupo_menu', Permissao::G_CONTEUDO)->get() as $permissao){
            $this->assertFalse(Perfil::find(2)->podeAcessarMenuConteudo());
            $this->relacionarPerfilPermissao($perfil, $permissao->controller, $permissao->metodo);

            $this->assertTrue(Perfil::find(2)->podeAcessarMenuConteudo());
            
            // remove a permissão para testar individualmente
            $perfil->permissoes()->detach($permissao->idpermissao);
        }
        
        $this->assertTrue(Perfil::find(1)->podeAcessarMenuConteudo());
        $this->assertFalse(Perfil::find(3)->podeAcessarMenuConteudo());
    }

    /** @test */
    public function pode_acessar_menu_atendimento()
    {        
        $user = factory('App\User')->create();
        $perfil = factory('App\Perfil')->create();
        $users = factory('App\User', 5)->create([
            'idperfil' => $perfil->idperfil
        ]);
        factory('App\Perfil')->create();

        $this->relacionarPerfil($user->perfil);

        foreach(Permissao::where('grupo_menu', Permissao::G_ATENDIMENTO)->get() as $permissao){
            $this->assertFalse(Perfil::find(2)->podeAcessarMenuAtendimento());
            $this->relacionarPerfilPermissao($perfil, $permissao->controller, $permissao->metodo);

            $this->assertTrue(Perfil::find(2)->podeAcessarMenuAtendimento());
            
            // remove a permissão para testar individualmente
            $perfil->permissoes()->detach($permissao->idpermissao);
        }
        
        $this->assertTrue(Perfil::find(1)->podeAcessarMenuAtendimento());
        $this->assertFalse(Perfil::find(3)->podeAcessarMenuAtendimento());
    }

    /** @test */
    public function pode_acessar_menu_juridico()
    {        
        $user = factory('App\User')->create();
        $perfil = factory('App\Perfil')->create();
        $users = factory('App\User', 5)->create([
            'idperfil' => $perfil->idperfil
        ]);
        factory('App\Perfil')->create();

        $this->relacionarPerfil($user->perfil);

        foreach(Permissao::where('grupo_menu', Permissao::G_JURIDICO)->get() as $permissao){
            $this->assertFalse(Perfil::find(2)->podeAcessarMenuJuridico());
            $this->relacionarPerfilPermissao($perfil, $permissao->controller, $permissao->metodo);

            $this->assertTrue(Perfil::find(2)->podeAcessarMenuJuridico());
            
            // remove a permissão para testar individualmente
            $perfil->permissoes()->detach($permissao->idpermissao);
        }
        
        $this->assertTrue(Perfil::find(1)->podeAcessarMenuJuridico());
        $this->assertFalse(Perfil::find(3)->podeAcessarMenuJuridico());
    }

    /** @test */
    public function pode_acessar_menu_fiscal()
    {        
        $user = factory('App\User')->create();
        $perfil = factory('App\Perfil')->create();
        $users = factory('App\User', 5)->create([
            'idperfil' => $perfil->idperfil
        ]);
        factory('App\Perfil')->create();

        $this->relacionarPerfil($user->perfil);

        foreach(Permissao::where('grupo_menu', Permissao::G_FISCAL)->get() as $permissao){
            $this->assertFalse(Perfil::find(2)->podeAcessarMenuFiscal());
            $this->relacionarPerfilPermissao($perfil, $permissao->controller, $permissao->metodo);

            $this->assertTrue(Perfil::find(2)->podeAcessarMenuFiscal());
            
            // remove a permissão para testar individualmente
            $perfil->permissoes()->detach($permissao->idpermissao);
        }
        
        $this->assertTrue(Perfil::find(1)->podeAcessarMenuFiscal());
        $this->assertFalse(Perfil::find(3)->podeAcessarMenuFiscal());
    }

    /** @test */
    public function pode_acessar_sub_menu_balcao()
    {        
        $user = factory('App\User')->create();
        $perfil = factory('App\Perfil')->create();
        $users = factory('App\User', 5)->create([
            'idperfil' => $perfil->idperfil
        ]);
        factory('App\Perfil')->create();

        $this->relacionarPerfil($user->perfil);

        foreach(Permissao::whereIn('controller', ['BdoEmpresaController', 'BdoOportunidadeController'])->where('metodo', 'index')->get() as $permissao){
            $this->assertFalse(Perfil::find(2)->podeAcessarSubMenuBalcao());
            $this->relacionarPerfilPermissao($perfil, $permissao->controller, $permissao->metodo);

            $this->assertTrue(Perfil::find(2)->podeAcessarSubMenuBalcao());
            
            // remove a permissão para testar individualmente
            $perfil->permissoes()->detach($permissao->idpermissao);
        }
        
        $this->assertTrue(Perfil::find(1)->podeAcessarSubMenuBalcao());
        $this->assertFalse(Perfil::find(3)->podeAcessarSubMenuBalcao());
    }

    /** @test */
    public function pode_acessar_sub_menu_agendamento()
    {        
        $user = factory('App\User')->create();
        $perfil = factory('App\Perfil')->create();
        $users = factory('App\User', 5)->create([
            'idperfil' => $perfil->idperfil
        ]);
        factory('App\Perfil')->create();

        $this->relacionarPerfil($user->perfil);

        foreach(Permissao::whereIn('controller', ['AgendamentoController', 'AgendamentoBloqueioController'])->where('metodo', 'index')->get() as $permissao){
            $this->assertFalse(Perfil::find(2)->podeAcessarSubMenuAgendamento());
            $this->relacionarPerfilPermissao($perfil, $permissao->controller, $permissao->metodo);

            $this->assertTrue(Perfil::find(2)->podeAcessarSubMenuAgendamento());
            
            // remove a permissão para testar individualmente
            $perfil->permissoes()->detach($permissao->idpermissao);
        }
        
        $this->assertTrue(Perfil::find(1)->podeAcessarSubMenuAgendamento());
        $this->assertFalse(Perfil::find(3)->podeAcessarSubMenuAgendamento());
    }

    /** @test */
    public function pode_acessar_sub_menu_sala_reuniao()
    {        
        $user = factory('App\User')->create();
        $perfil = factory('App\Perfil')->create();
        $users = factory('App\User', 5)->create([
            'idperfil' => $perfil->idperfil
        ]);
        factory('App\Perfil')->create();

        $this->relacionarPerfil($user->perfil);

        foreach(Permissao::whereIn('controller', [
            'AgendamentoController', 'AgendamentoBloqueioController', 'SalaReuniaoController', 'SuspensaoExcecaoController'
            ])->where('metodo', 'index')->get() as $permissao){
            $this->assertFalse(Perfil::find(2)->podeAcessarSubMenuSalaReuniao());
            $this->relacionarPerfilPermissao($perfil, $permissao->controller, $permissao->metodo);

            $this->assertTrue(Perfil::find(2)->podeAcessarSubMenuSalaReuniao());
            
            // remove a permissão para testar individualmente
            $perfil->permissoes()->detach($permissao->idpermissao);
        }
        
        $this->assertTrue(Perfil::find(1)->podeAcessarSubMenuSalaReuniao());
        $this->assertFalse(Perfil::find(3)->podeAcessarSubMenuSalaReuniao());
    }

    /** @test */
    public function pode_acessar_sub_menu_plantao()
    {        
        $user = factory('App\User')->create();
        $perfil = factory('App\Perfil')->create();
        $users = factory('App\User', 5)->create([
            'idperfil' => $perfil->idperfil
        ]);
        factory('App\Perfil')->create();

        $this->relacionarPerfil($user->perfil);

        foreach(Permissao::whereIn('controller', ['PlantaoJuridicoController', 'PlantaoJuridicoBloqueioController'])->where('metodo', 'index')->get() as $permissao){
            $this->assertFalse(Perfil::find(2)->podeAcessarSubMenuPlantao());
            $this->relacionarPerfilPermissao($perfil, $permissao->controller, $permissao->metodo);

            $this->assertTrue(Perfil::find(2)->podeAcessarSubMenuPlantao());
            
            // remove a permissão para testar individualmente
            $perfil->permissoes()->detach($permissao->idpermissao);
        }
        
        $this->assertTrue(Perfil::find(1)->podeAcessarSubMenuPlantao());
        $this->assertFalse(Perfil::find(3)->podeAcessarSubMenuPlantao());
    }

    /** 
     * =======================================================================================================
     * TESTES PERFILSERVICE
     * =======================================================================================================
     */

    /** @test */
    public function all()
    {        
        $service = new PerfilService;
        
        $this->assertEquals(0, $service->all()->count());

        factory('App\Perfil', 3)->create();

        $this->assertEquals(3, $service->all()->count());
    }

    /** @test */
    public function permissoes_agrupadas_por_controller()
    {        
        $service = new PerfilService;
        
        // order by nome ASC
        $this->assertEquals([
            'AgendamentoController',
            'AgendamentoBloqueioController',
            'AvisoController',
            'BdoEmpresaController',
            'BdoOportunidadeController',
            'CartaServicos',
            'CompromissoController',
            'ConcursoController',
            'CursoController',
            'CursoInscritoController',
            'FiscalizacaoController',
            'HomeImagemController',
            'LicitacaoController',
            'NewsletterController',
            'NoticiaController',
            'PlantaoJuridicoController',
            'PlantaoJuridicoBloqueioController',
            'PostsController',
            'PaginaController',
            'RegionalController',
            'RepresentanteController',
            'SolicitaCedulaController',
            'RepresentanteEnderecoController',
            'SalaReuniaoController',
            'SuspensaoExcecaoController',
            'UserController',
        ], array_keys($service->permissoesAgrupadasPorController()->toArray()));
    }

    /** @test */
    public function listar()
    {        
        $service = new PerfilService;

        $final = $service->listar();
        $tabela = $final['tabela'];
        
        $this->assertEquals([
            'resultados', 'tabela', 'variaveis'
        ], array_keys($final));

        $this->assertEquals(0, $final['resultados']->total());
        $this->assertEquals("string", gettype($final['tabela']));
        $this->assertEquals((object) [
            'singular' => 'perfil',
            'singulariza' => 'o perfil',
            'plural' => 'perfis',
            'pluraliza' => 'perfis',
            'titulo_criar' => 'Cadastrar perfil',
            'btn_criar' => '<a href="' . route('perfis.create') . '" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Novo Perfil</a>'
        ], $final['variaveis']);

        factory('App\Perfil', 3)->create();

        $final = $service->listar();

        $this->assertEquals(3, $final['resultados']->total());
        $this->assertNotEquals($tabela, $final['tabela']);
    }

    /** @test */
    public function view_sem_id()
    {        
        $service = new PerfilService;

        $final = $service->view();
        
        $this->assertEquals([
            'variaveis'
        ], array_keys($final));

        $this->assertEquals((object) [
            'singular' => 'perfil',
            'singulariza' => 'o perfil',
            'plural' => 'perfis',
            'pluraliza' => 'perfis',
            'titulo_criar' => 'Cadastrar perfil',
            'btn_criar' => '<a href="' . route('perfis.create') . '" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Novo Perfil</a>'
        ], $final['variaveis']);

        factory('App\Perfil', 3)->create();

        $final = $service->view();

        $this->assertEquals([
            'variaveis'
        ], array_keys($final));
    }

    /** @test */
    public function view_com_id()
    {        
        $service = new PerfilService;

        factory('App\Perfil', 3)->create();

        $final = $service->view(1);
        
        $this->assertEquals([
            'perfil', 'permissoes', 'variaveis'
        ], array_keys($final));

        $this->assertEquals("App\Perfil", get_class($final['perfil']));
        $this->assertEquals("Illuminate\Database\Eloquent\Collection", get_class($final['permissoes']));
        $this->assertEquals((object) [
            'singular' => 'perfil',
            'singulariza' => 'o perfil ' . Perfil::find(1)->nome,
            'plural' => 'perfis',
            'pluraliza' => 'perfis',
            'titulo_criar' => 'Cadastrar perfil',
            'btn_criar' => '<a href="' . route('perfis.create') . '" class="btn btn-primary mr-1"><i class="fas fa-plus"></i> Novo Perfil</a>'
        ], $final['variaveis']);
    }

    /** @test */
    public function view_com_id_inexistente()
    {        
        $this->expectException('Illuminate\Database\Eloquent\ModelNotFoundException');

        $service = new PerfilService;

        $final = $service->view(1);
    }

    /** @test */
    public function save_sem_id()
    {        
        $this->signInAsAdmin();

        $service = new PerfilService;

        $dados = ['nome' => 'Qualquer Perfil'];
        $final = $service->save($dados);
        
        $this->assertEquals('App\Perfil', get_class($final));
    }

    /** @test */
    public function save_com_id()
    {        
        $this->signInAsAdmin();
        factory('App\Perfil', 3)->create();

        $service = new PerfilService;

        $this->assertEquals(0, Perfil::find(2)->permissoes()->count());

        $dados = ['permissoes' => [1, 2, 3, 4, 5]];
        $final = $service->save($dados, 2);
        
        $this->assertEquals(5, Perfil::find(2)->permissoes()->count());
    }

    /** @test */
    public function save_com_id_e_dados_errados()
    {        
        $this->expectException('\Exception');

        $this->signInAsAdmin();

        factory('App\Perfil', 3)->create();

        $service = new PerfilService;

        $this->assertEquals(0, Perfil::find(2)->permissoes()->count());

        $dados = ['permissoes' => 'Errado de proposito'];
        $final = $service->save($dados, 2);
        
        $this->assertEquals(0, Perfil::find(2)->permissoes()->count());
    }

    /** @test */
    public function save_com_id_inexistente()
    {        
        $this->expectException('Illuminate\Database\Eloquent\ModelNotFoundException');

        $this->signInAsAdmin();

        factory('App\Perfil', 3)->create();

        $service = new PerfilService;

        $final = $service->save(['permissoes' => [2, 3]], 55);
    }

    /** @test */
    public function delete_perfil()
    {        
        $this->signInAsAdmin();
        factory('App\Perfil', 3)->create();

        $service = new PerfilService;

        $this->assertEquals([
            'message' => '<i class="icon fa fa-check"></i>Perfil com ID 2 deletado com sucesso!',
            'class' => 'alert-success',
        ], $service->delete(2));
    }

    /** @test */
    public function nao_delete_perfil_1_ou_24()
    {        
        $this->signIn();
        factory('App\Perfil')->states('bloqueado')->create();

        $service = new PerfilService;

        $this->assertEquals([
            'message' => 'Perfil com ID 1 (Admin) não pode ser excluído!',
            'class' => 'alert-danger',
        ], $service->delete(1));

        $this->assertEquals([
            'message' => 'Perfil com ID 24 (Bloqueado) não pode ser excluído!',
            'class' => 'alert-danger',
        ], $service->delete(24));
    }

    /** @test */
    public function nao_delete_perfil_com_usuario()
    {        
        $this->signIn();

        $service = new PerfilService;

        $this->assertEquals([
            'message' => 'Perfil com ID 2 (' . Perfil::find(2)->nome . ') não pode ser excluído!',
            'class' => 'alert-danger',
        ], $service->delete(2));
    }
}
