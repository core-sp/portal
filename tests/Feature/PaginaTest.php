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
        $pagina->titulo = 'Outro Título';
        $this->post(route('paginas.store'), $pagina->toArray())->assertForbidden();
        $this->patch(route('paginas.update', $pagina->idpagina), $pagina->toArray())->assertForbidden();
        $this->delete(route('paginas.destroy', $pagina->idpagina))->assertForbidden();
        $this->get(route('paginas.restore', $pagina->idpagina))->assertForbidden();
        $this->get(route('paginas.busca'))->assertForbidden();
        $this->get(route('paginas.trashed'))->assertForbidden();
    }
    
    /** @test */
    public function pagina_can_be_created_by_an_user()
    {
        $user = $this->signInAsAdmin();
        $pagina = factory('App\Pagina')->raw([
            'idusuario' => $user->idusuario
        ]);

        $this->get(route('paginas.index'))->assertOk();
        $this->post(route('paginas.store'), $pagina);
        $this->assertDatabaseHas('paginas', $pagina);
    }

    /** @test */
    public function log_is_generated_when_pagina_is_created()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Pagina')->raw([
            'idusuario' => $user->idusuario
        ]);

        $this->post(route('paginas.store'), $attributes);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') criou *página* (id: 1)';
        $this->assertStringContainsString($txt, $log);
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
    public function a_pagina_requires_a_title_with_less_than_191_chars()
    {
        $faker = \Faker\Factory::create();
        $this->signInAsAdmin();

        $attributes = factory('App\Pagina')->raw([
            'titulo' => $faker->sentence(400)
        ]);

        $this->post(route('paginas.store'), $attributes)
        ->assertSessionHasErrors('titulo');

        $this->assertEquals(0, Pagina::count());
    }

    /** @test */
    public function a_pagina_requires_a_title_with_more_than_3_chars()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Pagina')->raw([
            'titulo' => 'ti'
        ]);

        $this->post(route('paginas.store'), $attributes)
        ->assertSessionHasErrors('titulo');

        $this->assertEquals(0, Pagina::count());
    }

    /** @test */
    public function pagina_with_same_title_cannot_be_created()
    {
        $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();

        $attributes = factory('App\Pagina')->raw([
            'titulo' => $pagina->titulo
        ]);

        $this->post(route('paginas.store', $attributes))
        ->assertSessionHasErrors('titulo');

        $this->assertEquals(Pagina::count(), 1);
        $this->assertDatabaseMissing('paginas', ['conteudo' => $attributes['conteudo']]);
    }

    /** @test */
    public function a_pagina_with_img_more_than_191_chars_cannot_be_created()
    {
        $faker = \Faker\Factory::create();
        $this->signInAsAdmin();

        $attributes = factory('App\Pagina')->raw([
            'img' => $faker->sentence(400)
        ]);

        $this->post(route('paginas.store'), $attributes)
        ->assertSessionHasErrors('img');

        $this->assertEquals(0, Pagina::count());
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
    public function pagina_can_be_updated()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();
        $antigo = $pagina->getAttributes();
        $attributes = $pagina->getAttributes();

        $attributes['titulo'] = 'Novo titulo';
        $attributes['slug'] = str_slug($attributes['titulo'], '-');
        $attributes['img'] = 'teste\imagem.jpg';
        $attributes['conteudo'] = $faker->sentence(400);
        $attributes['conteudoBusca'] = converterParaTextoCru($attributes['conteudo']);
        $attributes['idusuario'] = $user->idusuario;

        $this->patch(route('paginas.update', $pagina->idpagina), $attributes);

        $this->assertDatabaseHas('paginas', $attributes);
        $this->assertDatabaseMissing('paginas', $antigo);
    }

    /** @test */
    public function a_pagina_title_can_be_updated()
    {
        $user = $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();

        $attributes = $pagina->getAttributes();
        $attributes['titulo'] = 'Novo titulo';
        
        $this->patch(route('paginas.update', $pagina->idpagina), $attributes);

        $this->assertDatabaseHas('paginas', [
            'titulo' => $attributes['titulo']
        ]);
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

        $this->patch(route('paginas.update', $paginaDois->idpagina), $attributes)
        ->assertSessionHasErrors('titulo');

        $this->assertNotEquals(Pagina::find($paginaDois->idpagina)->titulo, $pagina->titulo);
    }

    /** @test */
    public function a_pagina_img_can_be_updated()
    {
        $user = $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();

        $attributes = $pagina->getAttributes();
        $attributes['img'] = 'teste\imagem.png';

        $this->patch(route('paginas.update', $pagina->idpagina), $attributes);
        
        $this->assertEquals(Pagina::find($pagina->idpagina)->img, $attributes['img']);
    }

    /** @test */
    public function a_pagina_subtitulo_can_be_updated()
    {
        $user = $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();

        $attributes = $pagina->getAttributes();
        $attributes['subtitulo'] = 'Teste subtitulo';

        $this->patch(route('paginas.update', $pagina->idpagina), $attributes);
        
        $this->assertEquals(Pagina::find($pagina->idpagina)->subtitulo, $attributes['subtitulo']);
    }

    /** @test */
    public function a_pagina_conteudo_can_be_updated()
    {
        $user = $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();

        $attributes = $pagina->getAttributes();
        $attributes['conteudo'] = 'Teste conteudo';

        $this->patch(route('paginas.update', $pagina->idpagina), $attributes);
        
        $this->assertEquals(Pagina::find($pagina->idpagina)->conteudo, $attributes['conteudo']);
    }

    /** @test */
    public function log_is_generated_when_pagina_is_updated()
    {
        $user = $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();

        $this->patch(route('paginas.update', $pagina->idpagina), [
            'idusuario' => $user->idusuario,
            'titulo' => 'Novo titulo',
            'conteudo' => $pagina->conteudo
        ]);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou *página* (id: 1)';
        $this->assertStringContainsString($txt, $log);
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
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') apagou *página* (id: 1)';
        $this->assertStringContainsString($txt, $log);
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

        $this->delete(route('paginas.destroy', $pagina->idpagina));
        $this->get(route('paginas.restore', $pagina->idpagina));
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') restaurou *página* (id: 1)';
        $this->assertStringContainsString($txt, $log);
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
    public function error_404_when_pagina_not_find_is_shown_on_the_website()
    {
        $this->get(route('paginas.site', 'teste-do-error-404'))
            ->assertStatus(404);
    }

    /** @test */
    public function log_is_generated_when_error_404_on_website_when_not_find_pagina()
    {
        $slug = 'teste-do-error-404';
        $this->get(route('paginas.site', $slug))
            ->assertStatus(404);

        $log = tailCustom(storage_path($this->pathLogErros()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.ERROR: ';
        $txt = $inicio . '[Erro: No query results for model [App\Pagina]. para o slug: '.$slug.'], [Controller: App\Http\Controllers\PaginaController@show], ';
        $txt .= '[Código: 0], [Arquivo: /home/vagrant/Workspace/portal/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php], [Linha: 470]';
        $this->assertStringContainsString($txt, $log);
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
    public function pagina_can_be_searched()
    {
        $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();

        $this->get(route('paginas.busca', ['q' => $pagina->titulo]))
            ->assertSeeText($pagina->titulo);
    }

    /** @test */
    public function pagina_author_is_shown_on_admin()
    {
        $user = $this->signInAsAdmin();

        factory('App\Pagina')->create();

        $this->get(route('paginas.index'))->assertSee($user->nome);
    }

    /** @test */
    public function link_to_create_pagina_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $this->get(route('paginas.index'))->assertSee(route('paginas.create'));
    }

    // /** @test */
    public function link_to_edit_pagina_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();

        $this->get(route('paginas.index'))->assertSee(route('paginas.edit', $pagina->idpagina));
    }

    // /** @test */
    public function link_to_destroy_pagina_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $pagina = factory('App\Pagina')->create();

        $this->get(route('paginas.index'))->assertSee(route('paginas.destroy', $pagina->idpagina));
    }

    /** @test */
    public function pagina_conteudoBusca_is_stored_with_no_tags()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Pagina')->raw();

        $attributes['conteudo'] = '<p>unit_test' . $attributes['conteudo'] . '</p>';

        $this->post(route('paginas.store'), $attributes);

        $pagina = Pagina::first();

        $this->assertStringNotContainsString('<p>', $pagina->conteudoBusca);
    }

    /** @test */
    public function pagina_can_be_searched_on_portal()
    {
        $pagina = factory('App\Pagina')->create([
            'titulo' => 'Teste título na busca da home'
        ]);

        $this->get('/')->assertOk();

        $this->get(route('site.busca', ['busca' => 'Teste']))->assertSee($pagina->titulo);
    }
}
