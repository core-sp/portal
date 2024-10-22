<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Perfil;
use App\Permissao;

class PerfilTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $perfil = factory('App\Perfil')->create();
        
        $this->get(route('perfis.lista'))->assertRedirect(route('login'));
        $this->get(route('perfis.create'))->assertRedirect(route('login'));
        $this->post(route('perfis.store'))->assertRedirect(route('login'));
        $this->get(route('perfis.permissoes.edit', $perfil->idperfil))->assertRedirect(route('login'));
        $this->put(route('perfis.permissoes.put', $perfil->idperfil))->assertRedirect(route('login'));
        $this->delete(route('perfis.destroy', $perfil->idperfil))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $perfil = factory('App\Perfil')->create();
        
        $this->get(route('perfis.lista'))->assertForbidden();
        $this->get(route('perfis.create'))->assertForbidden();
        $this->post(route('perfis.store'))->assertForbidden();
        $this->get(route('perfis.permissoes.edit', $perfil->idperfil))->assertForbidden();
        $this->put(route('perfis.permissoes.put', $perfil->idperfil))->assertForbidden();
        $this->delete(route('perfis.destroy', $perfil->idperfil))->assertForbidden();
    }

    /** @test */
    public function admin_can_access_links()
    {
        $this->signInAsAdmin();
        $perfil = factory('App\Perfil')->create();
        
        $this->get(route('perfis.lista'))->assertOk();
        $this->get(route('perfis.create'))->assertOk();
        $this->post(route('perfis.store'))->assertStatus(302);
        $this->get(route('perfis.permissoes.edit', $perfil->idperfil))->assertOk();
        $this->put(route('perfis.permissoes.put', $perfil->idperfil))->assertStatus(302);
        $this->delete(route('perfis.destroy', $perfil->idperfil))->assertStatus(302);
    }

    /** @test */
    public function can_change_password()
    {
        $user = $this->signInAsAdmin();

        $senha = $user->password;
        $this->get('/admin/perfil/senha')
        ->assertSee('<label for="password-text" class="m-0 p-0">Força da senha</label>')
        ->assertSee('<div class="progress" id="password-text"></div>')
        ->assertSee('<small><em>Em caso de senha fraca ou média, considere alterá-la para sua segurança.</em></small>')
        ->assertSuccessful();

        $this->put('/admin/perfil/senha', [
            'current-password' => 'Teste102030',
            'password' => 'TestePortal123@#$%&',
            'password_confirmation' => 'TestePortal123@#$%&', 
        ])->assertRedirect(route('admin.info'));

        $this->get(route('admin.info'))
        ->assertSeeText('Senha alterada com sucesso!');

        $this->assertNotEquals($user->fresh()->password, $senha);
    }

    // Mínimo 6 caracteres, com 1 letra maiúscula, 1 minúscula e 1 número.
    /** @test */
    public function cannot_change_password_with_wrong_regex()
    {
        $user = $this->signInAsAdmin();

        $senhas = ['esteortal', 'TestePortal', 'esteortal1', 'TESTEPORTAL1', '1234561', 'Test1'];

        foreach($senhas as $senha)
            $this->put('/admin/perfil/senha', [
                'current-password' => 'Teste102030',
                'password' => $senha,
                'password_confirmation' => $senha, 
            ])->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function log_is_generated_when_change_password()
    {
        $user = $this->signInAsAdmin();

        $this->put('/admin/perfil/senha', [
            'current-password' => 'Teste102030',
            'password' => 'TestePortal123@#$%&',
            'password_confirmation' => 'TestePortal123@#$%&', 
        ]);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $texto .= $user->nome.' (usuário '.$user->idusuario.') alterou senha *perfil* (id: ' . $user->idusuario . ')';
        $this->assertStringContainsString($texto, $log);
    }

    /** @test */
    public function profile_can_be_created_by_an_user()
    {
        $user = $this->signInAsAdmin();

        $this->get(route('perfis.lista'))->assertOk();
        $this->get(route('perfis.create'))->assertOk();

        $this->post(route('perfis.store'), ['nome' => 'Novo Perfil'])
        ->assertRedirect(route('perfis.lista'))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Perfil cadastrado com sucesso!');

        $this->assertDatabaseHas('perfis', [
            'idperfil' => 2,
            'nome' => 'Novo Perfil'
        ]);
    }

    /** @test */
    public function log_is_generated_when_profile_is_created()
    {
        $user = $this->signInAsAdmin();

        $this->post(route('perfis.store'), ['nome' => 'Novo Perfil'])
        ->assertRedirect(route('perfis.lista'));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') criou *perfil de usuário* (id: 2)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function profile_cannot_be_created_by_an_user_without_nome()
    {
        $user = $this->signInAsAdmin();

        $this->get(route('perfis.lista'))->assertOk();
        $this->get(route('perfis.create'))->assertOk();

        $this->post(route('perfis.store'), [])
        ->assertSessionHasErrors(['nome']);

        $this->assertDatabaseMissing('perfis', [
            'idperfil' => 2,
        ]);
    }

    /** @test */
    public function profile_cannot_be_created_by_an_user_with_nome_less_than_4_chars()
    {
        $user = $this->signInAsAdmin();

        $this->get(route('perfis.lista'))->assertOk();
        $this->get(route('perfis.create'))->assertOk();

        $this->post(route('perfis.store'), ['nome' => 'ABC'])
        ->assertSessionHasErrors(['nome']);

        $this->assertDatabaseMissing('perfis', [
            'idperfil' => 2,
        ]);
    }

    /** @test */
    public function profile_cannot_be_created_by_an_user_with_nome_more_than_191_chars()
    {
        $user = $this->signInAsAdmin();

        $this->get(route('perfis.lista'))->assertOk();
        $this->get(route('perfis.create'))->assertOk();

        $this->post(route('perfis.store'), ['nome' => $this->faker()->sentence(300)])
        ->assertSessionHasErrors(['nome']);

        $this->assertDatabaseMissing('perfis', [
            'idperfil' => 2,
        ]);
    }

    /** @test */
    public function profile_cannot_be_created_by_an_user_with_nome_exists_in_database()
    {
        $user = $this->signInAsAdmin();

        $this->get(route('perfis.lista'))->assertOk();
        $this->get(route('perfis.create'))->assertOk();

        $this->post(route('perfis.store'), ['nome' => 'Admin'])
        ->assertSessionHasErrors(['nome']);

        $this->assertDatabaseMissing('perfis', [
            'idperfil' => 2,
        ]);
    }

    /** @test */
    public function profile_24_cannot_access_services()
    {
        $user = $this->signIn(factory('App\User')->create([
            'idperfil' => factory('App\Perfil')->states('bloqueado')->create()
        ]));

        // sem permissão
        $this->get(route('regionais.index'))->assertOk();

        // com permissão
        $this->get(route('perfis.lista'))->assertStatus(403);
        $this->get(route('paginas.index'))->assertStatus(403);
        $this->get(route('paginas.create'))->assertStatus(403);
        $this->get(route('noticias.index'))->assertStatus(403);
        $this->get(route('cursos.index'))->assertStatus(403);
        $this->get(route('bdooportunidades.lista'))->assertStatus(403);
        $this->get(route('imagens.banner'))->assertStatus(403);
        $this->get(route('solicita-cedula.index'))->assertStatus(403);
        $this->get(route('concursos.index'))->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_list_profiles()
    {
        $this->signInAsAdmin();
        $perfis = factory('App\Perfil', 5)->create();
        
        $this->get(route('perfis.lista'))
        ->assertOk()
        ->assertSee('<p class="text-danger"><i class="fas fa-exclamation-triangle"></i><i>&nbsp;&nbsp;Somente perfil (exceto \'Admin\' e \'Bloqueado\') sem usuário(s) ativo(s) pode ser excluído.</i></p>')
        ->assertSeeText($perfis->get(0)->nome)
        ->assertSeeText($perfis->get(1)->nome)
        ->assertSeeText($perfis->get(2)->nome)
        ->assertSeeText($perfis->get(3)->nome)
        ->assertSeeText($perfis->get(4)->nome);
    }

    /** @test */
    public function admin_cannot_view_btn_destroy_profiles_1_and_24()
    {
        $this->signInAsAdmin();

        $perfis = factory('App\Perfil', 5)->create();
        factory('App\User')->create([
            'idperfil' => $perfis->get(0)->idperfil
        ]);
        factory('App\Perfil')->states('bloqueado')->create();
        
        $this->get(route('perfis.lista'))
        ->assertOk()
        ->assertSee('<p class="text-danger"><i class="fas fa-exclamation-triangle"></i><i>&nbsp;&nbsp;Somente perfil (exceto \'Admin\' e \'Bloqueado\') sem usuário(s) ativo(s) pode ser excluído.</i></p>')
        ->assertDontSee('<form method="POST" action="'. route('perfis.destroy', $perfis->get(0)->idperfil) . '" class="d-inline">')
        ->assertSee('<form method="POST" action="'. route('perfis.destroy', $perfis->get(1)->idperfil) . '" class="d-inline">')
        ->assertSee('<form method="POST" action="'. route('perfis.destroy', $perfis->get(2)->idperfil) . '" class="d-inline">')
        ->assertSee('<form method="POST" action="'. route('perfis.destroy', $perfis->get(3)->idperfil) . '" class="d-inline">')
        ->assertSee('<form method="POST" action="'. route('perfis.destroy', $perfis->get(4)->idperfil) . '" class="d-inline">')
        ->assertDontSee('<form method="POST" action="'. route('perfis.destroy', 1) . '" class="d-inline">')
        ->assertDontSee('<form method="POST" action="'. route('perfis.destroy', 24) . '" class="d-inline">')
        ->assertSee('<td>' . Perfil::find(1)->nome . '</td><td>1</td><td>74</td>')
        ->assertSee('<td>' . Perfil::find(24)->nome . '</td><td>0</td><td>0</td>');
    }

    /** @test */
    public function admin_can_view_btn_edit()
    {
        $this->signInAsAdmin();

        $perfis = factory('App\Perfil', 5)->create();
        factory('App\User')->create([
            'idperfil' => $perfis->get(0)->idperfil
        ]);
        factory('App\Perfil')->states('bloqueado')->create();
        
        $this->get(route('perfis.lista'))
        ->assertOk()
        ->assertSee('<p class="text-danger"><i class="fas fa-exclamation-triangle"></i><i>&nbsp;&nbsp;Somente perfil (exceto \'Admin\' e \'Bloqueado\') sem usuário(s) ativo(s) pode ser excluído.</i></p>')
        ->assertSee('<a href="'. route('perfis.permissoes.edit', $perfis->get(0)->idperfil) . '" class="btn btn-sm btn-primary mr-2"> Permissões</a> ')
        ->assertSee('<a href="'. route('perfis.permissoes.edit', $perfis->get(1)->idperfil) . '" class="btn btn-sm btn-primary mr-2"> Permissões</a> ')
        ->assertSee('<a href="'. route('perfis.permissoes.edit', $perfis->get(2)->idperfil) . '" class="btn btn-sm btn-primary mr-2"> Permissões</a> ')
        ->assertSee('<a href="'. route('perfis.permissoes.edit', $perfis->get(3)->idperfil) . '" class="btn btn-sm btn-primary mr-2"> Permissões</a> ')
        ->assertSee('<a href="'. route('perfis.permissoes.edit', $perfis->get(4)->idperfil) . '" class="btn btn-sm btn-primary mr-2"> Permissões</a> ')
        ->assertSee('<a href="'. route('perfis.permissoes.edit', 1) . '" class="btn btn-sm btn-primary mr-2"> Permissões</a> ')
        ->assertSee('<a href="'. route('perfis.permissoes.edit', 24) . '" class="btn btn-sm btn-primary mr-2"> Permissões</a> ');
    }

    /** @test */
    public function profile_can_be_deleted()
    {
        $this->signInAsAdmin();

        $perfil = factory('App\Perfil')->create();

        $this->delete(route('perfis.destroy', $perfil->idperfil))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Perfil com ID 2 deletado com sucesso!');

        $this->assertSoftDeleted('perfis', ['idperfil' => $perfil->idperfil]);
    }

    /** @test */
    public function log_is_generated_when_profile_is_deleted()
    {
        $user = $this->signInAsAdmin();

        $perfil = factory('App\Perfil')->create();
        $this->relacionarPerfilPermissao($perfil, 'CursoController', 'index');

        $this->delete(route('perfis.destroy', $perfil->idperfil))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Perfil com ID 2 deletado com sucesso!');

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') apagou *perfil de usuário e suas permissões* (id: 2)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function profile_cannot_be_deleted_with_users()
    {
        $this->signInAsAdmin();

        $user = factory('App\User')->create();

        $this->delete(route('perfis.destroy', $user->idperfil))
        ->assertSessionHas('message', 'Perfil com ID 2 (' . $user->perfil->nome . ') não pode ser excluído!');

        $this->assertDatabaseHas('perfis', ['idperfil' => $user->perfil->idperfil, 'deleted_at' => null]);
    }

    /** @test */
    public function profile_cannot_be_deleted_with_idperfil_1_or_24()
    {
        $this->signInAsAdmin();

        $user = factory('App\User')->create([
            'idperfil' => factory('App\Perfil')->states('bloqueado')->create()
        ]);

        // bloqueado com usuário vinculado
        $this->delete(route('perfis.destroy', $user->idperfil))
        ->assertSessionHas('message', 'Perfil com ID 24 (' . $user->perfil->nome . ') não pode ser excluído!');

        $this->assertDatabaseHas('perfis', ['idperfil' => $user->perfil->idperfil, 'deleted_at' => null]);

        // bloqueado sem usuário vinculado
        $user->update(['idperfil' => 1]);
        $this->delete(route('perfis.destroy', 24))
        ->assertSessionHas('message', 'Perfil com ID 24 (Bloqueado) não pode ser excluído!');

        $this->assertDatabaseHas('perfis', ['idperfil' => $user->perfil->idperfil, 'deleted_at' => null]);

        // admin
        $this->delete(route('perfis.destroy', 1))
        ->assertSessionHas('message', 'Perfil com ID 1 (Admin) não pode ser excluído!');

        $this->assertDatabaseHas('perfis', ['idperfil' => 1, 'deleted_at' => null]);
    }

    /** @test */
    public function permissions_can_be_deleted_when_profile_deleted()
    {
        $this->signInAsAdmin();

        $perfil = factory('App\Perfil')->create();
        $ids = [11, 12, 13, 14, 19, 20, 21, 22, 33, 34, 35, 36, 46];

        foreach(['index', 'create', 'edit', 'destroy'] as $acao)
        {
            $this->relacionarPerfilPermissao($perfil, 'CursoController', $acao);
            $this->relacionarPerfilPermissao($perfil, 'BdoEmpresaController', $acao);
            $this->relacionarPerfilPermissao($perfil, 'LicitacaoController', $acao);
        }

        $this->relacionarPerfilPermissao($perfil, 'RepresentanteEnderecoController', 'show');

        foreach($ids as $id)
            $this->assertDatabaseHas('perfil_permissao', ['perfil_id' => $perfil->idperfil, 'permissao_id' => $id]);

        // remover perfil
        $this->delete(route('perfis.destroy', $perfil->idperfil))
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Perfil com ID 2 deletado com sucesso!');

        foreach($ids as $id)
            $this->assertDatabaseMissing('perfil_permissao', ['perfil_id' => $perfil->idperfil, 'permissao_id' => $id]);

        $this->assertSoftDeleted('perfis', ['idperfil' => $perfil->idperfil]);
    }

    /** @test */
    public function admin_can_view_all_permissions_by_profile()
    {
        $this->signInAsAdmin();

        $permissoes = Permissao::orderBy('nome')->orderByRaw(
            'CASE 
            WHEN metodo = "index" THEN 1
            WHEN metodo = "create" THEN 2
            WHEN metodo = "edit" THEN 3
            WHEN metodo = "show" THEN 4
            WHEN metodo = "destroy" THEN 5
            END 
        ')->get()
        ->transform(function ($item, $key) {
            return ['<td>', '<input ', 'type="checkbox" ', 'class="form-check-input"', 
            'name="permissoes[]" value="' . $item->idpermissao . '"', 'checked', '/>', '</td>'];
        })->collapse()
        ->all();

        $this->get(route('perfis.permissoes.edit', 1))
        ->assertSeeInOrder($permissoes);
    }

    /** @test */
    public function permissions_can_be_edit_by_profile()
    {
        $this->signInAsAdmin();

        $perfil = factory('App\Perfil')->create();
        $permissoes = Permissao::orderBy('nome')->get()->pluck('idpermissao')->all();

        $this->get(route('perfis.permissoes.edit', $perfil->idperfil))
        ->assertOk();

        $this->put(route('perfis.permissoes.put', $perfil->idperfil), ['permissoes' => $permissoes])
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Permissões do perfil com ID ' . $perfil->idperfil . ' foram atualizadas com sucesso!');

        foreach($permissoes as $id)
            $this->assertDatabaseHas('perfil_permissao', ['perfil_id' => $perfil->idperfil, 'permissao_id' => $id]);
    }

    /** @test */
    public function log_is_generated_when_profile_is_updated()
    {
        $user = $this->signInAsAdmin();

        $perfil = factory('App\Perfil')->create();
        $permissoes = Permissao::orderBy('nome')->get()->pluck('idpermissao')->all();

        $this->put(route('perfis.permissoes.put', $perfil->idperfil), ['permissoes' => $permissoes])
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Permissões do perfil com ID ' . $perfil->idperfil . ' foram atualizadas com sucesso!');

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou *permissões do perfil ' . $perfil->idperfil .'* (id: ' . implode(', ', $permissoes) . ')';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function profile_cannot_be_edit_by_profile_without_permission_except_id_24()
    {
        $this->signInAsAdmin();

        $perfil = factory('App\Perfil')->create();
        $bloqueado = factory('App\Perfil')->states('bloqueado')->create();

        $this->get(route('perfis.permissoes.edit', $perfil->idperfil))
        ->assertOk();

        $this->put(route('perfis.permissoes.put', $perfil->idperfil), ['permissoes' => []])
        ->assertSessionHasErrors(['permissoes']);

        $this->put(route('perfis.permissoes.put', $bloqueado->idperfil), ['permissoes' => []])
        ->assertSessionHas('message', '<i class="icon fa fa-check"></i>Permissões do perfil com ID ' . $bloqueado->idperfil . ' foram atualizadas com sucesso!');

        $this->assertDatabaseMissing('perfil_permissao', ['perfil_id' => $perfil->idperfil]);
        $this->assertDatabaseMissing('perfil_permissao', ['perfil_id' => $bloqueado->idperfil]);
    }

    /** @test */
    public function profile_cannot_be_edit_by_profile_without_array()
    {
        $this->signInAsAdmin();

        $perfil = factory('App\Perfil')->create();

        $this->get(route('perfis.permissoes.edit', $perfil->idperfil))
        ->assertOk();

        $this->put(route('perfis.permissoes.put', $perfil->idperfil), ['permissoes' => 1])
        ->assertSessionHasErrors(['permissoes']);

        $this->assertDatabaseMissing('perfil_permissao', ['perfil_id' => $perfil->idperfil]);
    }

    /** @test */
    public function profile_cannot_be_edit_by_profile_without_id_in_database()
    {
        $this->signInAsAdmin();

        $perfil = factory('App\Perfil')->create();

        $this->get(route('perfis.permissoes.edit', $perfil->idperfil))
        ->assertOk();

        $this->put(route('perfis.permissoes.put', $perfil->idperfil), ['permissoes' => [1, 555, 2]])
        ->assertSessionHasErrors(['permissoes.*']);

        $this->assertDatabaseMissing('perfil_permissao', ['perfil_id' => $perfil->idperfil]);
    }

    /** @test */
    public function profile_cannot_be_edit_by_profile_without_distinct_id()
    {
        $this->signInAsAdmin();

        $perfil = factory('App\Perfil')->create();

        $this->get(route('perfis.permissoes.edit', $perfil->idperfil))
        ->assertOk();

        $this->put(route('perfis.permissoes.put', $perfil->idperfil), ['permissoes' => [1, 2, 2]])
        ->assertSessionHasErrors(['permissoes.*']);

        $this->assertDatabaseMissing('perfil_permissao', ['perfil_id' => $perfil->idperfil]);
    }
}
