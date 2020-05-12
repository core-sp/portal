<?php

namespace Tests\Feature;

use App\Post;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    function post_can_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw();

        $this->get(route('posts.create'))->assertOk();
        $this->post(route('posts.store'), $attributes);

        $this->assertDatabaseHas('posts', [
            'titulo' => $attributes['titulo'],
            'subtitulo' => $attributes['subtitulo']
        ]);
    }

    /** @test */
    public function non_authorized_users_cannot_create_posts()
    {
        $this->signIn();

        $this->get(route('posts.index'))->assertStatus(403);

        $attributes = factory('App\Post')->raw();
        
        $this->post(route('posts.store'), $attributes)->assertStatus(403);
        $this->assertDatabaseMissing('posts', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function posts_are_shown_on_the_admin_panel()
    {
        $this->signInAsAdmin();
        $post = factory('App\Post')->create();
        $postDois = factory('App\Post')->create();
        
        $this->get(route('posts.index'))->assertSee($post->titulo);
        $this->get(route('posts.index'))->assertSee($postDois->titulo);
    }

    /** @test */
    public function non_authorized_users_cannot_see_posts_on_admin()
    {
        $this->signIn();

        $post = factory('App\Post')->create();

        $this->get(route('posts.index'))
            ->assertStatus(403)
            ->assertDontSee($post->titulo);
    }

    /** @test */
    function post_requires_a_title()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw([
            'titulo' => ''
        ]);

        $this->post(route('posts.store'), $attributes)->assertSessionHasErrors('titulo');
        $this->assertDatabaseMissing('posts', ['conteudo' => $attributes['conteudo']]);
    }

    /** @test */
    function a_post_requires_a_subtitle()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw([
            'subtitulo' => ''
        ]);

        $this->post(route('posts.store'), $attributes)->assertSessionHasErrors('subtitulo');
        $this->assertDatabaseMissing('posts', ['conteudo' => $attributes['conteudo']]);
    }

    /** @test */
    function a_post_requires_a_content()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw([
            'conteudo' => ''
        ]);

        $this->post(route('posts.store'), $attributes)->assertSessionHasErrors('conteudo');
        $this->assertDatabaseMissing('posts', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    function a_post_cannot_have_duplicate_title()
    {
        $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $attributes = factory('App\Post')->raw([
            'titulo' => $post->titulo
        ]);

        $this->post(route('posts.store'), $attributes)->assertSessionHasErrors('titulo');
        $this->assertEquals(1, Post::count());
    }

    /** @test */
    function a_post_will_show_previous_and_next_post_if_available()
    {
        $posts = factory('App\Post', 3)->create();

        $this->get($posts[1]->path())->assertSee($posts[0]->titulo)->assertSee($posts[2]->titulo);
    }

    /** @test */
    function a_post_title_can_be_updated()
    {
        $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $attributes = $post->getAttributes();

        $attributes['titulo'] = 'Novo título';

        $this->get(route('posts.edit', $post->id))->assertOk();

        $this->patch(route('posts.update', $post->id), $attributes);

        $this->assertDatabaseHas('posts', [
            'titulo' => 'Novo título',
            'slug' => 'novo-titulo'
        ]);
    }

    /** @test */
    public function non_authorized_users_cannot_update_posts_on_admin()
    {
        $this->signIn();

        $post = factory('App\Post')->create();

        $this->get(route('posts.edit', $post->id))->assertStatus(403);

        $titulo = 'Novo titulo';

        $this->patch(route('posts.update', $post->id), ['titulo' => $titulo])->assertStatus(403);
        $this->assertDatabaseMissing('posts', ['titulo' => $titulo]);
    }

    /** @test */
    function post_can_be_destroyed()
    {
        $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $this->delete(route('posts.destroy', $post->id));

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
        $this->assertNotNull(Post::withTrashed()->find($post->id)->deleted_at);
    }

    /** @test */
    public function non_authorized_users_cannot_destroy_a_post()
    {
        $this->signIn();

        $post = factory('App\Post')->create();

        $this->delete(route('posts.destroy', $post->id))->assertStatus(403);

        $this->assertDatabaseHas('posts', ['titulo' => $post->titulo]);
    }

    /** @test */
    function post_can_be_searched()
    {
        $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $this->get('/admin/posts/busca', ['q' => $post->titulo])->assertSeeText($post->subtitulo);
    }

    /** @test */
    function link_to_edit_post_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $this->get(route('posts.index'))->assertSee(route('posts.edit', $post->id));
    }

    /** @test */
    function link_to_destroy_post_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $this->get(route('posts.index'))->assertSee(route('posts.destroy', $post->id));
    }
}
