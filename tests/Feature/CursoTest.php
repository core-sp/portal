<?php

namespace Tests\Feature;

use App\Permissao;
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
}
