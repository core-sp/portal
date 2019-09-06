<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SimuladorTest extends TestCase
{
    /** @test */
    function simulador_mostra_tabela_PF()
    {
        $this->post('/simulador', [
            'tipoPessoa' => 2,
            'dataInicio' => date('d\/m\/Y')
        ])->assertSee('Pessoa Física');
    }

    /** @test */
    function simulador_mostra_tabela_PJ()
    {
        $this->post('/simulador', [
            'tipoPessoa' => 1,
            'dataInicio' => date('d\/m\/Y')
        ])->assertSee('Pessoa Jurídica');
    }

    /** @test */
    function simulador_mostra_tabela_RT()
    {
        $this->post('/simulador', [
            'tipoPessoa' => 5,
            'dataInicio' => date('d\/m\/Y')
        ])->assertSee('Pessoa Física RT');
    }
}
