<?php

namespace Tests\Feature;

use App\Licitacao;
use App\Permissao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LicitacaoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permissao::insert([
            [
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
            ]
        ]);
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
        
        $this->get('/licitacoes')
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
        
        $this->get('/licitacao/' . $licitacao->idlicitacao)
            ->assertSee($licitacao->titulo)
            ->assertSee($licitacao->nrlicitacao)
            ->assertSee($licitacao->nrprocesso)
            ->assertSee($licitacao->modalidade);
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
        $this->assertNotNull(Licitacao::withTrashed()->find($licitacao->idlicitacao)->deleted_at);
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
}
