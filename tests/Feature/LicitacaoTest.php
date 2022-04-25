<?php

namespace Tests\Feature;

use App\Licitacao;
use App\Permissao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicitacaoTest extends TestCase
{
    use RefreshDatabase;

    // protected function setUp(): void
    // {
    //     parent::setUp();
    //     Permissao::insert([
    //         [
    //             'controller' => 'LicitacaoController',
    //             'metodo' => 'index',
    //             'perfis' => '1,'
    //         ], [
    //             'controller' => 'LicitacaoController',
    //             'metodo' => 'create',
    //             'perfis' => '1,'
    //         ], [
    //             'controller' => 'LicitacaoController',
    //             'metodo' => 'edit',
    //             'perfis' => '1,'
    //         ], [
    //             'controller' => 'LicitacaoController',
    //             'metodo' => 'destroy',
    //             'perfis' => '1,'
    //         ]
    //     ]);
    // }

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.index'))->assertRedirect(route('login'));
        $this->get(route('licitacoes.create'))->assertRedirect(route('login'));
        $this->get(route('licitacoes.edit', $licitacao->idlicitacao))->assertRedirect(route('login'));
        $this->post(route('licitacoes.store'))->assertRedirect(route('login'));
        $this->patch(route('licitacoes.update', $licitacao->idlicitacao))->assertRedirect(route('login'));
        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao))->assertRedirect(route('login'));
        $this->get(route('licitacoes.restore', $licitacao->idlicitacao))->assertRedirect(route('login'));
        $this->get(route('licitacoes.busca'))->assertRedirect(route('login'));
        $this->get(route('licitacoes.trashed'))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.index'))->assertForbidden();
        $this->get(route('licitacoes.create'))->assertForbidden();
        $this->get(route('licitacoes.edit', $licitacao->idlicitacao))->assertForbidden();
        $this->post(route('licitacoes.store'), $licitacao->toArray())->assertForbidden();
        $this->patch(route('licitacoes.update', $licitacao->idlicitacao), $licitacao->toArray())->assertForbidden();
        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao))->assertForbidden();
        $this->get(route('licitacoes.restore', $licitacao->idlicitacao))->assertForbidden();
        $this->get(route('licitacoes.busca'))->assertForbidden();
        $this->get(route('licitacoes.trashed'))->assertForbidden();
    }

    /** @test */
    public function licitacao_can_be_created_by_an_user()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw();

        $this->get(route('licitacoes.index'))->assertOk();
        $this->post(route('licitacoes.store'), $attributes);
        $this->assertDatabaseHas('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function log_is_generated_when_licitacao_is_created()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->raw();

        $this->post(route('licitacoes.store'), $attributes);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('criou', $log);
        $this->assertStringContainsString('licitação', $log);
    }

    /** @test */
    public function licitacao_is_shown_on_admin_panel_after_its_creation()
    {
        $this->signInAsAdmin();
        $licitacao = factory('App\Licitacao')->create();
        
        $this->get(route('licitacoes.index'))
            ->assertSee($licitacao->idlicitacao)
            ->assertSee($licitacao->modalidade);
    }

    /** @test */
    public function licitacao_is_shown_on_website_list_after_its_creation()
    {
        $this->signInAsAdmin();
        $licitacao = factory('App\Licitacao')->create();
        
        $this->get(route('licitacoes.siteGrid'))
            ->assertSee($licitacao->titulo)
            ->assertSee($licitacao->nrlicitacao)
            ->assertSee($licitacao->nrprocesso)
            ->assertSee($licitacao->modalidade);
    }

    /** @test */
    public function licitacao_is_shown_on_website_after_its_creation()
    {
        $this->signInAsAdmin();
        $licitacao = factory('App\Licitacao')->create();
        
        $this->get(route('licitacoes.show', $licitacao->idlicitacao))
            ->assertSee($licitacao->titulo)
            ->assertSee($licitacao->nrlicitacao)
            ->assertSee($licitacao->nrprocesso)
            ->assertSee($licitacao->modalidade);
    }

    /** @test */
    public function link_to_licitacao_is_shown_on_website()
    {
        $this->signInAsAdmin();
        $licitacao = factory('App\Licitacao')->create();
        
        $this->get(route('licitacoes.siteGrid', $licitacao->idlicitacao))
            ->assertSee(route('licitacoes.show', $licitacao->idlicitacao));
    }

    /** @test */
    public function licitacao_without_modalidade_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'modalidade' => ''
        ]);

        $this->post(route('licitacoes.store'), $attributes)->assertSessionHasErrors('modalidade');
        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function licitacao_without_titulo_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'titulo' => ''
        ]);

        $this->post(route('licitacoes.store'), $attributes)->assertSessionHasErrors('titulo');
        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function licitacao_without_nrlicitacao_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'nrlicitacao' => ''
        ]);

        $this->post(route('licitacoes.store'), $attributes)->assertSessionHasErrors('nrlicitacao');
        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function licitacao_without_nrprocesso_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'nrprocesso' => ''
        ]);

        $this->post(route('licitacoes.store'), $attributes)->assertSessionHasErrors('nrprocesso');
        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function licitacao_without_situacao_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'situacao' => ''
        ]);

        $this->post(route('licitacoes.store'), $attributes)->assertSessionHasErrors('situacao');
        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function licitacao_without_objeto_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'objeto' => ''
        ]);

        $this->post(route('licitacoes.store'), $attributes)->assertSessionHasErrors('objeto');
        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function the_name_of_the_user_who_created_licitacao_is_shown_on_admin_panel()
    {
        $user = $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.edit', $licitacao->idlicitacao))
            ->assertOk()
            ->assertSee($user->nome);
    }

    /** @test */
    public function non_authorized_users_cannot_create_licitacoes()
    {
        $this->signIn();

        $this->get(route('licitacoes.create'))->assertForbidden();

        $attributes = factory('App\Licitacao')->raw();

        $this->post(route('licitacoes.store'), $attributes)->assertForbidden();
        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function non_authorized_users_cannot_see_licitacoes_on_admin_panel()
    {
        $this->signIn();

        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.index'))->assertForbidden()->assertDontSee($licitacao->titulo);
    }

    /** @test */
    function a_licitacao_can_be_created()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->assertDatabaseHas('licitacoes', ['titulo' => $licitacao->titulo]);
        $this->assertEquals(1, Licitacao::count());
    }

    /** @test */
    function multiple_licitacoes_can_be_created()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();
        $licitacaoDois = factory('App\Licitacao')->create();

        $this->assertDatabaseHas('licitacoes', ['titulo' => $licitacao->titulo]);
        $this->assertDatabaseHas('licitacoes', ['titulo' => $licitacaoDois->titulo]);
        $this->assertEquals(2, Licitacao::count());
    }

    /** @test */
    public function licitacao_can_be_updated()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();
        $attributes = factory('App\Licitacao')->raw();

        $this->patch(route('licitacoes.update', $licitacao->idlicitacao), $attributes);

        $lic = Licitacao::find($licitacao->idlicitacao);
        $this->assertEquals($lic->titulo, $attributes['titulo']);
        $this->assertEquals($lic->modalidade, $attributes['modalidade']);
        $this->assertEquals($lic->situacao, $attributes['situacao']);
        $this->assertDatabaseHas('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function log_is_generated_when_licitacao_is_updated()
    {
        $user = $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();
        $attributes = factory('App\Licitacao')->raw();

        $this->patch(route('licitacoes.update', $licitacao->idlicitacao), $attributes);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('editou', $log);
        $this->assertStringContainsString('licitação', $log);
    }

    /** @test */
    public function non_authorized_users_cannot_update_licitacoes()
    {
        $this->signIn();

        $licitacao = factory('App\Licitacao')->create();
        $attributes = factory('App\Licitacao')->raw();

        $this->get(route('licitacoes.edit', $licitacao->idlicitacao))->assertForbidden();
        $this->patch(route('licitacoes.update', $licitacao->idlicitacao), $attributes)->assertForbidden();

        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function licitacao_can_be_deleted()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao));
        $this->assertSoftDeleted('licitacoes', ['idlicitacao' => $licitacao->idlicitacao]);
    }

    /** @test */
    public function log_is_generated_when_licitacao_is_deleted()
    {
        $user = $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao));
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('apagou', $log);
        $this->assertStringContainsString('licitação', $log);
    }

    /** @test */
    public function non_authorized_users_cannot_delete_licitacao()
    {
        $this->signIn();

        $licitacao = factory('App\Licitacao')->create();

        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao))->assertForbidden();
        $this->assertNull(Licitacao::withTrashed()->find($licitacao->idlicitacao)->deleted_at);
    }

    /** @test */
    public function deleted_licitacoes_are_shown_in_trash()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao));

        $this->get(route('licitacoes.trashed'))->assertOk()->assertSee($licitacao->idlicitacao);
    }

    /** @test */
    public function deleted_licitacoes_are_not_shown_on_index()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao));

        $this->get(route('licitacoes.index'))->assertOk()->assertDontSee($licitacao->titulo);
    }

    /** @test */
    public function deleted_licitacoes_can_be_restored()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao));
        $this->get(route('licitacoes.restore', $licitacao->idlicitacao));

        $this->assertNull(Licitacao::find($licitacao->idlicitacao)->deleted_at);
        $this->get(route('licitacoes.index'))->assertSee($licitacao->idlicitacao);
    }

    /** @test */
    public function log_is_generated_when_licitacao_is_restored()
    {
        $user = $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao));
        $this->get(route('licitacoes.restore', $licitacao->idlicitacao));
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('restaurou', $log);
        $this->assertStringContainsString('licitação', $log);
    }

    /** @test */
    function licitacao_can_be_searched()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.busca', ['q' => $licitacao->titulo]))
            ->assertSeeText($licitacao->titulo);
    }

    /** @test */
    function link_to_edit_licitacao_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.index'))->assertSee(route('licitacoes.edit', $licitacao->idlicitacao));
    }

    /** @test */
    function link_to_destroy_licitacao_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.index'))->assertSee(route('licitacoes.destroy', $licitacao->idlicitacao));
    }

    /** @test */
    function link_to_create_licitacao_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $this->get(route('licitacoes.index'))->assertSee(route('licitacoes.create'));
    }

    /** @test */
    function licitacao_can_be_searched_by_modalidade_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.siteBusca', [
            'modalidade' => $licitacao->modalidade
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
    }

    /** @test */
    function licitacao_can_be_searched_by_palavrachave_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();
        $array_titulo = explode($licitacao->titulo, ' ');
        $first_word = $array_titulo[0];

        $this->get(route('licitacoes.siteBusca', [
            'palavra-chave' => $first_word
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
    }

    /** @test */
    function licitacao_can_be_searched_by_nrprocesso_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.siteBusca', [
            'nrprocesso' => $licitacao->nrprocesso
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
    }

    /** @test */
    function licitacao_can_be_searched_by_nrlicitacao_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.siteBusca', [
            'nrlicitacao' => $licitacao->nrlicitacao
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
    }

    /** @test */
    function licitacao_can_be_searched_by_situacao_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.siteBusca', [
            'situacao' => $licitacao->situacao
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
    }

    /** @test */
    function licitacao_can_be_searched_by_datarealizacao_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.siteBusca', [
            'datarealizacao' => onlyDate($licitacao->datarealizacao)
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
    }

    /** @test */
    function licitacao_can_be_searched_by_more_than_one_param_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.siteBusca', [
            'nrprocesso' => $licitacao->nrprocesso,
            'situacao' => $licitacao->situacao
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
    }
}
