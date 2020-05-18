<?php

namespace Tests\Feature;

use App\Pagina;
use App\Permissao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaginaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permissao::insert([
            [
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
            ]
        ]);
    }
    
    /** @test */
    public function pagina_can_be_created()
    {
        $pagina = factory('App\Pagina')->create();

        $this->assertDatabaseHas('paginas', ['titulo' => $pagina->titulo]);
    }

    /** @test */
    public function log_is_generated_when_pagina_is_created()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Pagina')->raw();

        $this->post(route('paginas.store'), $attributes);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('criou', $log);
        $this->assertStringContainsString('página', $log);
    }

    /** @test */
    public function pagina_can_be_created_by_user()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Pagina')->raw();

        $this->get(route('paginas.create'))->assertOk();
        $this->post(route('paginas.store', $attributes));

        $this->assertDatabaseHas('paginas', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function pagina_title_is_required()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Pagina')->raw([
            'titulo' => ''
        ]);

        $this->post(route('paginas.store', $attributes))->assertSessionHasErrors('titulo');
        $this->assertDatabaseMissing('paginas', ['subtitulo' => $attributes['subtitulo']]);
    }

    /** @test */
    public function pagina_conteudo_is_required()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Pagina')->raw([
            'conteudo' => ''
        ]);

        $this->post(route('paginas.store', $attributes))->assertSessionHasErrors('conteudo');
        $this->assertDatabaseMissing('paginas', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function pagina_with_same_title_cannot_be_created()
    {
        $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();

        $attributes = factory('App\Pagina')->raw([
            'titulo' => $pagina->titulo
        ]);

        $this->post(route('paginas.store', $attributes));
        $this->assertEquals(Pagina::count(), 1);
        $this->assertDatabaseMissing('paginas', ['conteudo' => $attributes['conteudo']]);
    }

    /** @test */
    public function non_authorized_users_cannot_create_pagina()
    {
        $this->signIn();

        $attributes = factory('App\Pagina')->raw();

        $this->get(route('paginas.create'))->assertForbidden();
        $this->post(route('paginas.store', $attributes));

        $this->assertDatabaseMissing('paginas', ['titulo' => $attributes['titulo']]);        
    }

    /** @test */
    public function created_paginas_are_shown_on_the_admin_panel()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Pagina')->raw();
        $this->post(route('paginas.store'), $attributes);
        
        $this->get(route('paginas.index'))->assertSee($attributes['titulo']);
    }

    /** @test */
    public function pagina_user_creator_is_shown_on_the_admin_panel()
    {
        $user = $this->signInAsAdmin();
        $pagina = factory('App\Pagina')->create();
        
        $this->get(route('paginas.edit', $pagina->idpagina))->assertSee($user->nome);
    }

    /** @test */
    public function created_paginas_are_shown_on_the_website()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Pagina')->raw();
        $this->post(route('paginas.store'), $attributes);
        
        $this->get(route('paginas.site', $attributes['slug']))
            ->assertSee($attributes['titulo'])
            ->assertSee($attributes['conteudo']);
    }

    /** @test */
    public function pagina_can_be_updated()
    {
        $user = $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();
        $titulo = 'Novo titulo';

        $this->get(route('paginas.edit', $pagina->idpagina))->assertOk();
        $this->patch(route('paginas.update', $pagina->idpagina), [
            'idusuario' => $user->idusuario,
            'titulo' => $titulo,
            'conteudo' => $pagina->conteudo
        ]);

        $this->assertEquals(Pagina::find($pagina->idpagina)->titulo, $titulo);
    }

    /** @test */
    public function log_is_generated_when_pagina_is_updated()
    {
        $user = $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();
        $titulo = 'Novo titulo';

        $this->patch(route('paginas.update', $pagina->idpagina), [
            'idusuario' => $user->idusuario,
            'titulo' => $titulo,
            'conteudo' => $pagina->conteudo
        ]);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('editou', $log);
        $this->assertStringContainsString('página', $log);
    }

    /** @test */
    public function non_authorized_users_cannot_update_paginas()
    {
        $user = $this->signIn();

        $pagina = factory('App\Pagina')->create();
        $titulo = 'Novo titulo';

        $this->get(route('paginas.edit', $pagina->idpagina))->assertForbidden();
        $this->patch(route('paginas.update', $pagina->idpagina), [
            'idusuario' => $user->idusuario,
            'titulo' => $titulo,
            'conteudo' => $pagina->conteudo
        ]);

        $this->assertNotEquals(Pagina::find($pagina->idpagina)->titulo, $titulo);
    }

    /** @test */
    public function pagina_can_be_deleted()
    {
        $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();

        $this->delete(route('paginas.destroy', $pagina->idpagina));
        $this->assertSoftDeleted('paginas', ['idpagina' => $pagina->idpagina]);
    }

    /** @test */
    public function log_is_generated_when_pagina_is_deleted()
    {
        $user = $this->signInAsAdmin();
        $pagina = factory('App\Pagina')->create();

        $this->delete(route('paginas.destroy', $pagina->idpagina));
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('apagou', $log);
        $this->assertStringContainsString('página', $log);
    }

    /** @test */
    public function non_authorized_users_cannot_delete_pagina()
    {
        $this->signIn();

        $pagina = factory('App\Pagina')->create();

        $this->delete(route('paginas.destroy', $pagina->idpagina))->assertForbidden();
        $this->assertNull(Pagina::find($pagina->idpagina)->deleted_at);
    }

    /** @test */
    public function deleted_paginas_are_shown_on_trash()
    {
        $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();

        $this->delete(route('paginas.destroy', $pagina->idpagina));
        $this->get(route('paginas.trashed'))->assertSee($pagina->titulo);
        $this->get(route('paginas.index'))->assertDontSee($pagina->titulo);
    }

    /** @test */
    public function deleted_paginas_can_be_restored()
    {
        $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();

        $this->delete(route('paginas.destroy', $pagina->idpagina));
        $this->get(route('paginas.restore', $pagina->idpagina));

        $this->assertNull(Pagina::find($pagina->idpagina)->deleted_at);
    }

    /** @test */
    public function log_is_generated_when_pagina_is_restored()
    {
        $user = $this->signInAsAdmin();
        $pagina = factory('App\Pagina')->create();

        Pagina::find($pagina->idpagina)->delete();
        $this->get(route('paginas.restore', $pagina->idpagina));
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('restaurou', $log);
        $this->assertStringContainsString('página', $log);
    }

    /** @test */
    public function pagina_can_be_searched()
    {
        $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();

        $this->get(route('paginas.busca', ['q' => $pagina->titulo]))
            ->assertSeeText($pagina->titulo);
    }

    /** @test */
    function pagina_author_is_shown_on_admin()
    {
        $user = $this->signInAsAdmin();

        factory('App\Pagina')->create();

        $this->get(route('paginas.index'))->assertSee($user->nome);
    }

    /** @test */
    function link_to_edit_pagina_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();

        $this->get(route('paginas.index'))->assertSee(route('paginas.edit', $pagina->idpagina));
    }

    /** @test */
    function link_to_destroy_pagina_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();

        $this->get(route('paginas.index'))->assertSee(route('paginas.destroy', $pagina->idpagina));
    }
}
