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

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $pagina = factory('App\Pagina')->create();

        $this->get(route('paginas.index'))->assertRedirect(route('login'));
        $this->get(route('paginas.create'))->assertRedirect(route('login'));
        $this->get(route('paginas.edit', $pagina->idpagina))->assertRedirect(route('login'));
        $this->post(route('paginas.store'))->assertRedirect(route('login'));
        $this->patch(route('paginas.update', $pagina->idpagina))->assertRedirect(route('login'));
        $this->delete(route('paginas.destroy', $pagina->idpagina))->assertRedirect(route('login'));
        $this->get(route('paginas.restore', $pagina->idpagina))->assertRedirect(route('login'));
        $this->get(route('paginas.busca'))->assertRedirect(route('login'));
        $this->get(route('paginas.trashed'))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $pagina = factory('App\Pagina')->create();

        $this->get(route('paginas.index'))->assertForbidden();
        $this->get(route('paginas.create'))->assertForbidden();
        $this->get(route('paginas.edit', $pagina->idpagina))->assertForbidden();
        $this->post(route('paginas.store'), $pagina->toArray())->assertForbidden();
        $this->patch(route('paginas.update', $pagina->idpagina), $pagina->toArray())->assertForbidden();
        $this->delete(route('paginas.destroy', $pagina->idpagina))->assertForbidden();
        $this->get(route('paginas.restore', $pagina->idpagina))->assertForbidden();
        $this->get(route('paginas.busca'))->assertForbidden();
        $this->get(route('paginas.trashed'))->assertForbidden();
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
    public function pagina_can_be_created_with_img_blur()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Pagina')->raw();

        $this->gerenciarPastasLazyLoad($attributes['img']);

        $this->get(route('paginas.create'))->assertOk();
        $this->post(route('paginas.store', $attributes));

        $this->assertDatabaseHas('paginas', ['titulo' => $attributes['titulo']]);

        $this->assertTrue(\File::exists(public_path($attributes['img'])));

        $nome = substr($attributes['img'], strripos($attributes['img'], '/desktop_') + 1);
        $this->assertTrue(\File::exists(public_path('/imagens/fake/' . date('Y-m') . '/.blur/small-' . $nome)));
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
    public function pagina_cannot_be_updated_to_an_exisiting_title()
    {
        $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();
        $paginaDois = factory('App\Pagina')->create();

        $attributes = factory('App\Pagina')->raw([
            'titulo' => $pagina->titulo
        ]);

        $this->post(route('paginas.update', $paginaDois->idpagina), $attributes);
        $this->assertNotEquals(Pagina::find($paginaDois->idpagina)->titulo, $pagina->titulo);
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
    public function created_paginas_are_shown_on_the_website_with_generic_img()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Pagina')->raw([
            'img' => null
        ]);
        $this->post(route('paginas.store'), $attributes);
        
        $this->get(route('paginas.site', $attributes['slug']))
            ->assertSee($attributes['titulo'])
            ->assertSee($attributes['conteudo'])
            ->assertSee('<img src="' . asset('img/institucional.png') .'" alt="CORE-SP">');
    }

    /** @test */
    public function created_paginas_are_shown_on_the_website_without_img_created()
    {
        $this->pagina_can_be_created_by_user();

        $pagina = Pagina::first();
        
        $this->get(route('paginas.site', $pagina['slug']))
            ->assertSee($pagina['titulo'])
            ->assertSee($pagina['conteudo'])
            ->assertSee('<img class="lazy-loaded-image lazy" src="" data-src="'. asset($pagina->img) .'" />');
    }

    /** @test */
    public function created_paginas_are_shown_on_the_website_with_img_created()
    {
        $this->pagina_can_be_created_with_img_blur();

        $pagina = Pagina::first();
        
        $this->get(route('paginas.site', $pagina['slug']))
            ->assertSee($pagina['titulo'])
            ->assertSee($pagina['conteudo'])
            ->assertSee('<img class="lazy-loaded-image lazy" src="' .$pagina->imgBlur(). '" data-src="'. asset($pagina->img) .'" />');
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
    public function pagina_can_be_updated_with_img_blur()
    {
        $user = $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();
        $titulo = 'Novo titulo';

        $img = factory('App\Pagina')->raw()['img'];

        $this->gerenciarPastasLazyLoad($img);

        $this->get(route('paginas.edit', $pagina->idpagina))->assertOk();
        $this->patch(route('paginas.update', $pagina->idpagina), [
            'idusuario' => $user->idusuario,
            'titulo' => $titulo,
            'conteudo' => $pagina->conteudo,
            'img' => $img
        ]);

        $this->assertEquals(Pagina::find($pagina->idpagina)->titulo, $titulo);

        $this->assertTrue(\File::exists(public_path($img)));

        $nome = substr($img, strripos($img, '/desktop_') + 1);
        $this->assertTrue(\File::exists(public_path('/imagens/fake/' . date('Y-m') . '/.blur/small-' . $nome)));
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
    function link_to_create_pagina_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $this->get(route('paginas.index'))->assertSee(route('paginas.create'));
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

    /** @test */
    function pagina_conteudoBusca_is_stored_with_no_tags()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Pagina')->raw();

        $attributes['conteudo'] = '<p>unit_test' . $attributes['conteudo'] . '</p>';

        $this->post(route('paginas.store'), $attributes);

        $pagina = Pagina::first();

        $this->assertStringNotContainsString('<p>', $pagina->conteudoBusca);

    }
}
