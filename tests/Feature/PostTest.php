<?php

namespace Tests\Feature;

use App\Post;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_post_can_be_created()
    {
        $post = factory('App\Post')->create();

        $this->assertDatabaseHas('posts', ['titulo' => $post->titulo]);
    }
    
    /** @test */
    function post_can_be_created_by_an_user()
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
    public function log_is_generated_when_post_is_created()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Post')->raw();

        $this->post(route('posts.store'), $attributes);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('criou', $log);
        $this->assertStringContainsString('post', $log);
    }

    /** @test */
    public function non_authorized_users_cannot_create_posts()
    {
        $this->signIn();

        $this->get(route('posts.index'))->assertForbidden();

        $attributes = factory('App\Post')->raw();
        
        $this->post(route('posts.store'), $attributes)->assertForbidden();
        $this->assertDatabaseMissing('posts', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    function a_post_is_shown_correctly_on_site_after_its_creation()
    {
        $post = factory('App\Post')->create();

        $this->get('/blog/' . $post->slug)
            ->assertOk()
            ->assertSee($post->titulo)
            ->assertSee($post->conteudo);
    }

    /** @test */
    function created_posts_are_shown_correctly()
    {
        $post = factory('App\Post')->create();

        $this->get('/blog')->assertOk()->assertSee($post->titulo);
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
            ->assertForbidden()
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
        $user = $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $attributes = $post->getAttributes();

        $attributes['titulo'] = 'Novo titulo';

        $this->get(route('posts.edit', $post->id))->assertOk();

        $this->patch(route('posts.update', $post->id), $attributes);
        $this->assertEquals(Post::find($post->id)->titulo, 'Novo titulo');
    }

    /** @test */
    public function log_is_generated_when_post_is_updated()
    {
        $user = $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $attributes = $post->getAttributes();

        $attributes['titulo'] = 'Novo titulo';

        $this->patch(route('posts.update', $post->id), $attributes);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('editou', $log);
        $this->assertStringContainsString('post', $log);
    }

    /** @test */
    public function non_authorized_users_cannot_update_posts_on_admin()
    {
        $this->signIn();

        $post = factory('App\Post')->create();

        $this->get(route('posts.edit', $post->id))->assertForbidden();

        $titulo = 'Novo titulo';

        $this->patch(route('posts.update', $post->id), ['titulo' => $titulo])->assertForbidden();
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
    public function log_is_generated_when_post_is_deleted()
    {
        $user = $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $this->delete(route('posts.destroy', $post->id));
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('apagou', $log);
        $this->assertStringContainsString('post', $log);
    }

    /** @test */
    public function non_authorized_users_cannot_destroy_a_post()
    {
        $this->signIn();

        $post = factory('App\Post')->create();

        $this->delete(route('posts.destroy', $post->id))->assertForbidden();

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
