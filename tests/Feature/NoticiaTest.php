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
    public function noticia_can_be_created()
    {
        $this->signInAsAdmin();
        $attributes = factory('App\Noticia')->raw();

        $this->get(route('noticias.index'))->assertOk();
        $this->post(route('noticias.store'), $attributes);
        $this->assertDatabaseHas('noticias', ['titulo' => $attributes['titulo']]);
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
    public function noticia_is_shown_on_the_website()
    {
        $this->signInAsAdmin();
        $noticia = factory('App\Noticia')->create();

        $this->get('/noticia/' . $noticia->slug)
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
        $this->assertNotNull(Noticia::withTrashed()->find($noticia->idnoticia)->deleted_at);
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
}
