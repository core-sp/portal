<?php

namespace Tests\Feature;

use App\Post;
use App\Permissao;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class PostTest extends TestCase
{
    use RefreshDatabase;

    // protected function setUp(): void
    // {
    //     parent::setUp();
    //     Permissao::insert([
    //         [
    //             'controller' => 'PostsController',
    //             'metodo' => 'index',
    //             'perfis' => '1,'
    //         ], [
    //             'controller' => 'PostsController',
    //             'metodo' => 'create',
    //             'perfis' => '1,'
    //         ], [
    //             'controller' => 'PostsController',
    //             'metodo' => 'edit',
    //             'perfis' => '1,'
    //         ], [
    //             'controller' => 'PostsController',
    //             'metodo' => 'destroy',
    //             'perfis' => '1,'
    //         ]
    //     ]);
    // }

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $post = factory('App\Post')->create();

        $this->get('/admin/posts/busca')->assertRedirect(route('login'));
        $this->get('/admin/posts/')->assertRedirect(route('login'));
        $this->get('/admin/posts/create')->assertRedirect(route('login'));
        $this->post('admin/posts')->assertRedirect(route('login'));
        $this->get('/admin/posts/'.$post->id.'/edit')->assertRedirect(route('login'));
        $this->patch('/admin/posts/'.$post->id)->assertRedirect(route('login'));
        $this->delete('/admin/posts/'.$post->id)->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $post = factory('App\Post')->create();

        $this->get('/admin/posts/busca')->assertForbidden();
        $this->get('/admin/posts/')->assertForbidden();
        $this->get('/admin/posts/create')->assertForbidden();

        $post->titulo = 'Teste do store';
        $this->post('admin/posts', $post->toArray())->assertForbidden();
        $this->get('/admin/posts/'.$post->id.'/edit')->assertForbidden();
        $this->patch('/admin/posts/'.$post->id, $post->toArray())->assertForbidden();
        $this->delete('/admin/posts/'.$post->id)->assertForbidden();
    }

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

        $attributes = factory('App\Post')->raw();

        $this->get(route('posts.create'))->assertForbidden();

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
    public function post_user_creator_is_shown_on_the_admin_panel()
    {
        $user = $this->signInAsAdmin();
        $post = factory('App\Post')->create();
        
        $this->get(route('posts.edit', $post->id))->assertSee($user->nome);
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

        $post->titulo = 'Novo titulo';

        $this->patch(route('posts.update', $post->id), $post->getAttributes())->assertForbidden();
        
        $this->assertDatabaseMissing('posts', ['titulo' => $post->titulo]);
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
    function link_to_create_post_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $this->get(route('posts.index'))->assertSee(route('posts.create'));
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

    // Sistema deve salvar o campo 'conteudoBusca' sem tags e entities do HTML
    /** @test */
    function post_conteudoBusca_is_stored_with_no_tags()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw();

        $attributes['conteudo'] = '<p>unit_test' . $attributes['conteudo'] . '</p>';

        $this->post(route('posts.store'), $attributes);

        $post = Post::first();

        $this->assertStringNotContainsString('<p>', $post->conteudoBusca);

    }

    //Sistema não deve permitir atualização de posts com título já existente
    /** @test */
    function post_title_cannot_be_updated_with_duplicated()
    {
        $user = $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $post2 = factory('App\Post')->create();

        $post2->titulo = $post->titulo;

        $this->patch(route('posts.update', $post2->id), $post2->getAttributes())->assertSessionHasErrors('titulo');

        $this->assertEquals(1 , Post::where('titulo', $post->titulo)->count());
    }
}
