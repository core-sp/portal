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
        $this->put(route('avisos.editar', $aviso->id), $dados)->assertRedirect(route('avisos.index'));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $texto .= $user->nome . ' (usuário ' . $user->idusuario . ') editou *aviso* (id: ' . $aviso->id . ')';
        $this->assertStringContainsString($texto, $log);
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
            'status' => 'Ativado'
        ]);
        $this->put(route('avisos.editar.status', $aviso->id), $dados)->assertRedirect(route('avisos.index'));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $texto = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: 127.0.0.1] - ';
        $texto .= $user->nome . ' (usuário ' . $user->idusuario . ') editou o status para ' .$dados['status'] . ' *aviso* (id: ' . $aviso->id . ')';
        $this->assertStringContainsString($texto, $log);
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
        $dados = factory('App\Aviso')->raw();
        $this->get(route('avisos.index'))->assertOk();
        $this->get(route('avisos.editar.view', $aviso->id))->assertOk();
        $this->put(route('avisos.editar', $aviso->id), $dados)->assertRedirect(route('avisos.index'));
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
        $dados = factory('App\Aviso')->raw();
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
            'status' => 'Ativado'
        ]);
        $this->get(route('avisos.index'))->assertOk();
        $this->put(route('avisos.editar.status', $aviso->id), $dados)->assertRedirect(route('avisos.index'));
        $this->assertDatabaseHas('avisos', ['status' => $dados['status']]);
    }

    /** @test */
    public function aviso_can_be_edited_with_dia_hora_ativar()
    {
        $user = $this->signInAsAdmin();

        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'dia_hora_ativar' => now()->addHour()->format('Y-m-d H:i')
        ]);

        $this->put(route('avisos.editar', $aviso->id), $dados)
        ->assertRedirect(route('avisos.index'));

        $this->assertDatabaseHas('avisos', [
            'dia_hora_ativar' => $dados['dia_hora_ativar']
        ]);
    }

    /** @test */
    public function aviso_can_be_edited_with_dia_hora_desativar()
    {
        $user = $this->signInAsAdmin();

        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'dia_hora_desativar' => now()->addHour()->format('Y-m-d H:i')
        ]);

        $this->put(route('avisos.editar', $aviso->id), $dados)
        ->assertRedirect(route('avisos.index'));

        $this->assertDatabaseHas('avisos', [
            'dia_hora_desativar' => $dados['dia_hora_desativar']
        ]);
    }

    /** @test */
    public function aviso_can_be_edited_with_dia_hora_ativar_and_dia_hora_desativar()
    {
        $user = $this->signInAsAdmin();

        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'dia_hora_ativar' => now()->addHour()->format('Y-m-d H:i'),
            'dia_hora_desativar' => now()->addDay()->format('Y-m-d H:i')
        ]);

        $this->put(route('avisos.editar', $aviso->id), $dados)
        ->assertRedirect(route('avisos.index'));

        $this->assertDatabaseHas('avisos', [
            'dia_hora_ativar' => $dados['dia_hora_ativar'],
            'dia_hora_desativar' => $dados['dia_hora_desativar']
        ]);
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
    public function cannot_updated_aviso_titulo_is_required()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'titulo' => '',
        ]);
        $this->put(route('avisos.editar', $aviso->id), $dados)->assertSessionHasErrors('titulo');
        $this->assertDatabaseMissing('avisos', ['conteudo' => $dados['conteudo']]);
    }

    /** @test 
     * 
    */
    public function can_updated_aviso_titulo_empty_if_bdo()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->states('bdo')->create();
        $dados = factory('App\Aviso')->states('bdo')->raw([
            'titulo' => 'Teste teste teste',
        ]);
        $this->put(route('avisos.editar', $aviso->id), $dados);
        $this->assertDatabaseMissing('avisos', ['titulo' => $dados['titulo']]);
    }

    /** @test 
     * 
     * Não pode editar aviso com o conteudo vazio
     * 
    */
    public function cannot_updated_aviso_conteudo_is_required()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'conteudo' => '',
        ]);
        $this->put(route('avisos.editar', $aviso->id), $dados)->assertSessionHasErrors('conteudo');
        $this->assertDatabaseMissing('avisos', ['titulo' => $dados['titulo']]);
    }

    /** @test 
    */
    public function cannot_updated_aviso_when_not_find_cor_fundo()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'cor_fundo_titulo' => 'qualquer',
        ]);
        $this->put(route('avisos.editar', $aviso->id), $dados)->assertSessionHasErrors('cor_fundo_titulo');
        $this->assertDatabaseMissing('avisos', ['cor_fundo_titulo' => $dados['cor_fundo_titulo']]);
    }

    /** @test 
     * 
     * Não pode editar aviso sem usuario
     * 
    */
    public function cannot_updated_aviso_usuario_is_required()
    {
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw();
        $this->put(route('avisos.editar', $aviso->id), $dados)->assertRedirect(route('login'));
        $this->assertDatabaseMissing('avisos', ['titulo' => $dados['titulo']]);
    }

    /** @test */
    public function cannot_updated_aviso_with_dia_hora_desativar_input_invalid_type()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'dia_hora_desativar' => 'abc',
        ]);

        $this->put(route('avisos.editar', $aviso->id), $dados)
        ->assertSessionHasErrors('dia_hora_desativar');
    }

    /** @test */
    public function cannot_updated_aviso_with_dia_hora_desativar_input_invalid_format()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'dia_hora_desativar' => now()->format('d/m/Y'),
        ]);

        $this->put(route('avisos.editar', $aviso->id), $dados)
        ->assertSessionHasErrors('dia_hora_desativar');
    }

    /** @test */
    public function cannot_updated_aviso_with_dia_hora_desativar_input_invalid_day()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'dia_hora_desativar' => now()->subDay()->format('Y-m-d H:i'),
        ]);

        $this->put(route('avisos.editar', $aviso->id), $dados)
        ->assertSessionHasErrors('dia_hora_desativar');
    }

    /** @test */
    public function cannot_updated_aviso_with_dia_hora_desativar_input_invalid_hour()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'dia_hora_desativar' => now()->subHour()->format('Y-m-d H:i'),
        ]);

        $this->put(route('avisos.editar', $aviso->id), $dados)
        ->assertSessionHasErrors('dia_hora_desativar');
    }

    /** @test */
    public function cannot_updated_aviso_with_dia_hora_desativar_input_invalid_minute()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'dia_hora_desativar' => now()->format('Y-m-d H:i'),
        ]);

        $this->put(route('avisos.editar', $aviso->id), $dados)
        ->assertSessionHasErrors('dia_hora_desativar');
    }

    /** @test */
    public function cannot_updated_aviso_with_dia_hora_ativar_input_invalid_type()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'dia_hora_ativar' => 'abc',
        ]);

        $this->put(route('avisos.editar', $aviso->id), $dados)
        ->assertSessionHasErrors('dia_hora_ativar');
    }

    /** @test */
    public function cannot_updated_aviso_with_dia_hora_ativar_input_invalid_format()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'dia_hora_ativar' => now()->format('d/m/Y'),
        ]);

        $this->put(route('avisos.editar', $aviso->id), $dados)
        ->assertSessionHasErrors('dia_hora_ativar');
    }

    /** @test */
    public function cannot_updated_aviso_with_dia_hora_ativar_input_invalid_day()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'dia_hora_ativar' => now()->subDay()->format('Y-m-d H:i'),
        ]);

        $this->put(route('avisos.editar', $aviso->id), $dados)
        ->assertSessionHasErrors('dia_hora_ativar');
    }

    /** @test */
    public function cannot_updated_aviso_with_dia_hora_ativar_input_invalid_hour()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'dia_hora_ativar' => now()->subHour()->format('Y-m-d H:i'),
        ]);

        $this->put(route('avisos.editar', $aviso->id), $dados)
        ->assertSessionHasErrors('dia_hora_ativar');
    }

    /** @test */
    public function cannot_updated_aviso_with_dia_hora_ativar_input_invalid_minute()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'dia_hora_ativar' => now()->format('Y-m-d H:i'),
        ]);

        $this->put(route('avisos.editar', $aviso->id), $dados)
        ->assertSessionHasErrors('dia_hora_ativar');
    }

    /** @test */
    public function cannot_updated_aviso_with_dia_hora_desativar_input_less_than_dia_hora_ativar_input()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'dia_hora_ativar' => now()->addHour()->format('Y-m-d H:i'),
            'dia_hora_desativar' => now()->format('Y-m-d H:i'),
        ]);

        $this->put(route('avisos.editar', $aviso->id), $dados)
        ->assertSessionHasErrors('dia_hora_desativar');

        $dados = factory('App\Aviso')->raw([
            'dia_hora_ativar' => now()->addHour()->format('Y-m-d H:i'),
            'dia_hora_desativar' => now()->addHour()->addMinutes(59)->format('Y-m-d H:i'),
        ]);

        $this->put(route('avisos.editar', $aviso->id), $dados)
        ->assertSessionHasErrors('dia_hora_desativar');
    }

    /** @test */
    public function cannot_updated_aviso_with_dia_hora_desativar_input_equal_dia_hora_ativar_input()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
        $dados = factory('App\Aviso')->raw([
            'dia_hora_ativar' => now()->addHour()->format('Y-m-d H:i'),
            'dia_hora_desativar' => now()->addHour()->format('Y-m-d H:i'),
        ]);

        $this->put(route('avisos.editar', $aviso->id), $dados)
        ->assertSessionHasErrors('dia_hora_desativar');
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

        $aviso = factory('App\Aviso')->states('bdo')->create();
        $this->get(route('avisos.index'))->assertSee($aviso['area']);
    }

    /** @test 
     * 
     * Visualiza na listagem quem editou o aviso
     * 
    */
    public function aviso_user_edited_is_shown_on_the_admin_panel()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->create();
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
        ]);
        $this->put(route('avisos.editar.status', $aviso->id), $dados)->assertRedirect(route('avisos.index'));
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
        $this->put(route('avisos.editar.status', $aviso->id), $dados)->assertRedirect(route('login'));
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
        ]);
        $this->put(route('avisos.editar.status', $aviso->id), $dados)->assertRedirect(route('avisos.index'));
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
        ]);
        $this->get(route('avisos.show', $aviso->id))
        ->assertSee($aviso->titulo)
        ->assertSee($aviso->conteudo)
        ->assertSee($aviso->cor_de_fundo);

        $aviso = factory('App\Aviso')->states('bdo')->create([
            'status' => 'Ativado',
        ]);
        $this->get(route('avisos.show', $aviso->id))
        ->assertDontSee($aviso->titulo)
        ->assertSee($aviso->conteudo)
        ->assertSee($aviso->cor_de_fundo);
    }

    /** @test */
    public function view_componente_by_area_aviso()
    {
        $user = $this->signInAsAdmin();

        $aviso = factory('App\Aviso')->create();
        $this->get(route('avisos.show', $aviso->id))
        ->assertSee('<a data-toggle="collapse" href="#collapseOne"><i class="fas fa-angle-down"></i>&nbsp;&nbsp;');

        $aviso = factory('App\Aviso')->states('bdo')->create();
        $this->get(route('avisos.show', $aviso->id))
        ->assertSee('<div class="alert alert-'. $aviso->cor_fundo_titulo . '">');
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
        ]);
        $this->put(route('avisos.editar', $aviso->id), $dados)->assertRedirect(route('avisos.index'));
        $this->get(route('avisos.show', $aviso->id))->assertSee($dados['cor_fundo_titulo']);
    }

    /** @test */
    public function view_msg_bdo_about_disabled_form()
    {
        $user = $this->signInAsAdmin();
        $aviso = factory('App\Aviso')->states('bdo')->create();
        $this->get(route('avisos.editar.view', $aviso->id))->assertSee(
            '<p><strong><span class="text-danger">ATENÇÃO!</span></strong> Esse aviso <strong>ATIVADO</strong> desabilita o envio de formulário para anunciar vaga!</p>'
        );
        $this->get(route('avisos.show', $aviso->id))->assertSee(
            '<p><strong><span class="text-danger">ATENÇÃO!</span></strong> Esse aviso <strong>ATIVADO</strong> desabilita o envio de formulário para anunciar vaga!</p>'
        );
    }

    /** @test 
     * 
     * Ver o aviso
     * 
    */
    public function badge_aviso_menu()
    {
        $user = $this->signInAsAdmin();
        factory('App\Aviso')->create([
            'status' => 'Ativado',
        ]);
        $this->get('/admin')
        ->assertSee('<span class="badge badge-pill badge-warning">Ativo</span>');

        factory('App\Aviso')->states('bdo')->create([
            'status' => 'Ativado',
        ]);
        $this->get('/admin')
        ->assertSee('<span class="badge badge-pill badge-warning">Ativo</span>');

        Aviso::find(1)->update([
            'status' => 'Desativado',
        ]);
        $this->get('/admin')
        ->assertSee('<span class="badge badge-pill badge-warning">Ativo</span>');

        Aviso::find(2)->update([
            'status' => 'Desativado',
        ]);
        $this->get('/admin')
        ->assertDontSee('<span class="badge badge-pill badge-warning">Ativo</span>');
    }

    /** 
     * =======================================================================================================
     * TESTES AVISO NA ÁREA DO REPRESENTANTE
     * =======================================================================================================
     */

    /** @test 
     * 
     * Visualiza o aviso que está ativado.
     * 
    */
    public function view_aviso_with_status_ativado()
    {
        $aviso = factory('App\Aviso')->create([
            'status' => 'Ativado',
        ]);
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.dashboard'))->assertSee($aviso->titulo);
    }

    /** @test 
     * 
     * Não visualiza o aviso que está desativado.
     * 
    */
    public function cannot_view_aviso_with_status_desativado()
    {
        $aviso = factory('App\Aviso')->create();
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.dashboard'))->assertDontSee($aviso->titulo);
    }

    /** @test 
     *
     * Não visualiza o aviso que não existe.
     * 
    */
    public function cannot_view_aviso_uncreated()
    {
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.dashboard'))->assertViewMissing('aviso');
    }

    /** @test 
     * 
     * Visualiza o aviso ativado em todas as rotas do RC após logon.
     * 
    */
    public function view_aviso_ativado_in_all_routes_get_after_logon()
    {
        $aviso = factory('App\Aviso')->create([
            'status' => 'Ativado',
        ]);
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.dashboard'))->assertSee($aviso->titulo);
        $this->get(route('representante.dados-gerais'))->assertSee($aviso->titulo);
        $this->get(route('representante.contatos.view'))->assertSee($aviso->titulo);
        $this->get(route('representante.enderecos.view'))->assertSee($aviso->titulo);
        $this->get(route('representante.inserir-ou-alterar-contato.view'))->assertSee($aviso->titulo);
        $this->get(route('representante.inserir-endereco.view'))->assertSee($aviso->titulo);
        $this->get(route('representante.lista-cobrancas'))->assertSee($aviso->titulo);
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO',
        ]);
        factory('App\BdoOportunidade')->create();
        $this->get(route('representante.bdo'))->assertSee($aviso->titulo);
        $this->get(route('representante.solicitarCedulaView'))->assertSee($aviso->titulo);
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertSee($aviso->titulo);
    }

    /** @test 
     *
     * Não visualiza o aviso desativado em todas as rotas do RC após logon.
     * 
    */
    public function cannot_view_aviso_desativado_in_all_routes_get_after_logon()
    {
        $aviso = factory('App\Aviso')->create();
        $representante = factory('App\Representante')->create();
        $this->post(route('representante.login.submit'), ['cpf_cnpj' => $representante['cpf_cnpj'], 'password' => 'teste102030']);
        $this->get(route('representante.dashboard'))->assertDontSee($aviso->titulo);
        $this->get(route('representante.dados-gerais'))->assertDontSee($aviso->titulo);
        $this->get(route('representante.contatos.view'))->assertDontSee($aviso->titulo);
        $this->get(route('representante.enderecos.view'))->assertDontSee($aviso->titulo);
        $this->get(route('representante.inserir-ou-alterar-contato.view'))->assertDontSee($aviso->titulo);
        $this->get(route('representante.inserir-endereco.view'))->assertDontSee($aviso->titulo);
        $this->get(route('representante.lista-cobrancas'))->assertDontSee($aviso->titulo);
        factory('App\Regional')->create([
            'regional' => 'SÃO PAULO',
        ]);
        factory('App\BdoOportunidade')->create();
        $this->get(route('representante.bdo'))->assertDontSee($aviso->titulo);
        $this->get(route('representante.solicitarCedulaView'))->assertDontSee($aviso->titulo);
        $this->get(route('representante.inserirSolicitarCedulaView'))->assertDontSee($aviso->titulo);
    }

    /** 
     * =======================================================================================================
     * TESTES AVISO (E DESABILITA ENVIO DE FORM) NA PÁGINA DE ANUNCIAR VAGA DO BALCÃO DE OPORTUNIDADES
     * =======================================================================================================
     */

    /** @test */
    public function view_aviso_bdo_with_status_ativado()
    {
        $aviso = factory('App\Aviso')->states('bdo')->create([
            'status' => 'Ativado',
        ]);
        $this->get(route('bdosite.anunciarVagaView'))->assertSee($aviso->conteudo);
    }

    /** @test */
    public function cannot_view_aviso_bdo_with_status_desativado()
    {
        $aviso = factory('App\Aviso')->states('bdo')->create();
        $this->get(route('bdosite.anunciarVagaView'))->assertDontSee($aviso->conteudo);
    }

    /** @test */
    public function cannot_view_aviso_bdo_uncreated()
    {
        $this->get(route('bdosite.anunciarVagaView'))->assertViewMissing('aviso');
    }

    /** @test */
    public function cannot_submit_when_aviso_bdo_with_status_ativado()
    {
        $bdoEmpresa = factory('App\BdoEmpresa')->raw();
        $bdoOportunidade = factory('App\BdoOportunidade')->raw();
        $dados = array_merge($bdoEmpresa, $bdoOportunidade);

        $aviso = factory('App\Aviso')->states('bdo')->create([
            'status' => 'Ativado',
        ]);
        $this->get(route('bdosite.anunciarVagaView'))->assertSee($aviso->conteudo);
        $this->post(route('bdosite.anunciarVaga'), $dados)
        ->assertSessionHasErrors([
            'idempresa',
            'cnpj',
            'titulo',
            'segmentoOportunidade',
            'nrVagas',
            'regiaoAtuacao',
            'descricaoOportunidade',
            'contatonome',
            'contatotelefone',
            'contatoemail',
            'termo'
        ], null, 'default');

        $this->assertDatabaseMissing('bdo_oportunidades', ['titulo' => $bdoOportunidade['titulo']]);
    }

    /** @test */
    public function can_submit_when_aviso_bdo_with_status_desativado()
    {
        $bdoEmpresa = factory('App\BdoEmpresa')->create()->toArray();
        $bdoOportunidade = factory('App\BdoOportunidade')->raw();
        $bdoOportunidade['segmentoOportunidade'] = $bdoOportunidade['segmento'];
        $bdoOportunidade['nrVagas'] = $bdoOportunidade['vagasdisponiveis'];
        $bdoOportunidade['regiaoAtuacao'] = explode(',', $bdoOportunidade['regiaoatuacao']);
        $bdoOportunidade['descricaoOportunidade'] = $bdoOportunidade['descricao'];

        $dados = array_merge($bdoEmpresa, $bdoOportunidade, ['termo' => 'on']);

        $aviso = factory('App\Aviso')->states('bdo')->create();
        
        $this->get(route('bdosite.anunciarVagaView'))->assertDontSee($aviso->conteudo);
        $this->post(route('bdosite.anunciarVaga'), $dados)
        ->assertViewIs('site.agradecimento');

        $this->assertDatabaseHas('bdo_oportunidades', ['titulo' => $bdoOportunidade['titulo']]);
    }

    /** @test */
    public function cannot_view_errors_after_submit_when_aviso_bdo_with_status_ativado()
    {
        $bdoEmpresa = factory('App\BdoEmpresa')->raw();
        $bdoOportunidade = factory('App\BdoOportunidade')->raw();
        $dados = array_merge($bdoEmpresa, $bdoOportunidade);

        $aviso = factory('App\Aviso')->states('bdo')->create([
            'status' => 'Ativado',
        ]);
        $this->get(route('bdosite.anunciarVagaView'))->assertSee($aviso->conteudo);
        $this->post(route('bdosite.anunciarVaga'), $dados)
        ->assertSessionHasErrors([
            'idempresa',
            'cnpj',
            'titulo',
            'segmentoOportunidade',
            'nrVagas',
            'regiaoAtuacao',
            'descricaoOportunidade',
            'contatonome',
            'contatotelefone',
            'contatoemail',
            'termo'
        ], null, 'default');

        $this->get(route('bdosite.anunciarVagaView'))
        ->assertDontSee('Por favor, informe o CNPJ')
        ->assertDontSee('Por favor, informe a quantidade de vagas da oportunidade')
        ->assertDontSee('Por favor, selecione ao menos uma região de atuação')
        ->assertDontSee('Por favor, insira a descrição da oportunidade')
        ->assertDontSee('Por favor, informe o nome do contato')
        ->assertDontSee('Por favor, informe o telefone do contato')
        ->assertDontSee('Por favor, informe o email do contato')
        ->assertDontSee('Por favor, informe o segmento da oportunidade')
        ->assertDontSee('Email inválido')
        ->assertDontSee('Você deve concordar com o Termo de Consentimento');

        $this->assertDatabaseMissing('bdo_oportunidades', ['titulo' => $bdoOportunidade['titulo']]);
    }

    /** @test */
    public function can_view_errors_after_submit_when_aviso_bdo_with_status_desativado()
    {
        $bdoEmpresa = factory('App\BdoEmpresa')->raw();
        $bdoOportunidade = factory('App\BdoOportunidade')->raw();
        $dados = array_merge($bdoEmpresa, $bdoOportunidade);

        $aviso = factory('App\Aviso')->states('bdo')->create();

        $this->get(route('bdosite.anunciarVagaView'))->assertDontSee($aviso->conteudo);
        $this->post(route('bdosite.anunciarVaga'), $dados)
        ->assertSessionHasErrors([
            'segmentoOportunidade',
            'nrVagas',
            'regiaoAtuacao',
            'descricaoOportunidade',
            'termo'
        ], null, 'default');

        $this->get(route('bdosite.anunciarVagaView'))
        ->assertSee('Por favor, informe a quantidade de vagas da oportunidade')
        ->assertSee('Por favor, selecione ao menos uma região de atuação')
        ->assertSee('Por favor, insira a descrição da oportunidade')
        ->assertSee('Por favor, informe o segmento da oportunidade')
        ->assertSee('Você deve concordar com o Termo de Consentimento');
    }

    /** @test */
    public function cannot_verify_cnpj_when_aviso_bdo_with_status_ativado()
    {
        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $aviso = factory('App\Aviso')->states('bdo')->create([
            'status' => 'Ativado',
        ]);

        $this->get(route('bdosite.apiGetEmpresa', apenasNumeros($bdoEmpresa->cnpj)))
        ->assertJson([
            'empresa' => '{}',
            'message' => 'Não é possível verificar no momento se a empresa está cadastrada.',
            'class' => 'alert-warning'
        ]);
    }

    /** @test */
    public function can_verify_cnpj_when_aviso_bdo_with_status_desativado()
    {
        $bdoEmpresa = factory('App\BdoEmpresa')->raw();
        $aviso = factory('App\Aviso')->states('bdo')->create();

        $this->get(route('bdosite.apiGetEmpresa', apenasNumeros($bdoEmpresa['cnpj'])))
        ->assertStatus(500);
        
        $bdoEmpresa = factory('App\BdoEmpresa')->create();

        $this->get(route('bdosite.apiGetEmpresa', apenasNumeros($bdoEmpresa->cnpj)))
        ->assertJsonFragment([
            'message' => 'Empresa já cadastrada. Favor seguir com o preenchimento da oportunidade abaixo.',
            'class' => 'alert-success'
        ]);
    }

    /** 
     * =======================================================================================================
     * TESTES AVISO NA PÁGINA DE ANUIDADE
     * =======================================================================================================
     */

    /** @test */
    public function view_aviso_anuidade_with_status_ativado()
    {
        $aviso = factory('App\Aviso')->states('anuidade')->create([
            'status' => 'Ativado',
        ]);
        $this->get(route('anuidade-ano-vigente'))->assertSee($aviso->conteudo);
    }

    /** @test */
    public function cannot_view_aviso_anuidade_with_status_desativado()
    {
        $aviso = factory('App\Aviso')->states('anuidade')->create();
        $this->get(route('anuidade-ano-vigente'))->assertDontSee($aviso->conteudo);
    }

    /** 
     * =======================================================================================================
     * TESTES AVISO NA PÁGINA DE AGENDAMENTO
     * =======================================================================================================
     */

    /** @test */
    public function view_aviso_agendamento_with_status_ativado()
    {
        $aviso = factory('App\Aviso')->states('agendamento')->create([
            'status' => 'Ativado',
        ]);
        $this->get(route('agendamentosite.formview'))->assertSee($aviso->conteudo);
    }

    /** @test */
    public function cannot_view_aviso_agendamento_with_status_desativado()
    {
        $aviso = factory('App\Aviso')->states('agendamento')->create();
        $this->get(route('agendamentosite.formview'))->assertDontSee($aviso->conteudo);
    }

    /* ROTINAS KERNEL */

    /** @test */
    public function kernel_desativar()
    {
        $aviso_bdo = factory('App\Aviso')->states(['bdo', 'data_desativar'])->create();
        $aviso_rep = factory('App\Aviso')->states('data_desativar')->create();

        $this->assertDatabaseHas('avisos', [
            'titulo' => $aviso_bdo['titulo'],
            'status' => 'Ativado',
            'dia_hora_desativar' => $aviso_bdo->dia_hora_desativar
        ]);

        $this->assertDatabaseHas('avisos', [
            'titulo' => $aviso_rep['titulo'],
            'status' => 'Ativado',
            'dia_hora_desativar' => $aviso_rep->dia_hora_desativar
        ]);

        $service = resolve('App\Contracts\MediadorServiceInterface');
        $service->getService('Aviso')->executarRotina();

        $this->assertDatabaseHas('avisos', [
            'titulo' => $aviso_bdo['titulo'],
            'status' => 'Desativado',
            'dia_hora_desativar' => null
        ]);

        $this->assertDatabaseHas('avisos', [
            'titulo' => $aviso_rep['titulo'],
            'status' => 'Desativado',
            'dia_hora_desativar' => null
        ]);
    }

    /** @test */
    public function log_is_generated_when_kernel_desativar()
    {
        $aviso_bdo = factory('App\Aviso')->states(['bdo', 'data_desativar'])->create();

        $service = resolve('App\Contracts\MediadorServiceInterface');
        $service->getService('Aviso')->executarRotina();

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [Rotina Portal - Avisos] - Aviso com ID ';
        $txt = $inicio . $aviso_bdo->id. ' e da área "' . $aviso_bdo->area. '" foi desativado.';
        $this->assertStringContainsString($txt, $log);

        $aviso_rep = factory('App\Aviso')->states('data_desativar')->create();
        $service->getService('Aviso')->executarRotina();

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [Rotina Portal - Avisos] - Aviso com ID ';
        $txt = $inicio . $aviso_rep->id. ' e da área "' . $aviso_rep->area. '" foi desativado.';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function kernel_ativar()
    {
        $aviso_bdo = factory('App\Aviso')->states(['bdo', 'data_ativar'])->create();
        $aviso_rep = factory('App\Aviso')->states('data_ativar')->create();

        $this->assertDatabaseHas('avisos', [
            'titulo' => $aviso_bdo['titulo'],
            'status' => 'Desativado',
            'dia_hora_ativar' => $aviso_bdo->dia_hora_ativar
        ]);

        $this->assertDatabaseHas('avisos', [
            'titulo' => $aviso_rep['titulo'],
            'status' => 'Desativado',
            'dia_hora_ativar' => $aviso_rep->dia_hora_ativar
        ]);

        $service = resolve('App\Contracts\MediadorServiceInterface');
        $service->getService('Aviso')->executarRotina();

        $this->assertDatabaseHas('avisos', [
            'titulo' => $aviso_bdo['titulo'],
            'status' => 'Ativado',
            'dia_hora_ativar' => null
        ]);

        $this->assertDatabaseHas('avisos', [
            'titulo' => $aviso_rep['titulo'],
            'status' => 'Ativado',
            'dia_hora_ativar' => null
        ]);
    }

    /** @test */
    public function log_is_generated_when_kernel_ativar()
    {
        $aviso_bdo = factory('App\Aviso')->states(['bdo', 'data_ativar'])->create();

        $service = resolve('App\Contracts\MediadorServiceInterface');
        $service->getService('Aviso')->executarRotina();

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [Rotina Portal - Avisos] - Aviso com ID ';
        $txt = $inicio . $aviso_bdo->id. ' e da área "' . $aviso_bdo->area. '" foi ativado.';
        $this->assertStringContainsString($txt, $log);

        $aviso_rep = factory('App\Aviso')->states('data_ativar')->create();
        $service->getService('Aviso')->executarRotina();

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [Rotina Portal - Avisos] - Aviso com ID ';
        $txt = $inicio . $aviso_rep->id. ' e da área "' . $aviso_rep->area. '" foi ativado.';
        $this->assertStringContainsString($txt, $log);
    }
}
