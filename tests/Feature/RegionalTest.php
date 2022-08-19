<?php

namespace Tests\Feature;

use App\Permissao;
use App\Regional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegionalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $regional = factory('App\Regional')->create();

        $this->get(route('regionais.index'))->assertRedirect(route('login'));
        $this->get(route('regionais.busca'))->assertRedirect(route('login'));
        $this->get(route('regionais.edit', $regional->idregional))->assertRedirect(route('login'));
        $this->patch(route('regionais.update', $regional->idregional))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $regional = factory('App\Regional')->create();

        $this->get(route('regionais.edit', $regional->idregional))->assertForbidden();

        $dados = $regional->toArray();
        $dados['horariosage'] = ['10:00'];

        $this->patch(route('regionais.update', $regional->idregional), $dados)->assertForbidden();
    }

    /** @test */
    public function regionais_are_shown_on_admin_panel()
    {
        $this->signIn();

        $regional = factory('App\Regional')->create();
        $regionalDois = factory('App\Regional')->create();

        $this->get(route('regionais.index'))
            ->assertOk()
            ->assertSee($regional->regional)
            ->assertSee($regionalDois->regional);
    }

    /** @test */
    public function link_to_edit_regionais_are_shown_on_admin_panel()
    {
        $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();

        $this->get(route('regionais.index'))
            ->assertSee(route('regionais.update', $regional->idregional));
    }

    /** @test */
    public function non_authorized_users_cannot_see_link_to_edit_regionais_on_admin_panel()
    {
        $this->signIn();

        $regional = factory('App\Regional')->create();

        $this->get(route('regionais.index'))
            ->assertDontSee(route('regionais.update', $regional->idregional));
    }

    /** @test */
    public function authorized_user_can_update_regionais()
    {
        $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();
        $attributes = factory('App\Regional')->raw(['horariosage' => ['20:00', '21:00']]);

        $this->get(route('regionais.edit', $regional->idregional));
        $this->patch(route('regionais.update', $regional->idregional), $attributes);
        $this->assertEquals(Regional::find($regional->idregional)->regional, $attributes['regional']);
        $this->assertEquals(Regional::find($regional->idregional)->email, $attributes['email']);
    }

    /** @test */
    public function log_is_generated_when_regional_is_updated()
    {
        $user = $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();
        $attributes = factory('App\Regional')->raw(['horariosage' => ['20:00', '21:00']]);

        $this->patch(route('regionais.update', $regional->idregional), $attributes);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('editou', $log);
        $this->assertStringContainsString('regional', $log);
    }

    /** @test */
    public function regional_is_required_to_update_regionais()
    {
        $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();
        $dados = $regional->toArray();
        $dados['horariosage'] = ['10:00'];
        $dados['regional'] = '';

        $this->patch(route('regionais.update', $regional->idregional), $dados)
            ->assertSessionHasErrors('regional');
        $this->assertEquals(Regional::find($regional->idregional)->regional, $regional->regional);
    }

    /** @test */
    public function email_is_required_to_update_regionais()
    {
        $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();
        $dados = $regional->toArray();
        $dados['horariosage'] = ['10:00'];
        $dados['email'] = '';

        $this->patch(route('regionais.update', $regional->idregional), $dados)
            ->assertSessionHasErrors('email');
        $this->assertEquals(Regional::find($regional->idregional)->email, $regional->email);
    }

    /** @test */
    public function cannot_update_regionais_with_wrong_email()
    {
        $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();
        $dados = $regional->toArray();
        $dados['horariosage'] = ['10:00'];
        $dados['email'] = 'abc@teste;';

        $this->patch(route('regionais.update', $regional->idregional), $dados)
            ->assertSessionHasErrors('email');
        $this->assertEquals(Regional::find($regional->idregional)->email, $regional->email);
    }

    /** @test */
    public function endereco_is_required_to_update_regionais()
    {
        $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();
        $dados = $regional->toArray();
        $dados['horariosage'] = ['10:00'];
        $dados['endereco'] = '';

        $this->patch(route('regionais.update', $regional->idregional), $dados)
            ->assertSessionHasErrors('endereco');
        $this->assertEquals(Regional::find($regional->idregional)->endereco, $regional->endereco);
    }

    /** @test */
    public function bairro_is_required_to_update_regionais()
    {
        $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();
        $dados = $regional->toArray();
        $dados['horariosage'] = ['10:00'];
        $dados['bairro'] = '';

        $this->patch(route('regionais.update', $regional->idregional), $dados)
            ->assertSessionHasErrors('bairro');
        $this->assertEquals(Regional::find($regional->idregional)->bairro, $regional->bairro);
    }

    /** @test */
    public function numero_is_required_to_update_regionais()
    {
        $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();
        $dados = $regional->toArray();
        $dados['horariosage'] = ['10:00'];
        $dados['numero'] = '';

        $this->patch(route('regionais.update', $regional->idregional), $dados)
            ->assertSessionHasErrors('numero');
        $this->assertEquals(Regional::find($regional->idregional)->numero, $regional->numero);
    }

    /** @test */
    public function cep_is_required_to_update_regionais()
    {
        $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();
        $dados = $regional->toArray();
        $dados['horariosage'] = ['10:00'];
        $dados['cep'] = '';

        $this->patch(route('regionais.update', $regional->idregional), $dados)
            ->assertSessionHasErrors('cep');
        $this->assertEquals(Regional::find($regional->idregional)->cep, $regional->cep);
    }

    /** @test */
    public function telefone_is_required_to_update_regionais()
    {
        $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();
        $dados = $regional->toArray();
        $dados['horariosage'] = ['10:00'];
        $dados['telefone'] = '';

        $this->patch(route('regionais.update', $regional->idregional), $dados)
            ->assertSessionHasErrors('telefone');
        $this->assertEquals(Regional::find($regional->idregional)->telefone, $regional->telefone);
    }

    /** @test */
    public function cannot_update_regionais_with_horariosage_not_array()
    {
        $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();
        $dados = $regional->toArray();
        $dados['horariosage'] = '10:00';

        $this->patch(route('regionais.update', $regional->idregional), $dados)
            ->assertSessionHasErrors('horariosage');
        $this->assertEquals(Regional::find($regional->idregional)->horariosage, $regional->horariosage);
    }

    /** @test */
    public function regionais_can_be_searched()
    {
        $this->signInAsAdmin();

        $regional = factory('App\Regional')->create();

        $this->get(route('regionais.busca', ['q' => $regional->regional]))
            ->assertSeeText($regional->regional);
    }

    /** @test */
    public function regional_is_shown_on_the_website()
    {
        $regional = factory('App\Regional')->create();

        $this->get(route('regionais.show', $regional->idregional))
            ->assertOk()
            ->assertSee($regional->regional)
            ->assertSee($regional->endereco)
            ->assertSee($regional->bairro);
    }

    /** @test */
    public function link_return_regionais_list_when_regional_is_shown_on_the_website()
    {
        $regional = factory('App\Regional')->create();

        $this->get(route('regionais.show', $regional->idregional))
            ->assertOk()
            ->assertSee(route('regionais.siteGrid'));
    }

    /** @test */
    public function noticia_from_regional_is_shown_on_the_website()
    {
        $regional = factory('App\Regional')->create();
        $noticia = factory('App\Noticia')->create([
            'idregional' => $regional->idregional
        ]);

        $this->get(route('regionais.show', $regional->idregional))
            ->assertOk()
            ->assertSee($noticia->titulo)
            ->assertSee(route('noticias.show', $noticia->slug));
    }

    /** @test */
    public function regionais_list_is_shown_on_the_website()
    {
        $regional = factory('App\Regional')->create();
        $regionalDois = factory('App\Regional')->create();

        $this->get(route('regionais.siteGrid'))
            ->assertOk()
            ->assertSee($regional->regional)
            ->assertSee($regionalDois->regional);
    }

    /** @test */
    public function regionais_list_show_links_to_each_regional_on_the_website()
    {
        $regional = factory('App\Regional')->create();
        $regionalDois = factory('App\Regional')->create();

        $this->get(route('regionais.siteGrid'))
            ->assertOk()
            ->assertSee(route('regionais.show', $regional->idregional))
            ->assertSee(route('regionais.show', $regionalDois->idregional));
    }
}
