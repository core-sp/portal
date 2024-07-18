<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\PreRegistroCnpj;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;

class PreRegistroCnpjTest extends TestCase
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
            'razao_social',
            'capital_social',
            'nire',
            'tipo_empresa',
            'dt_inicio_atividade',
            'nome_fantasia',
            'cep',
            'bairro',
            'logradouro',
            'numero',
            'complemento',
            'cidade',
            'uf',
        ], PreRegistroCnpj::camposPreRegistro());
    }

    /** @test */
    public function pre_registro()
    {
        $dados = factory('App\PreRegistroCnpj')->create();
        factory('App\PreRegistroCnpj')->create();

        $this->assertEquals(1, PreRegistroCnpj::find(1)->preRegistro()->count());
        $this->assertEquals(1, PreRegistroCnpj::find(2)->preRegistro()->count());

        PreRegistroCnpj::find(1)->preRegistro()->delete();
        $this->assertNotEquals(null, PreRegistroCnpj::find(1)->preRegistro()->first()->deleted_at);
        $this->assertEquals(1, PreRegistroCnpj::find(1)->preRegistro()->count());
    }

    /** @test */
    public function responsavel_tecnico()
    {
        $dados = factory('App\PreRegistroCnpj')->create();
        factory('App\PreRegistroCnpj')->create();

        $this->assertEquals(1, PreRegistroCnpj::find(1)->responsavelTecnico()->count());
        $this->assertEquals(1, PreRegistroCnpj::find(2)->responsavelTecnico()->count());

        PreRegistroCnpj::find(1)->update(['responsavel_tecnico_id' => null]);
        PreRegistroCnpj::find(2)->update(['responsavel_tecnico_id' => null]);

        $this->assertEquals(0, PreRegistroCnpj::find(1)->responsavelTecnico()->count());
        $this->assertEquals(0, PreRegistroCnpj::find(2)->responsavelTecnico()->count());

        PreRegistroCnpj::find(1)->update(['responsavel_tecnico_id' => 1]);

        PreRegistroCnpj::find(1)->responsavelTecnico()->delete();
        $this->assertNotEquals(null, PreRegistroCnpj::find(1)->responsavelTecnico()->first()->deleted_at);
        $this->assertEquals(1, PreRegistroCnpj::find(1)->responsavelTecnico()->count());
    }

    /** @test */
    public function socios()
    {
        $dados = factory('App\PreRegistroCnpj')->create();
        factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        factory('App\PreRegistroCnpj')->states('com_limite_socios')->create();

        $this->assertEquals(2, PreRegistroCnpj::find(1)->socios()->count());
        $this->assertEquals(3, PreRegistroCnpj::find(2)->socios()->count());
        $this->assertEquals(PreRegistroCnpj::TOTAL_HIST_SOCIO, PreRegistroCnpj::find(3)->socios()->count());

        PreRegistroCnpj::find(1)->socios()->detach();
        PreRegistroCnpj::find(2)->socios()->detach(4);
        PreRegistroCnpj::find(3)->socios()->detach(7);
        PreRegistroCnpj::find(3)->socios()->detach(9);

        $this->assertEquals(0, PreRegistroCnpj::find(1)->socios()->count());
        $this->assertEquals(2, PreRegistroCnpj::find(2)->socios()->count());
        $this->assertEquals(PreRegistroCnpj::TOTAL_HIST_SOCIO - 2, PreRegistroCnpj::find(3)->socios()->count());
    }

    /** @test */
    public function socio_rt()
    {
        $dados = factory('App\PreRegistroCnpj')->create();
        factory('App\PreRegistroCnpj')->states('rt_socio')->create();

        $this->assertEquals(0, PreRegistroCnpj::find(1)->socioRT()->count());
        $this->assertEquals(1, PreRegistroCnpj::find(2)->socioRT()->count());

        PreRegistroCnpj::find(2)->socios()->detach(5);

        $this->assertEquals(0, PreRegistroCnpj::find(2)->socioRT()->count());
    }

    /** @test */
    public function array_validacao_inputs_do_pre_registro()
    {
        $dados = factory('App\PreRegistroCnpj')->create();

        $this->assertEquals([
            'razao_social' => $dados->razao_social,
            'capital_social' => $dados->capital_social,
            'nire' => $dados->nire,
            'tipo_empresa' => $dados->tipo_empresa,
            'dt_inicio_atividade' => $dados->dt_inicio_atividade,
            'nome_fantasia' => $dados->nome_fantasia,
            'cep_empresa' => $dados->cep,
            'bairro_empresa' => $dados->bairro,
            'logradouro_empresa' => $dados->logradouro,
            'numero_empresa' => $dados->numero,
            'complemento_empresa' => $dados->complemento,
            'cidade_empresa' => $dados->cidade,
            'uf_empresa' => $dados->uf,
            'checkEndEmpresa' => 'off',
        ], $dados->arrayValidacaoInputs());
    }

    /** @test */
    public function pode_criar_socio()
    {
        $sim = factory('App\PreRegistroCnpj')->create();
        $nao = factory('App\PreRegistroCnpj')->states('com_limite_socios')->create();
        
        $this->assertEquals(true, $sim->podeCriarSocio());
        $this->assertEquals(false, $nao->podeCriarSocio());

        PreRegistroCnpj::find(2)->socios()->detach(3);

        $this->assertEquals(true, $nao->fresh()->podeCriarSocio());
    }

    /** @test */
    public function atendente_pode_aprovar()
    {
        $sim = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj', 'analise_inicial')->create(),
        ]);
        $sim->responsavelTecnico->update(['registro' => '0000001']);

        factory('App\ResponsavelTecnico')->create();
        $nao = factory('App\PreRegistroCnpj')->create([
            'pre_registro_id' => factory('App\PreRegistro')->states('pj')->create()
        ]);
        
        $this->assertEquals(true, $sim->atendentePodeAprovar());
        $this->assertEquals(false, $nao->atendentePodeAprovar());
    }

    /** @test */
    public function possui_socio()
    {
        $sim = factory('App\PreRegistroCnpj')->create();

        $nao = factory('App\PreRegistroCnpj')->create();
        $nao->socios()->detach();
        
        $this->assertEquals(true, $sim->possuiSocio());
        $this->assertEquals(false, $nao->possuiSocio());
    }

    /** @test */
    public function possui_rt()
    {
        $sim = factory('App\PreRegistroCnpj')->create();

        $nao = factory('App\PreRegistroCnpj')->create([
            'responsavel_tecnico_id' => null
        ]);
        
        $this->assertEquals(true, $sim->possuiRT());
        $this->assertEquals(false, $nao->possuiRT());
    }

    /** @test */
    public function possui_rt_socio()
    {
        $sim = factory('App\PreRegistroCnpj')->states('rt_socio')->create();

        $nao = factory('App\PreRegistroCnpj')->create();
        
        $this->assertEquals(true, $sim->possuiRTSocio());
        $this->assertEquals(false, $nao->possuiRTSocio());
    }

    /** @test */
    public function possui_socio_pf()
    {
        $sim = factory('App\PreRegistroCnpj')->create();

        $nao = factory('App\PreRegistroCnpj')->create();
        $nao->socios()->detach(3);

        $this->assertEquals(true, $sim->possuiSocioPF());
        $this->assertEquals(false, $nao->possuiSocioPF());
    }

    /** @test */
    public function possui_socio_brasileiro()
    {
        $sim = factory('App\PreRegistroCnpj')->create();

        $nao = factory('App\PreRegistroCnpj')->create();
        $nao->socios()->find(3)->update(['nacionalidade' => 'ARGENTINA']);

        $nulo = factory('App\PreRegistroCnpj')->create();
        $nulo->socios()->find(5)->update(['nacionalidade' => null]);

        $this->assertEquals(true, $sim->possuiSocioBrasileiro());
        $this->assertEquals(false, $nao->possuiSocioBrasileiro());
        $this->assertEquals(false, $nulo->possuiSocioBrasileiro());
    }

    /** @test */
    public function possui_socio_reservista()
    {
        $sim = factory('App\PreRegistroCnpj')->create();

        $nao = factory('App\PreRegistroCnpj')->create();
        $nao->socios()->find(3)->update(['dt_nascimento' => now()->subYears(46)->format('Y-m-d')]);

        $nulo = factory('App\PreRegistroCnpj')->create();
        $nulo->socios()->find(5)->update(['dt_nascimento' => null]);

        $this->assertEquals(true, $sim->possuiSocioReservista());
        $this->assertEquals(false, $nao->possuiSocioReservista());
        $this->assertEquals(false, $nulo->possuiSocioReservista());
    }

    /** @test */
    public function remover_rt()
    {
        $rt1 = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $rt2 = factory('App\PreRegistroCnpj')->create();

        $this->assertEquals(true, $rt1->possuiRT());
        $rt1->removerRT();
        $this->assertEquals(false, $rt1->possuiRT());

        $this->assertEquals(true, $rt2->possuiRT());
        $rt2->removerRT();
        $this->assertEquals(false, $rt2->possuiRT());
    }

    /** @test */
    public function relacionar_rt()
    {
        $dados = factory('App\PreRegistroCnpj')->create();
        $rt = factory('App\ResponsavelTecnico')->create();

        $this->assertEquals($rt->nome, $dados->relacionarRT($rt->id)->nome);
        $dados->fresh()->removerRT();

        $socio = factory('App\Socio')->create([
            'cpf_cnpj' => $rt->cpf,
            'nome' => $rt->nome
        ]);
        $dados->socios()->attach($socio->id);

        $this->assertEquals(3, $dados->fresh()->relacionarRT($rt->id)->id_socio);
    }

    /** @test */
    public function relacionar_socio()
    {
        $dados = factory('App\PreRegistroCnpj')->create();
        $rt = $dados->responsavelTecnico;
        $socio = factory('App\Socio')->create();

        $socioRT = factory('App\Socio')->create([
            'cpf_cnpj' => $rt->cpf,
            'nome' => $rt->nome
        ]);

        $this->assertEquals($socio->tabHTML(), $dados->relacionarSocio($socio)->tab);
        $this->assertEquals(true, $dados->relacionarSocio($socioRT)->rt);
    }

    /** @test */
    public function socio_esta_relacionado()
    {
        $dados = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $socio = factory('App\Socio')->create();

        foreach($dados->socios as $socioSalvo)
            $this->assertEquals(true, $dados->socioEstaRelacionado($socioSalvo->id));

        $this->assertEquals(false, $dados->socioEstaRelacionado($socio->id));
    }

    /** @test */
    public function get_historico_array()
    {
        // historico RT
        $dados = factory('App\PreRegistroCnpj')->create();
        $rt = factory('App\ResponsavelTecnico')->create();

        $this->assertEquals('0', $dados->getHistoricoArray()['tentativas']);
        $dados->relacionarRT($rt->id);
        $this->assertEquals('1', $dados->getHistoricoArray()['tentativas']);
        $dados->update(['historico_rt' => null]);
        $this->assertEquals([], $dados->getHistoricoArray());

        // historico Sócio
        $dados = factory('App\PreRegistroCnpj')->create();
        $socio = factory('App\Socio')->create();

        $this->assertEquals('0', $dados->getHistoricoArray(get_class($socio))['tentativas']);
        $dados->relacionarSocio($socio);
        $this->assertEquals('1', $dados->getHistoricoArray(get_class($socio))['tentativas']);
        $dados->update(['historico_socio' => null]);
        $this->assertEquals([], $dados->getHistoricoArray(get_class($socio)));

        $this->expectException(\Exception::class);
        $dados->getHistoricoArray(null);
    }

    /** @test */
    public function set_historico()
    {
        // historico RT
        $dados = factory('App\PreRegistroCnpj')->create();

        $this->assertEquals('0', $dados->getHistoricoArray()['tentativas']);
        $this->assertEquals(json_encode([
            'tentativas' => 1,
            'update' => now()->format('Y-m-d H:i:s')
        ]), $dados->setHistorico());

        $dados->update(['historico_rt' => $dados->setHistorico()]);
        $this->assertEquals(json_encode([
            'tentativas' => 1,
            'update' => now()->format('Y-m-d H:i:s')
        ]), $dados->setHistorico());

        // historico Sócio
        $dados = factory('App\PreRegistroCnpj')->create();

        $this->assertEquals('0', $dados->getHistoricoArray(PreRegistroCnpj::RELACAO_SOCIO)['tentativas']);
        $this->assertEquals(json_encode([
            'tentativas' => 1,
            'update' => now()->format('Y-m-d H:i:s')
        ]), $dados->setHistorico(PreRegistroCnpj::RELACAO_SOCIO));

        $dados->update(['historico_socio' => $dados->setHistorico(PreRegistroCnpj::RELACAO_SOCIO)]);
        $this->assertEquals(json_encode([
            'tentativas' => 2,
            'update' => now()->format('Y-m-d H:i:s')
        ]), $dados->setHistorico(PreRegistroCnpj::RELACAO_SOCIO));

        $this->expectException(\Exception::class);
        $dados->setHistorico('rere');
    }

    /** @test */
    public function get_historico_can_edit()
    {
        // historico RT
        $dados = factory('App\PreRegistroCnpj')->create();

        $this->assertEquals('0', $dados->getHistoricoArray()['tentativas']);
        $this->assertEquals(true, $dados->getHistoricoCanEdit());

        // alcançou o limite de tentativas
        $dados->update(['historico_rt' => $dados->setHistorico()]);
        $this->assertEquals(false, $dados->getHistoricoCanEdit());

        // alcançou o limite de tentativas, mas já passou do prazo de espera
        $temp = json_decode($dados->setHistorico(), true);
        $temp['update'] = now()->subDays(2)->format('Y-m-d H:i:s');
        $dados->update(['historico_rt' => json_encode($temp)]);

        $this->assertEquals(true, $dados->getHistoricoCanEdit());

        // historico Socio
        $dados = factory('App\PreRegistroCnpj')->create();

        $this->assertEquals('0', $dados->getHistoricoArray(PreRegistroCnpj::RELACAO_SOCIO)['tentativas']);
        $this->assertEquals(true, $dados->getHistoricoCanEdit(PreRegistroCnpj::RELACAO_SOCIO));

        // alcançou o limite de tentativas
        $temp = json_decode($dados->setHistorico(PreRegistroCnpj::RELACAO_SOCIO), true);
        $temp['tentativas'] = PreRegistroCnpj::TOTAL_HIST_SOCIO;
        $dados->update(['historico_socio' => json_encode($temp)]);
        $dados->update(['historico_socio' => $dados->setHistorico(PreRegistroCnpj::RELACAO_SOCIO)]);
        $this->assertEquals(false, $dados->getHistoricoCanEdit(PreRegistroCnpj::RELACAO_SOCIO));

        // alcançou o limite de tentativas, mas já passou do prazo de espera
        $temp = json_decode($dados->setHistorico(PreRegistroCnpj::RELACAO_SOCIO), true);
        $temp['update'] = now()->subDays(3)->format('Y-m-d H:i:s');
        $dados->update(['historico_socio' => json_encode($temp)]);
        
        $this->assertEquals(true, $dados->getHistoricoCanEdit(PreRegistroCnpj::RELACAO_SOCIO));

        $this->expectException(\Exception::class);
        $dados->getHistoricoCanEdit(PreRegistroCnpj::RELACAO_SOCIO . 'e');
    }

    /** @test */
    public function get_next_update_historico()
    {
        // historico RT
        $dados = factory('App\PreRegistroCnpj')->create();
        $data = Carbon::createFromFormat('Y-m-d H:i:s', json_decode($dados->historico_rt, true)['update']);
        $data = formataData($data->addDays(PreRegistroCnpj::TOTAL_HIST_DIAS_UPDATE));

        $this->assertEquals($data, $dados->getNextUpdateHistorico());

        // historico Socio
        $dados = factory('App\PreRegistroCnpj')->create();
        $data = Carbon::createFromFormat('Y-m-d H:i:s', json_decode($dados->historico_socio, true)['update']);
        $data = formataData($data->addDays(PreRegistroCnpj::TOTAL_HIST_DIAS_UPDATE_SOCIO));

        $this->assertEquals($data, $dados->getNextUpdateHistorico(PreRegistroCnpj::RELACAO_SOCIO));

        $this->expectException(\Exception::class);
        $dados->getNextUpdateHistorico(2);
    }

    /** @test */
    public function get_endereco()
    {
        $dados = factory('App\PreRegistroCnpj')->create();

        $this->assertEquals([
            'cep' => $dados->cep, 
            'logradouro' => $dados->logradouro, 
            'numero' => $dados->numero, 
            'complemento' => $dados->complemento, 
            'bairro' => $dados->bairro, 
            'cidade' => $dados->cidade, 
            'uf' => $dados->uf, 
        ], $dados->getEndereco());
    }

    /** @test */
    public function mesmo_endereco()
    {
        $dados = factory('App\PreRegistroCnpj')->create();

        $this->assertEquals(false, $dados->mesmoEndereco());

        $dados = factory('App\PreRegistroCnpj')->create();
        $dados->update([
            'cep' => $dados->preRegistro->cep, 
            'logradouro' => $dados->preRegistro->logradouro, 
            'numero' => $dados->preRegistro->numero, 
            'bairro' => $dados->preRegistro->bairro, 
            'cidade' => $dados->preRegistro->cidade, 
            'uf' => $dados->preRegistro->uf, 
        ]);

        $this->assertEquals(true, $dados->mesmoEndereco());
    }

    /** @test */
    public function atualizar_final()
    {
        $pr = factory('App\PreRegistroCnpj')->create();
        $novo = factory('App\PreRegistroCnpj')->raw();

        foreach(PreRegistroCnpj::camposPreRegistro() as $dado)
        {
            $this->assertEquals(null, PreRegistroCnpj::first()->atualizarFinal($dado, $novo[$dado]));
            $this->assertEquals($novo[$dado], PreRegistroCnpj::first()[$dado]);
        }

        $this->assertEquals(null, PreRegistroCnpj::first()->atualizarFinal('checkEndEmpresa', 'on'));
        $this->assertEquals(PreRegistroCnpj::first()->preRegistro->logradouro, PreRegistroCnpj::first()->logradouro);
        $this->assertEquals(PreRegistroCnpj::first()->preRegistro->numero, PreRegistroCnpj::first()->numero);

        $pr = factory('App\PreRegistroCnpj')->create();

        $this->assertEquals(null, PreRegistroCnpj::find(2)->atualizarFinal('checkEndEmpresa', 'onn'));
        $this->assertNotEquals(PreRegistroCnpj::find(2)->preRegistro->logradouro, PreRegistroCnpj::find(2)->logradouro);
        $this->assertNotEquals(PreRegistroCnpj::find(2)->preRegistro->numero, PreRegistroCnpj::find(2)->numero);
    }

    /** @test */
    public function soft_delete()
    {
        $user = factory('App\PreRegistroCnpj')->create();

        $this->assertEquals(1, PreRegistroCnpj::count());
        $this->assertDatabaseHas('pre_registros_cnpj', ['id' => 1, 'deleted_at' => null]);

        $user->delete();

        $this->assertEquals(0, PreRegistroCnpj::count());
        $this->assertDatabaseMissing('pre_registros_cnpj', ['id' => 1, 'deleted_at' => null]);

        PreRegistroCnpj::withTrashed()->first()->restore();

        $this->assertEquals(1, PreRegistroCnpj::count());
        $this->assertDatabaseHas('pre_registros_cnpj', ['id' => 1, 'deleted_at' => null]);
    }
}
