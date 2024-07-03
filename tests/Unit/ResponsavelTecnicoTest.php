<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\ResponsavelTecnico;
use Illuminate\Foundation\Testing\WithFaker;

class ResponsavelTecnicoTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** 
     * =======================================================================================================
     * TESTES MODEL
     * =======================================================================================================
     */

    /** @test */
    public function pessoas_juridicas()
    {
        $dados = factory('App\PreRegistroCnpj')->create();
        factory('App\PreRegistroCnpj')->create();

        $this->assertEquals(2, ResponsavelTecnico::first()->pessoasJuridicas->count());
    }

    /** @test */
    public function campos_pre_registro()
    {
        $this->assertEquals([
            'cpf',
            'registro',
            'nome',
            'nome_social',
            'dt_nascimento',
            'sexo',
            'tipo_identidade',
            'identidade',
            'orgao_emissor',
            'dt_expedicao',
            'cep',
            'bairro',
            'logradouro',
            'numero',
            'complemento',
            'cidade',
            'uf',
            'nome_mae',
            'nome_pai',
            'titulo_eleitor',
            'zona',
            'secao',
            'ra_reservista',
        ], ResponsavelTecnico::camposPreRegistro());
    }

    /** @test */
    public function array_validacao_inputs_do_pre_registro()
    {
        $rt = factory('App\ResponsavelTecnico')->create();
        $dados = factory('App\PreRegistroCnpj')->create();

        $this->assertEquals([
            'cpf_rt' => $rt->cpf,
            'nome_rt' => $rt->nome,
            'nome_social_rt' => $rt->nome_social,
            'dt_nascimento_rt' => $rt->dt_nascimento,
            'sexo_rt' => $rt->sexo,
            'tipo_identidade_rt' => $rt->tipo_identidade,
            'identidade_rt' => $rt->identidade,
            'orgao_emissor_rt' => $rt->orgao_emissor,
            'dt_expedicao_rt' => $rt->dt_expedicao,
            'cep_rt' => $rt->cep,
            'bairro_rt' => $rt->bairro,
            'logradouro_rt' => $rt->logradouro,
            'numero_rt' => $rt->numero,
            'complemento_rt' => $rt->complemento,
            'cidade_rt' => $rt->cidade,
            'uf_rt' => $rt->uf,
            'nome_mae_rt' => $rt->nome_mae,
            'nome_pai_rt' => $rt->nome_pai,
            'titulo_eleitor_rt' => $rt->titulo_eleitor,
            'zona_rt' => $rt->zona,
            'secao_rt' => $rt->secao,
            'ra_reservista_rt' => $rt->ra_reservista,
        ], $rt->arrayValidacaoInputs());
    }

    /** @test */
    public function buscar()
    {
        factory('App\ResponsavelTecnico', 5)->create();
        $nao_existe = factory('App\ResponsavelTecnico')->raw();
        
        // RT existe, sem gerenti e sem verificação se pode editar
        $this->assertEquals(ResponsavelTecnico::class, get_class(ResponsavelTecnico::buscar(ResponsavelTecnico::first()->cpf, null)));
        $this->assertEquals(5, ResponsavelTecnico::count());

        // RT existe, com gerenti e sem verificação se pode editar
        $this->assertEquals(ResponsavelTecnico::class, get_class(ResponsavelTecnico::buscar(ResponsavelTecnico::first()->cpf, ['registro' => '00000001', 'nome_mae' => 'TESTE MÃE RT'])));
        $this->assertDatabaseHas('responsaveis_tecnicos', ['id' => 1, 'nome_mae' => 'TESTE MÃE RT', 'registro' => '00000001']);

        // RT existe, sem gerenti e com verificação se pode editar
        $this->assertEquals(ResponsavelTecnico::class, get_class(ResponsavelTecnico::buscar(ResponsavelTecnico::first()->cpf, null, true)));
        $this->assertEquals('notUpdate', ResponsavelTecnico::buscar(ResponsavelTecnico::first()->cpf, null, false));
        $this->assertEquals(5, ResponsavelTecnico::count());

        // RT não existe, então cria, sem gerenti e sem verificação se pode editar
        $this->assertEquals(ResponsavelTecnico::class, get_class(ResponsavelTecnico::buscar($nao_existe['cpf'], null)));
        $this->assertEquals(6, ResponsavelTecnico::count());

        $nao_existe = factory('App\ResponsavelTecnico')->raw();

        // RT não existe, então cria, com gerenti e sem verificação se pode editar
        $this->assertEquals(ResponsavelTecnico::class, get_class(ResponsavelTecnico::buscar($nao_existe['cpf'], ['cpf' => $nao_existe['cpf'], 'registro' => '00000002', 'nome_mae' => 'TESTE MÃE RT DOIS'])));
        $this->assertEquals(7, ResponsavelTecnico::count());
        $this->assertDatabaseHas('responsaveis_tecnicos', ['id' => 7, 'cpf' => $nao_existe['cpf'], 'registro' => '00000002', 'nome_mae' => 'TESTE MÃE RT DOIS']);

        $nao_existe = factory('App\ResponsavelTecnico')->raw();

        // RT não existe, então cria, sem gerenti e com verificação se pode editar
        $this->assertEquals(ResponsavelTecnico::class, get_class(ResponsavelTecnico::buscar($nao_existe['cpf'], null, true)));
        $this->assertEquals('notUpdate', ResponsavelTecnico::buscar($nao_existe['cpf'], null, false));
        $this->assertEquals(8, ResponsavelTecnico::count());

        // sem cpf
        $this->expectException(\Exception::class);
        ResponsavelTecnico::buscar(null, null);
    }

    /** @test */
    public function criar_final()
    {
        factory('App\ResponsavelTecnico', 5)->create();
        $pr = factory('App\PreRegistroCnpj')->create()->makeVisible(['historico_rt']);
        $socio_pf = $pr->socios->first();
        $nao_existe = factory('App\ResponsavelTecnico')->raw();

        // com RT que existe, sem gerenti
        $this->assertEquals(ResponsavelTecnico::class, get_class(ResponsavelTecnico::criarFinal('cpf', ResponsavelTecnico::first()->cpf, null, $pr->preRegistro)));

        // com RT que existe, com gerenti
        $pr->fresh()->update(['historico_rt' => json_encode(['tentativas' => 0, 'update' => now()->format('Y-m-d H:i:s')])]);
        $this->assertEquals(ResponsavelTecnico::class, get_class(ResponsavelTecnico::criarFinal('cpf', ResponsavelTecnico::first()->cpf, ['cpf' => ResponsavelTecnico::first()->cpf, 'registro' => '00000002', 'nome_mae' => 'TESTE MÃE RT DOIS'], $pr->preRegistro->fresh())));
        $this->assertDatabaseHas('responsaveis_tecnicos', ['id' => 1, 'cpf' => ResponsavelTecnico::first()->cpf, 'registro' => '00000002', 'nome_mae' => 'TESTE MÃE RT DOIS']);

        // com RT que não existe e sem gerenti
        $pr->fresh()->update(['historico_rt' => json_encode(['tentativas' => 0, 'update' => now()->format('Y-m-d H:i:s')])]);
        $this->assertEquals(ResponsavelTecnico::class, get_class(ResponsavelTecnico::criarFinal('cpf', $nao_existe['cpf'], null, $pr->preRegistro->fresh())));

        $nao_existe = factory('App\ResponsavelTecnico')->raw();

        // com RT que não existe e com gerenti
        $pr->fresh()->update(['historico_rt' => json_encode(['tentativas' => 0, 'update' => now()->format('Y-m-d H:i:s')])]);
        $this->assertEquals(ResponsavelTecnico::class, get_class(ResponsavelTecnico::criarFinal('cpf', $nao_existe['cpf'], ['cpf' => $nao_existe['cpf'], 'registro' => '00000001', 'nome_mae' => 'TESTE MÃE RT'], $pr->preRegistro->fresh())));
        $this->assertDatabaseHas('responsaveis_tecnicos', ['id' => 7, 'cpf' => $nao_existe['cpf'], 'registro' => '00000001', 'nome_mae' => 'TESTE MÃE RT']);
        
        // com RT que não existe e é Sócio
        $this->assertDatabaseHas('socio_pre_registro_cnpj', ['id' => 1, 'rt' => 0]);
        $pr->fresh()->update(['historico_rt' => json_encode(['tentativas' => 0, 'update' => now()->format('Y-m-d H:i:s')])]);
        $this->assertEquals(ResponsavelTecnico::class, get_class(ResponsavelTecnico::criarFinal('cpf', $socio_pf->cpf_cnpj, null, $pr->preRegistro->fresh())));
        $this->assertDatabaseHas('socio_pre_registro_cnpj', ['id' => 1, 'rt' => 1]);

        $pr = factory('App\PreRegistroCnpj')->states('bloqueado_rt')->create();

        $nao_existe = factory('App\ResponsavelTecnico')->raw();

        // não pode criar
        $this->assertEquals('update', array_keys(ResponsavelTecnico::criarFinal('cpf', $nao_existe['cpf'], null, $pr->preRegistro))[0]);
        $this->assertEquals(8, ResponsavelTecnico::count());

        $this->expectException(\Exception::class);
        ResponsavelTecnico::criarFinal('cpff', null, null, $pr->preRegistro);
    }

    /** @test */
    public function atualizar_final()
    {
        factory('App\ResponsavelTecnico', 2)->create();
        $pr = factory('App\PreRegistroCnpj')->create();
        $novo = factory('App\ResponsavelTecnico')->raw();

        // sem cpf
        foreach(ResponsavelTecnico::camposPreRegistro() as $dado)
        {
            if($dado == 'cpf')
                continue;
            $this->assertEquals(null, ResponsavelTecnico::find(2)->atualizarFinal($dado, $novo[$dado], $pr));
            $this->assertEquals($novo[$dado], ResponsavelTecnico::find(2)[$dado]);
        }

        // com cpf
        $this->assertEquals('remover', ResponsavelTecnico::find(2)->atualizarFinal('cpf', '12345678901', $pr));
        $this->assertDatabaseHas('pre_registros_cnpj', ['id' => 1, 'responsavel_tecnico_id' => null]);
    }
}
