<?php

namespace Tests\Feature;

use App\Curso;
use App\Permissao;
use DateInterval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permissao::insert([
            [
                'controller' => 'CursoController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'CursoController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'CursoController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'CursoController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ],
            [
                'controller' => 'CursoInscritoController',
                'metodo' => 'index',
                'perfis' => '1,'
            ], [
                'controller' => 'CursoInscritoController',
                'metodo' => 'create',
                'perfis' => '1,'
            ], [
                'controller' => 'CursoInscritoController',
                'metodo' => 'edit',
                'perfis' => '1,'
            ], [
                'controller' => 'CursoInscritoController',
                'metodo' => 'destroy',
                'perfis' => '1,'
            ]
        ]);
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
        $this->withoutExceptionHandling();
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
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('criou', $log);
        $this->assertStringContainsString('curso', $log);
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
    public function curso_endereco_is_required_if_tema_not_live()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'tema' => 'Curso',
            'endereco' => ''
        ]);

        $this->post(route('cursos.store'), $attributes)->assertSessionHasErrors('endereco');
        $this->assertDatabaseMissing('cursos', ['resumo' => $attributes['resumo']]);
    }

    /** @test */
    public function curso_endereco_is_not_required_if_tema_is_live()
    {
        $this->signInAsAdmin();

        $attributes = factory('App\Curso')->raw([
            'tema' => 'Live',
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
        $this->withoutExceptionHandling();
        $user = $this->signInAsAdmin();

        $curso = factory('App\Curso')->create();
        $attributes = factory('App\Curso')->raw();

        $this->patch(route('cursos.update', $curso->idcurso), $attributes);

        $log = tailCustom(storage_path($this->pathLogInterno()));
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('editou', $log);
        $this->assertStringContainsString('curso', $log);
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
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('cancelou', $log);
        $this->assertStringContainsString('curso', $log);
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
        $this->withoutExceptionHandling();
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
        $this->assertStringContainsString($user->nome, $log);
        $this->assertStringContainsString('reabriu', $log);
        $this->assertStringContainsString('curso', $log);
    }

    /** @test */
    function curso_can_be_searched_on_admin_panel()
    {
        $this->withoutExceptionHandling();
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
    function curso_is_shown_on_website()
    {
        $curso = factory('App\Curso')->create();

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
        $date = new \DateTime();
        $date->sub(new DateInterval('P30D'));
        $realizacao = $date->format('Y-m-d\TH:i:s');
        $new_date = new \DateTime();
        $new_date->sub(new DateInterval('P31D'));
        $termino = $new_date->format('Y-m-d\TH:i:s');

        $curso = factory('App\Curso')->create([
            'datarealizacao' => $realizacao,
            'datatermino' => $termino
        ]);

        $this->get(route('cursos.index.website'))
            ->assertOk()
            ->assertDontSee($curso->tema);
    }

    /** @test */
    function previous_cursos_are_shown_on_previous_curso_list_on_website()
    {
        $date = new \DateTime();
        $date->sub(new DateInterval('P30D'));
        $realizacao = $date->format('Y-m-d\TH:i:s');
        $new_date = new \DateTime();
        $new_date->sub(new DateInterval('P31D'));
        $termino = $new_date->format('Y-m-d\TH:i:s');

        $curso = factory('App\Curso')->create([
            'datarealizacao' => $realizacao,
            'datatermino' => $termino
        ]);

        $this->get(route('cursos.previous.website'))
            ->assertOk()
            ->assertSee(route('cursos.show', $curso->idcurso))
            ->assertSee($curso->tema);
    }
}
