<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Socio;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;

class SocioTest extends TestCase
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
        $pj_um = factory('App\PreRegistroCnpj')->create();
        $pj_dois = factory('App\PreRegistroCnpj')->create();
        $pj_dois->socios()->attach(1);
        $pj_dois->socios()->attach(2);

        $this->assertEquals(2, Socio::find(1)->pessoasJuridicas->count());
        $this->assertEquals(2, Socio::find(2)->pessoasJuridicas->count());
        $this->assertEquals(1, Socio::find(3)->pessoasJuridicas->count());
        $this->assertEquals(1, Socio::find(4)->pessoasJuridicas->count());

        $pj_um->socios()->attach(3);

        $this->assertEquals(2, Socio::find(3)->pessoasJuridicas->count());
    }

    /** @test */
    public function campos_pre_registro()
    {
        $this->assertEquals([
            'cpf_cnpj',
            'registro',
            'nome',
            'nome_social',
            'dt_nascimento',
            'identidade',
            'orgao_emissor',
            'cep',
            'bairro',
            'logradouro',
            'numero',
            'complemento',
            'cidade',
            'uf',
            'nome_mae',
            'nome_pai',
            'nacionalidade',
            'naturalidade_estado',
        ], Socio::camposPreRegistro());
    }

    /** @test */
    public function socio_pf()
    {
        $dados = factory('App\PreRegistroCnpj')->states('rt_socio')->create();

        $this->assertEquals(true, $dados->socios->first()->socioPF());
        $this->assertEquals(false, $dados->socios->find(2)->socioPF());
        $this->assertEquals(true, $dados->socios->find(3)->socioPF());
    }

    /** @test */
    public function socio_rt()
    {
        $dados = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        
        $this->assertEquals(false, $dados->socios->first()->socioRT());
        $this->assertEquals(false, $dados->socios->find(2)->socioRT());
        $this->assertEquals(true, $dados->socios->find(3)->socioRT());
    }

    /** @test */
    public function tab_html()
    {
        $textos_pf = [
            '<span class="label_nome_social bold">Nome Social:</span>',
            '<span class="label_dt_nascimento bold">Data de Nascimento:</span>',
            '<span class="label_identidade bold">Identidade:</span>',
            '<span class="label_orgao_emissor bold">Órgão Emissor:</span>',
            '<span class="label_nome_mae bold">Nome da Mãe:</span>',
            '<span class="label_nome_pai bold">Nome do Pai:</span>',
            '<span class="label_nacionalidade bold">Nacionalidade:</span>',
            '<span class="label_naturalidade_estado bold">Naturalidade:</span>',
        ];

        $textos_pf_pj = [
            '<span class="label_registro bold">Registro:</span>',
            '<span class="label_nome bold">Nome:</span>',
            '<span class="label_cep bold">Cep:</span>',
            '<span class="label_logradouro bold">Logradouro:</span>',
            '<span class="label_numero bold">Número:</span>',
            '<span class="label_complemento bold">Complemento:</span>',
            '<span class="label_bairro bold">Bairro:</span>',
            '<span class="label_cidade bold">Município:</span>',
            '<span class="label_uf bold">Estado:</span>',
        ];

        $textos_rt = [
            '<span class="badge badge-warning pt-1">RT</span>',
            '<p class="text-danger mb-2"><strong><i>Dados do Responsável Técnico',
            '<button class="btn btn-link font-italic m-0 p-0" type="button" id="link-tab-rt">aba "Contato / RT"</button>',
        ];

        $dados = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $tab_pf = $dados->socios->first()->tabHTML();
        $tab_pj = $dados->socios->find(2)->tabHTML();
        $tab_rt = $dados->socios->find(3)->tabHTML();
        
        $this->assertStringContainsString('<strong>' . formataCpfCnpj($dados->socios->first()->cpf_cnpj) . '</strong>', $tab_pf);
        $this->assertStringNotContainsString('<strong>' . formataCpfCnpj($dados->socios->find(2)->cpf_cnpj) . '</strong>', $tab_pf);
        $this->assertStringNotContainsString('<strong>' . formataCpfCnpj($dados->socios->find(3)->cpf_cnpj) . '</strong>', $tab_pf);

        $this->assertStringNotContainsString('<strong>' . formataCpfCnpj($dados->socios->first()->cpf_cnpj) . '</strong>', $tab_pj);
        $this->assertStringContainsString('<strong>' . formataCpfCnpj($dados->socios->find(2)->cpf_cnpj) . '</strong>', $tab_pj);
        $this->assertStringNotContainsString('<strong>' . formataCpfCnpj($dados->socios->find(3)->cpf_cnpj) . '</strong>', $tab_pj);

        $this->assertStringNotContainsString('<strong>' . formataCpfCnpj($dados->socios->first()->cpf_cnpj) . '</strong>', $tab_rt);
        $this->assertStringNotContainsString('<strong>' . formataCpfCnpj($dados->socios->find(2)->cpf_cnpj) . '</strong>', $tab_rt);
        $this->assertStringContainsString('<strong>' . formataCpfCnpj($dados->socios->find(3)->cpf_cnpj) . '</strong>', $tab_rt);

        foreach($textos_pf as $key => $texto){
            $this->assertStringContainsString($texto, $tab_pf);
            $this->assertStringNotContainsString($texto, $tab_pj);
            in_array($key, [6, 7]) ? $this->assertStringContainsString($texto, $tab_rt) : $this->assertStringNotContainsString($texto, $tab_rt);
        }

        foreach($textos_pf_pj as $key => $texto){
            $this->assertStringContainsString($texto, $tab_pf);
            $this->assertStringContainsString($texto, $tab_pj);
            $this->assertStringNotContainsString($texto, $tab_rt);
        }

        foreach($textos_rt as $key => $texto){
            $this->assertStringNotContainsString($texto, $tab_pf);
            $this->assertStringNotContainsString($texto, $tab_pj);
            $this->assertStringContainsString($texto, $tab_rt);
        }

        $this->assertStringContainsString('<span class="label_id bold">ID:</span> <span class="id_socio editar_dado">'.$dados->socios->first()->id .'</span>', $tab_pf);
        $this->assertStringContainsString('<span class="label_id bold">ID:</span> <span class="id_socio editar_dado">'.$dados->socios->find(2)->id .'</span>', $tab_pj);
        $this->assertStringContainsString('<span class="label_id bold">ID:</span> <span class="id_socio editar_dado">'.$dados->socios->find(3)->id .'</span>', $tab_rt);
    }

    /** @test */
    public function array_validacao_inputs_pf_do_pre_registro()
    {
        $pf = factory('App\PreRegistroCnpj')->create()->socios->first();

        $this->assertEquals([
            'cpf_cnpj_socio_' . $pf->id => $pf->cpf_cnpj,
            'nome_socio_' . $pf->id => $pf->nome,
            'nome_social_socio_' . $pf->id => $pf->nome_social,
            'dt_nascimento_socio_' . $pf->id => $pf->dt_nascimento,
            'identidade_socio_' . $pf->id => $pf->identidade,
            'orgao_emissor_socio_' . $pf->id => $pf->orgao_emissor,
            'cep_socio_' . $pf->id => $pf->cep,
            'bairro_socio_' . $pf->id => $pf->bairro,
            'logradouro_socio_' . $pf->id => $pf->logradouro,
            'numero_socio_' . $pf->id => $pf->numero,
            'complemento_socio_' . $pf->id => $pf->complemento,
            'cidade_socio_' . $pf->id => $pf->cidade,
            'uf_socio_' . $pf->id => $pf->uf,
            'nome_mae_socio_' . $pf->id => $pf->nome_mae,
            'nome_pai_socio_' . $pf->id => $pf->nome_pai,
            'nacionalidade_socio_' . $pf->id => $pf->nacionalidade,
            'naturalidade_estado_socio_' . $pf->id => $pf->naturalidade_estado,
        ], $pf->arrayValidacaoInputs());
    }

    /** @test */
    public function array_validacao_inputs_pj_do_pre_registro()
    {
        $pj = factory('App\PreRegistroCnpj')->create()->socios->find(2);

        $this->assertEquals([
            'cpf_cnpj_socio_' . $pj->id => $pj->cpf_cnpj,
            'nome_socio_' . $pj->id => $pj->nome,
            'cep_socio_' . $pj->id => $pj->cep,
            'bairro_socio_' . $pj->id => $pj->bairro,
            'logradouro_socio_' . $pj->id => $pj->logradouro,
            'numero_socio_' . $pj->id => $pj->numero,
            'complemento_socio_' . $pj->id => $pj->complemento,
            'cidade_socio_' . $pj->id => $pj->cidade,
            'uf_socio_' . $pj->id => $pj->uf,
        ], $pj->arrayValidacaoInputs());
    }

    /** @test */
    public function array_validacao_inputs_rt_do_pre_registro()
    {
        $rt = factory('App\PreRegistroCnpj')->states('rt_socio')->create()->socios->find(3);

        $this->assertEquals([
            'nacionalidade_socio_' . $rt->id => $rt->nacionalidade,
            'naturalidade_estado_socio_' . $rt->id => $rt->naturalidade_estado,
        ], $rt->arrayValidacaoInputs());
    }

    /** @test */
    public function array_validacao_pf_do_pre_registro()
    {
        $pf = factory('App\PreRegistroCnpj')->create()->socios->first();

        $this->assertEquals([
            'nome_social_socio_' . $pf->id,
            'dt_nascimento_socio_' . $pf->id,
            'identidade_socio_' . $pf->id,
            'orgao_emissor_socio_' . $pf->id,
            'nome_mae_socio_' . $pf->id,
            'nome_pai_socio_' . $pf->id,
            'nacionalidade_socio_' . $pf->id,
            'naturalidade_estado_socio_' . $pf->id,
            'checkRT_socio',
            'cpf_cnpj_socio_' . $pf->id,
            'nome_socio_' . $pf->id,
            'cep_socio_' . $pf->id,
            'bairro_socio_' . $pf->id,
            'logradouro_socio_' . $pf->id,
            'numero_socio_' . $pf->id,
            'complemento_socio_' . $pf->id,
            'cidade_socio_' . $pf->id,
            'uf_socio_' . $pf->id,
        ], array_keys($pf->arrayValidacao()));
    }

    /** @test */
    public function array_validacao_pj_do_pre_registro()
    {
        $pj = factory('App\PreRegistroCnpj')->create()->socios->find(2);

        $this->assertEquals([
            'checkRT_socio',
            'cpf_cnpj_socio_' . $pj->id,
            'nome_socio_' . $pj->id,
            'cep_socio_' . $pj->id,
            'bairro_socio_' . $pj->id,
            'logradouro_socio_' . $pj->id,
            'numero_socio_' . $pj->id,
            'complemento_socio_' . $pj->id,
            'cidade_socio_' . $pj->id,
            'uf_socio_' . $pj->id,
        ], array_keys($pj->arrayValidacao()));
    }

    /** @test */
    public function array_validacao_rt_do_pre_registro()
    {
        $rt = factory('App\PreRegistroCnpj')->states('rt_socio')->create()->socios->find(3);

        $this->assertEquals([
            'nacionalidade_socio_' . $rt->id,
            'naturalidade_estado_socio_' . $rt->id,
            'checkRT_socio',
        ], array_keys($rt->arrayValidacao()));
    }

    /** @test */
    public function array_validacao_msg_pf_do_pre_registro()
    {
        $pf = factory('App\PreRegistroCnpj')->create()->socios->first();

        $this->assertEquals([
            'nome_social_socio_' . $pf->id,
            'dt_nascimento_socio_' . $pf->id,
            'identidade_socio_' . $pf->id,
            'orgao_emissor_socio_' . $pf->id,
            'nome_mae_socio_' . $pf->id,
            'nome_pai_socio_' . $pf->id,
            'nacionalidade_socio_' . $pf->id,
            'naturalidade_estado_socio_' . $pf->id,
            'cpf_cnpj_socio_' . $pf->id,
            'nome_socio_' . $pf->id,
            'cep_socio_' . $pf->id,
            'bairro_socio_' . $pf->id,
            'logradouro_socio_' . $pf->id,
            'numero_socio_' . $pf->id,
            'complemento_socio_' . $pf->id,
            'cidade_socio_' . $pf->id,
            'uf_socio_' . $pf->id,
        ], array_keys($pf->arrayValidacaoMsg()));
    }

    /** @test */
    public function array_validacao_msg_pj_do_pre_registro()
    {
        $pj = factory('App\PreRegistroCnpj')->create()->socios->find(2);

        $this->assertEquals([
            'cpf_cnpj_socio_' . $pj->id,
            'nome_socio_' . $pj->id,
            'cep_socio_' . $pj->id,
            'bairro_socio_' . $pj->id,
            'logradouro_socio_' . $pj->id,
            'numero_socio_' . $pj->id,
            'complemento_socio_' . $pj->id,
            'cidade_socio_' . $pj->id,
            'uf_socio_' . $pj->id,
        ], array_keys($pj->arrayValidacaoMsg()));
    }

    /** @test */
    public function array_validacao_msg_rt_do_pre_registro()
    {
        $rt = factory('App\PreRegistroCnpj')->states('rt_socio')->create()->socios->find(3);

        $this->assertEquals([
            'nacionalidade_socio_' . $rt->id,
            'naturalidade_estado_socio_' . $rt->id,
        ], array_keys($rt->arrayValidacaoMsg()));
    }

    /** @test */
    public function buscar()
    {
        factory('App\Socio', 2)->create();
        $nao_existe = factory('App\Socio')->raw();
        
        // Sócio existe, sem gerenti e sem verificação se pode editar
        $this->assertEquals(Socio::class, get_class(Socio::buscar(Socio::first()->cpf_cnpj, null)));
        $this->assertEquals(2, Socio::count());

        // Sócio existe, com gerenti e sem verificação se pode editar
        $this->assertEquals(Socio::class, get_class(Socio::buscar(Socio::first()->cpf_cnpj, ['registro' => '00000001', 'nome_mae' => 'TESTE MÃE RT'])));
        $this->assertDatabaseHas('socios', ['id' => 1, 'nome_mae' => 'TESTE MÃE RT', 'registro' => '00000001']);

        // Sócio existe, sem gerenti e com verificação se pode editar
        $this->assertEquals(Socio::class, get_class(Socio::buscar(Socio::first()->cpf_cnpj, null, true)));
        $this->assertEquals('notUpdate', Socio::buscar(Socio::first()->cpf_cnpj, null, false));
        $this->assertEquals(2, Socio::count());

        // Sócio não existe, então cria, sem gerenti e sem verificação se pode editar
        $this->assertEquals(Socio::class, get_class(Socio::buscar($nao_existe['cpf_cnpj'], null)));
        $this->assertEquals(3, Socio::count());

        $nao_existe = factory('App\Socio')->raw();

        // Sócio não existe, então cria, com gerenti e sem verificação se pode editar
        $this->assertEquals(Socio::class, get_class(Socio::buscar($nao_existe['cpf_cnpj'], ['cpf_cnpj' => $nao_existe['cpf_cnpj'], 'registro' => '00000002', 'nome_mae' => 'TESTE MÃE RT DOIS'])));
        $this->assertEquals(4, Socio::count());
        $this->assertDatabaseHas('socios', ['id' => 4, 'cpf_cnpj' => $nao_existe['cpf_cnpj'], 'registro' => '00000002', 'nome_mae' => 'TESTE MÃE RT DOIS']);

        $nao_existe = factory('App\Socio')->raw();

        // Sócio não existe, então cria, sem gerenti e com verificação se pode editar
        $this->assertEquals(Socio::class, get_class(Socio::buscar($nao_existe['cpf_cnpj'], null, true)));
        $this->assertEquals('notUpdate', Socio::buscar($nao_existe['cpf_cnpj'], null, false));
        $this->assertEquals(5, Socio::count());

        // sem cpf_cnpj
        $this->expectException(\Exception::class);
        Socio::buscar(null, null);
    }

    /** @test */
    public function criar_final()
    {
        factory('App\Socio', 2)->create();
        $nao_existe = factory('App\Socio')->raw();
        $pr = factory('App\PreRegistroCnpj')->create()->makeVisible(['historico_socio']);

        // com Socio que existe, sem gerenti
        $resp = Socio::criarFinal('cpf_cnpj', Socio::first()->cpf_cnpj, null, $pr->preRegistro);
        $this->assertEquals([
            'tab' => $pr->fresh()->socios->find(1)->tabHTML(),
            'rt' => false
        ], $resp);

        // com Socio que existe, com gerenti
        $resp = Socio::criarFinal('cpf_cnpj', Socio::find(2)->cpf_cnpj, ['cpf_cnpj' => Socio::find(2)->cpf_cnpj, 'registro' => '00000002', 'nome_mae' => 'TESTE MÃE RT DOIS'], $pr->preRegistro);
        $this->assertEquals([
            'tab' => $pr->fresh()->socios->find(2)->tabHTML(),
            'rt' => false
        ], $resp);
        $this->assertDatabaseHas('socios', ['id' => 2, 'cpf_cnpj' => Socio::find(2)->cpf_cnpj, 'registro' => '00000002', 'nome_mae' => 'TESTE MÃE RT DOIS']);

        // com Socio que não existe e sem gerenti
        $resp = Socio::criarFinal('cpf_cnpj', $nao_existe['cpf_cnpj'], null, $pr->preRegistro->fresh());
        $this->assertEquals([
            'tab' => $pr->fresh()->socios->find(5)->tabHTML(),
            'rt' => false
        ], $resp);

        $nao_existe = factory('App\Socio')->raw();

        // com Socio que não existe e com gerenti
        $resp = Socio::criarFinal('cpf_cnpj', $nao_existe['cpf_cnpj'], ['cpf_cnpj' => $nao_existe['cpf_cnpj'], 'registro' => '00000001', 'nome_mae' => 'TESTE MÃE RT'], $pr->preRegistro->fresh());
        $this->assertEquals([
            'tab' => $pr->fresh()->socios->find(6)->tabHTML(),
            'rt' => false
        ], $resp);
        $this->assertDatabaseHas('socios', ['id' => 6, 'cpf_cnpj' => $nao_existe['cpf_cnpj'], 'registro' => '00000001', 'nome_mae' => 'TESTE MÃE RT']);
        
        // com Socio que não existe e é RT (checkRT_socio)
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', ['rt' => 1]);
        $resp = Socio::criarFinal('checkRT_socio', 'on', null, $pr->preRegistro->fresh());
        $this->assertEquals([
            'tab' => $pr->fresh()->socios->find(7)->tabHTML(),
            'rt' => true
        ], $resp);
        $this->assertDatabaseHas('socio_pre_registro_cnpj', ['rt' => 1]);

        // não pode criar quando já está relacionado ao PreRegistro
        $this->assertEquals('existente', array_keys(Socio::criarFinal('cpf_cnpj', Socio::find(4)->cpf_cnpj, null, $pr->preRegistro))[0]);
        $this->assertEquals(7, Socio::count());

        $pr = factory('App\PreRegistroCnpj')->states('bloqueado_socio')->create();
        $nao_existe = factory('App\Socio')->raw();

        // não pode criar quando alcança o limite de tentativas
        $this->assertEquals('update', array_keys(Socio::criarFinal('cpf_cnpj', $nao_existe['cpf_cnpj'], null, $pr->preRegistro))[0]);
        $this->assertEquals(9, Socio::count());

        $pr = factory('App\PreRegistroCnpj')->states('com_limite_socios')->create();
        $nao_existe = factory('App\Socio')->raw();

        // não pode criar quando possui o limite de quantidade de sócios
        $this->assertEquals('limite', array_keys(Socio::criarFinal('cpf_cnpj', $nao_existe['cpf_cnpj'], null, $pr->preRegistro))[0]);
        $this->assertEquals($pr::TOTAL_HIST_SOCIO, $pr->socios()->count());

        $pr = factory('App\PreRegistroCnpj')->create();

        // Deve ser o campo cpf_cnpj
        $this->expectException(\Exception::class);
        Socio::criarFinal('cpf_cnpj_', null, null, $pr->preRegistro);
    }

    /** @test */
    public function atualizar_final()
    {
        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $novo = factory('App\Socio')->raw();

        // sem cpf_cnpj PF
        foreach(Socio::camposPreRegistro() as $dado)
        {
            if($dado == 'cpf_cnpj')
                continue;
            $this->assertEquals([
                'atualizado', 
                'id'
            ], array_keys($pr->socios->find(1)->atualizarFinal($dado, $novo[$dado], $pr)));
            $this->assertEquals($novo[$dado], Socio::find(1)[$dado]);
        }

        $novo = factory('App\Socio')->states('pj')->raw();

        // sem cpf_cnpj PJ
        foreach([
            'registro',
            'nome',
            'cep',
            'bairro',
            'logradouro',
            'numero',
            'complemento',
            'cidade',
            'uf',
            ] as $dado)
        {
            if($dado == 'cpf_cnpj')
                continue;
            $this->assertEquals([
                'atualizado', 
                'id'
            ], array_keys($pr->socios->find(2)->atualizarFinal($dado, $novo[$dado], $pr)));
            $this->assertEquals($novo[$dado], Socio::find(2)[$dado]);
        }

        $novo = factory('App\Socio')->states('rt')->raw();

        // sem cpf_cnpj RT
        foreach([
            'nacionalidade',
            'naturalidade_estado',
            ] as $dado)
        {
            if($dado == 'cpf_cnpj')
                continue;
            $this->assertEquals([
                'atualizado', 
                'id'
            ], array_keys($pr->socios->find(3)->atualizarFinal($dado, $novo[$dado], $pr)));
            $this->assertEquals($novo[$dado], Socio::find(3)[$dado]);
        }

        // com cpf_cnpj PJ e PF
        $this->assertEquals('remover', $pr->socios->find(2)->atualizarFinal('cpf_cnpj', '12345678901', $pr));
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', ['socio_id' => 2]);

        $this->assertEquals('remover', $pr->socios->find(1)->atualizarFinal('cpf_cnpj', null, $pr));
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', ['socio_id' => 1]);

        // com checkRT_socio RT
        $this->assertDatabaseHas('socio_pre_registro_cnpj', ['rt' => true]);
        $this->assertEquals('remover', $pr->socios->find(3)->atualizarFinal('checkRT_socio', 'off', $pr));
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', ['socio_id' => 3]);
        $this->assertDatabaseMissing('socio_pre_registro_cnpj', ['rt' => true]);
    }

    /** @test */
    public function atualizar_final_com_exception_pj()
    {
        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $novo = factory('App\Socio')->raw();

        // sem cpf_cnpj PJ
        foreach([
            'nome_social',
            'dt_nascimento',
            'identidade',
            'orgao_emissor',
            'nome_mae',
            'nome_pai',
            'nacionalidade',
            'naturalidade_estado',
            ] as $dado)
        {
            if($dado == 'cpf_cnpj')
                continue;
            $this->expectException(\Exception::class);
            $pr->socios->find(2)->atualizarFinal($dado, $novo[$dado], $pr);
        }
    }

    /** @test */
    public function atualizar_final_com_exception_rt()
    {
        $pr = factory('App\PreRegistroCnpj')->states('rt_socio')->create();
        $novo = factory('App\Socio')->raw();

        // sem cpf_cnpj PJ
        foreach([
            'registro',
            'nome',
            'nome_social',
            'dt_nascimento',
            'identidade',
            'orgao_emissor',
            'cep',
            'bairro',
            'logradouro',
            'numero',
            'complemento',
            'cidade',
            'uf',
            'nome_mae',
            'nome_pai',
            ] as $dado)
        {
            if($dado == 'cpf_cnpj')
                continue;
            $this->expectException(\Exception::class);
            $pr->socios->find(3)->atualizarFinal($dado, $novo[$dado], $pr);
        }
    }

    /** @test */
    public function soft_delete()
    {
        $user = factory('App\Socio')->create();

        $this->assertEquals(1, Socio::count());
        $this->assertDatabaseHas('socios', ['id' => 1, 'deleted_at' => null]);

        $user->delete();

        $this->assertEquals(0, Socio::count());
        $this->assertDatabaseMissing('socios', ['id' => 1, 'deleted_at' => null]);

        Socio::withTrashed()->first()->restore();

        $this->assertEquals(1, Socio::count());
        $this->assertDatabaseHas('socios', ['id' => 1, 'deleted_at' => null]);
    }
}
