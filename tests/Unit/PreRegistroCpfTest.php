<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\PreRegistroCpf;
use Illuminate\Foundation\Testing\WithFaker;

class PreRegistroCpfTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** 
     * =======================================================================================================
     * TESTES MODEL
     * =======================================================================================================
     */

    /** @test */
    public function campos_pre_registro()
    {
        $this->assertEquals([
            'nome_social',
            'sexo',
            'dt_nascimento',
            'estado_civil',
            'nacionalidade',
            'naturalidade_cidade',
            'naturalidade_estado',
            'nome_mae',
            'nome_pai',
            'tipo_identidade',
            'identidade',
            'orgao_emissor',
            'dt_expedicao',
            'titulo_eleitor',
            'zona',
            'secao',
            'ra_reservista',
        ], PreRegistroCpf::camposPreRegistro());
    }

    /** @test */
    public function pre_registro()
    {
        $dados = factory('App\PreRegistroCpf')->create();
        factory('App\PreRegistroCpf')->create();

        $this->assertEquals(1, PreRegistroCpf::find(1)->preRegistro()->count());
        $this->assertEquals(1, PreRegistroCpf::find(2)->preRegistro()->count());

        PreRegistroCpf::find(1)->preRegistro()->delete();
        $this->assertNotEquals(null, PreRegistroCpf::find(1)->preRegistro()->first()->deleted_at);
        $this->assertEquals(1, PreRegistroCpf::find(1)->preRegistro()->count());
    }

    /** @test */
    public function array_validacao_inputs_do_pre_registro()
    {
        $dados = factory('App\PreRegistroCpf')->create();

        $this->assertEquals([
            'nome_social' => $dados->nome_social,
            'dt_nascimento' => $dados->dt_nascimento,
            'sexo' => $dados->sexo,
            'tipo_identidade' => $dados->tipo_identidade,
            'identidade' => $dados->identidade,
            'orgao_emissor' => $dados->orgao_emissor,
            'dt_expedicao' => $dados->dt_expedicao,
            'estado_civil' => $dados->estado_civil,
            'nacionalidade' => $dados->nacionalidade,
            'naturalidade_cidade' => $dados->naturalidade_cidade,
            'naturalidade_estado' => $dados->naturalidade_estado,
            'nome_mae' => $dados->nome_mae,
            'nome_pai' => $dados->nome_pai,
            'titulo_eleitor' => $dados->titulo_eleitor,
            'zona' => $dados->zona,
            'secao' => $dados->secao,
            'ra_reservista' => $dados->ra_reservista,
        ], $dados->arrayValidacaoInputs());
    }

    /** @test */
    public function mais_de_45_anos()
    {
        $menos = factory('App\PreRegistroCpf')->create();
        $igual = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => now()->subYears(45)->format('Y-m-d')
        ]);
        $mais = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => now()->subYears(46)->format('Y-m-d')
        ]);
        $nulo = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => null
        ]);
        
        $this->assertEquals(false, $menos->maisDe45Anos());
        $this->assertEquals(false, $igual->maisDe45Anos());
        $this->assertEquals(true, $mais->maisDe45Anos());
        $this->assertEquals(false, $nulo->maisDe45Anos());
    }

    /** @test */
    public function brasileira()
    {
        $brasileira = factory('App\PreRegistroCpf')->create();
        $gringa = factory('App\PreRegistroCpf')->create([
            'nacionalidade' => 'ARGENTINA'
        ]);
        $nulo = factory('App\PreRegistroCpf')->create([
            'nacionalidade' => null
        ]);
        
        $this->assertEquals(true, $brasileira->brasileira());
        $this->assertEquals(false, $gringa->brasileira());
        $this->assertEquals(false, $nulo->brasileira());
    }

    /** @test */
    public function reservista()
    {
        $reservista = factory('App\PreRegistroCpf')->create();
        $nao_reservista_sexo = factory('App\PreRegistroCpf')->create([
            'sexo' => 'F'
        ]);
        $nao_reservista_idade = factory('App\PreRegistroCpf')->create([
            'dt_nascimento' => now()->subYears(46)->format('Y-m-d')
        ]);
        $nao_reservista_ambos = factory('App\PreRegistroCpf')->create([
            'sexo' => 'F',
            'dt_nascimento' => now()->subYears(46)->format('Y-m-d')
        ]);
        $nulo = factory('App\PreRegistroCpf')->create([
            'sexo' => null,
            'dt_nascimento' => null,
        ]);
        
        $this->assertEquals(true, $reservista->reservista());
        $this->assertEquals(false, $nao_reservista_sexo->reservista());
        $this->assertEquals(false, $nao_reservista_idade->reservista());
        $this->assertEquals(false, $nao_reservista_ambos->reservista());
        $this->assertEquals(false, $nulo->reservista());
    }

    /** @test */
    public function atualizar_final()
    {
        $pr = factory('App\PreRegistroCpf')->create();
        $novo = factory('App\PreRegistroCpf')->raw();

        foreach(PreRegistroCpf::camposPreRegistro() as $dado)
        {
            $this->assertEquals(null, PreRegistroCpf::first()->atualizarFinal($dado, $novo[$dado]));
            $this->assertEquals($novo[$dado], PreRegistroCpf::first()[$dado]);
        }
    }

    /** @test */
    public function soft_delete()
    {
        $user = factory('App\PreRegistroCpf')->create();

        $this->assertEquals(1, PreRegistroCpf::count());
        $this->assertDatabaseHas('pre_registros_cpf', ['id' => 1, 'deleted_at' => null]);

        $user->delete();

        $this->assertEquals(0, PreRegistroCpf::count());
        $this->assertDatabaseMissing('pre_registros_cpf', ['id' => 1, 'deleted_at' => null]);

        PreRegistroCpf::withTrashed()->first()->restore();

        $this->assertEquals(1, PreRegistroCpf::count());
        $this->assertDatabaseHas('pre_registros_cpf', ['id' => 1, 'deleted_at' => null]);
    }
}
