<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BlogTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    function an_admin_can_create_a_blog_post()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw();

        $this->get('/admin/posts/create')->assertOk();
        $this->post('/admin/posts', $attributes);

        $this->assertDatabaseHas('posts', [
            'titulo' => $attributes['titulo'],
            'subtitulo' => $attributes['subtitulo']
        ]);
    }

    /** @test */
    function an_editor_can_create_a_blog_post()
    {
        $this->signInAsEditor();

        $attributes = factory('App\Post')->raw();

        $this->get('/admin/posts/create')->assertOk();
        $this->post('/admin/posts', $attributes);

        $this->assertDatabaseHas('posts', [
            'titulo' => $attributes['titulo'],
            'subtitulo' => $attributes['subtitulo']
        ]);
    }

    /** @test */
    function a_non_editor_cannot_create_a_blog_post()
    {
        $this->signInAsAtendimento();

        $attributes = factory('App\Post')->raw();

        $this->get('/admin/posts/create')->assertForbidden();
        $this->post('/admin/posts', $attributes);

        $this->assertDatabaseMissing('posts', [
            'titulo' => $attributes['titulo'],
            'subtitulo' => $attributes['subtitulo']
        ]);
    }

    /** @test */
    function a_post_requires_a_title()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw([
            'titulo' => ''
        ]);

        $this
            ->post('/admin/posts', $attributes)
            ->assertSessionHasErrors('titulo');
    }

    /** @test */
    function a_post_requires_a_subtitle()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw([
            'subtitulo' => ''
        ]);

        $this
            ->post('/admin/posts', $attributes)
            ->assertSessionHasErrors('subtitulo');
    }

    /** @test */
    function a_post_requires_a_content()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw([
            'conteudo' => ''
        ]);

        $this
            ->post('/admin/posts', $attributes)
            ->assertSessionHasErrors('conteudo');
    }

    /** @test */
    function a_post_cannot_have_duplicate_title()
    {
        $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $attributes = factory('App\Post')->raw([
            'titulo' => $post->titulo
        ]);

        $this
            ->post('/admin/posts', $attributes)
            ->assertSessionHasErrors('titulo');
    }

    /** @test */
    function a_post_will_show_previous_and_next_post_if_available()
    {
        $posts = factory('App\Post', 2)->create();

        $this->get($posts[1]->path())->assertSee($posts[0]->titulo);

        $this->get($posts[0]->path())->assertSee($posts[1]->titulo);
    }

    /** @test */
    function admins_and_editors_can_create_posts_via_dashboard()
    {
        $this->signInAsAdmin();

        $this->get('/admin/posts/create')->assertOk();

        $this->signInAsEditor();

        $this->get('/admin/posts/create')->assertOk();
    }

    /** @test */
    function an_admin_can_update_the_title_of_a_blog_post()
    {
        $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $attributes = $post->getAttributes();

        $attributes['titulo'] = 'Novo título';

        $this->get('/admin/posts/' . $post->id . '/edit')->assertOk();

        $this->patch('/admin/posts/' . $post->id, $attributes);

        $this->assertDatabaseHas('posts', [
            'titulo' => 'Novo título',
            'slug' => 'novo-titulo'
        ]);
    }

    /** @test */
    function an_editor_can_update_the_title_of_a_blog_post()
    {
        $this->signInAsEditor();

        $post = factory('App\Post')->create();

        $attributes = $post->getAttributes();

        $attributes['titulo'] = 'Novo título';

        $this->get('/admin/posts/' . $post->id . '/edit')->assertOk();

        $this->patch('/admin/posts/' . $post->id, $attributes);

        $this->assertDatabaseHas('posts', [
            'titulo' => 'Novo título',
            'slug' => 'novo-titulo'
        ]);
    }

    /** @test */
    function a_noneditor_or_nonadmin_cannot_update_the_title_of_a_blog_post()
    {
        $this->signInAsAtendimento();

        $post = factory('App\Post')->create();

        $attributes = $post->getAttributes();

        $attributes['titulo'] = 'Novo título';

        $this->get('/admin/posts/' . $post->id . '/edit')->assertForbidden();

        $this->patch('/admin/posts/' . $post->id, $attributes);

        $this->assertDatabaseMissing('posts', [
            'titulo' => 'Novo título',
            'slug' => 'novo-titulo'
        ]);
    }

    /** @test */
    function an_admin_or_an_editor_can_destroy_a_blog_post()
    {
        $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $this->delete('/admin/posts/' . $post->id);

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    /** @test */
    function the_search_must_show_a_blog_post()
    {
        $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $this
            ->get('/admin/posts/busca', ['q' => $post->titulo])
            ->assertSeeText($post->subtitulo);
    }
}
