<?php

namespace Tests\Feature;

use App\Curso;
use App\CursoInscrito;
use App\Permissao;
use DateInterval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Mail\CursoInscritoMailGuest;
use Illuminate\Support\Facades\Mail;

class CursoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_links()
    {
        $this->assertGuest();
        
        $curso = factory('App\Curso')->create();
        $inscrito = factory('App\CursoInscrito')->create([
            'idcurso' => $curso->idcurso,
        ]);
        $inscrito_raw = factory('App\CursoInscrito')->raw([
            'idcurso' => $curso->idcurso,
            'cpf' => '862.943.730-85',
            'termo' => 'on'
        ]);

        $this->get(route('cursos.index'))->assertRedirect(route('login'));
        $this->get(route('cursos.busca'))->assertRedirect(route('login'));
        $this->get(route('cursos.edit', $curso->idcurso))->assertRedirect(route('login'));
        $this->get(route('cursos.create'))->assertRedirect(route('login'));
        $this->get(route('cursos.lixeira'))->assertRedirect(route('login'));
        $this->get(route('cursos.restore', $curso->idcurso))->assertRedirect(route('login'));
        $this->post(route('cursos.store'))->assertRedirect(route('login'));
        $this->patch(route('cursos.update', $curso->idcurso))->assertRedirect(route('login'));
        $this->delete(route('cursos.destroy', $curso->idcurso))->assertRedirect(route('login'));

        $this->get(route('inscritos.index', $curso->idcurso))->assertRedirect(route('login'));
        $this->get(route('inscritos.busca', $curso->idcurso))->assertRedirect(route('login'));
        $this->get(route('inscritos.edit', $inscrito->idcursoinscrito))->assertRedirect(route('login'));
        $this->put(route('inscritos.update', $inscrito->idcursoinscrito))->assertRedirect(route('login'));
        $this->put(route('inscritos.update.presenca', $inscrito->idcursoinscrito))->assertRedirect(route('login'));
        $this->get(route('inscritos.create', $curso->idcurso))->assertRedirect(route('login'));
        $this->post(route('inscritos.store', $curso->idcurso))->assertRedirect(route('login'));
        $this->get(route('inscritos.download', $curso->idcurso))->assertRedirect(route('login'));
        $this->delete(route('inscritos.destroy', $inscrito->idcursoinscrito))->assertRedirect(route('login'));

        // Quando acesso privado
        $this->get(route('cursos.inscricao.website', $curso->idcurso))->assertRedirect(route('representante.login'));
        $this->post(route('cursos.inscricao', $curso->idcurso), $inscrito_raw)->assertRedirect(route('representante.login'));
    }

    /** @test */
    public function non_authorized_users_cannot_access_links()
    {
        $this->signIn();
        $this->assertAuthenticated('web');
        
        $curso = factory('App\Curso')->create();
        $inscrito = factory('App\CursoInscrito')->create([
            'idcurso' => $curso->idcurso,
        ]);
        $inscrito_raw = factory('App\CursoInscrito')->raw([
            'idcurso' => $curso->idcurso,
            'cpf' => '862.943.730-85',
            'termo' => 'on'
        ]);
        $update = $inscrito->makeHidden(['idcurso', 'idcursoinscrito'])->attributesToArray();

        $this->get(route('cursos.index'))->assertForbidden();
        $this->get(route('cursos.busca'))->assertForbidden();
        $this->get(route('cursos.edit', $curso->idcurso))->assertForbidden();
        $this->get(route('cursos.create'))->assertForbidden();
        $this->get(route('cursos.lixeira'))->assertForbidden();
        $this->get(route('cursos.restore', $curso->idcurso))->assertForbidden();
        $this->post(route('cursos.store'), $curso->toArray())->assertForbidden();
        $this->patch(route('cursos.update', $curso->idcurso), $curso->toArray())->assertForbidden();
        $this->delete(route('cursos.destroy', $curso->idcurso))->assertForbidden();

        $this->get(route('inscritos.index', $curso->idcurso))->assertForbidden();
        $this->get(route('inscritos.busca', $curso->idcurso))->assertForbidden();
        $this->get(route('inscritos.edit', $inscrito->idcursoinscrito))->assertForbidden();
        $this->put(route('inscritos.update', $inscrito->idcursoinscrito), $update)->assertForbidden();
        $this->put(route('inscritos.update.presenca', $inscrito->idcursoinscrito), ['presenca' => 'Sim'])->assertForbidden();
        $this->get(route('inscritos.create', $curso->idcurso))->assertForbidden();
        $this->post(route('inscritos.store', $curso->idcurso), $inscrito_raw)->assertForbidden();
        $this->get(route('inscritos.download', $curso->idcurso))->assertForbidden();
        $this->delete(route('inscritos.destroy', $inscrito->idcursoinscrito))->assertForbidden();
    }

    /** @test */
    public function curso_can_be_created()
    {
        $curso = factory('App\Curso')->create();

        $this->assertDatabaseHas('cursos', ['tema' => $curso->tema]);
    }

    /** @test */
    public function curso_can_be_created_by_an_user()
    {
        $user = $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw();

        $this->post(route('cursos.store'), $attributes);
        $this->assertDatabaseHas('cursos', [
            'tema' => $attributes['tema'],
            'idusuario' => $user->idusuario
        ]);
    }

    /** @test */
    public function log_is_generated_when_curso_is_created()
    {
        $user = $this->signInAsAdmin();
        $attributes = factory('App\Curso')->raw();

        $this->post(route('cursos.store'), $attributes);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') criou *curso* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function curso_is_shown_on_admin_panel_after_its_creation()
    {
        $this->signInAsAdmin();
        $curso = factory('App\Curso')->create();
        
        $this->get(route('cursos.index'))
            ->assertSee($curso->idcurso)
            ->assertSee($curso->tema);
    }

    /** @test */
    public function curso_without_resumo_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'resumo' => ''
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('resumo');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_without_descricao_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'descricao' => ''
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('descricao');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_without_acesso_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'acesso' => ''
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('acesso');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_without_tipo_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'tipo' => ''
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('tipo');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_with_invalid_value_in_tipo_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'tipo' => 'Lives'
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('tipo');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_with_invalid_value_in_acesso_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'acesso' => 'Liberado'
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('acesso');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_with_invalid_value_in_idregional_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'idregional' => '5'
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('idregional');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_with_invalid_value_in_nrvagas_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'nrvagas' => 'A'
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('nrvagas');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_with_invalid_value_in_publicado_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'publicado' => 'Nao'
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('publicado');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_without_tema_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'tema' => ''
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('tema');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_without_datarealizacao_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'datarealizacao' => ''
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('datarealizacao');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_with_invalid_format_in_datarealizacao_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'datarealizacao' => '20/12/2023'
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('datarealizacao');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_with_datarealizacao_before_today_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'inicio_inscricao' => null,
            'termino_inscricao' => null,
            'datarealizacao' => now()->format('Y-m-d H:i')
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('datarealizacao');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_without_datatermino_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'datatermino' => ''
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('datatermino');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_with_invalid_format_in_datatermino_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'datatermino' => '20/12/2023'
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('datatermino');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_with_datatermino_before_today_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'inicio_inscricao' => null,
            'termino_inscricao' => null,
            'datatermino' => now()->format('Y-m-d H:i')
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('datatermino');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_with_datatermino_before_1h_datarealizacao_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'inicio_inscricao' => null,
            'termino_inscricao' => null,
            'datarealizacao' => now()->format('Y-m-d H:i'),
            'datatermino' => now()->addMinutes(59)->format('Y-m-d H:i')
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('datatermino');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_without_termino_inscricao_cannot_be_created_if_inicio_inscricao_filled()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'termino_inscricao' => ''
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('termino_inscricao');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_with_invalid_format_in_inicio_inscricao_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'inicio_inscricao' => '20/12/2023'
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('inicio_inscricao');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_with_inicio_inscricao_after_or_equal_datarealizacao_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'inicio_inscricao' => now()->addDays(2)->format('Y-m-d H:i')
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('inicio_inscricao');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_with_invalid_format_in_termino_inscricao_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'termino_inscricao' => '20/12/2023'
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('termino_inscricao');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_with_termino_inscricao_after_or_equal_datarealizacao_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'termino_inscricao' => now()->addDays(2)->format('Y-m-d H:i')
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('termino_inscricao');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_with_termino_inscricao_before_or_equal_1day_inicio_inscricao_cannot_be_created()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'termino_inscricao' => now()->format('Y-m-d H:i')
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('termino_inscricao');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_endereco_is_required_if_tipo_not_live()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'tipo' => 'Curso',
            'endereco' => ''
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('endereco');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_endereco_is_not_required_if_tipo_is_live()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'tipo' => 'Live',
            'endereco' => ''
        ]);

        $this->post(route('cursos.store'), $attributes);
        $this->assertDatabaseHas('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function the_name_of_the_user_who_created_curso_is_shown_on_admin_panel()
    {
        $user = $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.edit', $curso->idcurso))
            ->assertOk()
            ->assertSee($user->nome);
    }

    /** @test */
    public function the_cursos_regional_is_shown_on_admin_panel()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.index'))
            ->assertSee($curso->regional->regional);
    }

    /** @test */
    public function non_authorized_users_cannot_create_cursos()
    {
        $this->signIn();

        $this->get(route('cursos.create'))->assertForbidden();

        $attributes = factory('App\Curso')->raw();

        $this->post(route('cursos.store'), $attributes)->assertForbidden();
        $this->assertDatabaseMissing('cursos', ['tema' => $attributes['tema']]);
    }

    /** @test */
    public function non_authorized_users_cannot_see_cursos_on_admin_panel()
    {
        $this->signIn();

        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.index'))
            ->assertForbidden()
            ->assertDontSee($curso->tema);
    }

    /** @test */
    function multiple_cursos_can_be_created()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $cursoDois = factory('App\Curso')->create();

        $this->assertDatabaseHas('cursos', ['tema' => $curso->tema]);
        $this->assertDatabaseHas('cursos', ['tema' => $cursoDois->tema]);
        $this->assertEquals(2, Curso::count());
    }

    /** @test */
    public function curso_can_be_updated()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\Curso')->raw();

        $this->patch(route('cursos.update', $curso->idcurso), $attributes);

        $cur = Curso::find($curso->idcurso);
        $this->assertEquals($cur->tema, $attributes['tema']);
        $this->assertEquals($cur->descricao, $attributes['descricao']);
        $this->assertEquals($cur->resumo, $attributes['resumo']);
        $this->assertDatabaseHas('cursos', [
            'tema' => $attributes['tema'],
            'descricao' => $attributes['descricao'],
            'resumo' => $attributes['resumo']
        ]);
    }

    /** @test */
    public function log_is_generated_when_curso_is_updated()
    {
        $user = $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\Curso')->raw();

        $this->patch(route('cursos.update', $curso->idcurso), $attributes);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou *curso* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function non_authorized_users_cannot_update_cursos()
    {
        $this->signIn();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\Curso')->raw();

        $this->get(route('cursos.edit', $curso->idcurso))->assertForbidden();
        $this->patch(route('cursos.update', $curso->idcurso), $attributes)->assertForbidden();

        $this->assertDatabaseMissing('cursos', ['tema' => $attributes['tema']]);
    }

    /** @test */
    public function curso_can_be_deleted()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->delete(route('cursos.destroy', $curso->idcurso));
        $this->assertSoftDeleted('cursos', ['idcurso' => $curso->idcurso]);
    }

    /** @test */
    public function log_is_generated_when_curso_is_deleted()
    {
        $user = $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $this->delete(route('cursos.destroy', $curso->idcurso));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') cancelou *curso* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function non_authorized_users_cannot_delete_curso()
    {
        $this->signIn();

        $curso = factory('App\Curso')->create();

        $this->delete(route('cursos.destroy', $curso->idcurso))->assertForbidden();
        $this->assertNull(Curso::withTrashed()->find($curso->idcurso)->deleted_at);
    }

    /** @test */
    public function canceled_cursos_are_shown_in_trash()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->delete(route('cursos.destroy', $curso->idcurso));

        $this->get(route('cursos.lixeira'))->assertOk()->assertSee($curso->idcurso);
    }

    /** @test */
    public function deleted_cursos_are_not_shown_on_index()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->delete(route('cursos.destroy', $curso->idcurso));

        $this->get(route('cursos.index'))->assertOk()->assertDontSee($curso->tema);
    }

    /** @test */
    public function deleted_cursos_can_be_restored()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->delete(route('cursos.destroy', $curso->idcurso));
        $this->get(route('cursos.restore', $curso->idcurso));

        $this->assertNull(Curso::find($curso->idcurso)->deleted_at);
        $this->get(route('cursos.index'))->assertSee($curso->tema);
    }

    /** @test */
    public function log_is_generated_when_curso_is_restored()
    {
        $user = $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $this->delete(route('cursos.destroy', $curso->idcurso));
        $this->get(route('cursos.restore', $curso->idcurso));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') reabriu *curso* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    function curso_can_be_searched_on_admin_panel()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.busca', ['q' => $curso->tema]))
            ->assertSeeText($curso->tema);
    }

    /** @test */
    function link_to_create_curso_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $this->get(route('cursos.index'))->assertSee(route('cursos.create'));
    }

    /** @test */
    function link_to_edit_curso_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.index'))->assertSee(route('cursos.edit', $curso->idcurso));
    }

    /** @test */
    function link_to_destroy_curso_is_shown_on_admin()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.index'))->assertSee(route('cursos.destroy', $curso->idcurso));
    }

    /** @test */
    function link_to_inscritos_curso_is_not_shown_on_admin_if_forbidden()
    {
        $this->signInAsAdmin();
        Permissao::find(15)->update(['perfis' => '']);

        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.index'))->assertDontSee(route('inscritos.index', $curso->idcurso));
    }

    /** 
     * =======================================================================================================
     * TESTES CURSOS SITE
     * =======================================================================================================
     */

    /** @test */
    function curso_published_is_shown_on_website()
    {
        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.show', $curso->idcurso))
            ->assertOk()
            ->assertSee($curso->tema);

        $this->signInAsAdmin();

        $this->get(route('cursos.show', $curso->idcurso))
            ->assertOk()
            ->assertSee($curso->tema);
    }

    /** @test */
    function curso_not_published_is_shown_on_website_if_authenticated_user()
    {
        $curso = factory('App\Curso')->create([
            'publicado' => 'Não'
        ]);

        $this->get(route('cursos.show', $curso->idcurso))
            ->assertNotFound();

        $this->signInAsAdmin();

        $this->get(route('cursos.show', $curso->idcurso))
            ->assertOk()
            ->assertSee($curso->tema);
    }

    /** @test */
    function next_cursos_are_shown_on_next_curso_lista_on_website()
    {
        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.index.website'))
            ->assertOk()
            ->assertSee(route('cursos.show', $curso->idcurso))
            ->assertSee($curso->tema);
    }

    /** @test */
    function next_cursos_are_not_shown_on_previous_curso_lista_on_website()
    {
        $curso = factory('App\Curso')->create();

        $this->get(route('cursos.previous.website'))
            ->assertOk()
            ->assertDontSee($curso->tema);
    }

    /** @test */
    function previous_cursos_are_not_shown_on_next_curso_list_on_website()
    {
        $curso = factory('App\Curso')->create([
            'datarealizacao' => now()->subDay()->format('Y-m-d H:i'),
            'datatermino' => now()->subDay()->addHour()->format('Y-m-d H:i')
        ]);

        $this->get(route('cursos.index.website'))
            ->assertOk()
            ->assertDontSee($curso->tema);
    }

    /** @test */
    function previous_cursos_are_shown_on_previous_curso_list_on_website()
    {
        $curso = factory('App\Curso')->create([
            'datarealizacao' => now()->subDay()->format('Y-m-d H:i'),
            'datatermino' => now()->subDay()->addHour()->format('Y-m-d H:i')
        ]);

        $this->get(route('cursos.previous.website'))
            ->assertOk()
            ->assertSee(route('cursos.show', $curso->idcurso))
            ->assertSee($curso->tema);
    }

    /** @test */
    function previous_cursos_with_noticia_are_shown_on_previous_curso_list_on_website()
    {
        $curso = factory('App\Curso')->create([
            'datarealizacao' => now()->subDay()->format('Y-m-d H:i'),
            'datatermino' => now()->subDay()->addHour()->format('Y-m-d H:i')
        ]);

        $noticias = factory('App\Noticia', 2)->create([
            'idcurso' => $curso->idcurso
        ]);

        $this->get(route('cursos.previous.website'))
            ->assertOk()
            ->assertSee(route('cursos.show', $curso->idcurso))
            ->assertSee($curso->tema)
            ->assertSeeText('Veja como foi')
            ->assertSee(route('noticias.show', $noticias->get(0)->slug))
            ->assertDontSee(route('noticias.show', $noticias->get(1)->slug));
    }

    /** 
     * =======================================================================================================
     * TESTES INSCRITOS ADMIN
     * =======================================================================================================
     */

    /** @test */
    public function inscrito_can_be_created_by_an_user()
    {
        $user = $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'cpf' => '54384875290'
        ]);

        $this->post(route('inscritos.create', $curso->idcurso), $attributes);
        $this->assertDatabaseHas('curso_inscritos', [
            'cpf' => formataCpfCnpj($attributes['cpf']),
        ]);
    }

    /** @test */
    public function log_is_generated_when_inscrito_is_created()
    {
        $user = $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'cpf' => '54384875290'
        ]);

        $this->post(route('inscritos.create', $curso->idcurso), $attributes);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') adicionou *inscrito em curso* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function inscrito_without_nome_cannot_be_created()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'nome' => ''
        ]);

        $this->post(route('inscritos.create', $curso->idcurso), $attributes)
        ->assertSessionHasErrors('nome');
    }

    /** @test */
    public function inscrito_with_nome_less_than_5_chars_cannot_be_created()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'nome' => 'Abcd'
        ]);

        $this->post(route('inscritos.create', $curso->idcurso), $attributes)
        ->assertSessionHasErrors('nome');
    }

    /** @test */
    public function inscrito_without_telefone_cannot_be_created()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'telefone' => ''
        ]);

        $this->post(route('inscritos.create', $curso->idcurso), $attributes)
        ->assertSessionHasErrors('telefone');
    }

    /** @test */
    public function inscrito_with_telefone_invalid_format_cannot_be_created()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'telefone' => '11 9998558-96'
        ]);

        $this->post(route('inscritos.create', $curso->idcurso), $attributes)
        ->assertSessionHasErrors('telefone');
    }

    /** @test */
    public function inscrito_without_email_cannot_be_created()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'email' => ''
        ]);

        $this->post(route('inscritos.create', $curso->idcurso), $attributes)
        ->assertSessionHasErrors('email');
    }

    /** @test */
    public function inscrito_with_email_invalid_format_cannot_be_created()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'email' => 'teste.com'
        ]);

        $this->post(route('inscritos.create', $curso->idcurso), $attributes)
        ->assertSessionHasErrors('email');
    }

    /** @test */
    public function inscrito_without_tipo_inscrito_cannot_be_created()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'tipo_inscrito' => ''
        ]);

        $this->post(route('inscritos.create', $curso->idcurso), $attributes)
        ->assertSessionHasErrors('tipo_inscrito');
    }

    /** @test */
    public function inscrito_with_tipo_inscrito_invalid_value_cannot_be_created()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'tipo_inscrito' => 'Novo Tipo'
        ]);

        $this->post(route('inscritos.create', $curso->idcurso), $attributes)
        ->assertSessionHasErrors('tipo_inscrito');
    }

    /** @test */
    public function inscrito_is_shown_on_admin_panel_after_its_creation()
    {
        $this->signInAsAdmin();
        $inscrito = factory('App\CursoInscrito')->create();
        
        $this->get(route('inscritos.index', $inscrito->idcurso))
            ->assertSee($inscrito->nome)
            ->assertSee($inscrito->cpf)
            ->assertSee($inscrito->email)
            ->assertSee($inscrito->tipo_inscrito);
    }

    /** @test */
    public function inscrito_private_can_be_created_by_admin()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();

        $inscrito = factory('App\CursoInscrito')->states('tipo_convidado')->raw([
            'idcurso' => $curso->idcurso,
        ]);
        $this->post(route('inscritos.store', $curso->idcurso), $inscrito)
        ->assertRedirect(route('inscritos.index', $curso->idcurso));

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf'], 'tipo_inscrito' => CursoInscrito::INSCRITO_CON]);

        CursoInscrito::find(1)->delete();
        $inscrito = factory('App\CursoInscrito')->states('tipo_autoridade')->raw([
            'idcurso' => $curso->idcurso,
        ]);
        $this->post(route('inscritos.store', $curso->idcurso), $inscrito)
        ->assertRedirect(route('inscritos.index', $curso->idcurso));

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf'], 'tipo_inscrito' => CursoInscrito::INSCRITO_AUT]);
    }

    /** @test */
    public function inscrito_public_can_be_created_by_admin()
    {
        $this->signInAsAdmin();

        $curso = factory('App\Curso')->states('publico')->create();

        $inscrito = factory('App\CursoInscrito')->states('tipo_convidado')->raw([
            'idcurso' => $curso->idcurso,
        ]);
        $this->post(route('inscritos.store', $curso->idcurso), $inscrito)
        ->assertRedirect(route('inscritos.index', $curso->idcurso));

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf'], 'tipo_inscrito' => CursoInscrito::INSCRITO_CON]);

        CursoInscrito::find(1)->delete();
        $inscrito = factory('App\CursoInscrito')->states('tipo_autoridade')->raw([
            'idcurso' => $curso->idcurso,
        ]);
        $this->post(route('inscritos.store', $curso->idcurso), $inscrito)
        ->assertRedirect(route('inscritos.index', $curso->idcurso));

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf'], 'tipo_inscrito' => CursoInscrito::INSCRITO_AUT]);
    }

    /** @test */
    public function inscrito_can_be_updated_by_an_user()
    {
        $user = $this->signInAsAdmin();

        $inscrito = factory('App\CursoInscrito')->create();
        $attributes = $inscrito->attributesToArray();
        $attributes['nome'] = "Novo nome de teste";
        unset($attributes['idcurso']);

        $this->put(route('inscritos.update', $inscrito->idcursoinscrito), $attributes)
        ->assertRedirect(route('inscritos.index', $inscrito->idcurso));

        $this->assertDatabaseHas('curso_inscritos', [
            'cpf' => $inscrito->cpf,
            'nome' => "Novo Nome De Teste",
        ]);
    }

    /** @test */
    public function log_is_generated_when_inscrito_is_updated()
    {
        $user = $this->signInAsAdmin();

        $inscrito = factory('App\CursoInscrito')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'nome' => 'Novo nome'
        ]);
        unset($attributes['idcurso']);

        $this->put(route('inscritos.update', $inscrito->idcursoinscrito), $attributes);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') editou *inscrito em curso* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function inscrito_cannot_be_cpf_when_updated()
    {
        $user = $this->signInAsAdmin();

        $inscrito = factory('App\CursoInscrito')->create();

        $this->assertDatabaseHas('curso_inscritos', [
            'cpf' => $inscrito->cpf,
        ]);

        $attributes = $inscrito->attributesToArray();
        $attributes['cpf'] = "862.943.730-85";
        unset($attributes['idcurso']);

        $this->put(route('inscritos.update', $inscrito->idcursoinscrito), $attributes)
        ->assertRedirect(route('inscritos.index', $inscrito->idcurso));

        $this->assertDatabaseMissing('curso_inscritos', [
            'cpf' => $attributes['cpf'],
        ]);
    }

    /** @test */
    public function inscrito_can_be_deleted_by_an_user()
    {
        $user = $this->signInAsAdmin();

        $inscrito = factory('App\CursoInscrito')->create();

        $this->delete(route('inscritos.destroy', $inscrito->idcursoinscrito))
        ->assertRedirect(route('inscritos.index', $inscrito->idcurso));

        $this->assertSoftDeleted('curso_inscritos', [
            'cpf' => $inscrito->cpf,
        ]);
    }

    /** @test */
    public function log_is_generated_when_inscrito_is_deleted()
    {
        $user = $this->signInAsAdmin();

        $inscrito = factory('App\CursoInscrito')->create();

        $this->delete(route('inscritos.destroy', $inscrito->idcursoinscrito));

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $inicio = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $txt = $inicio . $user->nome . ' (usuário '.$user->idusuario.') cancelou inscrição *inscrito em curso* (id: 1)';
        $this->assertStringContainsString($txt, $log);
    }

    /** @test */
    public function inscrito_cannot_be_deleted_after_termino_inscricao()
    {
        $user = $this->signInAsAdmin();

        $inscrito = factory('App\CursoInscrito')->create();
        $inscrito->curso->update(['termino_inscricao' => now()->subDay()->format('Y-m-d H:i')]);

        $this->delete(route('inscritos.destroy', $inscrito->idcursoinscrito))
        ->assertForbidden();

        $this->assertDatabaseHas('curso_inscritos', [
            'cpf' => $inscrito->cpf,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function inscrito_cannot_be_deleted_after_datatermino()
    {
        $user = $this->signInAsAdmin();

        $inscrito = factory('App\CursoInscrito')->create();
        $inscrito->curso->update(['datatermino' => now()->subDay()->format('Y-m-d H:i')]);

        $this->delete(route('inscritos.destroy', $inscrito->idcursoinscrito))
        ->assertForbidden();

        $this->assertDatabaseHas('curso_inscritos', [
            'cpf' => $inscrito->cpf,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function inscrito_cannot_be_created_after_termino_inscricao()
    {
        $user = $this->signInAsAdmin();

        $curso = factory('App\Curso')->create([
            'termino_inscricao' => now()->subDay()->format('Y-m-d H:i')
        ]);
        $attributes = factory('App\CursoInscrito')->raw();

        $this->post(route('inscritos.create', $curso->idcurso), $attributes)
        ->assertForbidden();
    }

    /** 
     * =======================================================================================================
     * TESTES INSCRITOS SITE
     * =======================================================================================================
     */

    /** @test */
    public function inscrito_private_can_be_created()
    {
        Mail::fake();

        $curso = factory('App\Curso')->create();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertSee('disabled');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['termo' => 'on'])
        ->assertViewIs('site.agradecimento');

        Mail::assertQueued(CursoInscritoMailGuest::class);

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $representante->cpf_cnpj]);
    }

    /** @test */
    public function log_is_generated_when_inscrito_private_is_created()
    {
        $curso = factory('App\Curso')->create();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['termo' => 'on']);
        $inscrito = CursoInscrito::find(1);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $string = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $string .= $inscrito->nome." (CPF: ".$inscrito->cpf.") *inscreveu-se* no curso *".$inscrito->curso->tipo." - ".$inscrito->curso->tema;
        $string .= "*, turma *".$inscrito->curso->idcurso."* e foi criado um novo registro no termo de consentimento, com a id: 1";
        $this->assertStringContainsString($string, $log);
    }

    /** @test */
    public function inscrito_private_can_be_created_with_cnpj_temp()
    {
        Mail::fake();

        $curso = factory('App\Curso')->create();

        $representante = factory('App\Representante')->create([
            'cpf_cnpj' => '11748345000144'
        ]);
        $this->actingAs($representante, 'representante');

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertSee('disabled');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['termo' => 'on'])
        ->assertViewIs('site.agradecimento');

        Mail::assertQueued(CursoInscritoMailGuest::class);

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $representante->cpf_cnpj]);
    }

    /** @test */
    public function inscrito_private_cannot_be_created_exists_cpf()
    {
        $curso = factory('App\Curso')->create();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['termo' => 'on']);

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $representante->cpf_cnpj]);

        $this->post(route('cursos.inscricao', $curso->idcurso), ['termo' => 'on'])
        ->assertSessionHasErrors('cpf');
    }

    /** @test */
    public function inscrito_private_cannot_be_created_gerenti_without_situacao_em_dia()
    {
        // Editar GerentiMock gerentiStatus()
        $curso = factory('App\Curso')->create();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertRedirect(route('representante.cursos'))
        ->assertSessionHas('message', '<i class="fas fa-info-circle"></i>&nbsp;Para liberar sua inscrição entre em contato com o setor de atendimento da <a href="'.route('regionais.siteGrid').'" target="_blank">seccional</a> de interesse.');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['termo' => 'on'])
        ->assertRedirect(route('representante.cursos'))
        ->assertSessionHas('message', '<i class="fas fa-info-circle"></i>&nbsp;Para liberar sua inscrição entre em contato com o setor de atendimento da <a href="'.route('regionais.siteGrid').'" target="_blank">seccional</a> de interesse.');

        $this->assertDatabaseMissing('curso_inscritos', ['cpf' => $representante->cpf_cnpj]);
    }

    /** @test */
    public function inscrito_public_can_be_created()
    {
        Mail::fake();

        $curso = factory('App\Curso')->states('publico')->create();
        $inscrito = factory('App\CursoInscrito')->raw([
            'idcurso' => $curso->idcurso,
            'termo' => 'on'
        ]);

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertDontSee('disabled');

        $this->post(route('cursos.inscricao', $curso->idcurso), $inscrito)
        ->assertViewIs('site.agradecimento');

        Mail::assertQueued(CursoInscritoMailGuest::class);

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf']]);
    }

    /** @test */
    public function log_is_generated_when_inscrito_public_is_created()
    {
        $curso = factory('App\Curso')->states('publico')->create();
        $inscrito = factory('App\CursoInscrito')->raw([
            'idcurso' => $curso->idcurso,
            'termo' => 'on'
        ]);

        $this->post(route('cursos.inscricao', $curso->idcurso), $inscrito);
        $inscrito = CursoInscrito::find(1);

        $log = tailCustom(storage_path($this->pathLogExterno()));
        $string = '[' . now()->format('Y-m-d H:i:s') . '] testing.INFO: [IP: '.request()->ip().'] - ';
        $string .= $inscrito->nome." (CPF: ".$inscrito->cpf.") *inscreveu-se* no curso *".$inscrito->curso->tipo." - ".$inscrito->curso->tema;
        $string .= "*, turma *".$inscrito->curso->idcurso."* e foi criado um novo registro no termo de consentimento, com a id: 1";
        $this->assertStringContainsString($string, $log);
    }

    /** @test */
    public function inscrito_private_can_be_created_with_cnpj_temp_if_authenticated()
    {
        Mail::fake();

        $curso = factory('App\Curso')->states('publico')->create();

        $representante = factory('App\Representante')->create([
            'cpf_cnpj' => '11748345000144'
        ]);
        $this->actingAs($representante, 'representante');

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertSee('disabled');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['termo' => 'on'])
        ->assertViewIs('site.agradecimento');

        Mail::assertQueued(CursoInscritoMailGuest::class);

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $representante->cpf_cnpj]);
    }

    /** @test */
    public function inscrito_public_cannot_be_created_with_cnpj_temp_without_authenticated()
    {
        $curso = factory('App\Curso')->states('publico')->create();
        $inscrito = factory('App\CursoInscrito')->raw([
            'idcurso' => $curso->idcurso,
            'cpf' => '11748345000144',
            'termo' => 'on'
        ]);

        $this->post(route('cursos.inscricao', $curso->idcurso), $inscrito)
        ->assertSessionHasErrors('cpf');
    }

    /** @test */
    public function inscrito_public_cannot_be_created_exists_cpf()
    {
        $curso = factory('App\Curso')->states('publico')->create();
        $inscrito = factory('App\CursoInscrito')->raw([
            'idcurso' => $curso->idcurso,
            'termo' => 'on'
        ]);

        $this->post(route('cursos.inscricao', $curso->idcurso), $inscrito);

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $inscrito['cpf']]);

        $this->post(route('cursos.inscricao', $curso->idcurso), $inscrito)
        ->assertSessionHasErrors('cpf');
    }

    /** @test */
    public function inscrito_public_can_be_created_by_representante()
    {
        Mail::fake();

        $curso = factory('App\Curso')->states('publico')->create();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertSee('disabled');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['termo' => 'on'])
        ->assertViewIs('site.agradecimento');

        Mail::assertQueued(CursoInscritoMailGuest::class);

        $this->assertDatabaseHas('curso_inscritos', ['cpf' => $representante->cpf_cnpj]);
    }

    /** @test */
    public function inscrito_public_cannot_be_created_without_required_input()
    {
        $curso = factory('App\Curso')->states('publico')->create();

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertDontSee('disabled');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['termo' => null])
        ->assertSessionHasErrors([
            'cpf',
            'nome',
            'telefone',
            'termo'
        ]);

        $this->assertDatabaseMissing('curso_inscritos', ['idcursoinscrito' => 1]);
    }

    /** @test */
    public function inscrito_site_without_nome_cannot_be_created()
    {
        $curso = factory('App\Curso')->states('publico')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'nome' => ''
        ]);

        $this->post(route('cursos.inscricao', $curso->idcurso), $attributes)
        ->assertSessionHasErrors('nome');
    }

    /** @test */
    public function inscrito_site_with_nome_less_than_5_chars_cannot_be_created()
    {
        $curso = factory('App\Curso')->states('publico')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'nome' => 'Abcd'
        ]);

        $this->post(route('cursos.inscricao', $curso->idcurso), $attributes)
        ->assertSessionHasErrors('nome');
    }

    /** @test */
    public function inscrito_site_without_telefone_cannot_be_created()
    {
        $curso = factory('App\Curso')->states('publico')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'telefone' => ''
        ]);

        $this->post(route('cursos.inscricao', $curso->idcurso), $attributes)
        ->assertSessionHasErrors('telefone');
    }

    /** @test */
    public function inscrito_site_with_telefone_invalid_format_cannot_be_created()
    {
        $curso = factory('App\Curso')->states('publico')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'telefone' => '11 9998558-96'
        ]);

        $this->post(route('cursos.inscricao', $curso->idcurso), $attributes)
        ->assertSessionHasErrors('telefone');
    }

    /** @test */
    public function inscrito_site_without_email_cannot_be_created()
    {
        $curso = factory('App\Curso')->states('publico')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'email' => ''
        ]);

        $this->post(route('cursos.inscricao', $curso->idcurso), $attributes)
        ->assertSessionHasErrors('email');
    }

    /** @test */
    public function inscrito_site_with_email_invalid_format_cannot_be_created()
    {
        $curso = factory('App\Curso')->states('publico')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'email' => 'teste.com'
        ]);

        $this->post(route('cursos.inscricao', $curso->idcurso), $attributes)
        ->assertSessionHasErrors('email');
    }

    /** @test */
    public function inscrito_site_without_termo_cannot_be_created()
    {
        $curso = factory('App\Curso')->states('publico')->create();
        $attributes = factory('App\CursoInscrito')->raw([
            'termo' => ''
        ]);

        $this->post(route('cursos.inscricao', $curso->idcurso), $attributes)
        ->assertSessionHasErrors('termo');
    }

    /** @test */
    public function inscrito_site_cannot_be_created_if_representante_exists_in_idcurso()
    {
        $inscrito = factory('App\CursoInscrito')->states('representante')->create();
        $this->actingAs(\App\Representante::find(1), 'representante');

        $dados = $inscrito->toArray();
        $dados['termo'] = 'on';

        $this->get(route('cursos.inscricao.website', $inscrito->idcurso))
        ->assertRedirect(route('representante.cursos'))
        ->assertSessionHas('message', 'Já está inscrito neste curso!');

        $this->post(route('cursos.inscricao', $inscrito->idcurso), $dados)
        ->assertSessionHasErrors('cpf');
    }

    /** @test */
    public function inscrito_site_cannot_be_created_if_finished_and_representante()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $curso = factory('App\Curso')->create([
            'datatermino' => now()->subDay()->format('Y-m-d H:i'),
        ]);
        $attributes = factory('App\CursoInscrito')->raw([
            'termo' => 'on'
        ]);

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertRedirect(route('representante.cursos'))
        ->assertSessionHas('message', 'Não é mais possível realizar inscrição neste curso');

        $this->post(route('cursos.inscricao', $curso->idcurso), $attributes)
        ->assertRedirect(route('representante.cursos'))
        ->assertSessionHas('message', 'Não é mais possível realizar inscrição neste curso');
    }

    /** @test */
    public function inscrito_site_cannot_be_created_if_finished()
    {
        $curso = factory('App\Curso')->states('publico')->create([
            'datatermino' => now()->subDay()->format('Y-m-d H:i'),
        ]);
        $attributes = factory('App\CursoInscrito')->raw([
            'termo' => 'on'
        ]);

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertRedirect(route('cursos.index.website'))
        ->assertSessionHas('message', 'Não é mais possível realizar inscrição neste curso');

        $this->post(route('cursos.inscricao', $curso->idcurso), $attributes)
        ->assertRedirect(route('cursos.index.website'))
        ->assertSessionHas('message', 'Não é mais possível realizar inscrição neste curso');
    }

    /** @test */
    public function inscrito_site_cannot_be_created_if_not_in_inicio_inscricao_and_termino_inscricao_and_representante()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $curso = factory('App\Curso')->create([
            'inicio_inscricao' => null,
            'termino_inscricao' => null
        ]);
        $attributes = factory('App\CursoInscrito')->raw([
            'termo' => 'on'
        ]);

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertRedirect(route('representante.cursos'))
        ->assertSessionHas('message', 'Não é mais possível realizar inscrição neste curso');

        $this->post(route('cursos.inscricao', $curso->idcurso), $attributes)
        ->assertRedirect(route('representante.cursos'))
        ->assertSessionHas('message', 'Não é mais possível realizar inscrição neste curso');
    }

    /** @test */
    public function inscrito_site_cannot_be_created_if_not_in_inicio_inscricao_and_termino_inscricao()
    {
        $curso = factory('App\Curso')->states('publico')->create([
            'inicio_inscricao' => null,
            'termino_inscricao' => null
        ]);
        $attributes = factory('App\CursoInscrito')->raw([
            'termo' => 'on'
        ]);

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertRedirect(route('cursos.index.website'))
        ->assertSessionHas('message', 'Não é mais possível realizar inscrição neste curso');

        $this->post(route('cursos.inscricao', $curso->idcurso), $attributes)
        ->assertRedirect(route('cursos.index.website'))
        ->assertSessionHas('message', 'Não é mais possível realizar inscrição neste curso');
    }

    /** @test */
    public function inscrito_site_cannot_be_created_without_nrvagas_and_representante()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $curso = factory('App\Curso')->create([
            'nrvagas' => 0,
        ]);
        $attributes = factory('App\CursoInscrito')->raw([
            'termo' => 'on'
        ]);

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertRedirect(route('representante.cursos'))
        ->assertSessionHas('message', 'Não é mais possível realizar inscrição neste curso');

        $this->post(route('cursos.inscricao', $curso->idcurso), $attributes)
        ->assertRedirect(route('representante.cursos'))
        ->assertSessionHas('message', 'Não é mais possível realizar inscrição neste curso');
    }

    /** @test */
    public function inscrito_site_cannot_be_created_without_nrvagas()
    {
        $curso = factory('App\Curso')->states('publico')->create([
            'nrvagas' => 0,
        ]);
        $attributes = factory('App\CursoInscrito')->raw([
            'termo' => 'on'
        ]);

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertRedirect(route('cursos.index.website'))
        ->assertSessionHas('message', 'Não é mais possível realizar inscrição neste curso');

        $this->post(route('cursos.inscricao', $curso->idcurso), $attributes)
        ->assertRedirect(route('cursos.index.website'))
        ->assertSessionHas('message', 'Não é mais possível realizar inscrição neste curso');
    }

    /** @test */
    public function inscrito_site_cannot_be_created_with_publicado_nao_and_representante()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $curso = factory('App\Curso')->create([
            'publicado' => 'Não',
        ]);
        $attributes = factory('App\CursoInscrito')->raw([
            'termo' => 'on'
        ]);

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertNotFound();

        $this->post(route('cursos.inscricao', $curso->idcurso), $attributes)
        ->assertNotFound();
    }

    /** @test */
    public function inscrito_site_cannot_be_created_with_publicado_nao()
    {
        $curso = factory('App\Curso')->states('publico')->create([
            'publicado' => 'Não',
        ]);
        $attributes = factory('App\CursoInscrito')->raw([
            'termo' => 'on'
        ]);

        $this->get(route('cursos.inscricao.website', $curso->idcurso))
        ->assertNotFound();

        $this->post(route('cursos.inscricao', $curso->idcurso), $attributes)
        ->assertNotFound();
    }

    /** 
     * =======================================================================================================
     * TESTES INSCRITOS ÁREA DO REPRESENTANTE
     * =======================================================================================================
     */

    /** @test */
    public function cannot_view_cards_without_cursos()
    {
        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->get(route('representante.cursos'))
        ->assertOk()
        ->assertSee('<p class="light pb-0">No momento não há cursos restritos com vagas abertas para o representante.</p>');

        $curso = factory('App\Curso')->create([
            'datatermino' => now()->subDay()->format('Y-m-d H:i:s')
        ]);

        $this->get(route('representante.cursos'))
        ->assertOk()
        ->assertSee('<p class="light pb-0">No momento não há cursos restritos com vagas abertas para o representante.</p>');
    }

    /** @test */
    public function can_view_cards_with_cursos()
    {
        $curso = factory('App\Curso')->create();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->get(route('representante.cursos'))
        ->assertOk()
        ->assertSee('<a href="'. route('cursos.show', $curso->idcurso) .'">')
        ->assertSee('<h6 class="light cinza-claro">'. $curso->regional->regional .' - '. onlyDate($curso->datarealizacao) .'</h6>')
        ->assertSee('<a href="'. route('cursos.inscricao.website', $curso->idcurso) .'" class="btn btn-sm btn-primary text-white mt-2">Inscrever-se</a>');
    }

    /** @test */
    public function can_view_button_inscrito_with_cursos()
    {
        $curso = factory('App\Curso')->create();

        $representante = factory('App\Representante')->create();
        $this->actingAs($representante, 'representante');

        $this->post(route('cursos.inscricao', $curso->idcurso), ['idcurso' => $curso->idcurso, 'termo' => 'on']);

        $this->get(route('representante.cursos'))
        ->assertOk()
        ->assertSee('<a href="'. route('cursos.show', $curso->idcurso) .'">')
        ->assertSee('<h6 class="light cinza-claro">'. $curso->regional->regional .' - '. onlyDate($curso->datarealizacao) .'</h6>')
        ->assertSee('<span class="'.$curso::TEXTO_BTN_INSCRITO.'">Inscrição realizada</span>');
    }
}
