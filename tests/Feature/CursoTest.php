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
}
