<?php

namespace Tests\Feature;

use App\Concurso;
use App\Permissao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcursoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permissao::insert([
            [
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
            ]
        ]);
    }

    /** @test */
    function a_concurso_can_be_created()
    {
        $concurso = factory('App\Concurso')->create();

        $this->assertDatabaseHas('concursos', ['titulo' => $concurso->titulo]);
        $this->assertEquals(1, Concurso::count());
    }

    /** @test */
    public function concurso_can_be_created_by_an_user()
    {
        $this->withoutExceptionHandling();
        $this->signInAsAdmin();
        $attributes = factory('App\Concurso')->raw();

        $this->get(route('concursos.index'))->assertOk();
        $this->post(route('concursos.store'), $attributes);
        $this->assertDatabaseHas('concursos', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function log_is_generated_when_concurso_is_created()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Concurso')->raw();

        $this->post(route('concursos.store'), $attributes);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('criou', $log);
        $this->assertStringContainsString('concurso', $log);
    }

    /** @test */
    public function non_authorized_users_cannot_create_concursos()
    {
        $this->signIn();

        $this->get(route('concursos.create'))->assertForbidden();

        $attributes = factory('App\Concurso')->raw();

        $this->post(route('concursos.store'), $attributes)->assertForbidden();
        $this->assertDatabaseMissing('concursos', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function concursos_are_shown_on_the_admin_panel()
    {
        $this->signInAsAdmin();
        $concurso = factory('App\Concurso')->create();
        $concursoDois = factory('App\Concurso')->create();
        
        $this->get(route('concursos.index'))->assertSee($concurso->idconcurso);
        $this->get(route('concursos.index'))->assertSee($concursoDois->idconcurso);
    }

    /** @test */
    public function concurso_user_creator_is_shown_on_the_admin_panel()
    {
        $user = $this->signInAsAdmin();
        $concurso = factory('App\Concurso')->create();
        
        $this->get(route('concursos.edit', $concurso->idconcurso))->assertSee($user->nome);
    }

    /** @test */
    public function non_authorized_users_cannot_see_concursos_on_admin()
    {
        $this->signIn();

        $concurso = factory('App\Concurso')->create();

        $this->get(route('concursos.index'))->assertForbidden()->assertDontSee($concurso->titulo);
    }

    /** @test */
    function multiple_concursos_can_be_created()
    {
        $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->create();
        $concursoDois = factory('App\Concurso')->create();

        $this->assertDatabaseHas('concursos', ['titulo' => $concurso->titulo]);
        $this->assertDatabaseHas('concursos', ['titulo' => $concursoDois->titulo]);
        $this->assertEquals(2, Concurso::count());
    }

    /** @test */
    function a_concurso_without_modalidade_cannot_be_created()
    {
        $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->raw([
            'modalidade' => ''
        ]);

        $this->post(route('concursos.store'), $concurso)->assertSessionHasErrors('modalidade');
        $this->assertEquals(0, Concurso::count());
    }

    /** @test */
    function a_concurso_without_titulo_cannot_be_created()
    {
        $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->raw([
            'titulo' => ''
        ]);

        $this->post(route('concursos.store'), $concurso)->assertSessionHasErrors('titulo');
        $this->assertEquals(0, Concurso::count());
    }

    /** @test */
    function a_concurso_without_nrprocesso_cannot_be_created()
    {
        $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->raw([
            'nrprocesso' => ''
        ]);

        $this->post(route('concursos.store'), $concurso)->assertSessionHasErrors('nrprocesso');
        $this->assertEquals(0, Concurso::count());
    }

    /** @test */
    function a_concurso_without_situacao_cannot_be_created()
    {
        $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->raw([
            'situacao' => ''
        ]);

        $this->post(route('concursos.store'), $concurso)->assertSessionHasErrors('situacao');
        $this->assertEquals(0, Concurso::count());
    }

    /** @test */
    function a_concurso_without_datarealizacao_cannot_be_created()
    {
        $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->raw([
            'datarealizacao' => ''
        ]);

        $this->post(route('concursos.store'), $concurso)->assertSessionHasErrors('datarealizacao');
        $this->assertEquals(0, Concurso::count());
    }

    /** @test */
    function a_concurso_without_objeto_cannot_be_created()
    {
        $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->raw([
            'objeto' => ''
        ]);

        $this->post(route('concursos.store'), $concurso)->assertSessionHasErrors('objeto');
        $this->assertEquals(0, Concurso::count());
    }

    /** @test */
    function a_concurso_cannot_have_duplicate_nrprocesso()
    {
        $this->signInAsAdmin();

        $concursoFirst = factory('App\Concurso')->create();
        $concurso = factory('App\Concurso')->raw([
            'nrprocesso' => $concursoFirst->nrprocesso
        ]);

        $this->post(route('concursos.store'), $concurso)->assertSessionHasErrors('nrprocesso');
        $this->assertEquals(1, Concurso::count());
    }

    /** @test */
    public function concurso_is_shown_on_the_website()
    {
        $concurso = factory('App\Concurso')->create();

        $this->get(route('concursos.show', $concurso->idconcurso))
            ->assertOk()
            ->assertSee($concurso->objeto);
    }

    /** @test */
    public function concursos_list_is_shown_on_the_website()
    {
        $this->withoutExceptionHandling();
        $concurso = factory('App\Concurso')->create();

        $this->get(route('concursos.siteGrid'))
            ->assertOk()
            ->assertSee($concurso->nrprocesso)
            ->assertSee(route('concursos.show', $concurso->idconcurso));
    }

    /** @test */
    public function concurso_can_be_updated_by_authorized_user()
    {
        $this->withoutExceptionHandling();
        $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->create();
        $attributes = factory('App\Concurso')->raw([
            'nrprocesso' => $concurso->nrprocesso
        ]);

        $this->get(route('concursos.edit', $concurso->idconcurso))->assertOk();
        $this->patch(route('concursos.update', $concurso->idconcurso), $attributes);
        $this->assertEquals(Concurso::find($concurso->idconcurso)->titulo, $attributes['titulo']);
    }

    /** @test */
    public function log_is_generated_when_concurso_is_updated()
    {
        $user = $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->create();
        $attributes = factory('App\Concurso')->raw();

        $this->get(route('concursos.edit', $concurso->idconcurso))->assertOk();
        $this->patch(route('concursos.update', $concurso->idconcurso), $attributes);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('editou', $log);
        $this->assertStringContainsString('concurso', $log);
    }

    /** @test */
    public function concurso_cannot_have_duplicated_nrprocesso_when_updated()
    {
        $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->create();
        $concursoDois = factory('App\Concurso')->create();
        $attributes = factory('App\Concurso')->raw([
            'nrprocesso' => $concursoDois->nrprocesso
        ]);

        $this->get(route('concursos.edit', $concurso->idconcurso))->assertOk();
        $this->patch(route('concursos.update', $concurso->idconcurso), $attributes)->assertSessionHasErrors('nrprocesso');
        $this->assertEquals(Concurso::find($concurso->idconcurso)->titulo, $concurso->titulo);
    }

    /** @test */
    public function concurso_can_be_destroyed_by_user()
    {
        $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->create();

        $this->delete(route('concursos.destroy', $concurso->idconcurso));
        $this->assertSoftDeleted('concursos', ['idconcurso' => $concurso->idconcurso]);
    }

    /** @test */
    public function log_is_generated_when_concurso_is_destroyed()
    {
        $user = $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->create();

        $this->delete(route('concursos.destroy', $concurso->idconcurso));
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('apagou', $log);
        $this->assertStringContainsString('concurso', $log);
    }

    /** @test */
    public function concurso_can_be_restored()
    {
        $this->signInAsAdmin();
        $concurso = factory('App\Concurso')->create();
        $concurso->delete();

        $this->get(route('concursos.restore', $concurso->idconcurso));
        $this->assertNull(Concurso::find($concurso->idconcurso)->deleted_at);
    }

    /** @test */
    public function log_is_generated_when_concurso_is_restored()
    {
        $user = $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->create();
        $concurso->delete();
        $this->get(route('concursos.restore', $concurso->idconcurso));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('restaurou', $log);
        $this->assertStringContainsString('concurso', $log);
    }

    /** @test */
    function concurso_can_be_searched()
    {
        $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->create();

        $this->get(route('concursos.busca', ['q' => $concurso->nrprocesso]))
            ->assertSeeText($concurso->nrprocesso);
    }

    /** @test */
    function concurso_author_is_shown_on_admin()
    {
        $user = $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->create();

        $this->get(route('concursos.edit', $concurso->idconcurso))->assertSee($user->nome);
    }

    /** @test */
    function link_to_edit_concurso_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->create();

        $this->get(route('concursos.index'))
            ->assertSee(route('concursos.edit', $concurso->idconcurso));
    }

    /** @test */
    function link_to_destroy_concurso_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $concurso = factory('App\Concurso')->create();

        $this->get(route('concursos.index'))
            ->assertSee(route('concursos.destroy', $concurso->idconcurso));
    }

    /** @test */
    function link_to_create_concurso_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $this->get(route('concursos.index'))->assertSee(route('concursos.create'));
    }

    /** @test */
    function concurso_can_be_searched_by_modalidade_on_website()
    {
        $concurso = factory('App\Concurso')->create();

        $this->get(route('concursos.siteBusca', [
            'modalidade' => $concurso->modalidade
        ]))->assertOk()
            ->assertSee($concurso->nrprocesso);
    }

    /** @test */
    function concurso_can_be_searched_by_situacao_on_website()
    {
        $concurso = factory('App\Concurso')->create();

        $this->get(route('concursos.siteBusca', [
            'situacao' => $concurso->situacao
        ]))->assertOk()
            ->assertSee($concurso->nrprocesso);
    }

    /** @test */
    function concurso_can_be_searched_by_nrprocesso_on_website()
    {
        $concurso = factory('App\Concurso')->create();

        $this->get(route('concursos.siteBusca', [
            'nrprocesso' => $concurso->nrprocesso
        ]))->assertOk()
            ->assertSee($concurso->nrprocesso);
    }

    /** @test */
    function concurso_can_be_searched_by_datarealizacao_on_website()
    {
        $concurso = factory('App\Concurso')->create();

        $this->get(route('concursos.siteBusca', [
            'datarealizacao' => onlyDate($concurso->datarealizacao)
        ]))->assertOk()
            ->assertSee($concurso->nrprocesso);
    }
}
