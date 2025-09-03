<?php

namespace Tests\Feature;

use App\Noticia;
use App\Permissao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Support\Str;

class NoticiaTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $noticia = factory('App\Noticia')->create();

        $this->get(route('noticias.index'))->assertRedirect(route('login'));
        $this->get(route('noticias.create'))->assertRedirect(route('login'));
        $this->get(route('noticias.edit', $noticia->idnoticia))->assertRedirect(route('login'));
        $this->post(route('noticias.store'))->assertRedirect(route('login'));
        $this->patch(route('noticias.update', $noticia->idnoticia))->assertRedirect(route('login'));
        $this->delete(route('noticias.destroy', $noticia->idnoticia))->assertRedirect(route('login'));
        $this->get(route('noticias.restore', $noticia->idnoticia))->assertRedirect(route('login'));
        $this->get(route('noticias.busca'))->assertRedirect(route('login'));
        $this->get(route('noticias.trashed'))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $noticia = factory('App\Noticia')->create();

        $this->get(route('noticias.index'))->assertForbidden();
        $this->get(route('noticias.create'))->assertForbidden();
        $this->get(route('noticias.edit', $noticia->idnoticia))->assertForbidden();

        $noticia->titulo = 'Qualquer título';
        $this->post(route('noticias.store'), $noticia->toArray())->assertForbidden();
        $this->patch(route('noticias.update', $noticia->idnoticia), $noticia->toArray())->assertForbidden();
        $this->delete(route('noticias.destroy', $noticia->idnoticia))->assertForbidden();
        $this->get(route('noticias.restore', $noticia->idnoticia))->assertForbidden();
        $this->get(route('noticias.busca'))->assertForbidden();
        $this->get(route('noticias.trashed'))->assertForbidden();
    }

    /** @test */
    public function noticia_can_be_created_by_an_user()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Noticia')->raw([
            'idusuario' => $user->idusuario
        ]);

        $this->get(route('noticias.index'))->assertOk();
        $this->post(route('noticias.store'), $attributes);
        $this->assertDatabaseHas('noticias', $attributes);
    }

    /** @test */
    public function noticia_can_be_created_with_img_blur()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Noticia')->raw([
            'idusuario' => $user->idusuario
        ]);

        $hash = $this->gerenciarPastasLazyLoad($attributes['img']);

        $this->get(route('noticias.index'))->assertOk();
        $this->post(route('noticias.store'), $attributes);

        $attributes['img'] = $this->trocarNomeImgLazyLoad($attributes['img'], $hash);
        $this->assertDatabaseHas('noticias', $attributes);

        $this->assertTrue(\File::exists(public_path($attributes['img'])));

        $this->assertTrue(\File::exists(public_path('/imagens/fake/' . date('Y-m') . '/.blur/small-' . $hash)));
    }

    /** @test */
    public function a_noticia_without_titulo_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Noticia')->raw([
            'titulo' => ''
        ]);

        $this->post(route('noticias.store'), $attributes)
        ->assertSessionHasErrors('titulo');

        $this->assertEquals(0, Noticia::count());
    }

    /** @test */
    public function a_noticia_requires_a_title_with_less_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $this->signInAsAdmin();

        $attributes = factory('App\Noticia')->raw([
            'titulo' => $faker->sentence(400)
        ]);

        $this->post(route('noticias.store'), $attributes)
        ->assertSessionHasErrors('titulo');

        $this->assertEquals(0, Noticia::count());
    }

    /** @test */
    public function a_noticia_requires_a_title_with_more_than_3_chars()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Noticia')->raw([
            'titulo' => 'ti'
        ]);

        $this->post(route('noticias.store'), $attributes)
        ->assertSessionHasErrors('titulo');

        $this->assertEquals(0, Noticia::count());
    }

    /** @test */
    public function noticia_with_same_title_cannot_be_created()
    {
        $this->signInAsAdmin();
        $noticia = factory('App\Noticia')->create();
        $attributes = factory('App\Noticia')->raw([
            'titulo' => $noticia->titulo
        ]);
        $this->post(route('noticias.store'), $attributes)
        ->assertSessionHasErrors('titulo');

        $this->assertEquals(1, Noticia::count());
    }

    /** @test */
    public function a_noticia_with_img_more_than_191_chars_cannot_be_created()
    {
        $faker = \Faker\Factory::create();
        $this->signInAsAdmin();

        $attributes = factory('App\Noticia')->raw([
            'img' => $faker->sentence(400)
        ]);

        $this->post(route('noticias.store'), $attributes)
        ->assertSessionHasErrors('img');

        $this->assertEquals(0, Noticia::count());
    }

    /** @test */
    public function a_noticia_without_conteudo_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Noticia')->raw([
            'conteudo' => ''
        ]);

        $this->post(route('noticias.store'), $attributes)
        ->assertSessionHasErrors('conteudo');

        $this->assertEquals(0, Noticia::count());
    }

    /** @test */
    public function a_noticia_with_conteudo_less_than_100_chars_cannot_be_created()
    {
        $faker = \Faker\Factory::create();
        $this->signInAsAdmin();

        $attributes = factory('App\Noticia')->raw([
            'conteudo' => $faker->text(90)
        ]);

        $this->post(route('noticias.store'), $attributes)
        ->assertSessionHasErrors('conteudo');

        $this->assertEquals(0, Noticia::count());
    }

    /** @test */
    public function a_noticia_with_categoria_with_value_wrong_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Noticia')->raw([
            'categoria' => 'Teste'
        ]);

        $this->post(route('noticias.store'), $attributes)
        ->assertSessionHasErrors('categoria');

        $this->assertEquals(0, Noticia::count());
    }

    /** @test */
    public function a_noticia_with_idregional_with_value_wrong_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Noticia')->raw([
            'idregional' => 5
        ]);

        $this->post(route('noticias.store'), $attributes)
        ->assertSessionHasErrors('idregional');

        $this->assertEquals(0, Noticia::count());
    }

    /** @test */
    public function a_noticia_with_idcurso_with_value_wrong_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Noticia')->raw([
            'idcurso' => 5
        ]);

        $this->post(route('noticias.store'), $attributes)
        ->assertSessionHasErrors('idcurso');

        $this->assertEquals(0, Noticia::count());
    }

    /** @test */
    public function log_is_generated_when_noticia_is_created()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Noticia')->raw([
            'idusuario' => $user->idusuario
        ]);

        $this->post(route('noticias.store'), $attributes);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') criou *notícia* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function noticia_can_be_updated()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();
        $antigo = $noticia->getAttributes();
        $attributes = $noticia->getAttributes();

        $attributes['titulo'] = 'Novo titulo';
        $attributes['slug'] = Str::slug($attributes['titulo'], '-');
        $attributes['img'] = 'teste\imagem.jpg';
        $attributes['idregional'] = null;
        $attributes['idcurso'] = null;
        $attributes['categoria'] = 'Feiras';
        $attributes['conteudo'] = $faker->sentence(400);
        $attributes['conteudoBusca'] = converterParaTextoCru($attributes['conteudo']);
        $attributes['idusuario'] = $user->idusuario;

        $this->patch(route('noticias.update', $noticia->idnoticia), $attributes);

        $this->assertDatabaseHas('noticias', $attributes);
        $this->assertDatabaseMissing('noticias', $antigo);
    }

    /** @test */
    public function noticia_can_be_updated_with_img_blur()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();
        $antigo = $noticia->getAttributes();
        $attributes = $noticia->getAttributes();

        $attributes['titulo'] = 'Novo titulo';
        $attributes['slug'] = Str::slug($attributes['titulo'], '-');
        $attributes['img'] = $noticia->img . '.jpg';
        $attributes['idregional'] = null;
        $attributes['idcurso'] = null;
        $attributes['categoria'] = 'Feiras';
        $attributes['conteudo'] = $faker->sentence(400);
        $attributes['conteudoBusca'] = converterParaTextoCru($attributes['conteudo']);
        $attributes['idusuario'] = $user->idusuario;

        $hash = $this->gerenciarPastasLazyLoad($attributes['img']);

        $this->patch(route('noticias.update', $noticia->idnoticia), $attributes);

        $attributes['img'] = $this->trocarNomeImgLazyLoad($attributes['img'], $hash);
        $this->assertDatabaseHas('noticias', $attributes);
        $this->assertDatabaseMissing('noticias', $antigo);

        $this->assertTrue(\File::exists(public_path($attributes['img'])));

        $this->assertTrue(\File::exists(public_path('/imagens/fake/' . date('Y-m') . '/.blur/small-' . $hash)));
    }

    /** @test */
    public function log_is_generated_when_update_noticia_img_blur()
    {
        $this->noticia_can_be_updated_with_img_blur();

        $log = tailCustom(storage_path($this->pathLogInterno()), 3);
        $txt = '" renomeada para "..' . Noticia::first()->img . '".';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function a_noticia_title_can_be_updated()
    {
        $user = $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $attributes = $noticia->getAttributes();
        $attributes['titulo'] = 'Novo titulo';
        
        $this->patch(route('noticias.update', $noticia->idnoticia), $attributes);

        $this->assertDatabaseHas('noticias', [
            'titulo' => $attributes['titulo']
        ]);
    }

    /** @test */
    public function a_noticia_img_can_be_updated()
    {
        $user = $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $attributes = $noticia->getAttributes();
        $attributes['img'] = 'teste\imagem.png';

        $this->patch(route('noticias.update', $noticia->idnoticia), $attributes);
        
        $this->assertEquals(Noticia::find($noticia->idnoticia)->img, $attributes['img']);
    }

    /** @test */
    public function a_noticia_idregional_can_be_updated()
    {
        $user = $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();
        $id = factory('App\Regional')->create()->idregional;

        $attributes = $noticia->getAttributes();
        $attributes['idregional'] = (string) $id;

        $this->patch(route('noticias.update', $noticia['idnoticia']), $attributes);

        $this->assertDatabaseHas('noticias', [
            'idregional' => $id
        ]);
    }

    /** @test */
    public function a_noticia_idcurso_can_be_updated()
    {
        $user = $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();
        $id = factory('App\Curso')->create()->idcurso;

        $attributes = $noticia->getAttributes();
        $attributes['idcurso'] = (string) $id;

        $this->patch(route('noticias.update', $noticia->idnoticia), $attributes);
        
        $this->assertDatabaseHas('noticias', [
            'idcurso' => $attributes['idcurso']
        ]);
    }

    /** @test */
    public function a_noticia_categoria_can_be_updated()
    {
        $user = $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $attributes = $noticia->getAttributes();
        $attributes['categoria'] = 'Feiras';

        $this->patch(route('noticias.update', $noticia->idnoticia), $attributes);
        
        $this->assertEquals(Noticia::first()->categoria, $attributes['categoria']);
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
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou *notícia* (id: 1)';
        $this->assertStringContainsString($txt, $log);
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
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') apagou *notícia* (id: 1)';
        $this->assertStringContainsString($txt, $log);
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
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') restaurou *notícia* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function noticia_is_shown_on_the_website()
    {
        $noticia = factory('App\Noticia')->create();

        $this->get(route('noticias.show', $noticia->slug))
            ->assertOk()
            ->assertSee($noticia->titulo);
    }

    /** @test */
    public function noticia_is_shown_on_the_website_with_generic_img()
    {
        $noticia = factory('App\Noticia')->create(['img' => null]);

        $this->get(route('noticias.show', $noticia->slug))
            ->assertOk()
            ->assertSee($noticia->titulo)
            ->assertDontSee('<img class="lazy-loaded-image lazy" src="" data-src="'. asset($noticia->img) .'" />');

        $this->get(route('noticias.siteGrid'))
            ->assertOk()
            ->assertSee($noticia->titulo)
            ->assertSee('<img class="lazy-loaded-image lazy bn-img" src="' .asset('img/small-news-generica-thumb.png'). '" data-src="'. asset('img/news-generica-thumb.png') .'" />');
    }

    /** @test */
    public function noticia_is_shown_on_the_website_without_img_created()
    {
        $this->noticia_can_be_created_by_an_user();

        $noticia = Noticia::first();

        $this->get(route('noticias.show', $noticia->slug))
            ->assertOk()
            ->assertSee($noticia->titulo)
            ->assertSee('<img class="lazy-loaded-image lazy" src="" data-src="'. asset($noticia->img) .'" />');

        $this->get(route('noticias.siteGrid'))
            ->assertOk()
            ->assertSee($noticia->titulo)
            ->assertSee('<img class="lazy-loaded-image lazy bn-img" src="" data-src="'. asset(imgToThumb($noticia->img)) .'" />');
    }

    /** @test */
    public function noticia_is_shown_on_the_website_with_img_created()
    {
        $this->noticia_can_be_created_with_img_blur();

        $noticia = Noticia::first();

        $this->get(route('noticias.show', $noticia->slug))
            ->assertOk()
            ->assertSee($noticia->titulo)
            ->assertSee('<img class="lazy-loaded-image lazy" src="' .$noticia->imgBlur(). '" data-src="'. asset($noticia->img) .'" />');

        $this->get(route('noticias.siteGrid'))
            ->assertOk()
            ->assertSee($noticia->titulo)
            ->assertSee('<img class="lazy-loaded-image lazy bn-img" src="' .$noticia->imgBlur(). '" data-src="'. asset(imgToThumb($noticia->img)) .'" />');
    }

    /** @test */
    public function error_404_when_noticia_not_find_is_shown_on_the_website()
    {
        $this->get(route('noticias.show', 'teste-do-error-404'))
            ->assertStatus(404);
    }

    /** @test */
    public function log_is_generated_when_error_404_on_website_when_not_find_noticia()
    {
        $slug = 'teste-do-error-404';
        $this->get(route('noticias.show', $slug))
            ->assertStatus(404);

        $log = tailCustom(storage_path($this->pathLogErros()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.ERROR: ';
        $txt = $inicio . '[Erro: No query results for model [App\Noticia]. para o slug: '.$slug.'], [Controller: App\Http\Controllers\NoticiaController@show], ';
        $txt .= '[Código: 0], [Arquivo: /var/www/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php], [Linha: 470]';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function noticias_are_shown_on_the_admin_panel()
    {
        $this->signInAsAdmin();
        $noticias = factory('App\Noticia', 2)->create();
        
        $this->get(route('noticias.index'))
        ->assertSee($noticias->get(0)->titulo)
        ->assertSee($noticias->get(1)->titulo);
    }

    /** @test */
    public function noticia_user_creator_is_shown_on_the_admin_panel()
    {
        $user = $this->signInAsAdmin();
        $noticia = factory('App\Noticia')->create();
        
        $this->get(route('noticias.edit', $noticia->idnoticia))->assertSee($user->nome);
    }

    /** @test */
    public function noticias_site_grid_is_shown_on_the_website()
    {
        $noticias = factory('App\Noticia', 5)->create();

        $this->get(route('noticias.siteGrid'))
        ->assertSee($noticias->get(0)->titulo)
        ->assertSee($noticias->get(1)->titulo)
        ->assertSee($noticias->get(2)->titulo)
        ->assertSee($noticias->get(3)->titulo)
        ->assertSee($noticias->get(4)->titulo);
    }

    /** @test */
    public function noticia_can_be_searched()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $this->get(route('noticias.busca', ['q' => $noticia->titulo]))
            ->assertSeeText($noticia->titulo);
    }

    /** @test */
    public function noticia_author_is_shown_on_admin()
    {
        $user = $this->signInAsAdmin();

        factory('App\Noticia')->create();

        $this->get(route('noticias.index'))->assertSee($user->nome);
    }

    /** @test */
    public function link_to_edit_noticia_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $this->get(route('noticias.index'))->assertSee(route('noticias.edit', $noticia->idnoticia));
    }

    /** @test */
    public function link_to_destroy_noticia_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $noticia = factory('App\Noticia')->create();

        $this->get(route('noticias.index'))->assertSee(route('noticias.destroy', $noticia->idnoticia));
    }

    /** @test */
    public function link_to_create_noticia_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $this->get(route('noticias.index'))->assertSee(route('noticias.create'));
    }

    /** @test */
    public function noticia_conteudoBusca_is_stored_with_no_tags()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Noticia')->raw();

        $attributes['conteudo'] = '<p>unit_test' . $attributes['conteudo'] . '</p>';

        $this->post(route('noticias.store'), $attributes);

        $noticia = Noticia::first();

        $this->assertStringNotContainsString('<p>', $noticia->conteudoBusca);

    }

    /** @test */
    public function noticia_can_be_searched_on_portal()
    {
        $noticia = factory('App\Noticia')->create([
            'titulo' => 'Teste título na busca da home'
        ]);

        $this->get('/')->assertOk();

        $this->get(route('site.busca', ['busca' => 'Teste']))->assertSee($noticia->titulo);
    }
}
