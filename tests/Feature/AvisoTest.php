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

    /** @test 
     * 
     * Ver as opções de cores ao editar o aviso
     * 
    */
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


}
