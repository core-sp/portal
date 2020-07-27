<?php

namespace Tests\Feature;

use App\Noticia;
use App\Permissao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class NoticiaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permissao::insert([
            [
                'controller' => 'NoticiaController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'NoticiaController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ]
        ]);
    }
    
    /** @test */
    public function noticia_can_be_created_by_an_user()
    {
        $this->signInAsAdmin();
        $attributes = factory('App\Noticia')->raw();

        $this->get(route('noticias.index'))->assertOk();
        $this->post(route('noticias.store'), $attributes);
        $this->assertDatabaseHas('noticias', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function log_is_generated_when_noticia_is_created()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Noticia')->raw();

        $this->post(route('noticias.store'), $attributes);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('criou', $log);
        $this->assertStringContainsString('notícia', $log);
    }

    /** @test */
    public function non_authorized_users_cannot_create_noticias()
    {
        $this->signIn();

        $this->get(route('noticias.create'))->assertForbidden();

        $attributes = factory('App\Noticia')->raw();

        $this->post(route('noticias.store'), $attributes)->assertForbidden();
        $this->assertDatabaseMissing('noticias', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function noticias_are_shown_on_the_admin_panel()
    {
        $this->signInAsAdmin();
        $noticia = factory('App\Noticia')->create();
        $noticiaDois = factory('App\Noticia')->create();
        
        $this->get(route('noticias.index'))->assertSee($noticia->titulo);
        $this->get(route('noticias.index'))->assertSee($noticiaDois->titulo);
    }

    /** @test */
    public function noticia_user_creator_is_shown_on_the_admin_panel()
    {
        $user = $this->signInAsAdmin();
        $noticia = factory('App\Noticia')->create();
        
        $this->get(route('noticias.edit', $noticia->idnoticia))->assertSee($user->nome);
    }

    /** @test */
    public function non_authorized_users_cannot_see_noticias_on_admin()
    {
        $this->signIn();

        $noticia = factory('App\Noticia')->create();

        $this->get(route('noticias.index'))->assertForbidden()->assertDontSee($noticia->titulo);
    }

    /** @test */
    function a_noticia_can_be_created()
    {
        $noticia = factory('App\Noticia')->create();

        $this->assertDatabaseHas('noticias', ['titulo' => $noticia->titulo]);
        $this->assertEquals(1, Noticia::count());
    }

    /** @test */
    function multiple_noticias_can_be_created()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();
        $noticiaDois = factory('App\Noticia')->create();

        $this->assertDatabaseHas('noticias', ['titulo' => $noticia->titulo]);
        $this->assertDatabaseHas('noticias', ['titulo' => $noticiaDois->titulo]);
        $this->assertEquals(2, Noticia::count());
    }

    /** @test */
    function a_noticia_without_titulo_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Noticia')->raw([
            'titulo' => ''
        ]);

        $this->post(route('noticias.store'), $attributes)->assertSessionHasErrors('titulo');
        $this->assertEquals(0, Noticia::count());
    }

    /** @test */
    function a_noticia_without_conteudo_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Noticia')->raw([
            'conteudo' => ''
        ]);

        $this->post(route('noticias.store'), $attributes)->assertSessionHasErrors('conteudo');
        $this->assertEquals(0, Noticia::count());
    }

    /** @test */
    public function noticia_is_shown_on_the_website()
    {
        $this->signInAsAdmin();
        $noticia = factory('App\Noticia')->create();

        $this->get(route('noticias.show', $noticia->slug))
            ->assertOk()
            ->assertSee($noticia->titulo);
    }

    /** @test */
    public function noticia_site_grid_is_shown_on_the_website()
    {
        $noticia = factory('App\Noticia')->create();

        $this->get(route('noticias.siteGrid', $noticia->slug))
            ->assertOk()
            ->assertSee($noticia->titulo);
    }

    /** @test */
    public function noticia_with_same_title_cannot_be_created()
    {
        $this->signInAsAdmin();
        $noticia = factory('App\Noticia')->create();
        $attributes = factory('App\Noticia')->raw([
            'titulo' => $noticia->titulo
        ]);
        $this->post(route('noticias.store'), $attributes);
        $this->assertEquals(1, Noticia::count());
    }

    /** @test */
    public function noticia_can_be_updated()
    {
        $user = $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $this->patch(route('noticias.update', $noticia->idnoticia), [
            'idusuario' => $user->idusuario,
            'titulo' => 'Novo titulo',
            'conteudo' => $noticia->conteudo
        ]);

        $this->assertEquals(Noticia::find($noticia->idnoticia)->titulo, 'Novo titulo');
    }

    /** @test */
    public function log_is_generated_when_noticia_is_updated()
    {
        $user = $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $this->patch(route('noticias.update', $noticia->idnoticia), [
            'idusuario' => $user->idusuario,
            'titulo' => 'Novo titulo',
            'conteudo' => $noticia->conteudo
        ]);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('editou', $log);
        $this->assertStringContainsString('notícia', $log);
    }

    /** @test */
    public function non_authorized_users_cannot_update_noticias()
    {
        $this->signIn();

        $noticia = factory('App\Noticia')->create();

        $this->get(route('noticias.edit', $noticia->idnoticia))->assertForbidden();

        $titulo = 'Novo titulo';

        $this->patch(route('noticias.update', $noticia->idnoticia), ['titulo' => $titulo])->assertForbidden();
        $this->assertDatabaseMissing('noticias', ['titulo' => $titulo]);
    }

    /** @test */
    public function noticia_cannot_be_updated_to_existing_title()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();
        $noticiaDois = factory('App\Noticia')->create();

        $this->patch(route('noticias.update', $noticia->idnoticia), [
            'titulo' => $noticiaDois->titulo
        ]);
        
        $this->assertNotEquals(Noticia::find($noticia->idnoticia)->titulo, $noticiaDois->titulo);
        $this->assertEquals(Noticia::find($noticia->idnoticia)->titulo, $noticia->titulo);
    }

    /** @test */
    public function noticia_can_be_deleted()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $this->delete(route('noticias.destroy', $noticia->idnoticia));
        $this->assertSoftDeleted('noticias', ['idnoticia' => $noticia->idnoticia]);
    }

    /** @test */
    public function log_is_generated_when_noticia_is_deleted()
    {
        $user = $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $this->delete(route('noticias.destroy', $noticia->idnoticia));
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('apagou', $log);
        $this->assertStringContainsString('notícia', $log);
    }

    /** @test */
    public function non_authorized_users_cannot_delete_noticia()
    {
        $this->signIn();

        $noticia = factory('App\Noticia')->create();

        $this->delete(route('noticias.destroy', $noticia->idnoticia))->assertForbidden();
        $this->assertNull(Noticia::withTrashed()->find($noticia->idnoticia)->deleted_at);
    }

    /** @test */
    public function deleted_noticias_are_shown_in_trash()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $this->delete(route('noticias.destroy', $noticia->idnoticia));

        $this->get(route('noticias.trashed'))->assertOk()->assertSee($noticia->titulo);
    }

    /** @test */
    public function deleted_noticias_are_not_shown_on_index()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $this->delete(route('noticias.destroy', $noticia->idnoticia));

        $this->get(route('noticias.index'))->assertOk()->assertDontSee($noticia->titulo);
    }

    /** @test */
    public function deleted_noticias_can_be_restored()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $this->delete(route('noticias.destroy', $noticia->idnoticia));
        $this->get(route('noticias.restore', $noticia->idnoticia));

        $this->assertNull(Noticia::find($noticia->idnoticia)->deleted_at);
        $this->get(route('noticias.index'))->assertSee($noticia->titulo);
    }

    /** @test */
    public function log_is_generated_when_noticia_is_restored()
    {
        $user = $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $this->delete(route('noticias.destroy', $noticia->idnoticia));
        $this->get(route('noticias.restore', $noticia->idnoticia));
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('restaurou', $log);
        $this->assertStringContainsString('notícia', $log);
    }

    /** @test */
    function noticia_can_be_searched()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $this->get(route('noticias.busca', ['q' => $noticia->titulo]))
            ->assertSeeText($noticia->titulo);
    }

    /** @test */
    function noticia_author_is_shown_on_admin()
    {
        $user = $this->signInAsAdmin();

        factory('App\Noticia')->create();

        $this->get(route('noticias.index'))->assertSee($user->nome);
    }

    /** @test */
    function link_to_edit_noticia_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $this->get(route('noticias.index'))->assertSee(route('noticias.edit', $noticia->idnoticia));
    }

    /** @test */
    function link_to_destroy_noticia_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $this->get(route('noticias.index'))->assertSee(route('noticias.destroy', $noticia->idnoticia));
    }

    /** @test */
    function link_to_create_noticia_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $this->get(route('noticias.index'))->assertSee(route('noticias.create'));
    }

    /** @test */
    function noticia_conteudoBusca_is_stored_with_no_tags()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Noticia')->raw();

        $attributes['conteudo'] = '<p>unit_test' . $attributes['conteudo'] . '</p>';

        $this->post(route('noticias.store'), $attributes);

        $noticia = Noticia::first();

        $this->assertStringNotContainsString('<p>', $noticia->conteudoBusca);

    }
}
