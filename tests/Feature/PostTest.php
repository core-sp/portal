<?php

namespace Tests\Feature;

use App\Post;
use App\Permissao;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $post = factory('App\Post')->create();

        $this->get(route('admin.posts.busca'))->assertRedirect(route('login'));
        $this->get(route('posts.index'))->assertRedirect(route('login'));
        $this->get(route('posts.create'))->assertRedirect(route('login'));
        $this->post(route('posts.store'))->assertRedirect(route('login'));
        $this->get(route('posts.edit', $post->id))->assertRedirect(route('login'));
        $this->patch(route('posts.update', $post->id))->assertRedirect(route('login'));
        $this->delete(route('posts.destroy', $post->id))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $post = factory('App\Post')->create();

        $this->get(route('admin.posts.busca'))->assertForbidden();
        $this->get(route('posts.index'))->assertForbidden();
        $this->get(route('posts.create'))->assertForbidden();

        $post->titulo = 'Teste do store';
        $this->post(route('posts.store'), $post->toArray())->assertForbidden();
        $this->get(route('posts.edit', $post->id))->assertForbidden();
        $this->patch(route('posts.update', $post->id), $post->toArray())->assertForbidden();
        $this->delete(route('posts.destroy', $post->id))->assertForbidden();
    }
    
    /** @test */
    public function post_can_be_created_by_an_user()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw([
            'idusuario' => $user->idusuario
        ]);

        $this->get(route('posts.create'))->assertOk();
        $this->post(route('posts.store'), $attributes);

        $this->assertDatabaseHas('posts', $attributes);
    }

    /** @test */
    public function post_can_be_created_with_img_blur()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw([
            'idusuario' => $user->idusuario
        ]);

        $hash = $this->gerenciarPastasLazyLoad($attributes['img']);

        $this->get(route('posts.create'))->assertOk();
        $this->post(route('posts.store'), $attributes);

        $attributes['img'] = $this->trocarNomeImgLazyLoad($attributes['img'], $hash);
        $this->assertDatabaseHas('posts', $attributes);

        $this->assertTrue(\File::exists(public_path($attributes['img'])));

        $this->assertTrue(\File::exists(public_path('/imagens/fake/' . date('Y-m') . '/.blur/small-' . $hash)));
    }

    /** @test */
    public function post_requires_a_title()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw([
            'titulo' => ''
        ]);

        $this->post(route('posts.store'), $attributes)
        ->assertSessionHasErrors('titulo');
        $this->assertDatabaseMissing('posts', $attributes);
    }

    /** @test */
    public function post_requires_a_title_with_less_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw([
            'titulo' => $faker->sentence(400)
        ]);

        $this->post(route('posts.store'), $attributes)
        ->assertSessionHasErrors('titulo');
        $this->assertDatabaseMissing('posts', $attributes);
    }

    /** @test */
    public function a_post_requires_a_subtitle()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw([
            'subtitulo' => ''
        ]);

        $this->post(route('posts.store'), $attributes)
        ->assertSessionHasErrors('subtitulo');
        $this->assertDatabaseMissing('posts', $attributes);
    }

    /** @test */
    public function post_requires_a_subtitle_with_less_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw([
            'subtitulo' => $faker->sentence(400)
        ]);

        $this->post(route('posts.store'), $attributes)
        ->assertSessionHasErrors('subtitulo');
        $this->assertDatabaseMissing('posts', $attributes);
    }

    /** @test */
    public function a_post_requires_a_content()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw([
            'conteudo' => ''
        ]);

        $this->post(route('posts.store'), $attributes)
        ->assertSessionHasErrors('conteudo');
        $this->assertDatabaseMissing('posts', $attributes);
    }

    /** @test */
    public function a_post_requires_a_image()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw([
            'img' => ''
        ]);

        $this->post(route('posts.store'), $attributes)
        ->assertSessionHasErrors('img');
        $this->assertDatabaseMissing('posts', $attributes);
    }

    /** @test */
    public function post_requires_a_img_with_less_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw([
            'img' => $faker->sentence(400)
        ]);

        $this->post(route('posts.store'), $attributes)
        ->assertSessionHasErrors('img');
        $this->assertDatabaseMissing('posts', $attributes);
    }

    /** @test */
    public function a_post_cannot_have_duplicate_title()
    {
        $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $attributes = factory('App\Post')->raw([
            'titulo' => $post->titulo
        ]);

        $this->post(route('posts.store'), $attributes)
        ->assertSessionHasErrors('titulo');
        $this->assertEquals(1, Post::count());
    }

    /** @test */
    public function log_is_generated_when_post_is_created()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Post')->raw();

        $this->post(route('posts.store'), $attributes);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') criou *post* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function post_can_be_updated()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $antigo = $post->getAttributes();
        $attributes = $post->getAttributes();

        $attributes['titulo'] = 'Novo titulo';
        $attributes['slug'] = Str::slug($attributes['titulo'], '-');
        $attributes['subtitulo'] = 'Novo subtitulo';
        $attributes['img'] = 'teste\imagem.jpg';
        $attributes['conteudo'] = $faker->sentence(400);
        $attributes['conteudoBusca'] = converterParaTextoCru($attributes['conteudo']);
        $attributes['idusuario'] = $user->idusuario;

        $this->get(route('posts.edit', $post->id))->assertOk();

        $this->patch(route('posts.update', $post->id), $attributes);
        $this->assertDatabaseHas('posts', $attributes);
        $this->assertDatabaseMissing('posts', $antigo);
    }

    /** @test */
    public function post_can_be_updated_with_img_blur()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $antigo = $post->getAttributes();
        $attributes = $post->getAttributes();

        $attributes['titulo'] = 'Novo titulo';
        $attributes['slug'] = Str::slug($attributes['titulo'], '-');
        $attributes['subtitulo'] = 'Novo subtitulo';
        $attributes['img'] = $post->img . '.jpeg';
        $attributes['conteudo'] = $faker->sentence(400);
        $attributes['conteudoBusca'] = converterParaTextoCru($attributes['conteudo']);
        $attributes['idusuario'] = $user->idusuario;

        $this->get(route('posts.edit', $post->id))->assertOk();

        $hash = $this->gerenciarPastasLazyLoad($attributes['img']);

        $this->patch(route('posts.update', $post->id), $attributes);

        $attributes['img'] = $this->trocarNomeImgLazyLoad($attributes['img'], $hash);
        $this->assertDatabaseHas('posts', $attributes);
        $this->assertDatabaseMissing('posts', $antigo);

        $this->assertTrue(\File::exists(public_path($attributes['img'])));

        $this->assertTrue(\File::exists(public_path('/imagens/fake/' . date('Y-m') . '/.blur/small-' . $hash)));
    }

    /** @test */
    public function log_is_generated_when_update_post_img_blur()
    {
        $this->post_can_be_updated_with_img_blur();

        $log = tailCustom(storage_path($this->pathLogInterno()), 3);
        $txt = '" renomeada para "..' . Post::first()->img . '".';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function a_post_title_can_be_updated()
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
    public function a_post_subtitle_can_be_updated()
    {
        $user = $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $attributes = $post->getAttributes();
        $attributes['subtitulo'] = 'Novo subtitulo';

        $this->get(route('posts.edit', $post->id))->assertOk();

        $this->patch(route('posts.update', $post->id), $attributes);
        $this->assertEquals(Post::find($post->id)->subtitulo, 'Novo subtitulo');
    }

    /** @test */
    public function a_post_img_can_be_updated()
    {
        $user = $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $attributes = $post->getAttributes();
        $attributes['img'] = 'imagem.png';

        $this->get(route('posts.edit', $post->id))->assertOk();

        $this->patch(route('posts.update', $post->id), $attributes);
        $this->assertEquals(Post::find($post->id)->img, 'imagem.png');
    }

    /** @test */
    public function a_post_content_can_be_updated()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $attributes = $post->getAttributes();
        $attributes['conteudo'] = $faker->sentence(400);

        $this->get(route('posts.edit', $post->id))->assertOk();

        $this->patch(route('posts.update', $post->id), $attributes);
        $this->assertEquals(Post::find($post->id)->conteudo, $attributes['conteudo']);
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
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou *post* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function a_post_is_shown_correctly_on_site_after_its_creation()
    {
        $post = factory('App\Post')->create();

        $this->get(route('site.blog.post', $post->slug))
            ->assertOk()
            ->assertSee($post->titulo)
            ->assertSee($post->subtitulo)
            ->assertSee($post->conteudo);
    }

    /** @test */
    public function error_404_when_post_not_find_is_shown_on_the_website()
    {
        $this->get(route('site.blog.post', 'teste-do-error-404'))
            ->assertStatus(404);
    }

    /** @test */
    public function log_is_generated_when_error_404_on_website_when_not_find_post()
    {
        $slug = 'teste-do-error-404';
        $this->get(route('site.blog.post', $slug))
            ->assertStatus(404);

        $log = tailCustom(storage_path($this->pathLogErros()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.ERROR: ';
        $txt = $inicio . '[Erro: No query results for model [App\Post]. para o slug: '.$slug.'], [Controller: App\Http\Controllers\PostsController@show], ';
        $txt .= '[Código: 0], [Arquivo: /var/www/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php], [Linha: 470]';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function post_user_creator_is_shown_on_the_admin_panel()
    {
        $user = $this->signInAsAdmin();
        $post = factory('App\Post')->create();
        
        $this->get(route('posts.edit', $post->id))->assertSee($user->nome);
    }

    /** @test */
    public function created_posts_are_shown_correctly()
    {
        $posts = factory('App\Post', 5)->create();
        $temp = array();
        foreach($posts as $post)
            array_push($temp, $post->titulo);

        $this->get(route('site.blog'))
        ->assertOk()
        ->assertSeeTextInOrder($temp);
    }

    /** @test */
    public function created_posts_are_shown_without_img_created()
    {
        $this->post_can_be_created_by_an_user();

        $post = Post::first();

        $this->get(route('site.blog.post', $post->slug))
            ->assertOk()
            ->assertSee($post->titulo)
            ->assertSee('<img class="lazy-loaded-image lazy" src="" data-src="'. asset($post->img) .'" />');

        $this->get(route('site.blog'))
            ->assertOk()
            ->assertSee($post->titulo)
            ->assertSee('<img class="lazy-loaded-image lazy bn-img" src="" data-src="'. asset(imgToThumb($post->img)) .'" />');
    }

    /** @test */
    public function created_posts_are_shown_with_img_created()
    {
        $this->post_can_be_created_with_img_blur();

        $post = Post::first();

        $this->get(route('site.blog.post', $post->slug))
            ->assertOk()
            ->assertSee($post->titulo)
            ->assertSee('<img class="lazy-loaded-image lazy" src="' .$post->imgBlur(). '" data-src="'. asset($post->img) .'" />');

        $this->get(route('site.blog'))
            ->assertOk()
            ->assertSee($post->titulo)
            ->assertSee('<img class="lazy-loaded-image lazy bn-img" src="' .$post->imgBlur(). '" data-src="'. asset(imgToThumb($post->img)) .'" />');
    }

    /** @test */
    public function created_posts_are_shown_correctly_home()
    {
        $posts = factory('App\Post', 3)->create();
        $temp = array();
        foreach($posts as $post)
            array_push($temp, '<h5 class="branco mt-1">'.$post->titulo.'</h5>');

        $this->get(route('site.home'))
        ->assertOk()
        ->assertSeeInOrder($temp);
    }

    /** @test */
    public function posts_are_shown_on_the_admin_panel()
    {
        $this->signInAsAdmin();
        $posts = factory('App\Post', 3)->create()->sortByDesc('id');
        $temp = array();
        foreach($posts as $post)
            array_push($temp, $post->titulo);
        
        $this->get(route('posts.index'))->assertSeeTextInOrder($temp);
    }

    /** @test */
    public function a_post_will_show_previous_and_next_post_if_available()
    {
        $posts = factory('App\Post', 6)->create();

        $this->get(route('site.blog.post', $posts->get(4)->slug))
        ->assertSee($posts->get(3)->titulo)->assertSee($posts->get(5)->titulo);
    }

    /** @test */
    public function post_can_be_destroyed()
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
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') apagou *post* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function post_can_be_searched()
    {
        $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $this->get(route('admin.posts.busca'), ['q' => $post->titulo])->assertSeeText($post->subtitulo);
    }

    /** @test */
    public function link_to_create_post_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $this->get(route('posts.index'))->assertSee(route('posts.create'));
    }

    /** @test */
    public function link_to_edit_post_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $this->get(route('posts.index'))->assertSee(route('posts.edit', $post->id));
    }

    /** @test */
    public function link_to_destroy_post_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $post = factory('App\Post')->create();

        $this->get(route('posts.index'))->assertSee(route('posts.destroy', $post->id));
    }

    // Sistema deve salvar o campo 'conteudoBusca' sem tags e entities do HTML
    /** @test */
    public function post_conteudoBusca_is_stored_with_no_tags()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Post')->raw();

        $attributes['conteudo'] = '<p>unit_test' . $attributes['conteudo'] . '</p>';

        $this->post(route('posts.store'), $attributes);

        $post = Post::first();

        $this->assertStringNotContainsString('<p>', $post->conteudoBusca);

    }

    /** @test */
    public function post_can_be_searched_on_portal()
    {
        $post = factory('App\Post')->create([
            'titulo' => 'Teste título na busca da home'
        ]);

        $this->get('/')->assertOk();

        $this->get(route('site.busca', ['busca' => 'Teste']))->assertSee($post->titulo);
    }
}
