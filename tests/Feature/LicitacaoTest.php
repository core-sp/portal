<?php

namespace Tests\Feature;

use App\Licitacao;
use App\Permissao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class LicitacaoTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * =======================================================================================================
     * TESTES NO ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $licitacao = factory('App\Licitacao')->create();
        $licitacao->datarealizacao = Carbon::create($licitacao->datarealizacao)->format('Y-m-d H:i');

        $this->get(route('licitacoes.index'))->assertRedirect(route('login'));
        $this->get(route('licitacoes.create'))->assertRedirect(route('login'));
        $this->get(route('licitacoes.edit', $licitacao->idlicitacao))->assertRedirect(route('login'));
        $this->post(route('licitacoes.store'))->assertRedirect(route('login'));
        $this->patch(route('licitacoes.update', $licitacao->idlicitacao))->assertRedirect(route('login'));
        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao))->assertRedirect(route('login'));
        $this->get(route('licitacoes.restore', $licitacao->idlicitacao))->assertRedirect(route('login'));
        $this->get(route('licitacoes.busca'))->assertRedirect(route('login'));
        $this->get(route('licitacoes.trashed'))->assertRedirect(route('login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $licitacao = factory('App\Licitacao')->create();
        $licitacao->datarealizacao = Carbon::create($licitacao->datarealizacao)->format('Y-m-d H:i');

        $this->get(route('licitacoes.index'))->assertForbidden();
        $this->get(route('licitacoes.create'))->assertForbidden();
        $this->get(route('licitacoes.edit', $licitacao->idlicitacao))->assertForbidden();
        $this->post(route('licitacoes.store'), $licitacao->toArray())->assertForbidden();
        $this->patch(route('licitacoes.update', $licitacao->idlicitacao), $licitacao->toArray())->assertForbidden();
        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao))->assertForbidden();
        $this->get(route('licitacoes.restore', $licitacao->idlicitacao))->assertForbidden();
        $this->get(route('licitacoes.busca'))->assertForbidden();
        $this->get(route('licitacoes.trashed'))->assertForbidden();
    }

    /** @test */
    public function licitacao_can_be_created_by_an_user()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->get(route('licitacoes.index'))->assertOk();
        $this->post(route('licitacoes.store'), $attributes)->assertRedirect(route('licitacoes.index'));
        $this->assertDatabaseHas('licitacoes', $attributes);
    }

    /** @test */
    public function licitacao_can_be_created_by_an_user_without_optional_inputs()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario,
            'uasg' => null,
            'edital' => null
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->get(route('licitacoes.index'))->assertOk();
        $this->post(route('licitacoes.store'), $attributes)->assertRedirect(route('licitacoes.index'));
        $this->assertDatabaseHas('licitacoes', $attributes);
    }

    /** @test */
    public function non_authorized_users_cannot_create_licitacoes()
    {
        $user = $this->signIn();

        $this->get(route('licitacoes.create'))->assertForbidden();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->post(route('licitacoes.store'), $attributes)->assertForbidden();
        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function log_is_generated_when_licitacao_is_created()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->post(route('licitacoes.store'), $attributes);
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('criou', $log);
        $this->assertStringContainsString('licitação', $log);
    }

    /** @test */
    public function licitacao_is_shown_on_admin_panel_after_its_creation()
    {
        $this->signInAsAdmin();
        $licitacao = factory('App\Licitacao')->create();
        
        $this->get(route('licitacoes.index'))
            ->assertSee($licitacao->idlicitacao)
            ->assertSee($licitacao->modalidade)
            ->assertSee($licitacao->nrlicitacao)
            ->assertSee($licitacao->nrprocesso)
            ->assertSee($licitacao->situacao)
            ->assertSee(formataData($licitacao->datarealizacao));
    }

    /** @test */
    public function licitacao_without_modalidade_cannot_be_created()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario,
            'modalidade' => ''
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('modalidade');
        
        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function licitacao_with_modalidade_with_wrong_value_cannot_be_created()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario,
            'modalidade' => 'Qualquer'
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('modalidade');
    }

    /** @test */
    public function licitacao_without_titulo_cannot_be_created()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario,
            'titulo' => ''
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('titulo');
    }

    /** @test */
    public function licitacao_with_titulo_more_than_191_chars_cannot_be_created()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario,
            'titulo' => $faker->sentence(400)
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('titulo');
        
        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function licitacao_without_nrlicitacao_cannot_be_created()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario,
            'nrlicitacao' => ''
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('nrlicitacao');

        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function licitacao_with_nrlicitacao_with_wrong_value_cannot_be_created()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario,
            'nrlicitacao' => '12345/12345'
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('nrlicitacao');

        $attributes['nrlicitacao'] = 'A12/1234';

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('nrlicitacao');

        $attributes['nrlicitacao'] = '2/123';

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('nrlicitacao');
    }

    /** @test */
    public function licitacao_without_nrprocesso_cannot_be_created()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario,
            'nrprocesso' => ''
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('nrprocesso');

        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function licitacao_with_nrprocesso_with_wrong_value_cannot_be_created()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario,
            'nrprocesso' => '12345/12345'
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('nrprocesso');

        $attributes['nrprocesso'] = '12A/1234';

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('nrprocesso');

        $attributes['nrprocesso'] = '1/1';

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('nrprocesso');
    }

    /** @test */
    public function licitacao_without_situacao_cannot_be_created()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario,
            'situacao' => ''
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('situacao');

        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function licitacao_with_situacao_with_wrong_value_cannot_be_created()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario,
            'situacao' => 'Qualquer'
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('situacao');

        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function licitacao_without_objeto_cannot_be_created()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario,
            'objeto' => ''
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('objeto');

        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function licitacao_without_datarealizacao_cannot_be_created()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario,
            'datarealizacao' => ''
        ]);

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('datarealizacao');

        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function licitacao_with_datarealizacao_with_invalid_format_cannot_be_created()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario,
            'datarealizacao' => 'texto'
        ]);

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('datarealizacao');
    }

    /** @test */
    public function licitacao_with_uasg_more_than_191_chars_cannot_be_created()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario,
            'uasg' => $faker->sentence(400)
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('uasg');
    }

    /** @test */
    public function licitacao_with_edital_more_than_191_chars_cannot_be_created()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->raw([
            'idusuario' => $user->idusuario,
            'edital' => $faker->sentence(400)
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $this->post(route('licitacoes.store'), $attributes)
        ->assertSessionHasErrors('edital');
    }

    /** @test */
    public function licitacao_can_be_updated_by_an_user()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->create();

        $attributes->datarealizacao = '2022-05-03 10:30';
        $attributes->titulo = 'Qualquer título para edição';
        $attributes->uasg = '123456';
        $attributes->modalidade = Licitacao::modalidadesLicitacao()[7];
        $attributes->situacao = Licitacao::situacoesLicitacao()[5];
        $attributes->nrlicitacao = '1/1234';
        $attributes->nrprocesso = '1/4567';

        $this->get(route('licitacoes.index'))->assertOk();
        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())->assertRedirect(route('licitacoes.index'));

        $attributes->idusuario = $user->idusuario;
        $dados = $attributes->toArray();
        unset($dados['updated_at']);

        $this->assertDatabaseHas('licitacoes', $dados);
    }

    /** @test */
    public function licitacao_can_be_updated_by_an_user_without_optional_inputs()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Licitacao')->create();

        $attributes->datarealizacao = '2022-05-03 10:30';
        $attributes->titulo = 'Qualquer título para edição';
        $attributes->uasg = null;
        $attributes->edital = null;
        $attributes->modalidade = Licitacao::modalidadesLicitacao()[7];
        $attributes->situacao = Licitacao::situacoesLicitacao()[5];
        $attributes->nrlicitacao = '1/1234';
        $attributes->nrprocesso = '1/4567';

        $this->get(route('licitacoes.index'))->assertOk();
        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())->assertRedirect(route('licitacoes.index'));

        $attributes->idusuario = $user->idusuario;
        $dados = $attributes->toArray();
        unset($dados['updated_at']);

        $this->assertDatabaseHas('licitacoes', $dados);
    }

    /** @test */
    public function non_authorized_users_cannot_update_licitacoes()
    {
        $user = $this->signIn();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');
        $attributes->titulo = 'Qualquer título para edição';

        $this->get(route('licitacoes.edit', $attributes->idlicitacao))->assertForbidden();

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())->assertForbidden();
        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes->titulo]);
    }

    /** @test */
    public function log_is_generated_when_licitacao_is_updated()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');
        $attributes->titulo = 'Qualquer título para edição';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray());
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('editou', $log);
        $this->assertStringContainsString('licitação', $log);
    }

    /** @test */
    public function licitacao_is_shown_on_admin_panel_after_its_updated()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');
        $attributes->modalidade = Licitacao::modalidadesLicitacao()[7];

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray());
        
        $this->get(route('licitacoes.index'))
            ->assertSee($attributes->idlicitacao)
            ->assertSee($attributes->modalidade)
            ->assertSee($attributes->nrlicitacao)
            ->assertSee($attributes->nrprocesso)
            ->assertSee($attributes->situacao)
            ->assertSee(formataData($attributes->datarealizacao));
    }

    /** @test */
    public function licitacao_without_modalidade_cannot_be_updated()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');
        $attributes->modalidade = '';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('modalidade');
        
        $this->assertDatabaseMissing('licitacoes', ['modalidade' => '']);
    }

    /** @test */
    public function licitacao_with_modalidade_with_wrong_value_cannot_be_updated()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');
        $attributes->modalidade = 'Qualquer';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('modalidade');
    }

    /** @test */
    public function licitacao_without_titulo_cannot_be_updated()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');
        $attributes->titulo = '';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('titulo');
    }

    /** @test */
    public function licitacao_with_titulo_more_than_191_chars_cannot_be_updated()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');
        $attributes->titulo = $faker->sentence(400);

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('titulo');
        
        $this->assertDatabaseMissing('licitacoes', ['titulo' => $attributes['titulo']]);
    }

    /** @test */
    public function licitacao_without_nrlicitacao_cannot_be_updated()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');
        $attributes->nrlicitacao = '';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('nrlicitacao');

        $this->assertDatabaseMissing('licitacoes', ['nrlicitacao' => $attributes['nrlicitacao']]);
    }

    /** @test */
    public function licitacao_with_nrlicitacao_with_wrong_value_cannot_be_updated()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $attributes['nrlicitacao'] = 'A12/12345';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('nrlicitacao');

        $attributes['nrlicitacao'] = 'A12/1234';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('nrlicitacao');

        $attributes['nrlicitacao'] = '2/123';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('nrlicitacao');
    }

    /** @test */
    public function licitacao_without_nrprocesso_cannot_be_updated()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');
        $attributes['nrprocesso'] = '';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('nrprocesso');

        $this->assertDatabaseMissing('licitacoes', ['nrprocesso' => $attributes['nrprocesso']]);
    }

    /** @test */
    public function licitacao_with_nrprocesso_with_wrong_value_cannot_be_updated()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');

        $attributes['nrprocesso'] = 'A12/12345';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('nrprocesso');

        $attributes['nrprocesso'] = 'A12/1234';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('nrprocesso');

        $attributes['nrprocesso'] = '2/1';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('nrprocesso');
    }

    /** @test */
    public function licitacao_without_situacao_cannot_be_updated()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');
        $attributes['situacao'] = '';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('situacao');

        $this->assertDatabaseMissing('licitacoes', ['situacao' => $attributes['situacao']]);
    }

    /** @test */
    public function licitacao_with_situacao_with_wrong_value_cannot_be_updated()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');
        $attributes['situacao'] = 'Qualquer';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('situacao');

        $this->assertDatabaseMissing('licitacoes', ['situacao' => $attributes['situacao']]);
    }

    /** @test */
    public function licitacao_without_objeto_cannot_be_updated()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');
        $attributes['objeto'] = '';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('objeto');

        $this->assertDatabaseMissing('licitacoes', ['objeto' => $attributes['objeto']]);
    }

    /** @test */
    public function licitacao_without_datarealizacao_cannot_be_updated()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = '';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('datarealizacao');

        $this->assertDatabaseMissing('licitacoes', ['datarealizacao' => $attributes['datarealizacao']]);
    }

    /** @test */
    public function licitacao_with_datarealizacao_with_invalid_format_cannot_be_updated()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = 'texto';

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('datarealizacao');

        $this->assertDatabaseMissing('licitacoes', ['datarealizacao' => $attributes['datarealizacao']]);
    }

    /** @test */
    public function licitacao_with_uasg_more_than_191_chars_cannot_be_updated()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');
        $attributes['uasg'] = $faker->sentence(400);

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('uasg');

        $this->assertDatabaseMissing('licitacoes', ['uasg' => $attributes['uasg']]);
    }

    /** @test */
    public function licitacao_with_edital_more_than_191_chars_cannot_be_updated()
    {
        $faker = \Faker\Factory::create();
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Licitacao')->create([
            'idusuario' => $user->idusuario
        ]);
        $attributes['datarealizacao'] = Carbon::create($attributes['datarealizacao'])->format('Y-m-d H:i');
        $attributes['edital'] = $faker->sentence(400);

        $this->patch(route('licitacoes.update', $attributes->idlicitacao), $attributes->toArray())
        ->assertSessionHasErrors('edital');

        $this->assertDatabaseMissing('licitacoes', ['edital' => $attributes['edital']]);
    }

    /** @test */
    public function view_situacoes()
    {
        $user = $this->signInAsAdmin();

        $this->get(route('licitacoes.create'))
        ->assertSeeInOrder(Licitacao::situacoesLicitacao());

        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.edit', $licitacao->idlicitacao))
        ->assertSeeInOrder(Licitacao::situacoesLicitacao());
    }

    /** @test */
    public function view_modalidades()
    {
        $user = $this->signInAsAdmin();
        
        $this->get(route('licitacoes.create'))
            ->assertSeeInOrder(Licitacao::modalidadesLicitacao());
        
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.edit', $licitacao->idlicitacao))
        ->assertSeeInOrder(Licitacao::modalidadesLicitacao());
    }

    /** @test */
    public function the_name_of_the_user_who_created_licitacao_is_shown_on_admin_panel()
    {
        $user = $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.edit', $licitacao->idlicitacao))
            ->assertOk()
            ->assertSee($user->nome);
    }

    /** @test */
    public function the_name_of_the_user_who_updated_licitacao_is_shown_on_admin_panel()
    {
        $user = $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();
        $licitacao->titulo = 'Qualquer';
        $this->patch(route('licitacoes.update', $licitacao->idlicitacao), $licitacao->toArray());

        $this->get(route('licitacoes.edit', $licitacao->idlicitacao))
            ->assertOk()
            ->assertSee($user->nome);
    }

    /** @test */
    public function non_authorized_users_cannot_see_licitacoes_on_admin_panel()
    {
        $this->signIn();

        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.index'))->assertForbidden()->assertDontSee($licitacao->titulo);
    }

    /** @test */
    public function licitacao_can_be_deleted()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao));
        $this->assertSoftDeleted('licitacoes', ['idlicitacao' => $licitacao->idlicitacao]);
    }

    /** @test */
    public function log_is_generated_when_licitacao_is_deleted()
    {
        $user = $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao));
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('apagou', $log);
        $this->assertStringContainsString('licitação', $log);
    }

    /** @test */
    public function non_authorized_users_cannot_delete_licitacao()
    {
        $this->signIn();

        $licitacao = factory('App\Licitacao')->create();

        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao))->assertForbidden();
        $this->assertNull(Licitacao::withTrashed()->find($licitacao->idlicitacao)->deleted_at);
    }

    /** @test */
    public function deleted_licitacoes_are_shown_in_trash()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao));

        $this->get(route('licitacoes.trashed'))->assertOk()->assertSee($licitacao->idlicitacao);
    }

    /** @test */
    public function deleted_licitacoes_are_not_shown_on_index()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao));

        $this->get(route('licitacoes.index'))->assertOk()->assertDontSee($licitacao->titulo);
    }

    /** @test */
    public function deleted_licitacoes_can_be_restored()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao));
        $this->get(route('licitacoes.restore', $licitacao->idlicitacao));

        $this->assertNull(Licitacao::find($licitacao->idlicitacao)->deleted_at);
        $this->get(route('licitacoes.index'))->assertSee($licitacao->idlicitacao);
    }

    /** @test */
    public function log_is_generated_when_licitacao_is_restored()
    {
        $user = $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->delete(route('licitacoes.destroy', $licitacao->idlicitacao));
        $this->get(route('licitacoes.restore', $licitacao->idlicitacao));
        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('restaurou', $log);
        $this->assertStringContainsString('licitação', $log);
    }

    /** @test */
    public function licitacao_can_be_searched()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.busca', ['q' => $licitacao->titulo]))
            ->assertSeeText($licitacao->titulo);
    }

    /** @test */
    public function link_to_edit_licitacao_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.index'))->assertSee(route('licitacoes.edit', $licitacao->idlicitacao));
    }

    /** @test */
    public function link_to_destroy_licitacao_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.index'))->assertSee(route('licitacoes.destroy', $licitacao->idlicitacao));
    }

    /** @test */
    public function link_to_create_licitacao_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $this->get(route('licitacoes.index'))->assertSee(route('licitacoes.create'));
    }

    /** 
     * =======================================================================================================
     * TESTES NO PORTAL
     * =======================================================================================================
     */

    /** @test */
    public function licitacoes_is_shown_on_website_list_after_its_creation()
    {
        $licitacoes = factory('App\Licitacao', 5)->create([
            'created_at' => now()->subDay()
        ]);
        
        foreach($licitacoes as $licitacao)
            $this->get(route('licitacoes.siteGrid'))
                ->assertSee($licitacao->titulo)
                ->assertSee($licitacao->nrlicitacao)
                ->assertSee($licitacao->nrprocesso)
                ->assertSee($licitacao->modalidade)
                ->assertSee($licitacao->situacao)
                ->assertSee('<strong>Divulgação:</strong> ' . onlyDate($licitacao->created_at))
                ->assertSee(onlyDate($licitacao->datarealizacao));
    }

    /** @test */
    public function licitacoes_is_shown_on_website_list_order_by_nrprocesso_desc_with_same_year()
    {
        // 1° nrprocesso decrescente
        // 2° nrlicitacao decrescente

        $number = 123;
        $ordem = ['127/2020', '126/2020', '125/2020', '124/2020', '123/2020'];

        for($i = 0; $i < 5; $i++)
            factory('App\Licitacao')->create([
                'nrprocesso' => $number++ . '/2020'
            ]);

        $this->get(route('licitacoes.siteGrid'))
        ->assertSeeTextInOrder($ordem);
    }

    /** @test */
    public function licitacoes_is_shown_on_website_list_order_by_nrprocesso_desc_with_different_year_and_number()
    {
        // 1° nrprocesso decrescente
        // 2° nrlicitacao decrescente

        $number = 123;
        $ano = 2020;
        $ordem = ['127/2024', '126/2023', '125/2022', '124/2021', '123/2020'];

        for($i = 0; $i < 5; $i++)
            factory('App\Licitacao')->create([
                'nrprocesso' => $number++ . '/' . $ano++
            ]);

        $this->get(route('licitacoes.siteGrid'))
        ->assertSeeTextInOrder($ordem);
    }

    /** @test */
    public function licitacoes_is_shown_on_website_list_order_by_nrprocesso_desc_with_same_number()
    {
        // 1° nrprocesso decrescente
        // 2° nrlicitacao decrescente

        $number = 123;
        $ano = 2020;
        $ordem = ['123/2024', '123/2023', '123/2022', '123/2021', '123/2020'];

        for($i = 0; $i < 5; $i++)
            factory('App\Licitacao')->create([
                'nrprocesso' => $number . '/' . $ano++
            ]);

        $this->get(route('licitacoes.siteGrid'))
        ->assertSeeTextInOrder($ordem);
    }

    /** @test */
    public function licitacoes_is_shown_on_website_list_order_by_nrlicitacao_desc()
    {
        // 1° nrprocesso decrescente
        // 2° nrlicitacao decrescente

        $ordem = ['250/2020', '250/2019', '249/2019'];

        factory('App\Licitacao')->create([
            'nrprocesso' => '123/2020',
            'nrlicitacao' => '250/2019'
        ]);

        factory('App\Licitacao')->create([
            'nrprocesso' => '123/2020',
            'nrlicitacao' => '249/2019'
        ]);

        factory('App\Licitacao')->create([
            'nrprocesso' => '122/2021',
            'nrlicitacao' => '250/2020'
        ]);

        $this->get(route('licitacoes.siteGrid'))
        ->assertSeeTextInOrder($ordem);
    }

    /** @test */
    public function licitacao_is_shown_on_website_after_its_creation()
    {
        $licitacao = factory('App\Licitacao')->create();
        
        $this->get(route('licitacoes.show', $licitacao->idlicitacao))
            ->assertSee($licitacao->titulo)
            ->assertSee($licitacao->nrlicitacao)
            ->assertSee($licitacao->nrprocesso)
            ->assertSee($licitacao->modalidade)
            ->assertSee($licitacao->situacao)
            ->assertSee(formataData($licitacao->datarealizacao));
    }

    /** @test */
    public function link_to_licitacao_is_shown_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();
        
        $this->get(route('licitacoes.siteGrid', $licitacao->idlicitacao))
            ->assertSee(route('licitacoes.show', $licitacao->idlicitacao));
    }

    /** @test */
    public function link_to_download_edital_is_shown_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();
        
        $this->get(route('licitacoes.show', $licitacao->idlicitacao))
        ->assertSeeText('Edital disponível para download')
        ->assertSeeText('Clique aqui para baixar o edital')
        ->assertSee('<a href="' . $licitacao->edital . '" download >');
    }

    /** @test */
    public function without_link_to_download_edital_is_shown_on_website_when_edital_null()
    {
        $licitacao = factory('App\Licitacao')->create([
            'edital' => null
        ]);
        
        $this->get(route('licitacoes.show', $licitacao->idlicitacao))
        ->assertDontSeeText('Edital disponível para download')
        ->assertDontSeeText('Clique aqui para baixar o edital')
        ->assertDontSee('<a href="' . $licitacao->edital . '" download >');
    }

    /** @test */
    public function licitacao_can_be_searched_by_modalidade_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.siteBusca', [
            'modalidade' => $licitacao->modalidade
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
    }

    /** @test */
    public function licitacao_can_be_searched_by_palavrachave_on_website()
    {
        $licitacao = factory('App\Licitacao')->create([
            'objeto' => htmlentities('teste com acentuação, comparação, à disposição, referência', ENT_NOQUOTES, 'UTF-8')
        ]);
        $array_titulo = explode($licitacao->titulo, ' ');
        $first_word = $array_titulo[0];

        $this->get(route('licitacoes.siteBusca', [
            'palavrachave' => $first_word
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
        
        $first_word = 'comparação';
    
        $this->get(route('licitacoes.siteBusca', [
            'palavrachave' => $first_word
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
    }

    /** @test */
    public function licitacao_can_be_searched_by_nrprocesso_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.siteBusca', [
            'nrprocesso' => $licitacao->nrprocesso
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
    }

    /** @test */
    public function licitacao_can_be_searched_by_nrlicitacao_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.siteBusca', [
            'nrlicitacao' => $licitacao->nrlicitacao
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
    }

    /** @test */
    public function licitacao_can_be_searched_by_situacao_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.siteBusca', [
            'situacao' => $licitacao->situacao
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
    }

    /** @test */
    public function licitacao_can_be_searched_by_datarealizacao_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.siteBusca', [
            'datarealizacao' => Carbon::create($licitacao->datarealizacao)->format('Y-m-d')
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
    }

    /** @test */
    public function licitacao_can_be_searched_by_more_than_one_param_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.siteBusca', [
            'nrprocesso' => $licitacao->nrprocesso,
            'situacao' => $licitacao->situacao
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
    }

    /** @test */
    public function licitacao_can_be_searched_by_all_param_filled_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();
        $array_titulo = explode($licitacao->titulo, ' ');
        $first_word = $array_titulo[0];

        $this->get(route('licitacoes.siteBusca', [
            'nrprocesso' => $licitacao->nrprocesso,
            'situacao' => $licitacao->situacao,
            'datarealizacao' => Carbon::create($licitacao->datarealizacao)->format('Y-m-d'),
            'nrlicitacao' => $licitacao->nrlicitacao,
            'modalidade' => $licitacao->modalidade,
            'palavrachave' => $first_word
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
    }

    /** @test */
    public function licitacao_can_be_searched_by_all_param_nullable_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.siteBusca', [
            'nrprocesso' => '',
            'situacao' => '',
            'datarealizacao' => '',
            'nrlicitacao' => '',
            'modalidade' => '',
            'palavrachave' => ''
        ]))->assertOk()
            ->assertSee($licitacao->titulo);
    }

    /** @test */
    public function msg_when_not_find_licitacao_on_website()
    {
        $this->get(route('licitacoes.siteBusca', [
            'nrprocesso' => '',
            'situacao' => '',
            'datarealizacao' => '',
            'nrlicitacao' => '',
            'modalidade' => '',
            'palavrachave' => ''
        ]))->assertOk()
            ->assertSeeText('Nenhuma licitação encontrada!');
    }

    /** @test */
    public function licitacao_cannot_be_searched_with_palavrachave_more_than_191_chars_on_website()
    {
        $faker = \Faker\Factory::create();
        $licitacao = factory('App\Licitacao')->create();

        $this->get(route('licitacoes.siteBusca', [
            'nrprocesso' => $licitacao->nrprocesso,
            'situacao' => $licitacao->situacao,
            'datarealizacao' => Carbon::create($licitacao->datarealizacao)->format('Y-m-d'),
            'nrlicitacao' => $licitacao->nrlicitacao,
            'modalidade' => $licitacao->modalidade,
            'palavrachave' => $faker->sentence(400)
        ]))
        ->assertSessionHasErrors('palavrachave');
    }

    /** @test */
    public function licitacao_cannot_be_searched_with_modalidade_with_wrong_value_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();
        $array_titulo = explode($licitacao->titulo, ' ');
        $first_word = $array_titulo[0];

        $this->get(route('licitacoes.siteBusca', [
            'nrprocesso' => $licitacao->nrprocesso,
            'situacao' => $licitacao->situacao,
            'datarealizacao' => Carbon::create($licitacao->datarealizacao)->format('Y-m-d'),
            'nrlicitacao' => $licitacao->nrlicitacao,
            'modalidade' => 'Qualquer',
            'palavrachave' => $first_word
        ]))
        ->assertSessionHasErrors('modalidade');
    }

    /** @test */
    public function licitacao_cannot_be_searched_with_situacao_with_wrong_value_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();
        $array_titulo = explode($licitacao->titulo, ' ');
        $first_word = $array_titulo[0];

        $this->get(route('licitacoes.siteBusca', [
            'nrprocesso' => $licitacao->nrprocesso,
            'situacao' => 'Qualquer',
            'datarealizacao' => Carbon::create($licitacao->datarealizacao)->format('Y-m-d'),
            'nrlicitacao' => $licitacao->nrlicitacao,
            'modalidade' => $licitacao->modalidade,
            'palavrachave' => $first_word
        ]))
        ->assertSessionHasErrors('situacao');
    }

    /** @test */
    public function licitacao_cannot_be_searched_with_datarealizacao_with_invalid_format_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();
        $array_titulo = explode($licitacao->titulo, ' ');
        $first_word = $array_titulo[0];

        $this->get(route('licitacoes.siteBusca', [
            'nrprocesso' => $licitacao->nrprocesso,
            'situacao' => $licitacao->situacao,
            'datarealizacao' => 'texto',
            'nrlicitacao' => $licitacao->nrlicitacao,
            'modalidade' => $licitacao->modalidade,
            'palavrachave' => $first_word
        ]))
        ->assertSessionHasErrors('datarealizacao');
    }

    /** @test */
    public function licitacao_cannot_be_searched_with_nrprocesso_with_invalid_format_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();
        $array_titulo = explode($licitacao->titulo, ' ');
        $first_word = $array_titulo[0];

        $this->get(route('licitacoes.siteBusca', [
            'nrprocesso' => '12A/12345',
            'situacao' => $licitacao->situacao,
            'datarealizacao' => Carbon::create($licitacao->datarealizacao)->format('Y-m-d'),
            'nrlicitacao' => $licitacao->nrlicitacao,
            'modalidade' => $licitacao->modalidade,
            'palavrachave' => $first_word
        ]))
        ->assertSessionHasErrors('nrprocesso');

        $this->get(route('licitacoes.siteBusca', [
            'nrprocesso' => '1245/1234',
            'situacao' => $licitacao->situacao,
            'datarealizacao' => Carbon::create($licitacao->datarealizacao)->format('Y-m-d'),
            'nrlicitacao' => $licitacao->nrlicitacao,
            'modalidade' => $licitacao->modalidade,
            'palavrachave' => $first_word
        ]))
        ->assertSessionHasErrors('nrprocesso');
    }

    /** @test */
    public function licitacao_cannot_be_searched_with_nrlicitacao_with_invalid_format_on_website()
    {
        $licitacao = factory('App\Licitacao')->create();
        $array_titulo = explode($licitacao->titulo, ' ');
        $first_word = $array_titulo[0];

        $this->get(route('licitacoes.siteBusca', [
            'nrprocesso' => $licitacao->nrprocesso,
            'situacao' => $licitacao->situacao,
            'datarealizacao' => Carbon::create($licitacao->datarealizacao)->format('Y-m-d'),
            'nrlicitacao' => '12A/12345',
            'modalidade' => $licitacao->modalidade,
            'palavrachave' => $first_word
        ]))
        ->assertSessionHasErrors('nrlicitacao');

        $this->get(route('licitacoes.siteBusca', [
            'nrprocesso' => $licitacao->nrprocesso,
            'situacao' => $licitacao->situacao,
            'datarealizacao' => Carbon::create($licitacao->datarealizacao)->format('Y-m-d'),
            'nrlicitacao' => '1245/1234',
            'modalidade' => $licitacao->modalidade,
            'palavrachave' => $first_word
        ]))
        ->assertSessionHasErrors('nrlicitacao');
    }
}
