<?php

namespace Tests\Feature;

use App\Aviso;
use App\Permissao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AvisoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();

        $aviso = factory('App\Aviso')->create();

        $this->get(route('avisos.index'))->assertRedirect(route('login'));
        $this->get(route('avisos.show', $aviso->id))->assertRedirect(route('login'));
        $this->get(route('avisos.editar.view', $aviso->id))->assertRedirect(route('login'));
        $this->put(route('avisos.editar', $aviso->id))->assertRedirect(route('login'));
        $this->put(route('avisos.editar.status', $aviso->id))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');

        $aviso = factory('App\Aviso')->create();

        $this->get(route('avisos.index'))->assertForbidden();
        $this->get(route('avisos.show', $aviso->id))->assertForbidden();
        $this->get(route('avisos.editar.view', $aviso->id))->assertForbidden();
        $this->put(route('avisos.editar', $aviso->id), $aviso->toArray())->assertForbidden();
        $this->put(route('avisos.editar.status', $aviso->id))->assertForbidden();
    }

    /** @test 
     * 
     * Log gerado após editar o aviso.
     * 
    */
    public function log_is_generated_when_aviso_is_edited()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'idusuario' => $user->idusuario
        ]);
        $this->put(route('avisos.editar', $aviso->id), $dados)->assertStatus(302);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('editou', $log);
        $this->assertStringContainsString('aviso', $log);
    }

    /** @test 
     * 
     * Log gerado após editar o status do aviso.
     * 
    */
    public function log_is_generated_when_status_is_edited()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'idusuario' => $user->idusuario,
            'status' => 'Ativado'
        ]);
        $this->put(route('avisos.editar.status', $aviso->id), $dados)->assertStatus(302);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('editou o status para ' .$dados['status'], $log);
        $this->assertStringContainsString('aviso', $log);
    }

    /** @test 
     * 
     * Aviso pode ser editado pelo usuario.
     * 
    */
    public function aviso_can_be_edited_by_user()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'idusuario' => $user->idusuario
        ]);
        $this->get(route('avisos.index'))->assertOk();
        $this->get(route('avisos.editar.view', $aviso->id))->assertOk();
        $this->put(route('avisos.editar', $aviso->id), $dados)->assertStatus(302);
        $this->assertDatabaseHas('avisos', ['titulo' => $dados['titulo']]);
    }

    /** @test 
     * 
     * Aviso não pode ser editado pelo usuario sem permissão.
     * 
    */
    public function aviso_cannot_be_edited_by_user_without_permission()
    {
        $user = $this->signIn();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'idusuario' => $user->idusuario
        ]);
        $this->get(route('avisos.index'))->assertForbidden();
        $this->get(route('avisos.editar.view', $aviso->id))->assertForbidden();
        $this->put(route('avisos.editar', $aviso->id), $dados)->assertForbidden();
        $this->assertDatabaseMissing('avisos', ['titulo' => $dados['titulo']]);
    }

    /** @test 
     * 
     * Status do aviso pode ser editado pelo usuario.
     * 
    */
    public function status_aviso_can_be_edited_by_user()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'idusuario' => $user->idusuario,
            'status' => 'Ativado'
        ]);
        $this->get(route('avisos.index'))->assertOk();
        $this->put(route('avisos.editar.status', $aviso->id), $dados)->assertStatus(302);
        $this->assertDatabaseHas('avisos', ['status' => $dados['status']]);
    }

    /** @test 
     * 
     * Status do aviso não pode ser editado pelo usuario sem permissão.
     * 
    */
    public function status_aviso_cannot_be_edited_by_user_without_permission()
    {
        $user = $this->signIn();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'idusuario' => $user->idusuario,
            'status' => 'Ativado'
        ]);
        $this->get(route('avisos.index'))->assertForbidden();
        $this->put(route('avisos.editar.status', $aviso->id), $dados)->assertForbidden();
        $this->assertDatabaseMissing('avisos', ['status' => $dados['status']]);
    }

    /** @test 
     * 
     * Não pode editar aviso com o título vazio
     * 
    */
    public function aviso_titulo_is_required()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'titulo' => '',
            'idusuario' => $user->idusuario
        ]);
        $this->put(route('avisos.editar', $aviso->id), $dados)->assertSessionHasErrors('titulo');
        $this->assertDatabaseMissing('avisos', ['conteudo' => $dados['conteudo']]);
    }

    /** @test 
     * 
     * Não pode editar aviso com o conteudo vazio
     * 
    */
    public function aviso_conteudo_is_required()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'conteudo' => '',
            'idusuario' => $user->idusuario
        ]);
        $this->put(route('avisos.editar', $aviso->id), $dados)->assertSessionHasErrors('conteudo');
        $this->assertDatabaseMissing('avisos', ['titulo' => $dados['titulo']]);
    }

    /** @test 
     * 
     * Não pode editar aviso sem usuario
     * 
    */
    public function aviso_usuario_is_required()
    {
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw();
        $this->put(route('avisos.editar', $aviso->id), $dados)->assertStatus(302);
        $this->assertDatabaseMissing('avisos', ['titulo' => $dados['titulo']]);
    }

    /** @test 
     * 
     * Visualiza na listagem o aviso criado
     * 
    */
    public function created_aviso_are_shown_on_the_admin_panel()
    {
        $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $this->get(route('avisos.index'))->assertSee($aviso['titulo']);
    }

    /** @test 
     * 
     * Visualiza na listagem quem editou o aviso
     * 
    */
    public function aviso_user_edited_is_shown_on_the_admin_panel()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create([
            'idusuario' => $user->idusuario,
        ]);
        $this->get(route('avisos.index'))->assertSee($user->nome);
    }

    /** @test 
     * 
     * Aviso atualiza o status
     * 
    */
    public function aviso_has_status_updated()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'status' => 'Ativado',
            'idusuario' => $user->idusuario
        ]);
        $this->put(route('avisos.editar.status', $aviso->id), $dados)->assertStatus(302);
        $this->assertDatabaseHas('avisos', ['status' => $dados['status']]);
    }

    /** @test 
     * 
     * Aviso não atualiza o status sem usuario
     * 
    */
    public function aviso_hasnt_status_updated_without_user()
    {
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'status' => 'Ativado'
        ]);
        $this->put(route('avisos.editar.status', $aviso->id), $dados)->assertStatus(302);
        $this->assertDatabaseHas('avisos', ['status' => $aviso['status']]);
    }

    /** @test 
     * 
     * O botão de ativar/desativar o status deve conter o valor inverso do status atual
     * 
    */
    public function view_button_ativar_not_equal_status()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'status' => 'Ativado',
            'idusuario' => $user->idusuario
        ]);
        $this->put(route('avisos.editar.status', $aviso->id), $dados)->assertStatus(302);
        $this->get(route('avisos.index'))->assertSee('Desativar');
    }

    /** @test 
     * 
     * Ver o aviso
     * 
    */
    public function view_aviso()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create([
            'status' => 'Ativado',
            'idusuario' => $user->idusuario
        ]);
        $this->get(route('avisos.show', $aviso->id))
        ->assertSee($aviso->titulo)
        ->assertSee($aviso->conteudo)
        ->assertSee($aviso->cor_de_fundo);
    }

    /** @test 
     * 
     * Ver as opções de cores ao editar o aviso
     * 
    */
    public function view_colors_options_when_edit()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $this->get(route('avisos.editar.view', $aviso->id))->assertViewHas('cores');
    }

    /** @test 
     * 
     * Ao atualizar com nova cor do título, deve aparecer ao ver o aviso
     * 
    */
    public function view_new_color_titulo_after_updated()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'cor_fundo_titulo' => 'danger',
            'idusuario' => $user->idusuario
        ]);
        $this->put(route('avisos.editar', $aviso->id), $dados)->assertStatus(302);
        $this->get(route('avisos.show', $aviso->id))->assertSee($dados['cor_fundo_titulo']);
    }
}
